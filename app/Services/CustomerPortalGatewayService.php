<?php

namespace App\Services;

use App\Models\CustomerPortalPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class CustomerPortalGatewayService
{
    public function initiate(CustomerPortalPayment $payment, string $callbackUrl): array
    {
        $provider = (string) config('services.customer_portal_gateway.provider', 'sandbox');
        $authority = $payment->authority ?: 'CPG-' . $payment->id . '-' . Str::upper(Str::random(24));
        $gatewayUrl = $this->gatewayUrl($provider, $payment, $authority, $callbackUrl);
        $metadata = $payment->metadata ?: [];
        $metadata['gateway'] = [
            'provider' => $provider,
            'callback_url' => $callbackUrl,
            'redirect_url' => $gatewayUrl,
            'merchant_id' => config('services.customer_portal_gateway.merchant_id'),
            'terminal_id' => config('services.customer_portal_gateway.terminal_id'),
            'payment_request' => $this->providerPayload($payment, $authority, $callbackUrl),
            'initiated_at' => now()->toDateTimeString(),
        ];

        $payment->update([
            'status' => 'initiated',
            'payment_method' => 'online_gateway',
            'gateway_provider' => $provider,
            'authority' => $authority,
            'metadata' => $metadata,
        ]);

        return [
            'payment' => $payment->refresh(),
            'authority' => $authority,
            'redirect_url' => $gatewayUrl,
        ];
    }

    public function verify(CustomerPortalPayment $payment, Request $request): array
    {
        $provider = (string) ($payment->gateway_provider ?: config('services.customer_portal_gateway.provider', 'sandbox'));
        $authority = (string) ($request->query('Authority') ?: $request->input('Authority') ?: $request->query('authority') ?: $request->input('authority'));
        $status = strtoupper((string) ($request->query('Status') ?: $request->input('Status') ?: $request->query('status') ?: $request->input('status')));
        $callbackReference = (string) ($request->query('RefID') ?: $request->input('RefID') ?: $request->query('ref_id') ?: $request->input('ref_id'));
        $callbackAccepted = $authority !== '' && hash_equals((string) $payment->authority, $authority) && in_array($status, ['OK', 'SUCCESS', 'PAID'], true);
        $providerVerification = $provider === 'sandbox'
            ? ['success' => $callbackAccepted, 'reference_number' => $callbackReference ?: 'SANDBOX-' . $payment->id, 'raw' => []]
            : $this->verifyWithProvider($payment, $authority, $callbackReference, $request);
        $success = $callbackAccepted && (bool) ($providerVerification['success'] ?? false);
        $referenceNumber = $success
            ? ((string) ($providerVerification['reference_number'] ?? $callbackReference) ?: 'GW-' . now()->format('YmdHis') . '-' . $payment->id)
            : null;
        $failureMessage = (string) ($providerVerification['message'] ?? 'پرداخت آنلاین ناموفق یا توسط کاربر لغو شد.');

        return [
            'success' => $success,
            'authority' => $authority,
            'reference_number' => $referenceNumber,
            'message' => $success ? 'پرداخت آنلاین تایید شد و سند تسویه در صف حسابداری قرار گرفت.' : $failureMessage,
            'raw' => [
                'callback' => $request->query() + $request->except(['_token']),
                'provider' => $providerVerification['raw'] ?? [],
            ],
        ];
    }

    private function gatewayUrl(string $provider, CustomerPortalPayment $payment, string $authority, string $callbackUrl): string
    {
        if ($provider === 'sandbox') {
            return $callbackUrl . (str_contains($callbackUrl, '?') ? '&' : '?') . http_build_query([
                'Authority' => $authority,
                'Status' => 'OK',
                'RefID' => 'SANDBOX-' . $payment->id,
            ]);
        }

        $baseUrl = trim((string) config('services.customer_portal_gateway.payment_url'));

        if ($baseUrl === '') {
            return $callbackUrl . (str_contains($callbackUrl, '?') ? '&' : '?') . http_build_query([
                'Authority' => $authority,
                'Status' => 'PENDING_PROVIDER',
            ]);
        }

        return $baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') . http_build_query([
            'amount' => (float) $payment->payable_amount,
            'authority' => $authority,
            'callback_url' => $callbackUrl,
            'description' => 'پرداخت پورتال مشتری #' . $payment->id,
            'merchant_id' => config('services.customer_portal_gateway.merchant_id'),
            'terminal_id' => config('services.customer_portal_gateway.terminal_id'),
        ]);
    }

    private function providerPayload(CustomerPortalPayment $payment, string $authority, string $callbackUrl): array
    {
        return [
            'amount' => (float) $payment->payable_amount,
            'authority' => $authority,
            'callback_url' => $callbackUrl,
            'description' => 'پرداخت پورتال مشتری #' . $payment->id,
            'merchant_id' => config('services.customer_portal_gateway.merchant_id'),
            'terminal_id' => config('services.customer_portal_gateway.terminal_id'),
            'payment_id' => $payment->id,
        ];
    }

    private function verifyWithProvider(CustomerPortalPayment $payment, string $authority, ?string $referenceNumber, Request $request): array
    {
        $verificationUrl = trim((string) config('services.customer_portal_gateway.verification_url'));

        if ($verificationUrl === '') {
            return [
                'success' => false,
                'reference_number' => null,
                'message' => 'تنظیمات تایید provider بانکی کامل نیست و پرداخت live تایید نشد.',
                'raw' => ['configuration_missing' => true],
            ];
        }

        $payload = [
            'amount' => (float) $payment->payable_amount,
            'authority' => $authority,
            'ref_id' => $referenceNumber,
            'merchant_id' => config('services.customer_portal_gateway.merchant_id'),
            'terminal_id' => config('services.customer_portal_gateway.terminal_id'),
            'payment_id' => $payment->id,
            'callback_status' => $request->query('Status') ?: $request->input('Status') ?: $request->query('status') ?: $request->input('status'),
        ];

        try {
            $response = Http::timeout((int) config('services.customer_portal_gateway.timeout', 15))
                ->acceptJson()
                ->asJson()
                ->post($verificationUrl, $payload);
            $body = $response->json() ?: ['body' => $response->body()];
            $status = strtoupper((string) data_get($body, 'status', data_get($body, 'Status', '')));
            $code = (string) data_get($body, 'code', data_get($body, 'status_code', ''));
            $providerSuccess = $response->successful() && (
                data_get($body, 'success') === true
                || data_get($body, 'verified') === true
                || in_array($status, ['OK', 'SUCCESS', 'PAID', 'VERIFIED'], true)
                || in_array($code, ['0', '00', '100'], true)
            );

            return [
                'success' => $providerSuccess,
                'reference_number' => data_get($body, 'reference_number')
                    ?: data_get($body, 'ref_id')
                    ?: data_get($body, 'RefID')
                    ?: data_get($body, 'tracking_code')
                    ?: data_get($body, 'transaction_id')
                    ?: $referenceNumber,
                'message' => $providerSuccess ? 'تایید provider بانکی موفق بود.' : 'provider بانکی پرداخت را تایید نکرد.',
                'raw' => ['request' => $payload, 'response' => $body, 'http_status' => $response->status()],
            ];
        } catch (Throwable $exception) {
            return [
                'success' => false,
                'reference_number' => null,
                'message' => 'ارتباط با provider بانکی برای تایید پرداخت ناموفق بود.',
                'raw' => ['request' => $payload, 'error' => $exception->getMessage()],
            ];
        }
    }
}

<?php

namespace App\Services;

use App\Models\CompanyAssetDisposal;
use App\Models\CompanyAssetTaxInvoice;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FixedAssetTaxInvoiceService
{
    public function __construct(private NumberingService $numberingService) {}

    public function prepare(CompanyAssetDisposal $disposal, array $payload, $user): CompanyAssetTaxInvoice
    {
        $disposal->loadMissing('asset', 'voucher', 'taxInvoice');

        if ($disposal->disposal_type !== 'sale') {
            throw ValidationException::withMessages(['company_asset_disposal_id' => 'فقط فروش دارایی ثابت برای صورت حساب مودیان قابل ثبت است.']);
        }

        if ((float) $disposal->proceeds_amount <= 0) {
            throw ValidationException::withMessages(['proceeds_amount' => 'برای ساخت صورت حساب مودیان، مبلغ فروش دارایی باید بزرگتر از صفر باشد.']);
        }

        $existing = $disposal->taxInvoice;

        if ($existing && in_array($existing->status, ['accepted', 'sent'], true)) {
            throw ValidationException::withMessages(['status' => 'صورت حساب ارسال شده یا تایید شده را از این فرم بازنویسی نکنید؛ فقط وضعیت پیگیری را بروزرسانی کنید.']);
        }

        $asset = $disposal->asset;
        $tenantId = $disposal->tenant_id ?: $asset?->tenant_id ?: $this->tenantId($user);
        $organizationId = $disposal->organization_id ?: $asset?->organization_id ?: $this->organizationId($user);
        $issueDate = Arr::get($payload, 'issue_date_en') ?: optional($disposal->disposal_date_en)->format('Y-m-d') ?: now()->toDateString();
        $saleAmount = round((float) $disposal->proceeds_amount, 2);
        $taxRate = round(max(0, (float) Arr::get($payload, 'tax_rate', 0)), 4);
        $taxAmount = round($saleAmount * $taxRate / 100, 2);
        $invoiceNumber = Arr::get($payload, 'invoice_number') ?: $existing?->invoice_number ?: $this->numberingService->nextVoucherNumber($tenantId, $issueDate, 'FAT');
        $attributes = [
            'company_asset_id' => $asset?->id ?: $disposal->company_asset_id,
            'voucher_id' => $disposal->voucher_id,
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'invoice_number' => $invoiceNumber,
            'tax_id' => Arr::get($payload, 'tax_id') ?: $existing?->tax_id,
            'reference_number' => Arr::get($payload, 'reference_number') ?: $existing?->reference_number,
            'issue_date_en' => $issueDate,
            'issue_date_fa' => $this->jalaliDate($issueDate),
            'status' => 'draft',
            'sale_amount' => $saleAmount,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_amount' => round($saleAmount + $taxAmount, 2),
            'buyer_name' => Arr::get($payload, 'buyer_name') ?: $disposal->buyer_name,
            'buyer_economic_number' => Arr::get($payload, 'buyer_economic_number'),
            'buyer_national_id' => Arr::get($payload, 'buyer_national_id'),
            'buyer_postal_code' => Arr::get($payload, 'buyer_postal_code'),
            'buyer_address' => Arr::get($payload, 'buyer_address'),
            'asset_code' => $asset?->asset_code,
            'asset_name' => $asset?->name,
            'error_message' => null,
            'updated_by' => $user?->id,
        ];
        $attributes['payload_json'] = $this->payload($disposal, $attributes);

        return DB::transaction(function () use ($disposal, $existing, $attributes, $user) {
            if ($existing) {
                $existing->update($attributes);

                return $existing->refresh()->load('asset', 'disposal', 'voucher');
            }

            $attributes['company_asset_disposal_id'] = $disposal->id;
            $attributes['created_by'] = $user?->id;

            return CompanyAssetTaxInvoice::create($attributes)->load('asset', 'disposal', 'voucher');
        });
    }

    public function updateStatus(CompanyAssetTaxInvoice $invoice, array $payload, $user): CompanyAssetTaxInvoice
    {
        $status = Arr::get($payload, 'status');

        if (!in_array($status, ['sent', 'failed', 'accepted', 'rejected'], true)) {
            throw ValidationException::withMessages(['status' => 'وضعیت ارسال صورت حساب معتبر نیست.']);
        }

        $updates = [
            'status' => $status,
            'tax_id' => Arr::get($payload, 'tax_id') ?: $invoice->tax_id,
            'reference_number' => Arr::get($payload, 'reference_number') ?: $invoice->reference_number,
            'error_message' => in_array($status, ['failed', 'rejected'], true) ? Arr::get($payload, 'error_message') : null,
            'response_json' => $this->responsePayload($payload),
            'updated_by' => $user?->id,
        ];

        if ($status === 'sent') {
            $updates['sent_at'] = now();
            $updates['retry_count'] = (int) $invoice->retry_count + 1;
        }

        if ($status === 'accepted') {
            $updates['accepted_at'] = now();
        }

        $invoice->update($updates);

        return $invoice->refresh()->load('asset', 'disposal', 'voucher');
    }

    private function payload(CompanyAssetDisposal $disposal, array $attributes): array
    {
        return [
            'invoice_number' => $attributes['invoice_number'],
            'issue_date' => $attributes['issue_date_en'],
            'subject' => 'fixed_asset_sale',
            'seller' => [
                'tenant_id' => $attributes['tenant_id'],
                'organization_id' => $attributes['organization_id'],
            ],
            'buyer' => [
                'name' => $attributes['buyer_name'],
                'economic_number' => $attributes['buyer_economic_number'],
                'national_id' => $attributes['buyer_national_id'],
                'postal_code' => $attributes['buyer_postal_code'],
                'address' => $attributes['buyer_address'],
            ],
            'asset' => [
                'id' => $attributes['company_asset_id'],
                'code' => $attributes['asset_code'],
                'name' => $attributes['asset_name'],
                'book_value' => round((float) $disposal->book_value, 2),
                'gain_amount' => round((float) $disposal->gain_amount, 2),
                'loss_amount' => round((float) $disposal->loss_amount, 2),
            ],
            'amounts' => [
                'sale_amount' => $attributes['sale_amount'],
                'tax_rate' => $attributes['tax_rate'],
                'tax_amount' => $attributes['tax_amount'],
                'total_amount' => $attributes['total_amount'],
            ],
            'trace' => [
                'company_asset_disposal_id' => $disposal->id,
                'voucher_id' => $disposal->voucher_id,
            ],
        ];
    }

    private function responsePayload(array $payload): array
    {
        return [
            'tax_id' => Arr::get($payload, 'tax_id'),
            'reference_number' => Arr::get($payload, 'reference_number'),
            'message' => Arr::get($payload, 'error_message'),
            'recorded_at' => now()->toDateTimeString(),
        ];
    }

    private function tenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }

    private function organizationId($user): ?int
    {
        $organizationId = $user?->organization_id;
        $decoded = is_string($organizationId) ? json_decode($organizationId, true) : null;

        if (is_array($decoded)) {
            return isset($decoded[0]) ? (int) $decoded[0] : null;
        }

        return $organizationId ? (int) $organizationId : null;
    }

    private function jalaliDate(string $date): string
    {
        try {
            return verta($date)->format('Y/m/d');
        } catch (\Throwable $exception) {
            return $date;
        }
    }
}

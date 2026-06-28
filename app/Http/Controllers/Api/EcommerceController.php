<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EcommerceChannel;
use App\Models\EcommerceOrderMapping;
use App\Services\EcommerceIntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EcommerceController extends Controller
{
    public function products(string $channelCode, Request $request, EcommerceIntegrationService $service): JsonResponse
    {
        $channel = $this->resolveChannel($channelCode, $request);

        return response()->json([
            'data' => $service->exportProducts($channel, $request->only(['updated_after', 'limit'])),
        ]);
    }

    public function storeOrder(string $channelCode, Request $request, EcommerceIntegrationService $service): JsonResponse
    {
        $channel = $this->resolveChannel($channelCode, $request);
        $payload = $request->validate([
            'external_order_id' => ['required', 'string', 'max:120'],
            'external_order_number' => ['nullable', 'string', 'max:120'],
            'ordered_at' => ['nullable', 'date'],
            'order_status' => ['nullable', 'string', 'max:40'],
            'payment_status' => ['nullable', 'string', 'max:40'],
            'delivery_status' => ['nullable', 'string', 'max:40'],
            'payment_method' => ['nullable', 'integer', 'in:1,2,3,4'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'shipping_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'net_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'customer' => ['required', 'array'],
            'customer.external_customer_id' => ['nullable', 'string', 'max:120'],
            'customer.name' => ['required', 'string', 'max:191'],
            'customer.mobile' => ['nullable', 'string', 'max:80'],
            'customer.phone' => ['nullable', 'string', 'max:80'],
            'customer.address' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.external_product_id' => ['nullable', 'string', 'max:120'],
            'items.*.external_variant_id' => ['nullable', 'string', 'max:120'],
            'items.*.sku' => ['nullable', 'string', 'max:120'],
            'items.*.local_product_id' => ['nullable', 'integer'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
        ]);
        $mapping = $service->ingestOrder($channel, $payload);

        return response()->json([
            'data' => [
                'external_order_id' => $mapping->external_order_id,
                'sync_status' => $mapping->sync_status,
                'pishfactor_id' => $mapping->pishfactor_id,
                'invoiceID' => optional($mapping->pishfactor)->invoiceID,
            ],
        ], $mapping->wasRecentlyCreated ? 201 : 200);
    }

    public function updateOrderStatus(string $channelCode, string $externalOrderId, Request $request): JsonResponse
    {
        $channel = $this->resolveChannel($channelCode, $request);
        $mapping = EcommerceOrderMapping::where('ecommerce_channel_id', $channel->id)
            ->where('external_order_id', $externalOrderId)
            ->firstOrFail();
        $payload = $request->validate([
            'order_status' => ['nullable', 'string', 'max:40'],
            'payment_status' => ['nullable', 'string', 'max:40'],
            'delivery_status' => ['nullable', 'string', 'max:40'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);
        $mapping->update(array_filter([
            'order_status' => $payload['order_status'] ?? null,
            'payment_status' => $payload['payment_status'] ?? null,
            'delivery_status' => $payload['delivery_status'] ?? null,
            'response_json' => ['message' => $payload['message'] ?? 'status received', 'recorded_at' => now()->toDateTimeString()],
        ], fn($value) => $value !== null));

        return response()->json(['data' => ['external_order_id' => $mapping->external_order_id, 'status' => 'processed']]);
    }

    private function resolveChannel(string $channelCode, Request $request): EcommerceChannel
    {
        $channel = EcommerceChannel::where('code', $channelCode)->where('is_active', true)->firstOrFail();
        $token = $request->bearerToken() ?: $request->header('X-Ecommerce-Token') ?: $request->query('token');

        if (!$channel->api_token_hash || !$token || !hash_equals($channel->api_token_hash, hash('sha256', $token))) {
            abort(response()->json(['message' => 'Invalid ecommerce channel token.'], 401));
        }

        return $channel;
    }
}

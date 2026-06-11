<?php

namespace App\Services;

use App\Models\Customers;
use App\Models\EcommerceChannel;
use App\Models\EcommerceOrderMapping;
use App\Models\EcommerceProductMapping;
use App\Models\EcommerceSyncLog;
use App\Models\Pishfactor;
use App\Models\PishFactorItems;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EcommerceIntegrationService
{
    public function saveChannel(array $payload, $user): EcommerceChannel
    {
        $tenantId = $this->tenantId($user);
        $organizationId = $this->organizationId($user);
        $channel = null;

        if (!empty($payload['id'])) {
            $channel = EcommerceChannel::query()
                ->when($tenantId, fn($query) => $query->where('tenant_id', $tenantId))
                ->findOrFail((int) $payload['id']);
        }

        $attributes = [
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'code' => Arr::get($payload, 'code') ?: 'online-' . now()->format('His'),
            'title' => Arr::get($payload, 'title') ?: 'فروشگاه اینترنتی',
            'platform' => Arr::get($payload, 'platform', 'custom'),
            'base_url' => Arr::get($payload, 'base_url'),
            'price_policy' => Arr::get($payload, 'price_policy', 'consumer'),
            'default_store_id' => Arr::get($payload, 'default_store_id'),
            'default_visitor_id' => Arr::get($payload, 'default_visitor_id') ?: $user?->id,
            'default_leader_id' => Arr::get($payload, 'default_leader_id') ?: $user?->id,
            'default_payment_method' => (int) Arr::get($payload, 'default_payment_method', 3),
            'order_status_policy' => Arr::get($payload, 'order_status_policy', 'draft'),
            'auto_create_customer' => (bool) Arr::get($payload, 'auto_create_customer', true),
            'auto_reserve_inventory' => (bool) Arr::get($payload, 'auto_reserve_inventory', false),
            'is_active' => (bool) Arr::get($payload, 'is_active', true),
            'settings_json' => Arr::get($payload, 'settings_json'),
            'updated_by' => $user?->id,
        ];

        if (!empty($payload['api_token'])) {
            $attributes['api_token_hash'] = hash('sha256', $payload['api_token']);
        }

        if ($channel) {
            $channel->update($attributes);

            return $channel->refresh();
        }

        $attributes['created_by'] = $user?->id;

        return EcommerceChannel::create($attributes);
    }

    public function saveProductMapping(array $payload, $user): EcommerceProductMapping
    {
        $channel = EcommerceChannel::findOrFail((int) Arr::get($payload, 'ecommerce_channel_id'));
        $this->authorizeChannel($channel, $user);
        $product = Product::findOrFail((int) Arr::get($payload, 'product_id'));

        return EcommerceProductMapping::updateOrCreate(
            [
                'ecommerce_channel_id' => $channel->id,
                'external_product_id' => Arr::get($payload, 'external_product_id'),
                'external_variant_id' => Arr::get($payload, 'external_variant_id'),
            ],
            [
                'tenant_id' => $channel->tenant_id,
                'organization_id' => $channel->organization_id,
                'product_id' => $product->id,
                'external_sku' => Arr::get($payload, 'external_sku') ?: $product->sku,
                'sync_direction' => Arr::get($payload, 'sync_direction', 'both'),
                'price_override' => Arr::get($payload, 'price_override'),
                'stock_buffer' => $this->money(Arr::get($payload, 'stock_buffer', 0)),
                'sync_price' => (bool) Arr::get($payload, 'sync_price', true),
                'sync_stock' => (bool) Arr::get($payload, 'sync_stock', true),
                'is_active' => (bool) Arr::get($payload, 'is_active', true),
            ]
        );
    }

    public function exportProducts(EcommerceChannel $channel, array $filters = [], $user = null): array
    {
        if ($user) {
            $this->authorizeChannel($channel, $user);
        }

        $products = Product::query()
            ->where('isActive', 1)
            ->when($channel->tenant_id, fn($query) => $query->where('tenant_id', $channel->tenant_id))
            ->when($channel->organization_id, fn($query) => $query->where(function ($query) use ($channel) {
                $query->where('organization_id', $channel->organization_id)->orWhereNull('organization_id');
            }))
            ->when(Arr::get($filters, 'updated_after'), fn($query, $date) => $query->where('updated_at', '>=', $date))
            ->with('baseUnit')
            ->latest('id')
            ->limit((int) Arr::get($filters, 'limit', 100))
            ->get();

        $mappings = EcommerceProductMapping::query()
            ->where('ecommerce_channel_id', $channel->id)
            ->whereIn('product_id', $products->pluck('id'))
            ->get()
            ->keyBy('product_id');

        $data = $products->map(function (Product $product) use ($channel, $mappings) {
            $mapping = $mappings->get($product->id);
            $stock = max(0, (float) $product->currentStock($channel->default_store_id) - (float) ($mapping?->stock_buffer ?: 0));
            $price = $mapping?->price_override !== null ? (float) $mapping->price_override : $this->productPrice($product, $channel->price_policy);

            return [
                'local_product_id' => $product->id,
                'external_product_id' => $mapping?->external_product_id,
                'external_variant_id' => $mapping?->external_variant_id,
                'sku' => $product->sku,
                'title' => $product->display_name ?: $product->title,
                'product_type' => $product->product_type ?: 'goods',
                'unit' => $product->baseUnit?->title,
                'price' => round($price, 2),
                'discount' => round((float) $product->discount, 2),
                'tax_rate' => round((float) $product->tax, 4),
                'stock' => round($stock, 4),
                'is_active' => (bool) $product->isActive,
                'sync_price' => (bool) ($mapping?->sync_price ?? true),
                'sync_stock' => (bool) ($mapping?->sync_stock ?? true),
                'updated_at' => optional($product->updated_at)->toDateTimeString(),
            ];
        })->values()->all();

        if (!Arr::get($filters, '_preview')) {
            $this->log($channel, 'outbound', 'product', null, null, 'export', 'processed', $filters, ['count' => count($data)], $user);
            $channel->update(['last_sync_at' => now()]);
        }

        return $data;
    }

    public function ingestOrder(EcommerceChannel $channel, array $payload, $user = null): EcommerceOrderMapping
    {
        if ($user) {
            $this->authorizeChannel($channel, $user);
        }

        if (!$channel->is_active) {
            throw ValidationException::withMessages(['channel' => 'کانال فروشگاه اینترنتی غیرفعال است.']);
        }

        $externalOrderId = (string) Arr::get($payload, 'external_order_id');

        if ($externalOrderId === '') {
            throw ValidationException::withMessages(['external_order_id' => 'شناسه سفارش خارجی الزامی است.']);
        }

        $existing = EcommerceOrderMapping::with('pishfactor.items')
            ->where('ecommerce_channel_id', $channel->id)
            ->where('external_order_id', $externalOrderId)
            ->first();

        if ($existing && $existing->pishfactor_id) {
            $this->log($channel, 'inbound', 'order', $externalOrderId, $existing->id, 'duplicate', 'processed', $payload, ['pishfactor_id' => $existing->pishfactor_id], $user);

            return $existing;
        }

        return DB::transaction(function () use ($channel, $payload, $user, $externalOrderId, $existing) {
            $customer = $this->customerForOrder($channel, Arr::get($payload, 'customer', []), $user);
            $rows = $this->normalizeOrderRows($channel, Arr::get($payload, 'items', []));
            $totals = $this->orderTotals($rows, $payload);
            $operatorId = $this->operatorId($channel, $user);
            $date = Arr::get($payload, 'ordered_at') ?: now()->toDateTimeString();

            $factor = Pishfactor::create([
                'customer_id' => $customer->id,
                'visitor_id' => $channel->default_visitor_id ?: $operatorId,
                'sarparast_id' => $channel->default_leader_id ?: $operatorId,
                'updated_by' => $operatorId,
                'payment_type' => (int) Arr::get($payload, 'payment_method', $channel->default_payment_method ?: 3),
                'recive_date' => $this->jalaliDate($date),
                'recive_date_en' => $date,
                'invoiceID' => $this->nextInvoiceId(),
                'mobile_order_uid' => 'EC-' . $channel->id . '-' . $externalOrderId,
                'distribution_order_type' => 'online_order',
                'sale_mode' => 'online',
                'promotion_discount_amount' => $totals['discount_amount'],
                'offline_created_at' => $date,
                'sync_status' => 'synced',
                'sales_document_type' => Arr::get($payload, 'document_type', 'sales_order'),
                'sales_status' => $channel->order_status_policy === 'approved' ? 'approved' : 'draft',
                'approval_status' => $channel->order_status_policy === 'approved' ? 'approved' : 'draft',
                'delivery_status' => Arr::get($payload, 'delivery_status', 'pending'),
                'settlement_status' => Arr::get($payload, 'payment_status') === 'paid' ? 'settled' : 'unsettled',
                'reserve_status' => $channel->auto_reserve_inventory ? 'reserved' : 'not_reserved',
                'warehouse_issue_status' => 'pending',
                'status' => $channel->order_status_policy === 'approved' ? 1 : 0,
                'step' => 0,
                'pat_price' => (string) $totals['gross_amount'],
                'fullPrice' => (string) $totals['net_amount'],
                'tozihat' => Arr::get($payload, 'note') ?: 'سفارش اینترنتی ' . (Arr::get($payload, 'external_order_number') ?: $externalOrderId),
                'organization_id' => $channel->organization_id,
                'tenant_id' => $channel->tenant_id,
                'tenants_id' => $channel->tenant_id,
                'area_id' => $customer->area ?: 0,
                'region_id' => $customer->region_id ?: 0,
            ]);

            foreach ($rows as $row) {
                PishFactorItems::create([
                    'pishfactor_id' => $factor->id,
                    'tenant_id' => $channel->tenant_id,
                    'pr_id' => $row['product_id'],
                    'unit_id' => $row['unit_id'],
                    'pack' => 0,
                    'tedad' => $row['quantity'],
                    'price' => $row['price'],
                    'discount' => 0,
                    'discount_amount' => $row['discount_amount'],
                    'tax_amount' => $row['tax_amount'],
                    'line_total' => $row['line_total'],
                    'reserved_quantity' => $channel->auto_reserve_inventory ? $row['quantity'] : 0,
                ]);
            }

            $attributes = [
                'ecommerce_channel_id' => $channel->id,
                'tenant_id' => $channel->tenant_id,
                'organization_id' => $channel->organization_id,
                'pishfactor_id' => $factor->id,
                'customer_id' => $customer->id,
                'external_order_id' => $externalOrderId,
                'external_order_number' => Arr::get($payload, 'external_order_number'),
                'external_customer_id' => Arr::get($payload, 'customer.external_customer_id'),
                'order_status' => Arr::get($payload, 'order_status', 'received'),
                'payment_status' => Arr::get($payload, 'payment_status', 'unknown'),
                'delivery_status' => Arr::get($payload, 'delivery_status', 'pending'),
                'gross_amount' => $totals['gross_amount'],
                'discount_amount' => $totals['discount_amount'],
                'shipping_amount' => $totals['shipping_amount'],
                'tax_amount' => $totals['tax_amount'],
                'net_amount' => $totals['net_amount'],
                'payload_json' => $payload,
                'response_json' => ['pishfactor_id' => $factor->id, 'invoiceID' => $factor->invoiceID],
                'sync_status' => 'processed',
                'attempts' => (int) ($existing?->attempts ?: 0) + 1,
                'conflict_reason' => null,
                'received_at' => now(),
                'processed_at' => now(),
            ];

            $mapping = $existing ?: new EcommerceOrderMapping();
            $mapping->fill($attributes)->save();

            $this->log($channel, 'inbound', 'order', $externalOrderId, $mapping->id, 'import', 'processed', $payload, $attributes['response_json'], $user);
            $channel->update(['last_sync_at' => now()]);

            return $mapping->refresh()->load(['pishfactor.items.product', 'customer', 'channel']);
        });
    }

    public function updateOrderStatus(EcommerceOrderMapping $mapping, array $payload, $user): EcommerceOrderMapping
    {
        $this->authorizeChannel($mapping->channel, $user);

        $mapping->update([
            'order_status' => Arr::get($payload, 'order_status', $mapping->order_status),
            'payment_status' => Arr::get($payload, 'payment_status', $mapping->payment_status),
            'delivery_status' => Arr::get($payload, 'delivery_status', $mapping->delivery_status),
            'response_json' => array_filter([
                'external_ack' => Arr::get($payload, 'external_ack'),
                'message' => Arr::get($payload, 'message'),
                'recorded_at' => now()->toDateTimeString(),
            ]),
        ]);

        if ($mapping->pishfactor) {
            $mapping->pishfactor->update([
                'settlement_status' => $mapping->payment_status === 'paid' ? 'settled' : $mapping->pishfactor->settlement_status,
                'delivery_status' => $mapping->delivery_status,
            ]);
        }

        $this->log($mapping->channel, 'outbound', 'order', $mapping->external_order_id, $mapping->id, 'status_update', 'processed', $payload, $mapping->response_json, $user);

        return $mapping->refresh();
    }

    private function normalizeOrderRows(EcommerceChannel $channel, array $items): array
    {
        if (empty($items)) {
            throw ValidationException::withMessages(['items' => 'حداقل یک ردیف سفارش اینترنتی لازم است.']);
        }

        return collect($items)->map(function ($item) use ($channel) {
            $product = $this->productForExternalItem($channel, $item);
            $quantity = max(0.0001, (float) Arr::get($item, 'quantity', 1));
            $price = $this->money(Arr::get($item, 'price', $this->productPrice($product, $channel->price_policy)));
            $gross = $this->money($quantity * $price);
            $discount = $this->money(Arr::get($item, 'discount_amount', 0));
            $taxRate = (float) Arr::get($item, 'tax_rate', $product->tax ?: 0);
            $tax = Arr::has($item, 'tax_amount') ? $this->money(Arr::get($item, 'tax_amount')) : $this->money(max(0, $gross - $discount) * $taxRate / 100);

            return [
                'product_id' => $product->id,
                'unit_id' => $product->base_unit_id,
                'quantity' => $quantity,
                'price' => $price,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'line_total' => $this->money($gross - $discount + $tax),
            ];
        })->all();
    }

    private function productForExternalItem(EcommerceChannel $channel, array $item): Product
    {
        $mapping = EcommerceProductMapping::query()
            ->where('ecommerce_channel_id', $channel->id)
            ->where('is_active', true)
            ->where(function ($query) use ($item) {
                $query->where('external_product_id', Arr::get($item, 'external_product_id'));

                if (Arr::get($item, 'external_variant_id')) {
                    $query->where('external_variant_id', Arr::get($item, 'external_variant_id'));
                }
            })
            ->first();

        if (!$mapping && Arr::get($item, 'sku')) {
            $mapping = EcommerceProductMapping::query()
                ->where('ecommerce_channel_id', $channel->id)
                ->where('external_sku', Arr::get($item, 'sku'))
                ->where('is_active', true)
                ->first();
        }

        if ($mapping?->product) {
            return $mapping->product;
        }

        $product = Product::query()
            ->when(Arr::get($item, 'local_product_id'), fn($query, $id) => $query->where('id', $id))
            ->when(!Arr::get($item, 'local_product_id') && Arr::get($item, 'sku'), fn($query, $sku) => $query->where('sku', $sku))
            ->first();

        if (!$product) {
            throw ValidationException::withMessages(['items' => 'کالای سفارش اینترنتی در نگاشت یا کاتالوگ داخلی پیدا نشد: ' . (Arr::get($item, 'sku') ?: Arr::get($item, 'external_product_id'))]);
        }

        return $product;
    }

    private function customerForOrder(EcommerceChannel $channel, array $payload, $user): Customers
    {
        $mobile = Arr::get($payload, 'mobile') ?: Arr::get($payload, 'phone');
        $externalCustomerId = Arr::get($payload, 'external_customer_id');
        $code = $externalCustomerId ? 'EC-' . $channel->id . '-' . $externalCustomerId : null;
        $customer = Customers::query()
            ->when($mobile, fn($query) => $query->where('mobile', $mobile))
            ->when(!$mobile && $code, fn($query) => $query->where('customer_code', $code))
            ->first();

        if ($customer) {
            return $customer;
        }

        if (!$channel->auto_create_customer) {
            throw ValidationException::withMessages(['customer' => 'مشتری سفارش اینترنتی پیدا نشد و ساخت خودکار مشتری غیرفعال است.']);
        }

        return Customers::create([
            'name' => Arr::get($payload, 'name') ?: 'مشتری فروشگاه اینترنتی',
            'national_id' => Arr::get($payload, 'national_id', ''),
            'economic_number' => Arr::get($payload, 'economic_number', ''),
            'phone' => Arr::get($payload, 'phone', ''),
            'mobile' => $mobile,
            'tablo' => Arr::get($payload, 'name') ?: 'فروشگاه اینترنتی',
            'customer_code' => $code ?: 'EC-' . $channel->id . '-' . now()->format('His'),
            'status' => 1,
            'area' => (int) Arr::get($payload, 'area_id', 0),
            'region_id' => (int) Arr::get($payload, 'region_id', 0),
            'address' => Arr::get($payload, 'address'),
            'store_address' => Arr::get($payload, 'address'),
            'created_by' => $user?->id ?: $channel->created_by,
            'tenant_id' => $channel->tenant_id,
            'organization_id' => $channel->organization_id,
        ]);
    }

    private function orderTotals(array $rows, array $payload): array
    {
        $gross = $this->money(array_sum(array_map(fn($row) => $row['quantity'] * $row['price'], $rows)));
        $discount = $this->money(Arr::get($payload, 'discount_amount', array_sum(array_column($rows, 'discount_amount'))));
        $shipping = $this->money(Arr::get($payload, 'shipping_amount', 0));
        $tax = $this->money(Arr::get($payload, 'tax_amount', array_sum(array_column($rows, 'tax_amount'))));
        $net = Arr::has($payload, 'net_amount') ? $this->money(Arr::get($payload, 'net_amount')) : $this->money($gross - $discount + $shipping + $tax);

        return compact('gross', 'discount', 'shipping', 'tax') + [
            'gross_amount' => $gross,
            'discount_amount' => $discount,
            'shipping_amount' => $shipping,
            'tax_amount' => $tax,
            'net_amount' => $net,
        ];
    }

    private function productPrice(Product $product, string $policy): float
    {
        return (float) match ($policy) {
            'wholesale' => $product->wholesale_price ?: $product->price,
            'representative' => $product->representative_price ?: $product->price,
            'purchase' => $product->purchase_price ?: $product->price,
            default => $product->consumer_price ?: $product->price,
        };
    }

    private function log(EcommerceChannel $channel, string $direction, string $entityType, ?string $entityKey, ?int $entityId, string $action, string $status, array $request, array $response, $user = null): EcommerceSyncLog
    {
        return EcommerceSyncLog::create([
            'ecommerce_channel_id' => $channel->id,
            'tenant_id' => $channel->tenant_id,
            'organization_id' => $channel->organization_id,
            'direction' => $direction,
            'entity_type' => $entityType,
            'entity_key' => $entityKey,
            'entity_id' => $entityId,
            'action' => $action,
            'status' => $status,
            'attempts' => 1,
            'request_payload' => $request,
            'response_payload' => $response,
            'message' => $status === 'processed' ? 'sync processed' : null,
            'processed_at' => now(),
            'created_by' => $user?->id,
        ]);
    }

    private function authorizeChannel(EcommerceChannel $channel, $user): void
    {
        if ((int) $user?->isGod === 1) {
            return;
        }

        if ((int) $channel->tenant_id !== (int) $this->tenantId($user)) {
            abort(403);
        }
    }

    private function operatorId(EcommerceChannel $channel, $user): int
    {
        return (int) ($user?->id ?: $channel->default_visitor_id ?: $channel->created_by ?: User::query()->value('id') ?: 1);
    }

    private function nextInvoiceId(): int
    {
        return ((int) Pishfactor::withTrashed()->max('invoiceID')) + 1;
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

    private function money($value): float
    {
        return round((float) $value, 2);
    }
}

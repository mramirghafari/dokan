<?php

namespace App\Http\Controllers;

use App\Models\EcommerceChannel;
use App\Models\EcommerceOrderMapping;
use App\Models\EcommerceProductMapping;
use App\Models\EcommerceSyncLog;
use App\Models\Product;
use App\Services\EcommerceIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class EcommerceIntegrationController extends Controller
{
    public function index(Request $request, EcommerceIntegrationService $service)
    {
        $user = Auth::user();
        $tenantId = $this->currentTenantId($user);
        $channels = EcommerceChannel::query()
            ->when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $tenantId))
            ->withCount(['productMappings', 'orderMappings'])
            ->latest('id')
            ->get();
        $activeChannel = $request->filled('channel_id')
            ? $channels->firstWhere('id', (int) $request->get('channel_id'))
            : $channels->first();
        $products = Product::forOrganizations($user)->where('isActive', 1)->orderBy('title')->limit(250)->get(['id', 'title', 'display_name', 'sku', 'price', 'consumer_price', 'wholesale_price', 'tax']);
        $mappings = EcommerceProductMapping::with(['channel', 'product'])
            ->when($activeChannel, fn($query) => $query->where('ecommerce_channel_id', $activeChannel->id))
            ->when(!$activeChannel && (int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $tenantId))
            ->latest('id')
            ->limit(50)
            ->get();
        $orders = EcommerceOrderMapping::with(['channel', 'pishfactor.customer'])
            ->when($activeChannel, fn($query) => $query->where('ecommerce_channel_id', $activeChannel->id))
            ->when(!$activeChannel && (int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $tenantId))
            ->latest('id')
            ->paginate(15);
        $orders->appends($request->query());
        $logs = EcommerceSyncLog::with('channel')
            ->when($activeChannel, fn($query) => $query->where('ecommerce_channel_id', $activeChannel->id))
            ->when(!$activeChannel && (int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $tenantId))
            ->latest('id')
            ->limit(30)
            ->get();
        $exportPreview = $activeChannel ? collect($service->exportProducts($activeChannel, ['limit' => 10, '_preview' => true], $user)) : collect();
        $totals = [
            'channels' => $channels->count(),
            'mappings' => $mappings->count(),
            'orders' => $orders->total(),
            'errors' => $logs->whereIn('status', ['failed', 'conflict'])->count(),
        ];

        return view('ecommerce.index', compact('channels', 'activeChannel', 'products', 'mappings', 'orders', 'logs', 'exportPreview', 'totals'));
    }

    public function storeChannel(Request $request, EcommerceIntegrationService $service)
    {
        $payload = $request->validate([
            'id' => ['nullable', 'integer', 'exists:ecommerce_channels,id'],
            'code' => ['nullable', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:191'],
            'platform' => ['required', 'in:custom,woocommerce,shopify,prestashop,magento'],
            'base_url' => ['nullable', 'string', 'max:191'],
            'api_token' => ['nullable', 'string', 'max:191'],
            'price_policy' => ['required', 'in:consumer,wholesale,representative,purchase'],
            'default_store_id' => ['nullable', 'integer'],
            'default_visitor_id' => ['nullable', 'integer', 'exists:users,id'],
            'default_leader_id' => ['nullable', 'integer', 'exists:users,id'],
            'default_payment_method' => ['nullable', 'integer', 'in:1,2,3,4'],
            'order_status_policy' => ['required', 'in:draft,approved'],
            'auto_create_customer' => ['nullable', 'boolean'],
            'auto_reserve_inventory' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $channel = $service->saveChannel($payload, Auth::user());

        Alert::success('ثبت شد', 'کانال فروشگاه اینترنتی ' . $channel->title . ' ذخیره شد.');

        return redirect()->route('ecommerce.index', ['channel_id' => $channel->id]);
    }

    public function storeMapping(Request $request, EcommerceIntegrationService $service)
    {
        $payload = $request->validate([
            'ecommerce_channel_id' => ['required', 'integer', 'exists:ecommerce_channels,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'external_product_id' => ['required', 'string', 'max:120'],
            'external_variant_id' => ['nullable', 'string', 'max:120'],
            'external_sku' => ['nullable', 'string', 'max:120'],
            'sync_direction' => ['required', 'in:both,export_only,import_only'],
            'price_override' => ['nullable', 'numeric', 'min:0'],
            'stock_buffer' => ['nullable', 'numeric', 'min:0'],
            'sync_price' => ['nullable', 'boolean'],
            'sync_stock' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $mapping = $service->saveProductMapping($payload, Auth::user());

        Alert::success('ثبت شد', 'نگاشت کالا برای فروشگاه ذخیره شد.');

        return redirect()->route('ecommerce.index', ['channel_id' => $mapping->ecommerce_channel_id]);
    }

    public function importSampleOrder(Request $request, EcommerceChannel $channel, EcommerceIntegrationService $service)
    {
        $payload = $request->validate([
            'external_order_id' => ['required', 'string', 'max:120'],
            'external_order_number' => ['nullable', 'string', 'max:120'],
            'customer_name' => ['required', 'string', 'max:191'],
            'customer_mobile' => ['nullable', 'string', 'max:80'],
            'customer_address' => ['nullable', 'string'],
            'external_product_id' => ['required', 'string', 'max:120'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'shipping_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['nullable', 'in:unknown,pending,paid,failed,refunded'],
        ]);

        $mapping = $service->ingestOrder($channel, [
            'external_order_id' => $payload['external_order_id'],
            'external_order_number' => $payload['external_order_number'] ?? null,
            'payment_status' => $payload['payment_status'] ?? 'unknown',
            'shipping_amount' => $payload['shipping_amount'] ?? 0,
            'customer' => [
                'name' => $payload['customer_name'],
                'mobile' => $payload['customer_mobile'] ?? null,
                'address' => $payload['customer_address'] ?? null,
            ],
            'items' => [[
                'external_product_id' => $payload['external_product_id'],
                'quantity' => $payload['quantity'],
                'price' => $payload['price'],
                'discount_amount' => $payload['discount_amount'] ?? 0,
                'tax_amount' => $payload['tax_amount'] ?? null,
            ]],
        ], Auth::user());

        Alert::success('وارد شد', 'سفارش اینترنتی به فاکتور داخلی ' . optional($mapping->pishfactor)->invoiceID . ' تبدیل شد.');

        return redirect()->route('ecommerce.index', ['channel_id' => $channel->id]);
    }

    public function updateOrderStatus(EcommerceOrderMapping $orderMapping, Request $request, EcommerceIntegrationService $service)
    {
        $payload = $request->validate([
            'order_status' => ['nullable', 'string', 'max:40'],
            'payment_status' => ['nullable', 'string', 'max:40'],
            'delivery_status' => ['nullable', 'string', 'max:40'],
            'external_ack' => ['nullable', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $mapping = $service->updateOrderStatus($orderMapping, $payload, Auth::user());

        Alert::success('بروزرسانی شد', 'وضعیت سفارش اینترنتی ' . $mapping->external_order_id . ' ثبت شد.');

        return redirect()->route('ecommerce.index', ['channel_id' => $mapping->ecommerce_channel_id]);
    }

    private function currentTenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }
}

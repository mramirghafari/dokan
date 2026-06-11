<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\SupplierQuotation;
use App\Services\InventoryReorderReportService;
use App\Services\TenantSettings;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class PurchaseRequisitionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!TenantSettings::enabled('feature_warehouse_management')) {
                Alert::warning('غیرفعال', 'مدیریت انبار و تامین برای این پنل غیرفعال است');
                return redirect()->route('index');
            }

            return $next($request);
        });
    }

    public function index()
    {
        $user = Auth::user();
        $query = PurchaseRequisition::with(['store', 'selectedSupplier', 'selectedQuotation', 'purchaseOrder', 'items.product', 'quotations.supplier'])
            ->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
        }

        $purchaseRequisitions = $query->paginate(25);

        return view('procurement.purchase_requisitions.index', compact('purchaseRequisitions'));
    }

    public function create()
    {
        $user = Auth::user();
        $stores = $this->stores($user);
        $today = now()->toDateString();

        return view('procurement.purchase_requisitions.create', compact('stores', 'today'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'request_date_en' => ['nullable', 'date'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'description' => ['nullable', 'string'],
            'product_id' => ['required', 'array'],
            'product_id.*' => ['nullable', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'array'],
            'item_description' => ['nullable', 'array'],
        ]);

        $user = Auth::user();
        $tenantId = $this->tenantId($user);
        $storeId = (int) $request->get('store_id');
        $organizationId = $this->storeOrganizationId($storeId) ?: $this->organizationId($user);
        $date = $request->get('request_date_en') ?: now()->toDateString();
        $lines = $this->normalizedRequestLines($request);

        $this->ensureStoreIsAllowed($storeId, $user);

        if (empty($lines)) {
            return back()->withErrors(['items' => 'برای ثبت درخواست خرید، حداقل یک قلم کالا با تعداد لازم است.'])->withInput();
        }

        $this->ensureProductsAreAllowed(array_column($lines, 'product_id'), $user, $organizationId);

        $purchaseRequisition = DB::transaction(function () use ($request, $user, $tenantId, $organizationId, $storeId, $date, $lines) {
            $purchaseRequisition = PurchaseRequisition::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'store_id' => $storeId,
                'request_number' => $this->nextRequisitionNumber($tenantId),
                'request_date_en' => $date,
                'request_date_fa' => $this->jalaliDate($date),
                'status' => 'open',
                'priority' => $request->get('priority'),
                'description' => $request->get('description'),
                'requested_by' => $user?->id,
            ]);

            foreach ($lines as $line) {
                $purchaseRequisition->items()->create(array_merge($line, [
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                ]));
            }

            return $purchaseRequisition;
        });

        Alert::success('ثبت شد', 'درخواست خرید شماره ' . $purchaseRequisition->request_number . ' ثبت شد.');

        return redirect()->route('purchase-requisitions.show', $purchaseRequisition);
    }

    public function storeFromReorder(Request $request, InventoryReorderReportService $service)
    {
        $request->validate([
            'reorder_key' => ['required', 'array', 'min:1'],
            'reorder_key.*' => ['required', 'string'],
            'quantity' => ['nullable', 'array'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
        ]);

        $user = Auth::user();
        $rows = $service->build($user, ['status' => 'all'])['rows']->keyBy('reorder_key');
        $requestedQuantities = $request->get('quantity', []);
        $selectedRows = collect($request->get('reorder_key', []))
            ->unique()
            ->map(function ($key) use ($rows, $requestedQuantities) {
                $row = $rows->get($key);

                if (!$row || (float) $row['suggested_quantity'] <= 0) {
                    return null;
                }

                $quantity = $this->money($requestedQuantities[$key] ?? $row['suggested_quantity']);

                if ($quantity <= 0) {
                    return null;
                }

                $row['request_quantity'] = $quantity;

                return $row;
            })
            ->filter()
            ->values();

        if ($selectedRows->isEmpty()) {
            return back()->withErrors(['reorder_key' => 'برای ساخت درخواست خرید، حداقل یک ردیف دارای پیشنهاد تامین را انتخاب کنید.']);
        }

        $purchaseRequisitions = DB::transaction(function () use ($request, $user, $selectedRows) {
            $tenantId = $this->tenantId($user);
            $date = now()->toDateString();
            $created = collect();

            foreach ($selectedRows->groupBy('store_id') as $storeId => $storeRows) {
                $storeId = (int) $storeId;
                $this->ensureStoreIsAllowed($storeId, $user);
                $organizationId = $this->storeOrganizationId($storeId) ?: $this->organizationId($user);
                $lines = $storeRows
                    ->groupBy('product_id')
                    ->map(function ($productRows) {
                        $firstRow = $productRows->first();

                        return [
                            'product_id' => (int) $firstRow['product_id'],
                            'quantity' => round((float) $productRows->sum('request_quantity'), 3),
                            'description' => 'ایجاد شده از گزارش کمبود موجودی و نقطه سفارش',
                        ];
                    })
                    ->values()
                    ->all();

                $this->ensureProductsAreAllowed(array_column($lines, 'product_id'), $user, $organizationId);

                $purchaseRequisition = PurchaseRequisition::create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'store_id' => $storeId,
                    'request_number' => $this->nextRequisitionNumber($tenantId),
                    'request_date_en' => $date,
                    'request_date_fa' => $this->jalaliDate($date),
                    'status' => 'open',
                    'priority' => $request->get('priority', 'normal'),
                    'description' => 'درخواست خرید خودکار از گزارش کمبود و پیشنهاد سفارش',
                    'requested_by' => $user?->id,
                ]);

                foreach ($lines as $line) {
                    $purchaseRequisition->items()->create(array_merge($line, [
                        'tenant_id' => $tenantId,
                        'organization_id' => $organizationId,
                    ]));
                }

                $created->push($purchaseRequisition);
            }

            return $created;
        });

        Alert::success('درخواست خرید ساخته شد', $purchaseRequisitions->count() . ' درخواست خرید از گزارش کمبود موجودی ایجاد شد.');

        if ($purchaseRequisitions->count() === 1) {
            return redirect()->route('purchase-requisitions.show', $purchaseRequisitions->first());
        }

        return redirect()->route('purchase-requisitions.index');
    }

    public function show(PurchaseRequisition $purchaseRequisition)
    {
        $this->authorizeRequisitionTenant($purchaseRequisition);

        $purchaseRequisition->load([
            'store',
            'selectedSupplier',
            'selectedQuotation.items.product',
            'purchaseOrder',
            'items.product',
            'quotations.supplier',
            'quotations.items.product',
        ]);
        $suppliers = Supplier::orderBy('title')->orderBy('name')->get();

        return view('procurement.purchase_requisitions.show', compact('purchaseRequisition', 'suppliers'));
    }

    public function storeQuotation(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        $this->authorizeRequisitionTenant($purchaseRequisition);

        $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'quotation_date_en' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'purchase_requisition_item_id' => ['required', 'array'],
            'purchase_requisition_item_id.*' => ['nullable', 'integer'],
            'quantity' => ['nullable', 'array'],
            'unit_price' => ['nullable', 'array'],
            'item_description' => ['nullable', 'array'],
        ]);

        if ($purchaseRequisition->converted_purchase_order_id) {
            return back()->withErrors(['quotation' => 'برای درخواست تبدیل شده نمی توان پیشنهاد جدید ثبت کرد.']);
        }

        $purchaseRequisition->loadMissing('items');
        $date = $request->get('quotation_date_en') ?: now()->toDateString();
        $lines = $this->normalizedQuotationLines($purchaseRequisition, $request);

        if (empty($lines)) {
            return back()->withErrors(['items' => 'برای ثبت پیشنهاد، حداقل یک قلم با مقدار و قیمت لازم است.'])->withInput();
        }

        DB::transaction(function () use ($request, $purchaseRequisition, $date, $lines) {
            $quotation = SupplierQuotation::create([
                'tenant_id' => $purchaseRequisition->tenant_id,
                'organization_id' => $purchaseRequisition->organization_id,
                'purchase_requisition_id' => $purchaseRequisition->id,
                'supplier_id' => $request->get('supplier_id'),
                'quotation_number' => $this->nextQuotationNumber($purchaseRequisition->tenant_id),
                'quotation_date_en' => $date,
                'quotation_date_fa' => $this->jalaliDate($date),
                'valid_until' => $request->get('valid_until'),
                'status' => 'submitted',
                'total_amount' => array_sum(array_column($lines, 'total_amount')),
                'description' => $request->get('description'),
                'created_by' => Auth::id(),
            ]);

            foreach ($lines as $line) {
                $quotation->items()->create(array_merge($line, [
                    'tenant_id' => $purchaseRequisition->tenant_id,
                    'organization_id' => $purchaseRequisition->organization_id,
                ]));
            }

            $purchaseRequisition->update(['status' => 'quoted']);
        });

        Alert::success('ثبت شد', 'پیشنهاد قیمت تامین کننده ثبت شد.');

        return redirect()->route('purchase-requisitions.show', $purchaseRequisition);
    }

    public function selectQuotation(PurchaseRequisition $purchaseRequisition, SupplierQuotation $supplierQuotation)
    {
        $this->authorizeRequisitionTenant($purchaseRequisition);

        if ((int) $supplierQuotation->purchase_requisition_id !== (int) $purchaseRequisition->id) {
            abort(404);
        }

        $purchaseOrder = DB::transaction(function () use ($purchaseRequisition, $supplierQuotation) {
            $purchaseRequisition = PurchaseRequisition::whereKey($purchaseRequisition->id)->lockForUpdate()->firstOrFail();

            if ($purchaseRequisition->converted_purchase_order_id) {
                return $purchaseRequisition->purchaseOrder;
            }

            $supplierQuotation = SupplierQuotation::whereKey($supplierQuotation->id)
                ->where('purchase_requisition_id', $purchaseRequisition->id)
                ->lockForUpdate()
                ->firstOrFail();
            $supplierQuotation->loadMissing(['items', 'supplier']);
            $lines = $supplierQuotation->items
                ->filter(fn($item) => $item->product_id && (float) $item->quantity > 0 && (float) $item->unit_price > 0)
                ->map(fn($item) => [
                    'product_id' => $item->product_id,
                    'quantity' => $this->money($item->quantity),
                    'received_quantity' => 0,
                    'unit_price' => $this->money($item->unit_price),
                    'total_amount' => round($this->money($item->quantity) * $this->money($item->unit_price), 2),
                    'description' => $item->description,
                ])
                ->values()
                ->all();

            if (empty($lines)) {
                abort(422, 'پیشنهاد انتخاب شده قلم معتبر برای تبدیل به سفارش خرید ندارد.');
            }

            $date = now()->toDateString();
            $purchaseOrder = PurchaseOrder::create([
                'tenant_id' => $purchaseRequisition->tenant_id,
                'organization_id' => $purchaseRequisition->organization_id,
                'supplier_id' => $supplierQuotation->supplier_id,
                'store_id' => $purchaseRequisition->store_id,
                'order_number' => $this->nextPurchaseOrderNumber($purchaseRequisition->tenant_id),
                'order_date_en' => $date,
                'order_date_fa' => $this->jalaliDate($date),
                'status' => 'draft',
                'total_amount' => array_sum(array_column($lines, 'total_amount')),
                'paid_amount' => 0,
                'payment_status' => 'unpaid',
                'description' => 'تبدیل شده از درخواست خرید ' . $purchaseRequisition->request_number . ' و پیشنهاد ' . $supplierQuotation->quotation_number,
                'created_by' => Auth::id(),
            ]);

            foreach ($lines as $line) {
                $purchaseOrder->items()->create(array_merge($line, [
                    'tenant_id' => $purchaseRequisition->tenant_id,
                    'organization_id' => $purchaseRequisition->organization_id,
                ]));
            }

            SupplierQuotation::where('purchase_requisition_id', $purchaseRequisition->id)
                ->whereKeyNot($supplierQuotation->id)
                ->where('status', 'submitted')
                ->update(['status' => 'not_selected']);
            $supplierQuotation->update([
                'status' => 'selected',
                'selected_by' => Auth::id(),
                'selected_at' => now(),
            ]);
            $purchaseRequisition->update([
                'status' => 'converted',
                'selected_supplier_id' => $supplierQuotation->supplier_id,
                'selected_supplier_quotation_id' => $supplierQuotation->id,
                'converted_purchase_order_id' => $purchaseOrder->id,
                'selected_by' => Auth::id(),
                'selected_at' => now(),
            ]);

            return $purchaseOrder;
        });

        Alert::success('تبدیل شد', 'پیشنهاد انتخاب شده به سفارش خرید شماره ' . $purchaseOrder->order_number . ' تبدیل شد.');

        return redirect()->route('purchase-requisitions.show', $purchaseRequisition);
    }

    private function normalizedRequestLines(Request $request): array
    {
        $productIds = $request->get('product_id', []);
        $quantities = $request->get('quantity', []);
        $descriptions = $request->get('item_description', []);
        $lines = [];

        foreach ($productIds as $index => $productId) {
            $quantity = $this->money($quantities[$index] ?? 0);

            if (!$productId || $quantity <= 0) {
                continue;
            }

            $lines[] = [
                'product_id' => (int) $productId,
                'quantity' => $quantity,
                'description' => $descriptions[$index] ?? null,
            ];
        }

        return $lines;
    }

    private function normalizedQuotationLines(PurchaseRequisition $purchaseRequisition, Request $request): array
    {
        $requestItemIds = $request->get('purchase_requisition_item_id', []);
        $quantities = $request->get('quantity', []);
        $unitPrices = $request->get('unit_price', []);
        $descriptions = $request->get('item_description', []);
        $items = $purchaseRequisition->items->keyBy('id');
        $lines = [];

        foreach ($requestItemIds as $index => $requestItemId) {
            $requestItem = $items->get((int) $requestItemId);
            $quantity = $this->money($quantities[$index] ?? 0);
            $unitPrice = $this->money($unitPrices[$index] ?? 0);

            if (!$requestItem || $quantity <= 0 || $unitPrice <= 0) {
                continue;
            }

            $lines[] = [
                'purchase_requisition_item_id' => $requestItem->id,
                'product_id' => $requestItem->product_id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => round($quantity * $unitPrice, 2),
                'description' => $descriptions[$index] ?? null,
            ];
        }

        return $lines;
    }

    private function stores($user)
    {
        $query = Store::query()->where('isActive', 1)->orderBy('title');

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->get();
    }

    private function products($user)
    {
        $query = Product::query()->where('isActive', 1)->orderBy('title')->orderBy('display_name');

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->get();
    }

    private function ensureStoreIsAllowed(int $storeId, $user): void
    {
        $query = Store::query()->whereKey($storeId)->where('isActive', 1);

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        if (!$query->exists()) {
            abort(403, 'انبار انتخاب شده برای این پنل مجاز نیست.');
        }
    }

    private function ensureProductsAreAllowed(array $productIds, $user, ?int $organizationId): void
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));

        if (empty($productIds)) {
            return;
        }

        $query = Product::query()->whereIn('id', $productIds)->where('isActive', 1);

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        if ($organizationId) {
            $this->whereOrganizationMatches($query, 'products.organization_id', $organizationId);
        }

        if ($query->distinct('id')->count('id') !== count($productIds)) {
            abort(403, 'یکی از کالاهای انتخاب شده برای این انبار یا پنل مجاز نیست.');
        }
    }

    private function nextRequisitionNumber(?int $tenantId): string
    {
        $year = verta()->format('Y');
        $base = 'PRQ-' . $year . '-';
        $query = PurchaseRequisition::where('request_number', 'like', $base . '%');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $lastNumber = $query->orderByDesc('id')->value('request_number');
        $next = 1;

        if ($lastNumber && preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $base . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function nextQuotationNumber(?int $tenantId): string
    {
        $year = verta()->format('Y');
        $base = 'RFQ-' . $year . '-';
        $query = SupplierQuotation::where('quotation_number', 'like', $base . '%');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $lastNumber = $query->orderByDesc('id')->value('quotation_number');
        $next = 1;

        if ($lastNumber && preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $base . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function nextPurchaseOrderNumber(?int $tenantId): string
    {
        $year = verta()->format('Y');
        $base = 'PO-' . $year . '-';
        $query = PurchaseOrder::where('order_number', 'like', $base . '%');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $lastNumber = $query->orderByDesc('id')->value('order_number');
        $next = 1;

        if ($lastNumber && preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $base . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function jalaliDate(string $date): string
    {
        return (new Verta($date))->format('Y/m/d');
    }

    private function money($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 3);
    }

    private function tenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }

    private function organizationId($user): ?int
    {
        return $this->firstOrganizationId($user?->organization_id);
    }

    private function storeOrganizationId(int $storeId): ?int
    {
        return $this->firstOrganizationId(Store::whereKey($storeId)->value('organization_id'));
    }

    private function firstOrganizationId($organizationId): ?int
    {
        $decoded = is_string($organizationId) ? json_decode($organizationId, true) : null;

        if (is_array($decoded)) {
            return isset($decoded[0]) ? (int) $decoded[0] : null;
        }

        return $organizationId ? (int) $organizationId : null;
    }

    private function whereOrganizationMatches($query, string $field, int $organizationId): void
    {
        $query->where(function ($subQuery) use ($field, $organizationId) {
            $subQuery->where($field, $organizationId)
                ->orWhere($field, (string) $organizationId)
                ->orWhere(function ($jsonQuery) use ($field, $organizationId) {
                    $jsonQuery->whereRaw("JSON_VALID({$field})")
                        ->whereJsonContains($field, $organizationId);
                })
                ->orWhere(function ($jsonQuery) use ($field, $organizationId) {
                    $jsonQuery->whereRaw("JSON_VALID({$field})")
                        ->whereJsonContains($field, (string) $organizationId);
                });
        });
    }

    private function authorizeRequisitionTenant(PurchaseRequisition $purchaseRequisition): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        if ((int) $purchaseRequisition->tenant_id !== (int) $this->tenantId($user)) {
            abort(403);
        }
    }
}

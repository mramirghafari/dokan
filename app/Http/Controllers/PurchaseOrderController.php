<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Currency;
use App\Models\ForeignPurchaseOrder;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseBudget;
use App\Models\PurchaseOrder;
use App\Models\PurchaseServiceInvoice;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Voucher;
use App\Models\Accounts;
use App\Services\PurchaseApprovalService;
use App\Services\ForeignPurchaseOrderService;
use App\Services\PurchaseInvoiceService;
use App\Services\PurchaseOrderService;
use App\Services\PurchaseServiceInvoiceService;
use App\Services\TenantSettings;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class PurchaseOrderController extends Controller
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
        $query = PurchaseOrder::with(['supplier', 'store', 'receipt', 'items.product', 'returns', 'approvalEvents', 'receiveDocuments.items', 'invoices.items', 'invoices.accountingVoucher', 'foreignImport.currency'])->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
        }

        $purchaseOrders = $query->paginate(25);

        return view('procurement.purchase_orders.index', compact('purchaseOrders'));
    }

    public function foreignImports(Request $request)
    {
        $user = Auth::user();
        $importsQuery = ForeignPurchaseOrder::with(['purchaseOrder.items.product', 'supplier', 'store', 'currency', 'items.product', 'costs', 'documents'])
            ->orderByDesc('order_date_en')
            ->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $importsQuery->where('tenant_id', $this->tenantId($user));
        }

        if ($request->filled('status')) {
            $importsQuery->where('status', $request->get('status'));
        }

        if ($request->filled('supplier_id')) {
            $importsQuery->where('supplier_id', (int) $request->get('supplier_id'));
        }

        if ($request->filled('currency_id')) {
            $importsQuery->where('currency_id', (int) $request->get('currency_id'));
        }

        $summaryRows = (clone $importsQuery)->get();
        $foreignOrders = $importsQuery->paginate(15)->withQueryString();
        $purchaseOrders = PurchaseOrder::with(['supplier', 'store', 'items.product', 'foreignImport.currency', 'foreignImport.items', 'foreignImport.costs', 'foreignImport.documents'])
            ->whereHas('items')
            ->when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $this->tenantId($user)))
            ->orderByDesc('id')
            ->limit(40)
            ->get();
        $suppliers = Supplier::orderBy('title')->orderBy('name')->get();
        $currencies = $this->currencies($user);
        $today = now()->toDateString();
        $statusLabels = $this->foreignImportStatuses();
        $costTypes = $this->foreignImportCostTypes();
        $allocationBases = $this->foreignImportAllocationBases();
        $documentTypes = $this->foreignImportDocumentTypes();
        $totals = [
            'count' => $summaryRows->count(),
            'foreign_goods' => round((float) $summaryRows->sum('foreign_goods_amount'), 4),
            'goods' => round((float) $summaryRows->sum('base_goods_amount'), 2),
            'costs' => round((float) $summaryRows->sum('additional_cost_amount'), 2),
            'landed' => round((float) $summaryRows->sum('landed_cost_amount'), 2),
        ];

        return view('procurement.purchase_orders.foreign_imports', compact('foreignOrders', 'purchaseOrders', 'suppliers', 'currencies', 'today', 'statusLabels', 'costTypes', 'allocationBases', 'documentTypes', 'totals'));
    }

    public function storeForeignImport(Request $request, ForeignPurchaseOrderService $foreignPurchaseOrderService)
    {
        $payload = $request->validate([
            'purchase_order_id' => ['required', 'integer', 'exists:purchase_orders,id'],
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            'exchange_rate' => ['required', 'numeric', 'min:0.000001'],
            'status' => ['nullable', Rule::in(array_keys($this->foreignImportStatuses()))],
            'import_number' => ['nullable', 'string', 'max:80'],
            'proforma_number' => ['nullable', 'string', 'max:120'],
            'contract_number' => ['nullable', 'string', 'max:120'],
            'lc_number' => ['nullable', 'string', 'max:120'],
            'customs_declaration_number' => ['nullable', 'string', 'max:120'],
            'bill_of_lading_number' => ['nullable', 'string', 'max:120'],
            'origin_country' => ['nullable', 'string', 'max:120'],
            'shipment_method' => ['nullable', 'string', 'max:80'],
            'order_date_en' => ['nullable', 'date'],
            'expected_arrival_date_en' => ['nullable', 'date'],
            'customs_date_en' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'foreign_unit_price' => ['nullable', 'array'],
            'foreign_unit_price.*' => ['nullable', 'numeric', 'min:0'],
            'manual_allocation_amount' => ['nullable', 'array'],
            'manual_allocation_amount.*' => ['nullable', 'numeric', 'min:0'],
            'item_description' => ['nullable', 'array'],
            'item_description.*' => ['nullable', 'string'],
            'cost_title' => ['nullable', 'array'],
            'cost_title.*' => ['nullable', 'string', 'max:191'],
            'cost_type' => ['nullable', 'array'],
            'cost_type.*' => ['nullable', Rule::in(array_keys($this->foreignImportCostTypes()))],
            'cost_date_en' => ['nullable', 'array'],
            'cost_date_en.*' => ['nullable', 'date'],
            'cost_foreign_amount' => ['nullable', 'array'],
            'cost_foreign_amount.*' => ['nullable', 'numeric', 'min:0'],
            'cost_exchange_rate' => ['nullable', 'array'],
            'cost_exchange_rate.*' => ['nullable', 'numeric', 'min:0'],
            'cost_base_amount' => ['nullable', 'array'],
            'cost_base_amount.*' => ['nullable', 'numeric', 'min:0'],
            'allocation_basis' => ['nullable', 'array'],
            'allocation_basis.*' => ['nullable', Rule::in(array_keys($this->foreignImportAllocationBases()))],
            'cost_document_number' => ['nullable', 'array'],
            'cost_document_number.*' => ['nullable', 'string', 'max:120'],
            'cost_reference_number' => ['nullable', 'array'],
            'cost_reference_number.*' => ['nullable', 'string', 'max:120'],
            'cost_description' => ['nullable', 'array'],
            'cost_description.*' => ['nullable', 'string'],
            'document_type' => ['nullable', 'array'],
            'document_type.*' => ['nullable', Rule::in(array_keys($this->foreignImportDocumentTypes()))],
            'document_number' => ['nullable', 'array'],
            'document_number.*' => ['nullable', 'string', 'max:120'],
            'document_date_en' => ['nullable', 'array'],
            'document_date_en.*' => ['nullable', 'date'],
            'document_reference_number' => ['nullable', 'array'],
            'document_reference_number.*' => ['nullable', 'string', 'max:120'],
            'document_file_path' => ['nullable', 'array'],
            'document_file_path.*' => ['nullable', 'string', 'max:255'],
            'document_description' => ['nullable', 'array'],
            'document_description.*' => ['nullable', 'string'],
        ]);

        $purchaseOrder = PurchaseOrder::with('items')->findOrFail((int) $payload['purchase_order_id']);
        $this->authorizePurchaseOrderTenant($purchaseOrder);

        $foreignOrder = $foreignPurchaseOrderService->save($purchaseOrder, $payload, Auth::user());

        Alert::success('ثبت شد', 'پرونده واردات ' . $foreignOrder->import_number . ' و بهای تمام شده وارداتی آن بروزرسانی شد.');

        return redirect()->route('purchase-orders.foreignImports');
    }

    public function updateForeignImportStatus(Request $request, ForeignPurchaseOrder $foreignPurchaseOrder, ForeignPurchaseOrderService $foreignPurchaseOrderService)
    {
        $this->authorizeForeignImportTenant($foreignPurchaseOrder);

        $payload = $request->validate([
            'status' => ['required', Rule::in(array_keys($this->foreignImportStatuses()))],
        ]);

        $foreignPurchaseOrderService->updateStatus($foreignPurchaseOrder, $payload['status'], Auth::user());

        Alert::success('بروزرسانی شد', 'وضعیت پرونده واردات ثبت شد.');

        return redirect()->route('purchase-orders.foreignImports');
    }

    public function serviceInvoices(Request $request)
    {
        $user = Auth::user();
        $query = PurchaseServiceInvoice::with(['supplier', 'purchaseOrder', 'items', 'accountingVoucher'])
            ->orderByDesc('invoice_date_en')
            ->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', (int) $request->get('supplier_id'));
        }

        if ($request->filled('invoice_type')) {
            $query->where('invoice_type', $request->get('invoice_type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date_en', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date_en', '<=', $request->get('date_to'));
        }

        $invoiceRows = $query->get();
        $serviceInvoices = $query->paginate(20)->withQueryString();
        $suppliers = Supplier::orderBy('title')->orderBy('name')->get();
        $purchaseOrders = PurchaseOrder::with('supplier')
            ->when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $this->tenantId($user)))
            ->orderByDesc('id')
            ->limit(80)
            ->get();
        $accounts = Accounts::query()->where('isActive', 1)->orderBy('code')->orderBy('name')->limit(200)->get();
        $today = now()->toDateString();
        $totals = [
            'count' => $invoiceRows->count(),
            'subtotal' => round((float) $invoiceRows->sum('subtotal_amount'), 2),
            'tax' => round((float) $invoiceRows->sum('tax_amount'), 2),
            'total' => round((float) $invoiceRows->sum('total_amount'), 2),
        ];

        return view('procurement.purchase_orders.service_invoices', compact('serviceInvoices', 'suppliers', 'purchaseOrders', 'accounts', 'today', 'totals'));
    }

    public function storeServiceInvoice(Request $request, PurchaseServiceInvoiceService $serviceInvoiceService)
    {
        $payload = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'purchase_order_id' => ['nullable', 'integer', 'exists:purchase_orders,id'],
            'invoice_type' => ['required', Rule::in(['service', 'additional_cost'])],
            'invoice_date_en' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'item_title' => ['required', 'array', 'min:1'],
            'item_title.*' => ['nullable', 'string', 'max:191'],
            'cost_type' => ['nullable', 'array'],
            'cost_type.*' => ['nullable', 'string', 'max:50'],
            'allocation_type' => ['nullable', 'array'],
            'allocation_type.*' => ['nullable', Rule::in(['expense', 'landed_cost'])],
            'expense_account_id' => ['nullable', 'array'],
            'expense_account_id.*' => ['nullable', 'integer', 'exists:accounts,id'],
            'amount' => ['nullable', 'array'],
            'amount.*' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'array'],
            'tax_amount.*' => ['nullable', 'numeric', 'min:0'],
            'item_description' => ['nullable', 'array'],
            'item_description.*' => ['nullable', 'string'],
        ]);

        if (!empty($payload['purchase_order_id'])) {
            $this->authorizePurchaseOrderTenant(PurchaseOrder::findOrFail((int) $payload['purchase_order_id']));
        }

        $invoice = $serviceInvoiceService->create($payload, Auth::user());

        Alert::success('ثبت شد', 'فاکتور ' . $invoice->invoice_number . ' و سند حسابداری ' . optional($invoice->accountingVoucher)->voucher_number . ' ثبت شد.');

        return redirect()->route('purchase-service-invoices.index');
    }

    public function cancelServiceInvoice(PurchaseServiceInvoice $purchaseServiceInvoice, PurchaseServiceInvoiceService $serviceInvoiceService)
    {
        $this->authorizeServiceInvoiceTenant($purchaseServiceInvoice);

        $serviceInvoiceService->cancel($purchaseServiceInvoice, Auth::user());

        Alert::success('ابطال شد', 'فاکتور خدمات و سند حسابداری موقت مرتبط ابطال شد.');

        return redirect()->route('purchase-service-invoices.index');
    }

    public function approvals(Request $request)
    {
        $user = Auth::user();
        $query = PurchaseOrder::with(['supplier', 'store', 'items.product', 'approvalEvents'])
            ->orderByDesc('approval_requested_at')
            ->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->get('approval_status'));
        } else {
            $query->whereIn('approval_status', ['pending_approval', 'approved', 'rejected']);
        }

        if ($request->filled('budget_status')) {
            $query->where('budget_status', $request->get('budget_status'));
        }

        $purchaseOrders = $query->paginate(25)->withQueryString();
        $budgetsQuery = PurchaseBudget::with('store')->orderByDesc('period')->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $budgetsQuery->where('tenant_id', $this->tenantId($user));
        }

        $budgets = $budgetsQuery->limit(60)->get();
        $stores = $this->stores($user);
        $currentPeriod = now()->format('Y-m');
        $totals = [
            'pending' => (clone $query)->where('approval_status', 'pending_approval')->count(),
            'over_budget' => (clone $query)->where('budget_status', 'over_budget')->count(),
            'amount' => round((float) $purchaseOrders->sum('total_amount'), 2),
        ];

        return view('procurement.purchase_orders.approvals', compact('purchaseOrders', 'budgets', 'stores', 'currentPeriod', 'totals'));
    }

    public function storeBudget(Request $request)
    {
        $payload = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'period' => ['required', 'date_format:Y-m'],
            'budget_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $user = Auth::user();
        $storeId = (int) $payload['store_id'];
        $this->ensureStoreIsAllowed($storeId, $user);

        PurchaseBudget::updateOrCreate(
            [
                'tenant_id' => $this->tenantId($user),
                'store_id' => $storeId,
                'period' => $payload['period'],
            ],
            [
                'organization_id' => $this->storeOrganizationId($storeId) ?: $this->organizationId($user),
                'budget_amount' => $this->money($payload['budget_amount']),
                'updated_by' => $user?->id,
                'created_by' => $user?->id,
            ]
        );

        Alert::success('ثبت شد', 'بودجه خرید ماهانه ثبت یا بروزرسانی شد.');

        return redirect()->route('purchase-orders.approvals');
    }

    public function report(Request $request)
    {
        $user = Auth::user();
        $query = PurchaseOrder::with(['supplier', 'store', 'returns'])
            ->orderByDesc('order_date_en')
            ->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', (int) $request->get('supplier_id'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->get('payment_status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('order_date_en', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('order_date_en', '<=', $request->get('date_to'));
        }

        $purchaseOrders = $query->get();
        $totals = [
            'gross' => round((float) $purchaseOrders->sum('total_amount'), 2),
            'returns' => round((float) $purchaseOrders->sum(fn($order) => $order->returned_amount), 2),
            'net' => round((float) $purchaseOrders->sum(fn($order) => $order->net_amount), 2),
            'paid' => round((float) $purchaseOrders->sum('paid_amount'), 2),
            'remaining' => round((float) $purchaseOrders->sum(fn($order) => $order->remaining_amount), 2),
        ];
        $supplierSummaries = $purchaseOrders
            ->groupBy(fn($order) => $order->supplier_id ?: 0)
            ->map(function ($orders) {
                $supplier = $orders->first()->supplier;

                return [
                    'supplier' => $supplier?->title ?: $supplier?->name ?: 'بدون تامین کننده',
                    'orders_count' => $orders->count(),
                    'gross' => round((float) $orders->sum('total_amount'), 2),
                    'returns' => round((float) $orders->sum(fn($order) => $order->returned_amount), 2),
                    'net' => round((float) $orders->sum(fn($order) => $order->net_amount), 2),
                    'paid' => round((float) $orders->sum('paid_amount'), 2),
                    'remaining' => round((float) $orders->sum(fn($order) => $order->remaining_amount), 2),
                ];
            })
            ->sortByDesc('remaining')
            ->values();
        $suppliers = Supplier::orderBy('title')->orderBy('name')->get();

        return view('procurement.purchase_orders.report', compact('purchaseOrders', 'supplierSummaries', 'suppliers', 'totals'));
    }

    public function supplierLedger(Request $request)
    {
        $user = Auth::user();
        $ordersQuery = PurchaseOrder::with(['supplier', 'returns', 'invoices.accountingVoucher'])
            ->orderBy('order_date_en')
            ->orderBy('id');

        if ((int) $user->isGod !== 1) {
            $ordersQuery->where('tenant_id', $this->tenantId($user));
        }

        if ($request->filled('supplier_id')) {
            $ordersQuery->where('supplier_id', (int) $request->get('supplier_id'));
        }

        if ($request->filled('payment_status')) {
            $ordersQuery->where('payment_status', $request->get('payment_status'));
        }

        if ($request->filled('date_from')) {
            $ordersQuery->whereDate('order_date_en', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $ordersQuery->whereDate('order_date_en', '<=', $request->get('date_to'));
        }

        $purchaseOrders = $ordersQuery->get();
        $serviceInvoicesQuery = PurchaseServiceInvoice::with(['supplier', 'accountingVoucher'])
            ->where('status', '<>', 'canceled')
            ->orderBy('invoice_date_en')
            ->orderBy('id');

        if ((int) $user->isGod !== 1) {
            $serviceInvoicesQuery->where('tenant_id', $this->tenantId($user));
        }

        if ($request->filled('supplier_id')) {
            $serviceInvoicesQuery->where('supplier_id', (int) $request->get('supplier_id'));
        }

        if ($request->filled('date_from')) {
            $serviceInvoicesQuery->whereDate('invoice_date_en', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $serviceInvoicesQuery->whereDate('invoice_date_en', '<=', $request->get('date_to'));
        }

        $serviceInvoices = $serviceInvoicesQuery->get();
        $orderIds = $purchaseOrders->pluck('id')->all();
        $purchaseOrdersById = $purchaseOrders->keyBy('id');
        $paymentVouchers = Voucher::query()
            ->where('document_type', 'purchase_supplier_payment')
            ->where('source_type', PurchaseOrder::class)
            ->whereIn('source_id', $orderIds)
            ->when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $this->tenantId($user)))
            ->when($request->filled('date_from'), fn($query) => $query->whereDate('voucher_date_en', '>=', $request->get('date_from')))
            ->when($request->filled('date_to'), fn($query) => $query->whereDate('voucher_date_en', '<=', $request->get('date_to')))
            ->orderBy('voucher_date_en')
            ->orderBy('id')
            ->get();

        $ledgerRows = collect();

        foreach ($purchaseOrders as $purchaseOrder) {
            $supplierName = optional($purchaseOrder->supplier)->title ?: optional($purchaseOrder->supplier)->name ?: 'بدون تامین کننده';

            if ($purchaseOrder->invoices->where('status', '<>', 'canceled')->isNotEmpty()) {
                foreach ($purchaseOrder->invoices->where('status', '<>', 'canceled') as $invoice) {
                    $ledgerRows->push([
                        'date_en' => optional($invoice->invoice_date_en)->format('Y-m-d') ?: $invoice->created_at?->format('Y-m-d'),
                        'date_fa' => $invoice->invoice_date_fa,
                        'supplier' => $supplierName,
                        'order_number' => $invoice->invoice_number,
                        'type' => 'فاکتور خرید',
                        'debit' => round((float) $invoice->total_amount, 2),
                        'credit' => 0,
                        'description' => trim(($invoice->supplier_invoice_number ? 'صورتحساب تامین کننده: ' . $invoice->supplier_invoice_number . ' - ' : '') . ($invoice->description ?: $purchaseOrder->order_number)),
                        'sort_id' => $invoice->id,
                    ]);
                }
            } else {
                $ledgerRows->push([
                    'date_en' => optional($purchaseOrder->order_date_en)->format('Y-m-d') ?: $purchaseOrder->created_at?->format('Y-m-d'),
                    'date_fa' => $purchaseOrder->order_date_fa,
                    'supplier' => $supplierName,
                    'order_number' => $purchaseOrder->order_number,
                    'type' => 'خرید',
                    'debit' => round((float) $purchaseOrder->total_amount, 2),
                    'credit' => 0,
                    'description' => $purchaseOrder->description,
                    'sort_id' => $purchaseOrder->id,
                ]);
            }

            foreach ($purchaseOrder->returns as $purchaseReturn) {
                $ledgerRows->push([
                    'date_en' => optional($purchaseReturn->return_date_en)->format('Y-m-d') ?: optional($purchaseOrder->order_date_en)->format('Y-m-d'),
                    'date_fa' => $purchaseReturn->return_date_fa,
                    'supplier' => $supplierName,
                    'order_number' => $purchaseOrder->order_number,
                    'type' => 'مرجوعی خرید',
                    'debit' => 0,
                    'credit' => round((float) $purchaseReturn->total_amount, 2),
                    'description' => $purchaseReturn->description,
                    'sort_id' => $purchaseReturn->id,
                ]);
            }
        }

        foreach ($paymentVouchers as $voucher) {
            $purchaseOrder = $purchaseOrdersById->get((int) $voucher->source_id);

            if (!$purchaseOrder) {
                continue;
            }

            $ledgerRows->push([
                'date_en' => optional($voucher->voucher_date_en)->format('Y-m-d') ?: $voucher->created_at?->format('Y-m-d'),
                'date_fa' => $voucher->voucher_date_fa,
                'supplier' => optional($purchaseOrder->supplier)->title ?: optional($purchaseOrder->supplier)->name ?: 'بدون تامین کننده',
                'order_number' => $purchaseOrder->order_number,
                'type' => 'پرداخت تامین کننده',
                'debit' => 0,
                'credit' => round((float) $voucher->amount, 2),
                'description' => $voucher->description,
                'sort_id' => $voucher->id,
            ]);
        }

        foreach ($serviceInvoices as $invoice) {
            $ledgerRows->push([
                'date_en' => optional($invoice->invoice_date_en)->format('Y-m-d') ?: $invoice->created_at?->format('Y-m-d'),
                'date_fa' => $invoice->invoice_date_fa,
                'supplier' => optional($invoice->supplier)->title ?: optional($invoice->supplier)->name ?: 'بدون تامین کننده',
                'order_number' => $invoice->invoice_number,
                'type' => $invoice->invoice_type === 'additional_cost' ? 'هزینه جانبی خرید' : 'فاکتور خدمات خرید',
                'debit' => round((float) $invoice->total_amount, 2),
                'credit' => 0,
                'description' => $invoice->description,
                'sort_id' => $invoice->id,
            ]);
        }

        $runningBalance = 0;
        $ledgerRows = $ledgerRows
            ->sortBy([
                ['date_en', 'asc'],
                ['sort_id', 'asc'],
            ])
            ->values()
            ->map(function ($row) use (&$runningBalance) {
                $runningBalance = round($runningBalance + $row['debit'] - $row['credit'], 2);
                $row['balance'] = $runningBalance;

                return $row;
            });

        $agingBuckets = [
            'current' => 0,
            'days_31_60' => 0,
            'days_61_90' => 0,
            'days_over_90' => 0,
        ];
        $supplierAging = [];

        foreach ($purchaseOrders as $purchaseOrder) {
            $remaining = round((float) $purchaseOrder->remaining_amount, 2);

            if ($remaining <= 0) {
                continue;
            }

            $days = $purchaseOrder->order_date_en ? $purchaseOrder->order_date_en->diffInDays(now()) : 0;
            $bucket = match (true) {
                $days <= 30 => 'current',
                $days <= 60 => 'days_31_60',
                $days <= 90 => 'days_61_90',
                default => 'days_over_90',
            };
            $supplierId = $purchaseOrder->supplier_id ?: 0;
            $supplierName = optional($purchaseOrder->supplier)->title ?: optional($purchaseOrder->supplier)->name ?: 'بدون تامین کننده';

            if (!isset($supplierAging[$supplierId])) {
                $supplierAging[$supplierId] = array_merge([
                    'supplier' => $supplierName,
                    'orders_count' => 0,
                    'total' => 0,
                ], $agingBuckets);
            }

            $agingBuckets[$bucket] += $remaining;
            $supplierAging[$supplierId][$bucket] += $remaining;
            $supplierAging[$supplierId]['total'] += $remaining;
            $supplierAging[$supplierId]['orders_count']++;
        }

        foreach ($serviceInvoices as $invoice) {
            $remaining = round((float) $invoice->total_amount, 2);
            $days = $invoice->invoice_date_en ? $invoice->invoice_date_en->diffInDays(now()) : 0;
            $bucket = match (true) {
                $days <= 30 => 'current',
                $days <= 60 => 'days_31_60',
                $days <= 90 => 'days_61_90',
                default => 'days_over_90',
            };
            $supplierId = $invoice->supplier_id ?: 0;
            $supplierName = optional($invoice->supplier)->title ?: optional($invoice->supplier)->name ?: 'بدون تامین کننده';

            if (!isset($supplierAging[$supplierId])) {
                $supplierAging[$supplierId] = array_merge([
                    'supplier' => $supplierName,
                    'orders_count' => 0,
                    'total' => 0,
                ], $agingBuckets);
            }

            $agingBuckets[$bucket] += $remaining;
            $supplierAging[$supplierId][$bucket] += $remaining;
            $supplierAging[$supplierId]['total'] += $remaining;
            $supplierAging[$supplierId]['orders_count']++;
        }

        $totals = [
            'purchases' => round((float) $ledgerRows->sum('debit'), 2),
            'payments_and_returns' => round((float) $ledgerRows->sum('credit'), 2),
            'balance' => round((float) ($ledgerRows->last()['balance'] ?? 0), 2),
            'open_orders' => $purchaseOrders->filter(fn($order) => $order->remaining_amount > 0)->count() + $serviceInvoices->count(),
            'aging' => array_map(fn($amount) => round((float) $amount, 2), $agingBuckets),
        ];

        $supplierAging = collect($supplierAging)
            ->map(function ($row) {
                foreach (['current', 'days_31_60', 'days_61_90', 'days_over_90', 'total'] as $key) {
                    $row[$key] = round((float) $row[$key], 2);
                }

                return $row;
            })
            ->sortByDesc('total')
            ->values();
        $suppliers = Supplier::orderBy('title')->orderBy('name')->get();

        return view('procurement.purchase_orders.supplier_ledger', compact('ledgerRows', 'supplierAging', 'suppliers', 'totals'));
    }

    public function commitmentReport(Request $request)
    {
        $user = Auth::user();
        $query = PurchaseOrder::with(['supplier', 'store', 'receipt', 'items.product'])
            ->orderByDesc('order_date_en')
            ->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', (int) $request->get('supplier_id'));
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', (int) $request->get('store_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('order_date_en', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('order_date_en', '<=', $request->get('date_to'));
        }

        $purchaseOrders = $query->get();
        $rows = $purchaseOrders->flatMap(function ($purchaseOrder) {
            $orderDate = $purchaseOrder->order_date_en;
            $receiptDate = $purchaseOrder->receipt?->date_en ? \Illuminate\Support\Carbon::parse($purchaseOrder->receipt->date_en) : null;
            $referenceDate = $receiptDate ?: now();
            $delayDays = $orderDate ? $orderDate->diffInDays($referenceDate) : 0;

            return $purchaseOrder->items->map(function ($item) use ($purchaseOrder, $delayDays, $receiptDate) {
                $orderedQuantity = round((float) $item->quantity, 3);
                $receivedQuantity = round((float) $item->received_quantity, 3);
                $remainingQuantity = max(0, round($orderedQuantity - $receivedQuantity, 3));
                $unitPrice = round((float) $item->unit_price, 2);
                $status = match (true) {
                    $remainingQuantity <= 0 => 'received',
                    $receivedQuantity <= 0 => 'not_received',
                    default => 'partial',
                };

                return [
                    'order_number' => $purchaseOrder->order_number,
                    'order_date' => $purchaseOrder->order_date_fa ?: optional($purchaseOrder->order_date_en)->format('Y-m-d'),
                    'receipt_date' => $purchaseOrder->receipt?->date_fa ?: optional($receiptDate)->format('Y-m-d'),
                    'supplier' => optional($purchaseOrder->supplier)->title ?: optional($purchaseOrder->supplier)->name ?: 'بدون تامین کننده',
                    'store' => optional($purchaseOrder->store)->title ?: '-',
                    'product' => optional($item->product)->title ?: optional($item->product)->display_name ?: optional($item->product)->name ?: '-',
                    'ordered_quantity' => $orderedQuantity,
                    'received_quantity' => $receivedQuantity,
                    'remaining_quantity' => $remainingQuantity,
                    'ordered_amount' => round($orderedQuantity * $unitPrice, 2),
                    'received_amount' => round($receivedQuantity * $unitPrice, 2),
                    'remaining_amount' => round($remainingQuantity * $unitPrice, 2),
                    'delay_days' => $delayDays,
                    'status' => $status,
                ];
            });
        });

        if ($request->filled('fulfillment_status')) {
            $status = $request->get('fulfillment_status');
            $rows = $status === 'open'
                ? $rows->filter(fn($row) => $row['remaining_quantity'] > 0)
                : $rows->filter(fn($row) => $row['status'] === $status);
        }

        $rows = $rows->values();
        $supplierSummaries = $rows
            ->groupBy('supplier')
            ->map(fn($group, $supplier) => [
                'supplier' => $supplier,
                'items_count' => $group->count(),
                'ordered_amount' => round((float) $group->sum('ordered_amount'), 2),
                'received_amount' => round((float) $group->sum('received_amount'), 2),
                'remaining_amount' => round((float) $group->sum('remaining_amount'), 2),
                'remaining_quantity' => round((float) $group->sum('remaining_quantity'), 3),
            ])
            ->sortByDesc('remaining_amount')
            ->values();
        $storeSummaries = $rows
            ->groupBy('store')
            ->map(fn($group, $store) => [
                'store' => $store,
                'items_count' => $group->count(),
                'ordered_amount' => round((float) $group->sum('ordered_amount'), 2),
                'received_amount' => round((float) $group->sum('received_amount'), 2),
                'remaining_amount' => round((float) $group->sum('remaining_amount'), 2),
                'remaining_quantity' => round((float) $group->sum('remaining_quantity'), 3),
            ])
            ->sortByDesc('remaining_amount')
            ->values();
        $totals = [
            'items_count' => $rows->count(),
            'open_items_count' => $rows->filter(fn($row) => $row['remaining_quantity'] > 0)->count(),
            'ordered_quantity' => round((float) $rows->sum('ordered_quantity'), 3),
            'received_quantity' => round((float) $rows->sum('received_quantity'), 3),
            'remaining_quantity' => round((float) $rows->sum('remaining_quantity'), 3),
            'ordered_amount' => round((float) $rows->sum('ordered_amount'), 2),
            'received_amount' => round((float) $rows->sum('received_amount'), 2),
            'remaining_amount' => round((float) $rows->sum('remaining_amount'), 2),
        ];
        $suppliers = Supplier::orderBy('title')->orderBy('name')->get();
        $stores = $this->stores($user);

        return view('procurement.purchase_orders.commitment_report', compact('rows', 'supplierSummaries', 'storeSummaries', 'suppliers', 'stores', 'totals'));
    }

    public function priceReport(Request $request)
    {
        $user = Auth::user();
        $query = PurchaseOrder::with(['supplier', 'store', 'items.product'])
            ->orderByDesc('order_date_en')
            ->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', (int) $request->get('supplier_id'));
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', (int) $request->get('store_id'));
        }

        if ($request->filled('product_id')) {
            $productId = (int) $request->get('product_id');
            $query->whereHas('items', fn($itemQuery) => $itemQuery->where('product_id', $productId));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('order_date_en', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('order_date_en', '<=', $request->get('date_to'));
        }

        $purchaseOrders = $query->get();
        $rows = $purchaseOrders->flatMap(function ($purchaseOrder) use ($request) {
            return $purchaseOrder->items
                ->when($request->filled('product_id'), fn($items) => $items->where('product_id', (int) $request->get('product_id')))
                ->filter(fn($item) => (float) $item->quantity > 0 && (float) $item->unit_price > 0)
                ->map(function ($item) use ($purchaseOrder) {
                    $quantity = round((float) $item->quantity, 3);
                    $unitPrice = round((float) $item->unit_price, 2);

                    return [
                        'product_id' => (int) $item->product_id,
                        'supplier_id' => (int) ($purchaseOrder->supplier_id ?: 0),
                        'order_id' => (int) $purchaseOrder->id,
                        'item_id' => (int) $item->id,
                        'order_number' => $purchaseOrder->order_number,
                        'order_date' => $purchaseOrder->order_date_fa ?: optional($purchaseOrder->order_date_en)->format('Y-m-d'),
                        'order_date_key' => optional($purchaseOrder->order_date_en)->format('Y-m-d') ?: optional($purchaseOrder->created_at)->format('Y-m-d'),
                        'supplier' => optional($purchaseOrder->supplier)->title ?: optional($purchaseOrder->supplier)->name ?: 'بدون تامین کننده',
                        'store' => optional($purchaseOrder->store)->title ?: '-',
                        'product' => optional($item->product)->title ?: optional($item->product)->display_name ?: optional($item->product)->name ?: '-',
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_amount' => round($quantity * $unitPrice, 2),
                    ];
                });
        })->values();

        $productSummaries = $rows
            ->groupBy('product_id')
            ->map(function ($group) {
                $sorted = $group->sortBy([
                    ['order_date_key', 'asc'],
                    ['order_id', 'asc'],
                    ['item_id', 'asc'],
                ])->values();
                $latest = $sorted->last();
                $previous = $sorted->count() > 1 ? $sorted->get($sorted->count() - 2) : null;
                $averagePrice = $group->sum('quantity') > 0
                    ? round((float) $group->sum('total_amount') / (float) $group->sum('quantity'), 2)
                    : 0;
                $changeAmount = $previous ? round($latest['unit_price'] - $previous['unit_price'], 2) : 0;
                $changePercent = $previous && $previous['unit_price'] > 0
                    ? round(($changeAmount / $previous['unit_price']) * 100, 2)
                    : 0;
                $priceAlert = match (true) {
                    !$previous => 'no_previous',
                    $changeAmount > 0 => 'increased',
                    $changeAmount < 0 => 'decreased',
                    default => 'stable',
                };

                return [
                    'product_id' => $latest['product_id'],
                    'product' => $latest['product'],
                    'purchases_count' => $group->count(),
                    'suppliers_count' => $group->pluck('supplier_id')->unique()->count(),
                    'latest_supplier' => $latest['supplier'],
                    'latest_order_number' => $latest['order_number'],
                    'latest_order_date' => $latest['order_date'],
                    'latest_price' => $latest['unit_price'],
                    'previous_price' => $previous['unit_price'] ?? null,
                    'average_price' => $averagePrice,
                    'min_price' => round((float) $group->min('unit_price'), 2),
                    'max_price' => round((float) $group->max('unit_price'), 2),
                    'change_amount' => $changeAmount,
                    'change_percent' => $changePercent,
                    'price_alert' => $priceAlert,
                ];
            })
            ->sortByDesc(fn($summary) => abs($summary['change_percent']))
            ->values();

        if ($request->filled('price_alert')) {
            $productSummaries = $productSummaries->filter(fn($summary) => $summary['price_alert'] === $request->get('price_alert'))->values();
            $allowedProductIds = $productSummaries->pluck('product_id')->all();
            $rows = $rows->filter(fn($row) => in_array($row['product_id'], $allowedProductIds, true))->values();
        }

        $supplierComparisons = $rows
            ->groupBy(fn($row) => $row['product_id'] . ':' . $row['supplier_id'])
            ->map(function ($group) {
                $latest = $group->sortBy([
                    ['order_date_key', 'asc'],
                    ['order_id', 'asc'],
                    ['item_id', 'asc'],
                ])->last();
                $averagePrice = $group->sum('quantity') > 0
                    ? round((float) $group->sum('total_amount') / (float) $group->sum('quantity'), 2)
                    : 0;

                return [
                    'product' => $latest['product'],
                    'supplier' => $latest['supplier'],
                    'purchases_count' => $group->count(),
                    'latest_price' => round((float) $latest['unit_price'], 2),
                    'average_price' => $averagePrice,
                    'min_price' => round((float) $group->min('unit_price'), 2),
                    'max_price' => round((float) $group->max('unit_price'), 2),
                    'latest_order_date' => $latest['order_date'],
                ];
            })
            ->sortBy(['product', 'latest_price'])
            ->values();
        $latestRows = $rows
            ->sortByDesc(fn($row) => ($row['order_date_key'] ?: '') . '-' . str_pad((string) $row['order_id'], 12, '0', STR_PAD_LEFT) . '-' . str_pad((string) $row['item_id'], 12, '0', STR_PAD_LEFT))
            ->take(100)
            ->values();
        $totals = [
            'products_count' => $productSummaries->count(),
            'rows_count' => $rows->count(),
            'average_price' => $rows->sum('quantity') > 0 ? round((float) $rows->sum('total_amount') / (float) $rows->sum('quantity'), 2) : 0,
            'increased_count' => $productSummaries->where('price_alert', 'increased')->count(),
            'decreased_count' => $productSummaries->where('price_alert', 'decreased')->count(),
            'no_previous_count' => $productSummaries->where('price_alert', 'no_previous')->count(),
        ];
        $suppliers = Supplier::orderBy('title')->orderBy('name')->get();
        $stores = $this->stores($user);
        return view('procurement.purchase_orders.price_report', compact('productSummaries', 'supplierComparisons', 'latestRows', 'suppliers', 'stores', 'totals'));
    }

    public function create()
    {
        $user = Auth::user();
        $suppliers = Supplier::orderBy('title')->orderBy('name')->get();
        $stores = $this->stores($user);
        $today = now()->toDateString();

        return view('procurement.purchase_orders.create', compact('suppliers', 'stores', 'today'));
    }

    public function directSupply()
    {
        $user = Auth::user();
        $suppliers = Supplier::orderBy('title')->orderBy('name')->get();
        $stores = $this->stores($user);
        $today = now()->toDateString();

        return view('procurement.purchase_orders.direct_supply', compact('suppliers', 'stores', 'today'));
    }

    public function storeDirectSupply(Request $request, PurchaseApprovalService $purchaseApprovalService)
    {
        $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'order_date_en' => ['nullable', 'date'],
            'direct_supply_type' => ['required', Rule::in(['urgent_purchase', 'field_purchase', 'no_requisition', 'manager_order', 'other'])],
            'direct_supply_reason' => ['required', 'string', 'max:1000'],
            'source_reference' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'product_id' => ['required', 'array'],
            'product_id.*' => ['nullable', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'array'],
            'unit_price' => ['nullable', 'array'],
            'item_description' => ['nullable', 'array'],
        ]);

        $user = Auth::user();
        $tenantId = $this->tenantId($user);
        $storeId = (int) $request->get('store_id');
        $organizationId = $this->storeOrganizationId($storeId) ?: $this->organizationId($user);
        $date = $request->get('order_date_en') ?: now()->toDateString();
        $lines = $this->normalizedLines($request);

        $this->ensureStoreIsAllowed($storeId, $user);

        if (empty($lines)) {
            return back()->withErrors(['items' => 'برای ثبت تامین مستقیم، حداقل یک قلم کالا با تعداد و مبلغ لازم است.'])->withInput();
        }

        $this->ensureProductsAreAllowed(array_column($lines, 'product_id'), $user, $organizationId);

        $purchaseOrder = DB::transaction(function () use ($request, $user, $tenantId, $organizationId, $storeId, $date, $lines) {
            $purchaseOrder = PurchaseOrder::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'supplier_id' => $request->get('supplier_id'),
                'store_id' => $storeId,
                'order_number' => $this->nextPurchaseOrderNumber($tenantId),
                'order_date_en' => $date,
                'order_date_fa' => $this->jalaliDate($date),
                'status' => 'draft',
                'total_amount' => array_sum(array_column($lines, 'total_amount')),
                'paid_amount' => 0,
                'payment_status' => 'unpaid',
                'procurement_source' => 'direct_supply',
                'direct_supply_type' => $request->get('direct_supply_type'),
                'direct_supply_reason' => $request->get('direct_supply_reason'),
                'source_reference' => $request->get('source_reference'),
                'description' => $request->get('description') ?: 'تامین مستقیم - ارسال خودکار به کارتابل تایید',
                'created_by' => $user?->id,
            ]);

            foreach ($lines as $line) {
                $purchaseOrder->items()->create(array_merge($line, [
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                ]));
            }

            return $purchaseOrder;
        });

        $purchaseApprovalService->requestApproval($purchaseOrder, $user);

        Alert::success('ارسال شد', 'تامین مستقیم به سفارش خرید ' . $purchaseOrder->order_number . ' تبدیل و برای تایید/بودجه ارسال شد.');

        return redirect()->route('purchase-orders.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'order_date_en' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'product_id' => ['required', 'array'],
            'product_id.*' => ['nullable', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'array'],
            'unit_price' => ['nullable', 'array'],
            'item_description' => ['nullable', 'array'],
        ]);

        $user = Auth::user();
        $tenantId = $this->tenantId($user);
        $storeId = (int) $request->get('store_id');
        $organizationId = $this->storeOrganizationId($storeId) ?: $this->organizationId($user);
        $date = $request->get('order_date_en') ?: now()->toDateString();
        $lines = $this->normalizedLines($request);

        $this->ensureStoreIsAllowed($storeId, $user);

        if (empty($lines)) {
            return back()->withErrors(['items' => 'برای ثبت سفارش خرید، حداقل یک قلم کالا با تعداد و مبلغ لازم است.'])->withInput();
        }

        $this->ensureProductsAreAllowed(array_column($lines, 'product_id'), $user, $organizationId);

        $purchaseOrder = DB::transaction(function () use ($request, $user, $tenantId, $organizationId, $date, $lines) {
            $purchaseOrder = PurchaseOrder::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'supplier_id' => $request->get('supplier_id'),
                'store_id' => $request->get('store_id'),
                'order_number' => $this->nextPurchaseOrderNumber($tenantId),
                'order_date_en' => $date,
                'order_date_fa' => $this->jalaliDate($date),
                'status' => 'draft',
                'total_amount' => array_sum(array_column($lines, 'total_amount')),
                'description' => $request->get('description'),
                'created_by' => $user?->id,
            ]);

            foreach ($lines as $line) {
                $purchaseOrder->items()->create(array_merge($line, [
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                ]));
            }

            return $purchaseOrder;
        });

        Alert::success('ثبت شد', 'سفارش خرید شماره ' . $purchaseOrder->order_number . ' ثبت شد.');

        return redirect()->route('purchase-orders.index');
    }

    public function requestApproval(PurchaseOrder $purchaseOrder, PurchaseApprovalService $purchaseApprovalService)
    {
        $this->authorizePurchaseOrderTenant($purchaseOrder);

        $purchaseApprovalService->requestApproval($purchaseOrder, Auth::user());

        Alert::success('ارسال شد', 'سفارش خرید برای تایید مدیریتی ارسال شد.');

        return redirect()->route('purchase-orders.index');
    }

    public function approveApproval(Request $request, PurchaseOrder $purchaseOrder, PurchaseApprovalService $purchaseApprovalService)
    {
        $this->authorizePurchaseOrderTenant($purchaseOrder);

        $payload = $request->validate([
            'approval_note' => ['nullable', 'string'],
        ]);

        $purchaseApprovalService->approve($purchaseOrder, $payload['approval_note'] ?? null, Auth::user());

        Alert::success('تایید شد', 'سفارش خرید تایید مدیریتی شد و آماده رسید انبار است.');

        return redirect()->route('purchase-orders.approvals');
    }

    public function rejectApproval(Request $request, PurchaseOrder $purchaseOrder, PurchaseApprovalService $purchaseApprovalService)
    {
        $this->authorizePurchaseOrderTenant($purchaseOrder);

        $payload = $request->validate([
            'approval_note' => ['nullable', 'string'],
        ]);

        $purchaseApprovalService->reject($purchaseOrder, $payload['approval_note'] ?? null, Auth::user());

        Alert::warning('برگشت خورد', 'سفارش خرید برای اصلاح به وضعیت پیش نویس برگشت.');

        return redirect()->route('purchase-orders.approvals');
    }

    public function approve(PurchaseOrder $purchaseOrder, PurchaseOrderService $purchaseOrderService)
    {
        $this->authorizePurchaseOrderTenant($purchaseOrder);

        if ($purchaseOrder->status === 'canceled') {
            Alert::warning('غیرقابل تایید', 'سفارش خرید ابطال شده قابل تایید نیست.');
            return back();
        }

        $purchaseOrderService->approve($purchaseOrder, Auth::user());

        Alert::success('تایید شد', 'رسید انبار و اسناد حسابداری خرید ساخته شدند.');

        return redirect()->route('purchase-orders.index');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder, PurchaseOrderService $purchaseOrderService)
    {
        $this->authorizePurchaseOrderTenant($purchaseOrder);

        $payload = $request->validate([
            'receive_date_en' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'purchase_order_item_id' => ['required', 'array'],
            'purchase_order_item_id.*' => ['nullable', 'integer'],
            'receive_quantity' => ['nullable', 'array'],
            'receive_quantity.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $purchaseOrderService->receive($purchaseOrder, $payload, Auth::user());

        Alert::success('رسید ثبت شد', 'دریافت مرحله ای سفارش خرید، موجودی و سند حسابداری آن ثبت شد.');

        return redirect()->route('purchase-orders.index');
    }

    public function storePurchaseInvoice(Request $request, PurchaseOrder $purchaseOrder, PurchaseInvoiceService $purchaseInvoiceService)
    {
        $this->authorizePurchaseOrderTenant($purchaseOrder);

        $payload = $request->validate([
            'supplier_invoice_number' => ['nullable', 'string', 'max:120'],
            'invoice_date_en' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'purchase_order_item_id' => ['required', 'array'],
            'purchase_order_item_id.*' => ['nullable', 'integer'],
            'invoice_quantity' => ['nullable', 'array'],
            'invoice_quantity.*' => ['nullable', 'numeric', 'min:0'],
            'invoice_unit_price' => ['nullable', 'array'],
            'invoice_unit_price.*' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'array'],
            'tax_amount.*' => ['nullable', 'numeric', 'min:0'],
            'item_description' => ['nullable', 'array'],
            'item_description.*' => ['nullable', 'string'],
        ]);

        $invoice = $purchaseInvoiceService->create($purchaseOrder, $payload, Auth::user());

        Alert::success('فاکتور خرید ثبت شد', 'فاکتور ' . $invoice->invoice_number . ' با سند حسابداری ' . optional($invoice->accountingVoucher)->voucher_number . ' ثبت شد.');

        return redirect()->route('purchase-orders.index');
    }

    public function pay(Request $request, PurchaseOrder $purchaseOrder, PurchaseOrderService $purchaseOrderService)
    {
        $this->authorizePurchaseOrderTenant($purchaseOrder);

        $payload = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'integer', Rule::in([1, 2, 3, 4])],
            'payment_date_en' => ['nullable', 'date'],
            'payment_terminal_id' => ['nullable', 'integer', 'exists:payment_terminals,id'],
            'issuing_bank' => ['nullable', 'string', 'max:120'],
            'cheque_number' => ['nullable', 'string', 'max:120'],
            'due_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $purchaseOrderService->paySupplier($purchaseOrder, $payload, Auth::user());

        Alert::success('پرداخت ثبت شد', 'پرداخت تامین کننده و سند حسابداری آن ثبت شد.');

        return redirect()->route('purchase-orders.index');
    }

    public function returnItems(Request $request, PurchaseOrder $purchaseOrder, PurchaseOrderService $purchaseOrderService)
    {
        $this->authorizePurchaseOrderTenant($purchaseOrder);

        $payload = $request->validate([
            'return_date_en' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'purchase_order_item_id' => ['required', 'array'],
            'purchase_order_item_id.*' => ['nullable', 'integer'],
            'return_quantity' => ['nullable', 'array'],
            'return_quantity.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $purchaseOrderService->returnItems($purchaseOrder, $payload, Auth::user());

        Alert::success('مرجوعی ثبت شد', 'مرجوعی خرید، خروج موجودی و سند حسابداری آن ثبت شد.');

        return redirect()->route('purchase-orders.index');
    }

    private function normalizedLines(Request $request): array
    {
        $productIds = $request->get('product_id', []);
        $quantities = $request->get('quantity', []);
        $unitPrices = $request->get('unit_price', []);
        $descriptions = $request->get('item_description', []);
        $lines = [];

        foreach ($productIds as $index => $productId) {
            $quantity = $this->money($quantities[$index] ?? 0);
            $unitPrice = $this->money($unitPrices[$index] ?? 0);

            if (!$productId || $quantity <= 0 || $unitPrice <= 0) {
                continue;
            }

            $lines[] = [
                'product_id' => (int) $productId,
                'quantity' => $quantity,
                'received_quantity' => 0,
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

    private function currencies($user)
    {
        $query = Currency::query()->where('isActive', 1)->orderByDesc('is_default')->orderBy('code');

        if ((int) $user->isGod !== 1) {
            $tenantId = $this->tenantId($user);
            $query->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            });
        }

        return $query->get();
    }

    private function foreignImportStatuses(): array
    {
        return [
            'draft' => 'پیش نویس',
            'ordered' => 'ثبت سفارش خارجی',
            'in_transit' => 'در مسیر',
            'customs' => 'گمرک',
            'cleared' => 'ترخیص شده',
            'received' => 'رسید انبار',
            'closed' => 'بسته شده',
            'canceled' => 'ابطال شده',
        ];
    }

    private function foreignImportCostTypes(): array
    {
        return [
            'freight' => 'حمل بین الملل',
            'insurance' => 'بیمه',
            'customs_duty' => 'حقوق و عوارض گمرکی',
            'clearance' => 'ترخیص و کارگزاری',
            'warehouse' => 'انبارداری و دموراژ',
            'bank_fee' => 'کارمزد بانکی/ارزی',
            'inspection' => 'بازرسی و استاندارد',
            'other' => 'سایر هزینه ها',
        ];
    }

    private function foreignImportAllocationBases(): array
    {
        return [
            'value' => 'بر اساس ارزش کالا',
            'quantity' => 'بر اساس تعداد',
            'manual' => 'سهم دستی ردیف ها',
        ];
    }

    private function foreignImportDocumentTypes(): array
    {
        return [
            'proforma' => 'پروفرما',
            'commercial_invoice' => 'سیاهه تجاری',
            'packing_list' => 'لیست عدل بندی',
            'bill_of_lading' => 'بارنامه',
            'customs_declaration' => 'اظهارنامه گمرکی',
            'clearance_permit' => 'مجوز ترخیص',
            'certificate' => 'گواهی/استاندارد',
            'other' => 'سایر اسناد',
        ];
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
        return round((float) str_replace(',', '', (string) $value), 2);
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

    private function authorizePurchaseOrderTenant(PurchaseOrder $purchaseOrder): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        if ((int) $purchaseOrder->tenant_id !== (int) $this->tenantId($user)) {
            abort(403);
        }
    }

    private function authorizeServiceInvoiceTenant(PurchaseServiceInvoice $invoice): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        if ((int) $invoice->tenant_id !== (int) $this->tenantId($user)) {
            abort(403);
        }
    }

    private function authorizePurchaseInvoiceTenant(PurchaseInvoice $invoice): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        if ((int) $invoice->tenant_id !== (int) $this->tenantId($user)) {
            abort(403);
        }
    }

    private function authorizeForeignImportTenant(ForeignPurchaseOrder $foreignPurchaseOrder): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        if ((int) $foreignPurchaseOrder->tenant_id !== (int) $this->tenantId($user)) {
            abort(403);
        }
    }
}

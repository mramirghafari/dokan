<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pishfactor;
use App\Models\PishFactorItems;
use App\Models\Product;
use App\Models\Customers;
use App\Models\Accounts;
use App\Models\Voucher;
use App\Models\VoucherItems;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\VoucherTemplate;
use App\Models\PaymentMethod;
use App\Models\PaymentTerminal;
use App\Models\TreasuryInstrument;
use App\Models\TreasuryChequeBook;
use App\Models\TreasuryChequeLeaf;
use App\Models\BankStatementLine;
use App\Models\CompanyAsset;
use App\Models\CompanyAssetAttachment;
use App\Models\CompanyAssetDepreciationPolicy;
use App\Models\CompanyAssetDisposal;
use App\Models\CompanyAssetEvent;
use App\Models\CompanyAssetTaxInvoice;
use App\Models\CostCenter;
use App\Models\ExpenseType;
use App\Models\FinancialAttachment;
use App\Models\FinancialPeriodClosing;
use App\Models\FiscalYear;
use App\Models\IncomeType;
use App\Models\OperationalExpense;
use App\Models\OperationalIncome;
use App\Models\PayrollAttendanceSummary;
use App\Models\PayrollContract;
use App\Models\PayrollRun;
use App\Models\PettyCashFund;
use App\Models\RevenueCenter;
use App\Models\Receipt;
use App\Models\Store;
use App\Models\User;
use App\Models\Employee;
use Hekmatinasser\Verta\Verta;
use App\Models\Depot;
use App\Services\AccountingPostingService;
use App\Services\AccountingAnalyticDimensionReportService;
use App\Services\AccountingDetailedLedgerService;
use App\Services\AccountingCurrencyReportService;
use App\Services\AccountingFinancialStatementService;
use App\Services\AccountingLedgerReportService;
use App\Services\AccountingPeriodClosingService;
use App\Services\OpeningVoucherService;
use App\Services\FixedAssetCapitalAdditionService;
use App\Services\FixedAssetDisposalService;
use App\Services\FixedAssetDepreciationService;
use App\Services\FixedAssetReportService;
use App\Services\FixedAssetTaxInvoiceService;
use App\Services\PayrollService;
use App\Services\PettyCashService;
use App\Services\TreasuryAlertService;
use App\Services\TreasuryChequeBookService;
use App\Services\TreasuryCashForecastService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;


class AccountingController extends Controller
{
    public function __construct()
    {
        $query = Voucher::with(['items.account', 'items.costCenter', 'items.branch', 'items.product', 'items.customer', 'items.employee', 'originalVoucher', 'reversalVoucher', 'mergedIntoVoucher'])->orderByDesc('id');
    }

    public function index(Request $request)
    {

        $Products = Product::all();

        $day = verta()->format('Y/m/d');

        if ($request->has('from_date') && $request->has('to_date')) {

            $fromDate = str_replace("/", "-", $request->get('from_date'));

            $jalaliFrom = explode("-", $fromDate);
            $miladiFrom = Verta::jalaliToGregorian($jalaliFrom[0], $jalaliFrom[1], $jalaliFrom[2]);
            $ymF = $miladiFrom[0];
            if (strlen($miladiFrom[1]) == 1) {
                $mmF = "0" . $miladiFrom[1];
            } else {
                $mmF = $miladiFrom[1];
            };
            if (strlen($miladiFrom[2]) == 1) {
                $dmF = "0" . $miladiFrom[2];
            } else {
                $dmF = $miladiFrom[2];
            };


            $toDate = str_replace("/", "-", $request->get('to_date'));

            $jalaliTo = explode("-", $toDate);
            $miladiTo = Verta::jalaliToGregorian($jalaliTo[0], $jalaliTo[1], $jalaliTo[2]);
            $ymT = $miladiTo[0];
            if (strlen($miladiTo[1]) == 1) {
                $mmT = "0" . $miladiTo[1];
            } else {
                $mmT = $miladiTo[1];
            };
            if (strlen($miladiTo[2]) == 1) {
                $dmT = "0" . $miladiTo[2];
            } else {
                $dmT = $miladiTo[2];
            };

            //$startDate = Carbon::createFromFormat('Y-m-d', $fromDate);
            //$endDate = Carbon::createFromFormat('Y-m-d', $toDate);

            $AllFactors = Pishfactor::whereIn('status', [1, 4])->orderBy('id', 'desc')->get();
            $PishFactors =  Pishfactor::where('status', 1)
                ->whereBetween('recive_date_en', ["$ymF-$mmF-$dmF", "$ymT-$mmT-$dmT"])
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $AllFactors = Pishfactor::whereIn('status', [1, 4])->orderBy('id', 'desc')->get();
            $PishFactors = Pishfactor::whereIn('status', [1, 4])->where('payment_type', 1)->orderBy('id', 'desc')->get();
            $Checki = Pishfactor::whereIn('status', [1, 4])->where('payment_type', 2)->orderBy('id', 'desc')->get();
            $unpayed = Pishfactor::whereIn('status', [1, 4])->whereNotIn('payment_type', [1, 2])->orderBy('id', 'desc')->get();

            $fromDate = null;
            $toDate = null;
        }



        return view('Accounting.Fund', compact('PishFactors', 'Checki', 'unpayed', 'AllFactors', 'fromDate', 'toDate', 'Products'));
    }

    public function AccountingReviews(Request $request)
    {
        $filters = [
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
        ];

        $review = $this->accountantReviewReport(Auth::user(), $filters);

        return view('Accounting.AccountingReviews', compact('review', 'filters'));
    }

    public function vouchers()
    {
        $user = Auth::user();
        $query = Voucher::with(['items.account', 'items.costCenter', 'items.revenueCenter', 'originalVoucher', 'reversalVoucher'])->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $user->tenant_id ?: $user->tenants_id);
        }

        $vouchers = $query->paginate(25);

        return view('Accounting.vouchers.index', compact('vouchers'));
    }

    public function voucherTemplates()
    {
        $user = Auth::user();
        $query = VoucherTemplate::with(['items.account', 'sourceVoucher'])->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->currentTenantId($user));
        }

        $templates = $query->paginate(25);

        return view('Accounting.voucher_templates.index', compact('templates'));
    }

    public function legalLedgers(Request $request, AccountingLedgerReportService $service)
    {
        $accounts = $this->accountingAccounts();
        $report = $service->build(Auth::user(), $request->only(['from_date', 'to_date', 'account_id', 'permanent_only']));

        return view('Accounting.reports.legal_ledgers', compact('accounts', 'report'));
    }

    public function financialStatements(Request $request, AccountingFinancialStatementService $service)
    {
        $fiscalYears = $this->fiscalYearsForCurrentUser();
        $selectedFiscalYear = $fiscalYears->firstWhere('id', (int) $request->get('fiscal_year_id'));
        $filters = $request->only(['from_date', 'to_date', 'permanent_only', 'revenue_center_id', 'profit_dimension']);

        if ($selectedFiscalYear) {
            $filters['from_date'] = $filters['from_date'] ?: optional($selectedFiscalYear->starts_at)->toDateString();
            $filters['to_date'] = $filters['to_date'] ?: optional($selectedFiscalYear->ends_at)->toDateString();
        }

        $report = $service->build(Auth::user(), $filters);
        $revenueCenters = $this->revenueCenterOptions();

        return view('Accounting.reports.financial_statements', compact('fiscalYears', 'selectedFiscalYear', 'report', 'revenueCenters'));
    }

    public function revenueCenters(Request $request)
    {
        $user = Auth::user();
        $query = RevenueCenter::with(['store', 'parent'])->orderBy('code')->orderBy('name');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->currentTenantId($user));
        }

        if ($request->filled('center_type')) {
            $query->where('center_type', $request->get('center_type'));
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', (int) $request->get('store_id'));
        }

        $centers = $query->paginate(25)->withQueryString();
        $stores = $this->accountingStores();
        $centerTypes = $this->revenueCenterTypes();
        $parentCenters = $this->revenueCenterOptions();

        return view('Accounting.revenue_centers.index', compact('centers', 'stores', 'centerTypes', 'parentCenters'));
    }

    public function storeRevenueCenter(Request $request)
    {
        $payload = $request->validate([
            'code' => ['nullable', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:180'],
            'center_type' => ['required', 'in:branch,store,product_group,sales_channel,salesperson,route,project,contract,customer_group,other'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'parent_id' => ['nullable', 'integer', 'exists:revenue_centers,id'],
            'manager_name' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $storeId = $payload['store_id'] ?? null;
        $organizationId = $storeId ? $this->storeOrganizationId((int) $storeId) : $this->currentOrganizationId($user);

        RevenueCenter::create(array_merge($payload, [
            'tenant_id' => $this->currentTenantId($user),
            'organization_id' => $organizationId,
            'is_active' => true,
            'created_by' => $user?->id,
        ]));

        Alert::success('ثبت شد', 'مرکز درآمد ثبت شد.');

        return redirect()->route('Accounting.revenueCenters');
    }

    public function incomes(Request $request)
    {
        $user = Auth::user();
        $query = OperationalIncome::with(['incomeType', 'revenueCenter.store', 'incomeAccount', 'receiptAccount', 'voucher', 'financialAttachments'])
            ->orderByDesc('income_date_en')
            ->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->currentTenantId($user));
        }

        if ($request->filled('income_type_id')) {
            $query->where('income_type_id', (int) $request->get('income_type_id'));
        }

        if ($request->filled('revenue_center_id')) {
            $query->where('revenue_center_id', (int) $request->get('revenue_center_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('income_date_en', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('income_date_en', '<=', $request->get('date_to'));
        }

        $incomeRows = (clone $query)->get();
        $incomes = $query->paginate(25)->withQueryString();
        $incomeTypes = $this->incomeTypes();
        $revenueCenters = $this->revenueCenterOptions();
        $accounts = $this->accountingAccounts();
        $stores = $this->accountingStores();
        $today = now()->toDateString();
        $totals = [
            'count' => $incomeRows->count(),
            'amount' => round((float) $incomeRows->sum('amount'), 2),
        ];
        $revenueCenterSummaries = $incomeRows
            ->groupBy(fn($income) => $income->revenue_center_id ?: 0)
            ->map(fn($items) => [
                'revenue_center' => optional($items->first()->revenueCenter)->name ?: 'بدون مرکز درآمد',
                'store' => optional($items->first()->revenueCenter?->store)->title ?: optional($items->first()->store)->title ?: '-',
                'count' => $items->count(),
                'total' => round((float) $items->sum('amount'), 2),
            ])
            ->sortByDesc('total')
            ->values();
        $incomeGroups = $this->incomeGroups();

        return view('Accounting.incomes.index', compact('incomes', 'incomeTypes', 'revenueCenters', 'accounts', 'stores', 'today', 'totals', 'revenueCenterSummaries', 'incomeGroups'));
    }

    public function storeIncomeType(Request $request)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'code' => ['nullable', 'string', 'max:60'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'income_group' => ['required', 'in:operational,service,rent,contract,commission,non_operational,other'],
            'description' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        IncomeType::create(array_merge($payload, [
            'tenant_id' => $this->currentTenantId($user),
            'organization_id' => $this->currentOrganizationId($user),
            'is_active' => true,
            'created_by' => $user?->id,
        ]));

        Alert::success('ثبت شد', 'نوع درآمد ثبت شد.');

        return redirect()->route('Accounting.incomes');
    }

    public function storeIncome(Request $request, AccountingPostingService $postingService)
    {
        $payload = $request->validate([
            'income_date_en' => ['nullable', 'date'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'revenue_center_id' => ['required', 'integer', 'exists:revenue_centers,id'],
            'income_type_id' => ['required', 'integer', 'exists:income_types,id'],
            'income_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'receipt_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'attachment_file' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt'],
            'attachment_note' => ['nullable', 'string', 'max:500'],
        ]);

        $user = Auth::user();
        $tenantId = $this->currentTenantId($user);
        $revenueCenter = RevenueCenter::findOrFail($payload['revenue_center_id']);
        $incomeType = IncomeType::findOrFail($payload['income_type_id']);

        if ((int) $user->isGod !== 1 && ((int) $revenueCenter->tenant_id !== (int) $tenantId || (int) $incomeType->tenant_id !== (int) $tenantId)) {
            abort(403);
        }

        $amount = $this->money($payload['amount']);
        $date = $payload['income_date_en'] ?: now()->toDateString();
        $storeId = $payload['store_id'] ?: $revenueCenter->store_id;
        $income = OperationalIncome::create([
            'tenant_id' => $tenantId,
            'organization_id' => $revenueCenter->organization_id ?: $this->currentOrganizationId($user),
            'store_id' => $storeId,
            'revenue_center_id' => $revenueCenter->id,
            'income_type_id' => $incomeType->id,
            'income_account_id' => $payload['income_account_id'] ?: $incomeType->account_id,
            'receipt_account_id' => $payload['receipt_account_id'],
            'income_number' => $this->nextIncomeNumber($tenantId),
            'income_date_en' => $date,
            'income_date_fa' => $this->jalaliDate($date),
            'status' => 'approved',
            'receipt_status' => 'registered',
            'amount' => $amount,
            'reference_number' => $payload['reference_number'] ?? null,
            'description' => $payload['description'] ?? null,
            'created_by' => $user?->id,
        ]);

        $voucher = $postingService->postOperationalIncomeVoucher($income, $user);
        $this->storeFinancialAttachment($request, $income, $voucher);

        Alert::success('ثبت شد', 'درآمد با شماره ' . $income->income_number . ' و سند حسابداری ' . $voucher->voucher_number . ' ثبت شد.');

        return redirect()->route('Accounting.incomes');
    }

    public function storeIncomeAttachment(Request $request, OperationalIncome $income)
    {
        $this->authorizeOperationalIncomeTenant($income);

        $request->validate([
            'attachment_file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt'],
            'attachment_note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->storeFinancialAttachment($request, $income, $income->voucher);

        Alert::success('ثبت شد', 'مدرک درآمد اضافه شد.');

        return redirect()->route('Accounting.incomes');
    }

    public function detailedLedgers(Request $request, AccountingDetailedLedgerService $service)
    {
        $report = $service->build(Auth::user(), $request->only(['from_date', 'to_date', 'permanent_only', 'level']));

        return view('Accounting.reports.detailed_ledgers', compact('report'));
    }

    public function analyticDimensions(Request $request, AccountingAnalyticDimensionReportService $service)
    {
        $report = $service->build(Auth::user(), $request->only(['from_date', 'to_date', 'dimension', 'permanent_only']));

        return view('Accounting.reports.analytic_dimensions', compact('report'));
    }

    public function currencyBalances(Request $request, AccountingCurrencyReportService $service)
    {
        $report = $service->build(Auth::user(), $request->all());

        return view('Accounting.reports.currency_balances', compact('report'));
    }

    public function companyAssetReport(Request $request, FixedAssetReportService $service)
    {
        $report = $service->build(Auth::user(), $request->only(['date_basis', 'from_date', 'to_date', 'status', 'asset_category', 'cost_center_id', 'store_id', 'custodian_employee_id', 'q']));
        $assetCategories = $this->companyAssetCategories();
        $assetStatuses = $this->companyAssetStatuses();
        $costCenters = $this->costCenters();
        $stores = $this->accountingStores();
        $employees = $this->payrollEmployees();

        return view('Accounting.company_assets.report', compact('report', 'assetCategories', 'assetStatuses', 'costCenters', 'stores', 'employees'));
    }

    public function storeCurrency(Request $request)
    {
        $payload = $request->validate([
            'code' => ['required', 'string', 'max:10'],
            'title' => ['required', 'string', 'max:191'],
            'symbol' => ['nullable', 'string', 'max:20'],
            'decimal_places' => ['nullable', 'integer', 'min:0', 'max:6'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();
        $tenantId = $this->currentTenantId($user);
        $code = strtoupper(trim($payload['code']));

        Currency::updateOrCreate(
            ['tenant_id' => $tenantId, 'code' => $code],
            [
                'title' => $payload['title'],
                'symbol' => $payload['symbol'] ?? $code,
                'decimal_places' => $payload['decimal_places'] ?? 2,
                'is_default' => (bool) ($payload['is_default'] ?? false),
                'isActive' => true,
            ]
        );

        Alert::success('ثبت شد', 'ارز ' . $code . ' ذخیره شد.');

        return redirect()->route('Accounting.currencyBalances');
    }

    public function storeExchangeRate(Request $request)
    {
        $payload = $request->validate([
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            'rate_date' => ['required', 'date'],
            'rate' => ['required', 'numeric', 'min:0.000001'],
            'source' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        $tenantId = $this->currentTenantId($user);

        ExchangeRate::updateOrCreate(
            ['tenant_id' => $tenantId, 'currency_id' => $payload['currency_id'], 'rate_date' => $payload['rate_date']],
            [
                'rate' => $payload['rate'],
                'source' => $payload['source'] ?? 'manual',
                'description' => $payload['description'] ?? null,
                'created_by' => $user?->id,
            ]
        );

        Alert::success('ثبت شد', 'نرخ ارز برای تاریخ انتخاب شده ذخیره شد.');

        return redirect()->route('Accounting.currencyBalances', ['currency_id' => $payload['currency_id'], 'to_date' => $payload['rate_date']]);
    }

    public function fiscalClosing(Request $request, AccountingPeriodClosingService $service)
    {
        $user = Auth::user();
        $fiscalYears = $this->fiscalYearsForCurrentUser();
        $selectedFiscalYear = $fiscalYears->firstWhere('id', (int) $request->get('fiscal_year_id')) ?: $fiscalYears->first();
        $preview = $selectedFiscalYear ? $service->preview($selectedFiscalYear, $user) : null;
        $closings = FinancialPeriodClosing::with(['fiscalYear', 'nextFiscalYear', 'closingVoucher', 'openingVoucher'])
            ->when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $this->currentTenantId($user)))
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('Accounting.reports.fiscal_closing', compact('fiscalYears', 'selectedFiscalYear', 'preview', 'closings'));
    }

    public function closeFiscalYear(FiscalYear $fiscalYear, AccountingPeriodClosingService $service)
    {
        $closing = $service->close($fiscalYear, Auth::user());

        Alert::success('دوره مالی بسته شد', 'سند اختتامیه و افتتاحیه با موفقیت ایجاد شد.');

        return redirect()->route('Accounting.fiscalClosing', ['fiscal_year_id' => $closing->next_fiscal_year_id]);
    }

    public function expenses(Request $request)
    {
        $user = Auth::user();
        $query = OperationalExpense::with(['expenseType', 'costCenter.store', 'expenseAccount', 'settlementAccount', 'voucher', 'product', 'approver', 'financialAttachments', 'allocations.product'])
            ->orderByDesc('expense_date_en')
            ->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->currentTenantId($user));
        }

        if ($request->filled('expense_type_id')) {
            $query->where('expense_type_id', (int) $request->get('expense_type_id'));
        }

        if ($request->filled('cost_center_id')) {
            $query->where('cost_center_id', (int) $request->get('cost_center_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date_en', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date_en', '<=', $request->get('date_to'));
        }

        if ($request->filled('specialized_kind')) {
            $query->where('specialized_kind', $request->get('specialized_kind'));
        }

        if ($request->filled('workflow_status')) {
            $query->where('workflow_status', $request->get('workflow_status'));
        }

        $expenseRows = $query->get();
        $expenses = $query->paginate(25)->withQueryString();
        $costCenters = $this->costCenters();
        $expenseTypes = $this->expenseTypes();
        $accounts = $this->accountingAccounts();
        $stores = $this->accountingStores();
        $today = now()->toDateString();
        $totals = [
            'count' => $expenseRows->count(),
            'amount' => round((float) $expenseRows->sum('amount'), 2),
            'tax' => round((float) $expenseRows->sum('tax_amount'), 2),
            'total' => round((float) $expenseRows->sum('total_amount'), 2),
        ];
        $costCenterSummaries = $expenseRows
            ->groupBy(fn($expense) => $expense->cost_center_id ?: 0)
            ->map(fn($items) => [
                'cost_center' => optional($items->first()->costCenter)->name ?: 'بدون مرکز هزینه',

                'store' => optional($items->first()->costCenter?->store)->title ?: optional($items->first()->store)->title ?: '-',
                'count' => $items->count(),
                'total' => round((float) $items->sum('total_amount'), 2),
            ])
            ->sortByDesc('total')
            ->values();
        $specializedSummaries = $expenseRows
            ->whereNotNull('specialized_kind')
            ->groupBy(fn($expense) => $expense->specialized_kind ?: 'standard')
            ->map(fn($items, $kind) => [
                'kind' => $kind,
                'label' => $this->specializedExpenseKinds()[$kind] ?? $kind,
                'count' => $items->count(),
                'pending' => $items->where('workflow_status', 'pending_approval')->count(),
                'approved' => $items->where('workflow_status', 'approved')->count(),
                'total' => round((float) $items->sum('total_amount'), 2),
            ])
            ->sortByDesc('total')
            ->values();
        $allocationSummaries = $expenseRows
            ->flatMap(fn($expense) => $expense->allocations)
            ->groupBy(fn($allocation) => ($allocation->allocation_target_type ?: $allocation->target_type ?: 'manual') . ':' . ($allocation->allocation_target_id ?: $allocation->target_id ?: $allocation->product_id ?: $allocation->project_code ?: $allocation->contract_code ?: 'manual'))
            ->map(fn($items) => [
                'target' => $this->expenseAllocationTargetLabel($items->first()),
                'basis' => $items->first()->allocation_basis ?: 'direct',
                'count' => $items->count(),
                'total' => round((float) $items->sum(fn($allocation) => (float) ($allocation->allocated_amount ?: $allocation->amount)), 2),
            ])
            ->sortByDesc('total')
            ->values();
        $specializedKinds = $this->specializedExpenseKinds();

        return view('Accounting.expenses.index', compact('expenses', 'costCenters', 'expenseTypes', 'accounts', 'stores', 'today', 'totals', 'costCenterSummaries', 'specializedSummaries', 'allocationSummaries', 'specializedKinds'));
    }

    public function storeCostCenter(Request $request)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'code' => ['nullable', 'string', 'max:60'],
            'center_type' => ['required', 'in:branch,unit,department,warehouse,route,project,production_line,other'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'allocation_basis' => ['nullable', 'in:amount,quantity,weight,time,equal,manual'],
            'description' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $storeId = $payload['store_id'] ?? null;
        $organizationId = $storeId ? $this->storeOrganizationId((int) $storeId) : $this->currentOrganizationId($user);

        CostCenter::create(array_merge($payload, [
            'tenant_id' => $this->currentTenantId($user),
            'organization_id' => $organizationId,
            'is_active' => true,
            'created_by' => $user?->id,
        ]));

        Alert::success('ثبت شد', 'مرکز هزینه ثبت شد.');

        return redirect()->route('Accounting.expenses');
    }

    public function storeExpenseType(Request $request)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'code' => ['nullable', 'string', 'max:60'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'expense_group' => ['required', 'in:operational,production,purchase,distribution,administrative,financial,import,asset,other'],
            'cost_behavior' => ['required', 'in:direct,indirect,overhead,initial,variable,fixed,mixed'],
            'workflow_type' => ['nullable', 'in:standard,insurance,customs,waste,commission,production_payroll,depreciation'],
            'requires_approval' => ['nullable', 'boolean'],
            'capitalization_policy' => ['nullable', 'in:expense,landed_cost,production_overhead,asset_cost,commission_payable'],
            'description' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        ExpenseType::create(array_merge($payload, [
            'tenant_id' => $this->currentTenantId($user),
            'organization_id' => $this->currentOrganizationId($user),
            'workflow_type' => $payload['workflow_type'] ?? 'standard',
            'requires_approval' => (bool) ($payload['requires_approval'] ?? false),
            'capitalization_policy' => $payload['capitalization_policy'] ?? 'expense',
            'is_active' => true,
            'created_by' => $user?->id,
        ]));

        Alert::success('ثبت شد', 'نوع هزینه ثبت شد.');

        return redirect()->route('Accounting.expenses');
    }

    public function storeSpecializedExpense(Request $request)
    {
        $payload = $request->validate([
            'expense_date_en' => ['nullable', 'date'],
            'specialized_kind' => ['required', 'in:insurance,customs,waste,commission,production_payroll,depreciation'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'cost_center_id' => ['required', 'integer', 'exists:cost_centers,id'],
            'expense_type_id' => ['required', 'integer', 'exists:expense_types,id'],
            'expense_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'settlement_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'allocation_target_type' => ['nullable', 'in:product,order,project,contract,cost_center,asset,manual'],
            'allocation_target_id' => ['nullable', 'integer'],
            'allocation_basis' => ['nullable', 'in:direct,manual,amount,quantity,weight,time,equal'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'project_code' => ['nullable', 'string', 'max:100'],
            'contract_code' => ['nullable', 'string', 'max:120'],
            'allocation_note' => ['nullable', 'string', 'max:500'],
            'workflow_note' => ['nullable', 'string'],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'attachment_file' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt'],
            'attachment_note' => ['nullable', 'string', 'max:500'],
        ]);

        $user = Auth::user();
        $tenantId = $this->currentTenantId($user);
        $costCenter = CostCenter::findOrFail($payload['cost_center_id']);
        $expenseType = ExpenseType::findOrFail($payload['expense_type_id']);

        if ((int) $user->isGod !== 1 && ((int) $costCenter->tenant_id !== (int) $tenantId || (int) $expenseType->tenant_id !== (int) $tenantId)) {
            abort(403);
        }

        $amount = $this->money($payload['amount']);
        $taxAmount = $this->money($payload['tax_amount'] ?? 0);
        $date = $payload['expense_date_en'] ?: now()->toDateString();
        $storeId = $payload['store_id'] ?: $costCenter->store_id;

        $expense = OperationalExpense::create([
            'tenant_id' => $tenantId,
            'organization_id' => $costCenter->organization_id ?: $this->currentOrganizationId($user),
            'store_id' => $storeId,
            'cost_center_id' => $costCenter->id,
            'expense_type_id' => $expenseType->id,
            'specialized_kind' => $payload['specialized_kind'],
            'expense_account_id' => $payload['expense_account_id'] ?: $expenseType->account_id,
            'settlement_account_id' => $payload['settlement_account_id'],
            'expense_number' => $this->nextExpenseNumber($tenantId),
            'expense_date_en' => $date,
            'expense_date_fa' => $this->jalaliDate($date),
            'status' => 'pending_approval',
            'workflow_status' => 'pending_approval',
            'payment_status' => 'workflow_pending',
            'allocation_target_type' => $payload['allocation_target_type'] ?? null,
            'allocation_target_id' => $payload['allocation_target_id'] ?? null,
            'allocation_basis' => $payload['allocation_basis'] ?? ($costCenter->allocation_basis ?: 'direct'),
            'product_id' => $payload['product_id'] ?? null,
            'project_code' => $payload['project_code'] ?? null,
            'contract_code' => $payload['contract_code'] ?? null,
            'allocation_note' => $payload['allocation_note'] ?? null,
            'workflow_note' => $payload['workflow_note'] ?? null,
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total_amount' => round($amount + $taxAmount, 2),
            'reference_number' => $payload['reference_number'] ?? null,
            'description' => $payload['description'] ?? ($this->specializedExpenseKinds()[$payload['specialized_kind']] ?? 'هزینه اختصاصی'),
            'created_by' => $user?->id,
        ]);
        $this->storeFinancialAttachment($request, $expense);

        Alert::success('در انتظار تایید', 'هزینه اختصاصی ' . $expense->expense_number . ' ثبت شد و برای تایید ارسال شد.');

        return redirect()->route('Accounting.expenses', ['workflow_status' => 'pending_approval']);
    }

    public function approveSpecializedExpense(OperationalExpense $expense, AccountingPostingService $postingService)
    {
        $this->authorizeOperationalExpenseTenant($expense);

        if ($expense->workflow_status !== 'pending_approval') {
            Alert::info('بدون تغییر', 'این هزینه در وضعیت قابل تایید نیست.');
            return redirect()->route('Accounting.expenses');
        }

        $voucher = $postingService->postOperationalExpenseVoucher($expense, Auth::user());
        $expense->update([
            'status' => 'approved',
            'workflow_status' => 'approved',
            'payment_status' => 'registered',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
        $expense->financialAttachments()->whereNull('voucher_id')->update([
            'voucher_id' => $voucher->id,
            'updated_by' => Auth::id(),
        ]);

        Alert::success('تایید شد', 'هزینه اختصاصی تایید شد و سند ' . $voucher->voucher_number . ' صادر شد.');

        return redirect()->route('Accounting.expenses');
    }

    public function rejectSpecializedExpense(Request $request, OperationalExpense $expense)
    {
        $this->authorizeOperationalExpenseTenant($expense);

        $payload = $request->validate([
            'workflow_note' => ['nullable', 'string'],
        ]);

        if ($expense->workflow_status !== 'pending_approval') {
            Alert::info('بدون تغییر', 'این هزینه در وضعیت قابل رد نیست.');
            return redirect()->route('Accounting.expenses');
        }

        $expense->update([
            'status' => 'rejected',
            'workflow_status' => 'rejected',
            'payment_status' => 'rejected',
            'workflow_note' => $payload['workflow_note'] ?? $expense->workflow_note,
            'rejected_at' => now(),
            'rejected_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        Alert::warning('رد شد', 'هزینه اختصاصی بدون حذف داده رد شد.');

        return redirect()->route('Accounting.expenses', ['workflow_status' => 'rejected']);
    }

    public function storeExpense(Request $request, AccountingPostingService $postingService)
    {
        $payload = $request->validate([
            'expense_date_en' => ['nullable', 'date'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'cost_center_id' => ['required', 'integer', 'exists:cost_centers,id'],
            'expense_type_id' => ['required', 'integer', 'exists:expense_types,id'],
            'expense_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'settlement_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'allocation_target_type' => ['nullable', 'in:product,order,project,contract,cost_center,asset,manual'],
            'allocation_target_id' => ['nullable', 'integer'],
            'allocation_basis' => ['nullable', 'in:direct,manual,amount,quantity,weight,time,equal'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'project_code' => ['nullable', 'string', 'max:100'],
            'contract_code' => ['nullable', 'string', 'max:120'],
            'allocation_note' => ['nullable', 'string', 'max:500'],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'attachment_file' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt'],
            'attachment_note' => ['nullable', 'string', 'max:500'],
        ]);

        $user = Auth::user();
        $tenantId = $this->currentTenantId($user);
        $costCenter = CostCenter::findOrFail($payload['cost_center_id']);
        $expenseType = ExpenseType::findOrFail($payload['expense_type_id']);

        if ((int) $user->isGod !== 1 && ((int) $costCenter->tenant_id !== (int) $tenantId || (int) $expenseType->tenant_id !== (int) $tenantId)) {
            abort(403);
        }

        $amount = $this->money($payload['amount']);
        $taxAmount = $this->money($payload['tax_amount'] ?? 0);
        $date = $payload['expense_date_en'] ?: now()->toDateString();
        $storeId = $payload['store_id'] ?: $costCenter->store_id;
        $expense = OperationalExpense::create([
            'tenant_id' => $tenantId,
            'organization_id' => $costCenter->organization_id ?: $this->currentOrganizationId($user),
            'store_id' => $storeId,
            'cost_center_id' => $costCenter->id,
            'expense_type_id' => $expenseType->id,
            'expense_account_id' => $payload['expense_account_id'] ?: $expenseType->account_id,
            'settlement_account_id' => $payload['settlement_account_id'],
            'expense_number' => $this->nextExpenseNumber($tenantId),
            'expense_date_en' => $date,
            'expense_date_fa' => $this->jalaliDate($date),
            'status' => 'approved',
            'payment_status' => 'registered',
            'allocation_target_type' => $payload['allocation_target_type'] ?? null,
            'allocation_target_id' => $payload['allocation_target_id'] ?? null,
            'allocation_basis' => $payload['allocation_basis'] ?? ($costCenter->allocation_basis ?: 'direct'),
            'product_id' => $payload['product_id'] ?? null,
            'project_code' => $payload['project_code'] ?? null,
            'contract_code' => $payload['contract_code'] ?? null,
            'allocation_note' => $payload['allocation_note'] ?? null,
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total_amount' => round($amount + $taxAmount, 2),
            'reference_number' => $payload['reference_number'] ?? null,
            'description' => $payload['description'] ?? null,
            'created_by' => $user?->id,
        ]);

        $voucher = $postingService->postOperationalExpenseVoucher($expense, $user);
        $this->storeFinancialAttachment($request, $expense, $voucher);

        Alert::success('ثبت شد', 'هزینه با شماره ' . $expense->expense_number . ' و سند حسابداری ' . $voucher->voucher_number . ' ثبت شد.');

        return redirect()->route('Accounting.expenses');
    }

    public function storeExpenseAttachment(Request $request, OperationalExpense $expense)
    {
        $this->authorizeOperationalExpenseTenant($expense);

        $request->validate([
            'attachment_file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt'],
            'attachment_note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->storeFinancialAttachment($request, $expense, $expense->voucher);

        Alert::success('ثبت شد', 'مدرک هزینه اضافه شد.');

        return redirect()->route('Accounting.expenses');
    }

    public function companyAssets(Request $request)
    {
        $user = Auth::user();
        $query = CompanyAsset::with([
            'store',
            'costCenter',
            'custodian',
            'assetAccount',
            'attachments' => fn($query) => $query->orderByDesc('id')->limit(5),
            'events.fromStore',
            'events.toStore',
            'events.fromEmployee',
            'events.toEmployee',
            'depreciations.voucher',
            'disposals.voucher',
            'disposals.taxInvoice',
            'capitalAdditions.voucher',
            'capitalAdditions' => fn($query) => $query->orderByDesc('addition_date_en')->orderByDesc('id'),
            'depreciationPolicies' => fn($query) => $query->orderByDesc('effective_date_en')->orderByDesc('id'),
        ])->withCount(['attachments', 'events', 'disposals', 'capitalAdditions'])
            ->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->currentTenantId($user));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('asset_category')) {
            $query->where('asset_category', $request->get('asset_category'));
        }

        if ($request->filled('cost_center_id')) {
            $query->where('cost_center_id', (int) $request->get('cost_center_id'));
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', (int) $request->get('store_id'));
        }

        if ($request->filled('q')) {
            $search = trim((string) $request->get('q'));
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('asset_code', 'like', '%' . $search . '%')
                    ->orWhere('plaque_number', 'like', '%' . $search . '%')
                    ->orWhere('serial_number', 'like', '%' . $search . '%');
            });
        }

        $assetRows = (clone $query)->get();
        $assets = $query->paginate(25)->withQueryString();
        $assetCategories = $this->companyAssetCategories();
        $assetStatuses = $this->companyAssetStatuses();
        $assetAttachmentTypes = $this->companyAssetAttachmentTypes();
        $assetEventTypes = $this->companyAssetEventTypes();
        $assetCapitalAdditionTypes = $this->companyAssetCapitalAdditionTypes();
        $assetTaxInvoiceStatuses = $this->companyAssetTaxInvoiceStatuses();
        $costCenters = $this->costCenters();
        $stores = $this->accountingStores();
        $employees = $this->payrollEmployees();
        $accounts = $this->accountingAccounts();
        $today = now()->toDateString();
        $depreciationFrom = $request->get('depreciation_from') ?: now()->startOfMonth()->toDateString();
        $depreciationTo = $request->get('depreciation_to') ?: now()->endOfMonth()->toDateString();
        $depreciationPreview = app(FixedAssetDepreciationService::class)->preview($assetRows, $depreciationFrom, $depreciationTo);
        $depreciationSummary = [
            'count' => $depreciationPreview->count(),
            'amount' => round((float) $depreciationPreview->sum('period_amount'), 2),
        ];
        $totals = [
            'count' => $assetRows->count(),
            'active' => $assetRows->where('status', 'active')->count(),
            'cost' => round((float) $assetRows->sum('acquisition_cost'), 2),
            'depreciation' => round((float) $assetRows->sum('accumulated_depreciation'), 2),
            'book_value' => round((float) $assetRows->sum(fn($asset) => $asset->bookValue()), 2),
        ];

        return view('Accounting.company_assets.index', compact('assets', 'assetCategories', 'assetStatuses', 'assetAttachmentTypes', 'assetEventTypes', 'assetCapitalAdditionTypes', 'assetTaxInvoiceStatuses', 'costCenters', 'stores', 'employees', 'accounts', 'today', 'totals', 'depreciationFrom', 'depreciationTo', 'depreciationPreview', 'depreciationSummary'));
    }

    public function storeCompanyAsset(Request $request)
    {
        $payload = $request->validate([
            'asset_code' => ['nullable', 'string', 'max:80'],
            'plaque_number' => ['nullable', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:180'],
            'asset_category' => ['required', 'in:building,vehicle,machinery,office_equipment,computer,furniture,tool,other'],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'cost_center_id' => ['nullable', 'integer', 'exists:cost_centers,id'],
            'custodian_employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'location' => ['nullable', 'string', 'max:180'],
            'acquisition_date_en' => ['nullable', 'date'],
            'in_service_date_en' => ['nullable', 'date'],
            'acquisition_cost' => ['required', 'numeric', 'min:0'],
            'salvage_value' => ['nullable', 'numeric', 'min:0'],
            'useful_life_months' => ['nullable', 'integer', 'min:1', 'max:1200'],
            'depreciation_method' => ['nullable', 'in:straight_line,none'],
            'accumulated_depreciation' => ['nullable', 'numeric', 'min:0'],
            'asset_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'accumulated_depreciation_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'depreciation_expense_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'status' => ['nullable', 'in:active,idle,under_repair,sold,scrapped'],
            'description' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $tenantId = $this->currentTenantId($user);
        $storeId = $payload['store_id'] ?? null;
        $organizationId = $storeId ? $this->storeOrganizationId((int) $storeId) : $this->currentOrganizationId($user);
        $acquisitionDate = $payload['acquisition_date_en'] ?? now()->toDateString();
        $inServiceDate = $payload['in_service_date_en'] ?? $acquisitionDate;
        $acquisitionCost = $this->money($payload['acquisition_cost']);
        $accumulatedDepreciation = min($this->money($payload['accumulated_depreciation'] ?? 0), $acquisitionCost);

        $asset = CompanyAsset::create([
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'store_id' => $storeId,
            'cost_center_id' => $payload['cost_center_id'] ?? null,
            'custodian_employee_id' => $payload['custodian_employee_id'] ?? null,
            'asset_account_id' => $payload['asset_account_id'] ?? null,
            'accumulated_depreciation_account_id' => $payload['accumulated_depreciation_account_id'] ?? null,
            'depreciation_expense_account_id' => $payload['depreciation_expense_account_id'] ?? null,
            'asset_code' => $payload['asset_code'] ?: $this->nextCompanyAssetCode($tenantId),
            'plaque_number' => $payload['plaque_number'] ?? null,
            'name' => $payload['name'],
            'asset_category' => $payload['asset_category'],
            'serial_number' => $payload['serial_number'] ?? null,
            'location' => $payload['location'] ?? null,
            'acquisition_date_en' => $acquisitionDate,
            'acquisition_date_fa' => $this->jalaliDate($acquisitionDate),
            'in_service_date_en' => $inServiceDate,
            'in_service_date_fa' => $this->jalaliDate($inServiceDate),
            'acquisition_cost' => $acquisitionCost,
            'salvage_value' => $this->money($payload['salvage_value'] ?? 0),
            'useful_life_months' => $payload['useful_life_months'] ?? null,
            'depreciation_method' => $payload['depreciation_method'] ?? 'straight_line',
            'accumulated_depreciation' => $accumulatedDepreciation,
            'status' => $payload['status'] ?? 'active',
            'description' => $payload['description'] ?? null,
            'created_by' => $user?->id,
        ]);

        Alert::success('ثبت شد', 'اموال شرکت با پلاک ' . ($asset->asset_code ?: $asset->id) . ' ثبت شد.');

        return redirect()->route('Accounting.companyAssets');
    }

    public function storeCompanyAssetAttachment(Request $request, CompanyAsset $companyAsset)
    {
        $this->authorizeCompanyAssetTenant($companyAsset);

        $payload = $request->validate([
            'attachment_file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt'],
            'attachment_type' => ['nullable', 'in:purchase_invoice,image,insurance,warranty,repair,contract,other'],
            'attachment_note' => ['nullable', 'string', 'max:500'],
        ]);

        $file = $request->file('attachment_file');
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $path = $file->storeAs(
            'company-assets/' . $companyAsset->id . '/' . now()->format('Y/m'),
            (string) Str::uuid() . '.' . $extension,
            'public'
        );

        CompanyAssetAttachment::create([
            'company_asset_id' => $companyAsset->id,
            'tenant_id' => $companyAsset->tenant_id,
            'organization_id' => $companyAsset->organization_id,
            'attachment_type' => $payload['attachment_type'] ?? 'document',
            'disk' => 'public',
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'note' => $payload['attachment_note'] ?? null,
            'created_by' => Auth::id(),
        ]);

        Alert::success('ثبت شد', 'مدرک دارایی به دفتر اموال اضافه شد.');

        return redirect()->route('Accounting.companyAssets', $request->only(['status', 'asset_category', 'cost_center_id', 'store_id', 'q']));
    }

    public function storeCompanyAssetEvent(Request $request, CompanyAsset $companyAsset)
    {
        $this->authorizeCompanyAssetTenant($companyAsset);

        $payload = $request->validate([
            'event_type' => ['required', 'in:registration,custody,transfer,repair,maintenance,insurance,valuation,status_change,sale,scrap,other'],
            'event_date_en' => ['nullable', 'date'],
            'title' => ['nullable', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'to_store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'to_employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'status_after' => ['nullable', 'in:active,idle,under_repair,sold,scrapped'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $eventDate = $payload['event_date_en'] ?? now()->toDateString();
        $eventTypes = $this->companyAssetEventTypes();

        DB::transaction(function () use ($companyAsset, $payload, $eventDate, $eventTypes) {
            CompanyAssetEvent::create([
                'company_asset_id' => $companyAsset->id,
                'tenant_id' => $companyAsset->tenant_id,
                'organization_id' => $companyAsset->organization_id,
                'event_type' => $payload['event_type'],
                'event_date_en' => $eventDate,
                'event_date_fa' => $this->jalaliDate($eventDate),
                'from_store_id' => $companyAsset->store_id,
                'to_store_id' => $payload['to_store_id'] ?? null,
                'from_employee_id' => $companyAsset->custodian_employee_id,
                'to_employee_id' => $payload['to_employee_id'] ?? null,
                'status_before' => $companyAsset->status,
                'status_after' => $payload['status_after'] ?? null,
                'amount' => isset($payload['amount']) ? $this->money($payload['amount']) : null,
                'title' => $payload['title'] ?: ($eventTypes[$payload['event_type']] ?? 'رخداد دارایی'),
                'description' => $payload['description'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $updates = ['updated_by' => Auth::id()];

            if (!empty($payload['to_store_id'])) {
                $updates['store_id'] = (int) $payload['to_store_id'];
                $updates['organization_id'] = $this->storeOrganizationId((int) $payload['to_store_id']) ?: $companyAsset->organization_id;
            }

            if (!empty($payload['to_employee_id'])) {
                $updates['custodian_employee_id'] = (int) $payload['to_employee_id'];
            }

            if (!empty($payload['status_after'])) {
                $updates['status'] = $payload['status_after'];
            }

            if (count($updates) > 1) {
                $companyAsset->update($updates);
            }
        });

        Alert::success('ثبت شد', 'رخداد عملیاتی دارایی ثبت شد.');

        return redirect()->route('Accounting.companyAssets', $request->only(['status', 'asset_category', 'cost_center_id', 'store_id', 'q']));
    }

    public function postCompanyAssetDepreciation(Request $request, FixedAssetDepreciationService $depreciationService)
    {
        $payload = $request->validate([
            'depreciation_from' => ['required', 'date'],
            'depreciation_to' => ['required', 'date', 'after_or_equal:depreciation_from'],
            'status' => ['nullable', 'string'],
            'asset_category' => ['nullable', 'string'],
            'cost_center_id' => ['nullable', 'integer'],
            'store_id' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $assets = $this->companyAssetDepreciationQuery($request)->get();
        $result = $depreciationService->post($assets, $payload['depreciation_from'], $payload['depreciation_to'], Auth::user());

        Alert::success('ثبت شد', 'برای ' . number_format($result['posted']) . ' دارایی سند استهلاک به مبلغ ' . number_format($result['total']) . ' ثبت شد.');

        return redirect()->route('Accounting.companyAssets', $request->only(['status', 'asset_category', 'cost_center_id', 'store_id', 'q', 'depreciation_from', 'depreciation_to']));
    }

    public function storeCompanyAssetDepreciationPolicy(Request $request, CompanyAsset $companyAsset)
    {
        $this->authorizeCompanyAssetTenant($companyAsset);

        $payload = $request->validate([
            'effective_date_en' => ['required', 'date'],
            'depreciation_method' => ['required', 'in:straight_line,rate_percent,none'],
            'useful_life_months' => ['nullable', 'integer', 'min:1', 'max:1200', 'required_if:depreciation_method,straight_line'],
            'salvage_value' => ['nullable', 'numeric', 'min:0'],
            'annual_rate_percent' => ['nullable', 'numeric', 'min:0.0001', 'max:100', 'required_if:depreciation_method,rate_percent'],
            'accumulated_depreciation_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'depreciation_expense_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'reason' => ['nullable', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($companyAsset, $payload) {
            $policy = CompanyAssetDepreciationPolicy::updateOrCreate(
                [
                    'company_asset_id' => $companyAsset->id,
                    'effective_date_en' => $payload['effective_date_en'],
                ],
                [
                    'tenant_id' => $companyAsset->tenant_id,
                    'organization_id' => $companyAsset->organization_id,
                    'effective_date_fa' => $this->jalaliDate($payload['effective_date_en']),
                    'depreciation_method' => $payload['depreciation_method'],
                    'useful_life_months' => $payload['useful_life_months'] ?? null,
                    'salvage_value' => $this->money($payload['salvage_value'] ?? $companyAsset->salvage_value),
                    'annual_rate_percent' => $payload['annual_rate_percent'] ?? null,
                    'accumulated_depreciation_account_id' => $payload['accumulated_depreciation_account_id'] ?? $companyAsset->accumulated_depreciation_account_id,
                    'depreciation_expense_account_id' => $payload['depreciation_expense_account_id'] ?? $companyAsset->depreciation_expense_account_id,
                    'reason' => $payload['reason'] ?? null,
                    'description' => $payload['description'] ?? null,
                    'created_by' => Auth::id(),
                ]
            );

            CompanyAssetEvent::create([
                'company_asset_id' => $companyAsset->id,
                'tenant_id' => $companyAsset->tenant_id,
                'organization_id' => $companyAsset->organization_id,
                'event_type' => 'valuation',
                'event_date_en' => $payload['effective_date_en'],
                'event_date_fa' => $policy->effective_date_fa,
                'status_before' => $companyAsset->status,
                'status_after' => $companyAsset->status,
                'title' => 'تغییر سیاست استهلاک',
                'description' => trim(($payload['reason'] ?? '') . ' ' . ($payload['description'] ?? '')) ?: 'ثبت سیاست استهلاک با تاریخ موثر',
                'created_by' => Auth::id(),
            ]);
        });

        Alert::success('ثبت شد', 'سیاست استهلاک دارایی از تاریخ موثر ثبت شد. دوره های قبلی تغییر نمی کنند.');

        return redirect()->route('Accounting.companyAssets', $request->only(['status', 'asset_category', 'cost_center_id', 'store_id', 'q', 'depreciation_from', 'depreciation_to']));
    }

    public function postCompanyAssetCapitalAddition(Request $request, CompanyAsset $companyAsset, FixedAssetCapitalAdditionService $capitalAdditionService)
    {
        $this->authorizeCompanyAssetTenant($companyAsset);

        $payload = $request->validate([
            'addition_type' => ['required', 'in:major_repair,expansion,component,upgrade'],
            'addition_date_en' => ['nullable', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'asset_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'credit_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'supplier_name' => ['nullable', 'string', 'max:191'],
            'reference_number' => ['nullable', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
        ]);

        $addition = $capitalAdditionService->post($companyAsset, $payload, Auth::user());

        Alert::success('ثبت شد', 'الحاق سرمایه ای ثبت شد؛ بهای تمام شده جدید دارایی ' . number_format((float) $addition->asset_cost_after) . ' است.');

        return redirect()->route('Accounting.companyAssets', $request->only(['status', 'asset_category', 'cost_center_id', 'store_id', 'q', 'depreciation_from', 'depreciation_to']));
    }

    public function postCompanyAssetDisposal(Request $request, CompanyAsset $companyAsset, FixedAssetDisposalService $disposalService)
    {
        $this->authorizeCompanyAssetTenant($companyAsset);

        $payload = $request->validate([
            'disposal_type' => ['required', 'in:sale,scrap,retirement'],
            'disposal_date_en' => ['nullable', 'date'],
            'proceeds_amount' => ['nullable', 'numeric', 'min:0'],
            'proceeds_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'buyer_name' => ['nullable', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
        ]);

        $disposal = $disposalService->post($companyAsset, $payload, Auth::user());

        Alert::success('ثبت شد', 'خروج دارایی ثبت شد؛ سود ' . number_format((float) $disposal->gain_amount) . ' و زیان ' . number_format((float) $disposal->loss_amount) . ' محاسبه شد.');

        return redirect()->route('Accounting.companyAssets', $request->only(['status', 'asset_category', 'cost_center_id', 'store_id', 'q']));
    }

    public function prepareCompanyAssetTaxInvoice(Request $request, CompanyAssetDisposal $disposal, FixedAssetTaxInvoiceService $taxInvoiceService)
    {
        $disposal->loadMissing('asset');

        if ($disposal->asset) {
            $this->authorizeCompanyAssetTenant($disposal->asset);
        }

        $payload = $request->validate([
            'invoice_number' => ['nullable', 'string', 'max:80'],
            'tax_id' => ['nullable', 'string', 'max:120'],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'issue_date_en' => ['nullable', 'date'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'buyer_name' => ['nullable', 'string', 'max:191'],
            'buyer_economic_number' => ['nullable', 'string', 'max:80'],
            'buyer_national_id' => ['nullable', 'string', 'max:80'],
            'buyer_postal_code' => ['nullable', 'string', 'max:30'],
            'buyer_address' => ['nullable', 'string', 'max:191'],
        ]);

        $invoice = $taxInvoiceService->prepare($disposal, $payload, Auth::user());

        Alert::success('ثبت شد', 'پیش نویس صورت حساب مودیان دارایی با شماره ' . $invoice->invoice_number . ' آماده شد.');

        return redirect()->route('Accounting.companyAssets', $request->only(['status', 'asset_category', 'cost_center_id', 'store_id', 'q']));
    }

    public function updateCompanyAssetTaxInvoiceStatus(Request $request, CompanyAssetTaxInvoice $taxInvoice, FixedAssetTaxInvoiceService $taxInvoiceService)
    {
        $taxInvoice->loadMissing('asset');

        if ($taxInvoice->asset) {
            $this->authorizeCompanyAssetTenant($taxInvoice->asset);
        }

        $payload = $request->validate([
            'status' => ['required', 'in:sent,failed,accepted,rejected'],
            'tax_id' => ['nullable', 'string', 'max:120'],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'error_message' => ['nullable', 'string', 'max:1000'],
        ]);

        $invoice = $taxInvoiceService->updateStatus($taxInvoice, $payload, Auth::user());

        Alert::success('بروزرسانی شد', 'وضعیت صورت حساب مودیان به ' . ($this->companyAssetTaxInvoiceStatuses()[$invoice->status] ?? $invoice->status) . ' تغییر کرد.');

        return redirect()->route('Accounting.companyAssets', $request->only(['status', 'asset_category', 'cost_center_id', 'store_id', 'q']));
    }

    private function companyAssetDepreciationQuery(Request $request)
    {
        $user = Auth::user();
        $query = CompanyAsset::query()->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->currentTenantId($user));
        }

        foreach (['status', 'asset_category', 'cost_center_id', 'store_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, in_array($filter, ['cost_center_id', 'store_id'], true) ? (int) $request->get($filter) : $request->get($filter));
            }
        }

        if ($request->filled('q')) {
            $search = trim((string) $request->get('q'));
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('asset_code', 'like', '%' . $search . '%')
                    ->orWhere('plaque_number', 'like', '%' . $search . '%')
                    ->orWhere('serial_number', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    public function payroll(Request $request)
    {
        $user = Auth::user();
        $query = PayrollRun::with(['items.employee', 'items.contract', 'items.attendanceSummary', 'items.components', 'payments.voucher', 'accountingVoucher'])->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->currentTenantId($user));
        }

        if ($request->filled('period_year')) {
            $query->where('period_year', (int) $request->get('period_year'));
        }

        if ($request->filled('period_month')) {
            $query->where('period_month', (int) $request->get('period_month'));
        }

        $payrollRows = $query->get();
        $payrollRuns = $query->paginate(20)->withQueryString();
        $employees = $this->payrollEmployees()->load('activePayrollContract');
        $today = now()->toDateString();
        $periodYear = verta()->format('Y');
        $periodMonth = verta()->format('n');
        $contractsQuery = PayrollContract::with('employee')->orderByDesc('id');
        $attendanceQuery = PayrollAttendanceSummary::with('employee')->orderByDesc('period_year')->orderByDesc('period_month')->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $tenantId = $this->currentTenantId($user);
            $contractsQuery->where('tenant_id', $tenantId);
            $attendanceQuery->where('tenant_id', $tenantId);
        }

        $contracts = $contractsQuery->limit(30)->get();
        $attendanceSummaries = $attendanceQuery->limit(30)->get();
        $totals = [
            'gross' => round((float) $payrollRows->sum('gross_salary'), 2),
            'net' => round((float) $payrollRows->sum('net_pay_amount'), 2),
            'tax' => round((float) $payrollRows->sum('tax_amount'), 2),
            'insurance' => round((float) $payrollRows->sum('employee_insurance_amount') + (float) $payrollRows->sum('employer_insurance_amount'), 2),
            'paid' => round((float) $payrollRows->sum('paid_amount'), 2),
        ];

        return view('Accounting.payroll.index', compact('payrollRuns', 'employees', 'contracts', 'attendanceSummaries', 'today', 'periodYear', 'periodMonth', 'totals'));
    }

    public function storePayrollContract(Request $request, PayrollService $payrollService)
    {
        $payload = $request->validate([
            'payroll_contract_id' => ['nullable', 'integer', 'exists:payroll_contracts,id'],
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'contract_number' => ['nullable', 'string', 'max:80'],
            'contract_type' => ['required', 'in:monthly,daily,hourly,project'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'start_date_en' => ['nullable', 'date'],
            'end_date_en' => ['nullable', 'date'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'daily_wage' => ['nullable', 'numeric', 'min:0'],
            'hourly_wage' => ['nullable', 'numeric', 'min:0'],
            'fixed_allowance_amount' => ['nullable', 'numeric', 'min:0'],
            'housing_allowance_amount' => ['nullable', 'numeric', 'min:0'],
            'child_allowance_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_exemption_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'employee_insurance_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'employer_insurance_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'work_days_per_month' => ['nullable', 'numeric', 'min:0', 'max:31'],
            'daily_work_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'status' => ['required', 'in:active,closed,suspended'],
            'description' => ['nullable', 'string'],
        ]);

        $contract = $payrollService->saveContract($payload, Auth::user());

        Alert::success('ثبت شد', 'قرارداد حقوقی ' . $contract->contract_number . ' ثبت یا بروزرسانی شد.');

        return redirect()->route('Accounting.payroll');
    }

    public function storePayrollAttendance(Request $request, PayrollService $payrollService)
    {
        $payload = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'period_year' => ['required', 'integer', 'min:1300', 'max:1600'],
            'period_month' => ['required', 'integer', 'min:1', 'max:12'],
            'work_days' => ['nullable', 'numeric', 'min:0', 'max:31'],
            'work_hours' => ['nullable', 'numeric', 'min:0'],
            'overtime_hours' => ['nullable', 'numeric', 'min:0'],
            'absence_days' => ['nullable', 'numeric', 'min:0', 'max:31'],
            'leave_days' => ['nullable', 'numeric', 'min:0', 'max:31'],
            'mission_days' => ['nullable', 'numeric', 'min:0', 'max:31'],
            'status' => ['required', 'in:draft,approved,locked'],
            'description' => ['nullable', 'string'],
        ]);

        $attendance = $payrollService->saveAttendance($payload, Auth::user());

        Alert::success('ثبت شد', 'کارکرد ' . optional($attendance->employee)->name . ' برای دوره ' . $attendance->period_year . '/' . $attendance->period_month . ' ذخیره شد.');

        return redirect()->route('Accounting.payroll', ['period_year' => $payload['period_year'], 'period_month' => $payload['period_month']]);
    }

    public function storePayrollRun(Request $request, PayrollService $payrollService)
    {
        $payload = $request->validate([
            'title' => ['nullable', 'string', 'max:191'],
            'period_year' => ['required', 'integer', 'min:1300', 'max:1600'],
            'period_month' => ['required', 'integer', 'min:1', 'max:12'],
            'payroll_date_en' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'employee_id' => ['required', 'array', 'min:1'],
            'employee_id.*' => ['nullable', 'integer', 'exists:employees,id'],
            'base_salary' => ['nullable', 'array'],
            'base_salary.*' => ['nullable', 'numeric', 'min:0'],
            'benefits_amount' => ['nullable', 'array'],
            'benefits_amount.*' => ['nullable', 'numeric', 'min:0'],
            'employee_insurance_amount' => ['nullable', 'array'],
            'employee_insurance_amount.*' => ['nullable', 'numeric', 'min:0'],
            'employer_insurance_amount' => ['nullable', 'array'],
            'employer_insurance_amount.*' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'array'],
            'tax_amount.*' => ['nullable', 'numeric', 'min:0'],
            'other_deductions_amount' => ['nullable', 'array'],
            'other_deductions_amount.*' => ['nullable', 'numeric', 'min:0'],
            'work_days' => ['nullable', 'array'],
            'work_days.*' => ['nullable', 'numeric', 'min:0'],
            'work_hours' => ['nullable', 'array'],
            'work_hours.*' => ['nullable', 'numeric', 'min:0'],
            'overtime_hours' => ['nullable', 'array'],
            'overtime_hours.*' => ['nullable', 'numeric', 'min:0'],
            'absence_days' => ['nullable', 'array'],
            'absence_days.*' => ['nullable', 'numeric', 'min:0'],
            'leave_days' => ['nullable', 'array'],
            'leave_days.*' => ['nullable', 'numeric', 'min:0'],
            'overtime_amount' => ['nullable', 'array'],
            'overtime_amount.*' => ['nullable', 'numeric', 'min:0'],
            'bonus_amount' => ['nullable', 'array'],
            'bonus_amount.*' => ['nullable', 'numeric', 'min:0'],
            'mission_amount' => ['nullable', 'array'],
            'mission_amount.*' => ['nullable', 'numeric', 'min:0'],
            'loan_deduction_amount' => ['nullable', 'array'],
            'loan_deduction_amount.*' => ['nullable', 'numeric', 'min:0'],
            'advance_deduction_amount' => ['nullable', 'array'],
            'advance_deduction_amount.*' => ['nullable', 'numeric', 'min:0'],
            'item_description' => ['nullable', 'array'],
            'item_description.*' => ['nullable', 'string'],
        ]);

        $run = $payrollService->createRun($payload, Auth::user());

        Alert::success('ثبت شد', 'لیست حقوق ' . $run->number . ' و سند حسابداری ' . optional($run->accountingVoucher)->voucher_number . ' ثبت شد.');

        return redirect()->route('Accounting.payroll');
    }

    public function payPayrollRun(Request $request, PayrollRun $payrollRun, PayrollService $payrollService)
    {
        $this->authorizePayrollRunTenant($payrollRun);

        $payload = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'integer', 'in:1,2,3,4'],
            'payment_date_en' => ['nullable', 'date'],
            'treasury_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'payment_terminal_id' => ['nullable', 'integer', 'exists:payment_terminals,id'],
            'issuing_bank' => ['nullable', 'string', 'max:120'],
            'cheque_number' => ['nullable', 'string', 'max:120'],
            'due_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $payment = $payrollService->createPayment($payrollRun, $payload, Auth::user());

        Alert::success('پرداخت شد', 'پرداخت حقوق ' . $payment->payment_number . ' و سند حسابداری مرتبط ثبت شد.');

        return redirect()->route('Accounting.payroll');
    }

    public function cancelPayrollRun(PayrollRun $payrollRun, PayrollService $payrollService)
    {
        $this->authorizePayrollRunTenant($payrollRun);

        $payrollService->cancel($payrollRun, Auth::user());

        Alert::success('ابطال شد', 'لیست حقوق و سند حسابداری موقت مرتبط ابطال شد.');

        return redirect()->route('Accounting.payroll');
    }

    public function createVoucher()
    {
        $accounts = $this->accountingAccounts();
        $today = now()->toDateString();
        $fiscalYears = $this->fiscalYearsForCurrentUser();
        $selectedFiscalYear = $fiscalYears->firstWhere('is_default', true) ?: $fiscalYears->first();

        return view('Accounting.vouchers.create', array_merge(compact('accounts', 'today', 'fiscalYears', 'selectedFiscalYear'), $this->voucherDimensionOptions()));
    }

    public function createOpeningVoucher(Request $request, OpeningVoucherService $service)
    {
        $fiscalYears = $this->fiscalYearsForCurrentUser();
        $selectedFiscalYear = $fiscalYears->firstWhere('id', (int) $request->get('fiscal_year_id'))
            ?: $fiscalYears->firstWhere('is_default', true)
            ?: $fiscalYears->first();

        $accounts = $this->accountingAccounts();
        $rows = $selectedFiscalYear ? $service->buildRows(Auth::user(), $selectedFiscalYear) : [];
        $existingDraft = $selectedFiscalYear ? $service->existingDraft(Auth::user(), $selectedFiscalYear) : null;
        $today = optional(optional($selectedFiscalYear)->starts_at)->toDateString() ?: now()->toDateString();

        return view('Accounting.vouchers.opening', array_merge(
            compact('accounts', 'today', 'fiscalYears', 'selectedFiscalYear', 'rows', 'existingDraft'),
            $this->voucherDimensionOptions()
        ));
    }

    public function storeOpeningVoucher(Request $request, OpeningVoucherService $service)
    {
        $request->validate([
            'fiscal_year_id' => ['required', 'integer', 'exists:fiscal_years,id'],
            'voucher_date_en' => ['nullable', 'date'],
            'reference_number' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string'],
            'account_id' => ['required', 'array'],
            'account_id.*' => ['nullable', 'integer', 'exists:accounts,id'],
            'debit_amount' => ['nullable', 'array'],
            'credit_amount' => ['nullable', 'array'],
            'item_description' => ['nullable', 'array'],
            'cost_center_id' => ['nullable', 'array'],
            'cost_center_id.*' => ['nullable', 'integer', 'exists:cost_centers,id'],
            'revenue_center_id' => ['nullable', 'array'],
            'revenue_center_id.*' => ['nullable', 'integer', 'exists:revenue_centers,id'],
            'branch_id' => ['nullable', 'array'],
            'branch_id.*' => ['nullable', 'integer', 'exists:stores,id'],
            'project_code' => ['nullable', 'array'],
            'product_id' => ['nullable', 'array'],
            'product_id.*' => ['nullable', 'integer', 'exists:products,id'],
            'customer_id' => ['nullable', 'array'],
            'customer_id.*' => ['nullable', 'integer', 'exists:customers,id'],
            'employee_id' => ['nullable', 'array'],
            'employee_id.*' => ['nullable', 'integer', 'exists:employees,id'],
            'currency_id' => ['nullable', 'array'],
            'currency_id.*' => ['nullable', 'integer', 'exists:currencies,id'],
            'foreign_debit_amount' => ['nullable', 'array'],
            'foreign_credit_amount' => ['nullable', 'array'],
            'exchange_rate' => ['nullable', 'array'],
        ]);

        $fiscalYear = $this->fiscalYearsForCurrentUser()->firstWhere('id', (int) $request->get('fiscal_year_id'));

        if (!$fiscalYear) {
            return redirect()->back()->withInput()->withErrors(['fiscal_year_id' => 'سال مالی انتخاب‌شده در دسترس پنل شما نیست.']);
        }

        $voucher = $service->save($request->all(), Auth::user(), $fiscalYear);

        Alert::success('ثبت شد', 'سند افتتاحیه شماره ' . $voucher->voucher_number . ' به صورت موقت ثبت شد. برای نهایی‌سازی آن را «دائمی» کنید.');

        return redirect()->route('Accounting.vouchers');
    }

    public function editVoucher(Voucher $voucher, AccountingPostingService $postingService)
    {
        $this->authorizeVoucherTenant($voucher);
        $postingService->ensureEditableDraftVoucher($voucher);

        $voucher->load('items');
        $accounts = $this->accountingAccounts();
        $today = optional($voucher->voucher_date_en)->format('Y-m-d') ?: now()->toDateString();
        $voucherRows = $voucher->items->map(fn($item) => [
            'account_id' => $item->account_id,
            'description' => $item->description,
            'debit_amount' => (float) $item->debit_amount,
            'credit_amount' => (float) $item->credit_amount,
            'cost_center_id' => $item->cost_center_id,
            'revenue_center_id' => $item->revenue_center_id,
            'branch_id' => $item->branch_id,
            'project_code' => $item->project_code,
            'product_id' => $item->product_id,
            'customer_id' => $item->customer_id,
            'employee_id' => $item->employee_id,
            'contract_code' => $item->contract_code,
            'route_code' => $item->route_code,
            'analytic_note' => $item->analytic_note,
            'currency_id' => $item->currency_id,
            'foreign_debit_amount' => $item->foreign_debit_amount,
            'foreign_credit_amount' => $item->foreign_credit_amount,
            'exchange_rate' => $item->exchange_rate,
        ])->values()->all();

        $fiscalYears = $this->fiscalYearsForCurrentUser();
        $selectedFiscalYear = $fiscalYears->firstWhere('id', (int) $voucher->fiscal_year_id)
            ?: $fiscalYears->firstWhere('is_default', true)
            ?: $fiscalYears->first();

        return view('Accounting.vouchers.create', array_merge(compact('accounts', 'today', 'voucher', 'voucherRows', 'fiscalYears', 'selectedFiscalYear'), $this->voucherDimensionOptions()));
    }

    public function treasury()
    {
        $user = Auth::user();
        $query = Voucher::with(['items.account', 'treasuryInstruments.counterAccount', 'treasuryInstruments.currentHolderAccount', 'treasuryInstruments.histories.holderAccount', 'treasuryInstruments.histories.settlementAccount'])
            ->where(function ($query) {
                $query->whereIn('document_type', ['treasury_receipt', 'treasury_payment', 'treasury_transfer'])
                    ->orWhere('document_type', 'like', 'treasury_cheque_%');
            })
            ->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $user->tenant_id ?: $user->tenants_id);
        }

        $vouchers = $query->paginate(25);

        $statuses = $this->treasuryInstrumentStatuses();
        $treasuryAccounts = $this->treasuryAccounts();

        return view('Accounting.treasury.index', compact('vouchers', 'statuses', 'treasuryAccounts'));
    }

    public function createTreasury(TreasuryChequeBookService $chequeBookService)
    {
        $user = Auth::user();
        $accounts = $this->accountingAccounts();
        $terminals = $this->paymentTerminals();
        $paymentMethods = $this->paymentMethods();
        $chequeLeaves = $chequeBookService->availableLeaves($user);
        $today = now()->toDateString();

        return view('Accounting.treasury.create', compact('accounts', 'terminals', 'paymentMethods', 'chequeLeaves', 'today'));
    }

    public function createTreasuryTransfer()
    {
        $accounts = $this->accountingAccounts();
        $today = now()->toDateString();

        return view('Accounting.treasury.transfer', compact('accounts', 'today'));
    }

    public function storeVoucher(Request $request, AccountingPostingService $postingService)
    {
        $request->validate([
            'voucher_date_en' => ['nullable', 'date'],
            'reference_number' => ['nullable', 'string', 'max:60'],
            'fiscal_year_id' => ['nullable', 'integer', 'exists:fiscal_years,id'],
            'description' => ['nullable', 'string'],
            'account_id' => ['required', 'array'],
            'account_id.*' => ['nullable', 'integer', 'exists:accounts,id'],
            'debit_amount' => ['nullable', 'array'],
            'credit_amount' => ['nullable', 'array'],
            'item_description' => ['nullable', 'array'],
            'cost_center_id' => ['nullable', 'array'],
            'cost_center_id.*' => ['nullable', 'integer', 'exists:cost_centers,id'],
            'revenue_center_id' => ['nullable', 'array'],
            'revenue_center_id.*' => ['nullable', 'integer', 'exists:revenue_centers,id'],
            'branch_id' => ['nullable', 'array'],
            'branch_id.*' => ['nullable', 'integer', 'exists:stores,id'],
            'project_code' => ['nullable', 'array'],
            'project_code.*' => ['nullable', 'string', 'max:80'],
            'product_id' => ['nullable', 'array'],
            'product_id.*' => ['nullable', 'integer', 'exists:products,id'],
            'customer_id' => ['nullable', 'array'],
            'customer_id.*' => ['nullable', 'integer', 'exists:customers,id'],
            'employee_id' => ['nullable', 'array'],
            'employee_id.*' => ['nullable', 'integer', 'exists:employees,id'],
            'contract_code' => ['nullable', 'array'],
            'contract_code.*' => ['nullable', 'string', 'max:120'],
            'route_code' => ['nullable', 'array'],
            'route_code.*' => ['nullable', 'string', 'max:120'],
            'analytic_note' => ['nullable', 'array'],
            'analytic_note.*' => ['nullable', 'string', 'max:191'],
            'currency_id' => ['nullable', 'array'],
            'currency_id.*' => ['nullable', 'integer', 'exists:currencies,id'],
            'foreign_debit_amount' => ['nullable', 'array'],
            'foreign_debit_amount.*' => ['nullable', 'numeric', 'min:0'],
            'foreign_credit_amount' => ['nullable', 'array'],
            'foreign_credit_amount.*' => ['nullable', 'numeric', 'min:0'],
            'exchange_rate' => ['nullable', 'array'],
            'exchange_rate.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $voucher = $postingService->createManualVoucher($request->all(), Auth::user());

        Alert::success('ثبت شد', 'سند حسابداری با شماره ' . $voucher->voucher_number . ' به صورت موقت ثبت شد.');

        return redirect()->route('Accounting.vouchers');
    }

    public function updateVoucher(Request $request, Voucher $voucher, AccountingPostingService $postingService)
    {
        $this->authorizeVoucherTenant($voucher);

        $request->validate([
            'voucher_date_en' => ['nullable', 'date'],
            'reference_number' => ['nullable', 'string', 'max:60'],
            'fiscal_year_id' => ['nullable', 'integer', 'exists:fiscal_years,id'],
            'description' => ['nullable', 'string'],
            'account_id' => ['required', 'array'],
            'account_id.*' => ['nullable', 'integer', 'exists:accounts,id'],
            'debit_amount' => ['nullable', 'array'],
            'credit_amount' => ['nullable', 'array'],
            'item_description' => ['nullable', 'array'],
            'cost_center_id' => ['nullable', 'array'],
            'cost_center_id.*' => ['nullable', 'integer', 'exists:cost_centers,id'],
            'revenue_center_id' => ['nullable', 'array'],
            'revenue_center_id.*' => ['nullable', 'integer', 'exists:revenue_centers,id'],
            'branch_id' => ['nullable', 'array'],
            'branch_id.*' => ['nullable', 'integer', 'exists:stores,id'],
            'project_code' => ['nullable', 'array'],
            'project_code.*' => ['nullable', 'string', 'max:80'],
            'product_id' => ['nullable', 'array'],
            'product_id.*' => ['nullable', 'integer', 'exists:products,id'],
            'customer_id' => ['nullable', 'array'],
            'customer_id.*' => ['nullable', 'integer', 'exists:customers,id'],
            'employee_id' => ['nullable', 'array'],
            'employee_id.*' => ['nullable', 'integer', 'exists:employees,id'],
            'contract_code' => ['nullable', 'array'],
            'contract_code.*' => ['nullable', 'string', 'max:120'],
            'route_code' => ['nullable', 'array'],
            'route_code.*' => ['nullable', 'string', 'max:120'],
            'analytic_note' => ['nullable', 'array'],
            'analytic_note.*' => ['nullable', 'string', 'max:191'],
            'currency_id' => ['nullable', 'array'],
            'currency_id.*' => ['nullable', 'integer', 'exists:currencies,id'],
            'foreign_debit_amount' => ['nullable', 'array'],
            'foreign_debit_amount.*' => ['nullable', 'numeric', 'min:0'],
            'foreign_credit_amount' => ['nullable', 'array'],
            'foreign_credit_amount.*' => ['nullable', 'numeric', 'min:0'],
            'exchange_rate' => ['nullable', 'array'],
            'exchange_rate.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $updated = $postingService->updateDraftVoucher($voucher, $request->all(), Auth::user());

        Alert::success('ویرایش شد', 'سند موقت شماره ' . $updated->voucher_number . ' با موفقیت ویرایش شد.');

        return redirect()->route('Accounting.vouchers');
    }

    public function mergeVouchers(Request $request, AccountingPostingService $postingService)
    {
        $payload = $request->validate([
            'voucher_ids' => ['required', 'array', 'min:2'],
            'voucher_ids.*' => ['integer', 'exists:vouchers,id'],
            'voucher_date_en' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $voucher = $postingService->mergeDraftVouchers($payload['voucher_ids'], $payload, Auth::user());

        Alert::success('ادغام شد', 'سند موقت تجمیعی شماره ' . $voucher->voucher_number . ' ساخته شد و اسناد مبدا از گزارش ها خارج شدند.');

        return redirect()->route('Accounting.vouchers');
    }

    public function storeVoucherTemplate(Request $request, Voucher $voucher, AccountingPostingService $postingService)
    {
        $this->authorizeVoucherTenant($voucher);

        $payload = $request->validate([
            'name' => ['nullable', 'string', 'max:191'],
            'frequency' => ['nullable', 'in:on_demand,monthly,seasonal,annual'],
            'description' => ['nullable', 'string'],
        ]);

        $template = $postingService->createTemplateFromVoucher($voucher, $payload, Auth::user());

        Alert::success('الگو ساخته شد', 'الگوی سند «' . $template->name . '» با موفقیت ذخیره شد.');

        return redirect()->route('Accounting.voucherTemplates');
    }

    public function createVoucherFromTemplate(Request $request, VoucherTemplate $voucherTemplate, AccountingPostingService $postingService)
    {
        $this->authorizeVoucherTemplateTenant($voucherTemplate);

        $payload = $request->validate([
            'voucher_date_en' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $voucher = $postingService->createDraftFromTemplate($voucherTemplate, $payload, Auth::user());

        Alert::success('سند ساخته شد', 'سند موقت شماره ' . $voucher->voucher_number . ' از الگو ساخته شد.');

        return redirect()->route('Accounting.vouchers');
    }

    public function storeTreasury(Request $request, AccountingPostingService $postingService)
    {
        $request->validate([
            'transaction_type' => ['required', 'in:receipt,payment'],
            'voucher_date_en' => ['nullable', 'date'],
            'payment_method' => ['required', 'integer', 'in:1,2,3,4'],
            'treasury_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'counter_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'payment_terminal_id' => ['nullable', 'integer', 'exists:payment_terminals,id'],
            'cheque_leaf_id' => ['nullable', 'integer', 'exists:treasury_cheque_leaves,id'],
            'amount' => ['required'],
            'issuing_bank' => ['nullable', 'string', 'max:255'],
            'cheque_number' => ['nullable', 'string', 'max:100'],
            'due_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $voucher = $postingService->createTreasuryVoucher($request->all(), Auth::user());

        Alert::success('ثبت شد', 'سند خزانه با شماره ' . $voucher->voucher_number . ' به صورت موقت ثبت شد.');

        return redirect()->route('Accounting.treasury');
    }

    public function storeTreasuryTransfer(Request $request, AccountingPostingService $postingService)
    {
        $request->validate([
            'voucher_date_en' => ['nullable', 'date'],
            'from_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'to_account_id' => ['required', 'integer', 'exists:accounts,id', 'different:from_account_id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
        ]);

        $voucher = $postingService->createTreasuryTransferVoucher($request->all(), Auth::user());

        Alert::success('ثبت شد', 'انتقال خزانه با شماره سند ' . $voucher->voucher_number . ' ثبت شد.');

        return redirect()->route('Accounting.treasury');
    }

    public function treasuryChequeReport(Request $request)
    {
        $user = Auth::user();
        $query = TreasuryInstrument::with(['voucher', 'counterAccount', 'currentHolderAccount', 'histories.holderAccount', 'histories.settlementAccount'])
            ->where('instrument_type', 'cheque')
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $user->tenant_id ?: $user->tenants_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('direction')) {
            $query->where('direction', $request->get('direction'));
        }

        if ($request->filled('due_from')) {
            $query->whereDate('due_date', '>=', $request->get('due_from'));
        }

        if ($request->filled('due_to')) {
            $query->whereDate('due_date', '<=', $request->get('due_to'));
        }

        $instruments = $query->paginate(50)->withQueryString();

        return view('Accounting.treasury.cheque_report', [
            'instruments' => $instruments,
            'statuses' => $this->treasuryInstrumentStatuses(),
            'treasuryAccounts' => $this->treasuryAccounts(),
        ]);
    }

    public function treasuryChequeAgingReport(Request $request)
    {
        $user = Auth::user();
        $query = TreasuryInstrument::with(['voucher', 'counterAccount'])
            ->where('instrument_type', 'cheque')
            ->whereNotNull('due_date');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->currentTenantId($user));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('direction')) {
            $query->where('direction', $request->get('direction'));
        }

        if ($request->filled('due_from')) {
            $query->whereDate('due_date', '>=', $request->get('due_from'));
        }

        if ($request->filled('due_to')) {
            $query->whereDate('due_date', '<=', $request->get('due_to'));
        }

        $baseDate = \Carbon\Carbon::parse($request->get('base_date') ?: now()->toDateString())->startOfDay();
        $instruments = $query->orderBy('due_date')->get();
        $totalAmount = (float) $instruments->sum('amount');
        $weightedDays = $totalAmount > 0
            ? round($instruments->sum(fn($instrument) => (float) $instrument->amount * $baseDate->diffInDays($instrument->due_date, false)) / $totalAmount, 2)
            : 0;
        $weightedDueDate = $totalAmount > 0 ? $baseDate->copy()->addDays((int) round($weightedDays))->toDateString() : null;
        $groups = $instruments->groupBy(fn($instrument) => $instrument->direction . '|' . $instrument->status)
            ->map(function ($items, $key) use ($baseDate) {
                [$direction, $status] = explode('|', $key);
                $amount = (float) $items->sum('amount');

                return [
                    'direction' => $direction,
                    'status' => $status,
                    'count' => $items->count(),
                    'amount' => $amount,
                    'weighted_days' => $amount > 0 ? round($items->sum(fn($item) => (float) $item->amount * $baseDate->diffInDays($item->due_date, false)) / $amount, 2) : 0,
                ];
            })
            ->values();

        return view('Accounting.treasury.cheque_aging_report', [
            'instruments' => $instruments,
            'groups' => $groups,
            'statuses' => $this->treasuryInstrumentStatuses(),
            'baseDate' => $baseDate->toDateString(),
            'totalAmount' => $totalAmount,
            'weightedDays' => $weightedDays,
            'weightedDueDate' => $weightedDueDate,
        ]);
    }

    public function chequeBooks(Request $request, TreasuryAlertService $alertService)
    {
        $user = Auth::user();
        $books = TreasuryChequeBook::with('account')
            ->withCount([
                'leaves as available_leaves_count' => fn($query) => $query->where('status', 'available'),
                'leaves as issued_leaves_count' => fn($query) => $query->where('status', 'issued'),
                'leaves as blocked_leaves_count' => fn($query) => $query->whereIn('status', ['blocked', 'voided', 'cancelled']),
            ])
            ->when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $this->currentTenantId($user)))
            ->orderByDesc('id')
            ->get();
        $leaves = TreasuryChequeLeaf::with(['book.account', 'instrument.counterAccount'])
            ->when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $this->currentTenantId($user)))
            ->when($request->filled('book_id'), fn($query) => $query->where('treasury_cheque_book_id', $request->get('book_id')))
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->get('status')))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();
        $accounts = $this->treasuryAccounts();
        $alerts = $alertService->build($user, (int) $request->get('alert_days', 7));

        return view('Accounting.treasury.cheque_books', compact('books', 'leaves', 'accounts', 'alerts'));
    }

    public function storeChequeBook(Request $request, TreasuryChequeBookService $service)
    {
        $payload = $request->validate([
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'book_number' => ['nullable', 'string', 'max:80'],
            'cheque_prefix' => ['nullable', 'string', 'max:40'],
            'first_leaf_number' => ['required', 'integer', 'min:1'],
            'last_leaf_number' => ['required', 'integer', 'min:1'],
            'warning_threshold' => ['nullable', 'integer', 'min:0', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive,finished'],
            'description' => ['nullable', 'string'],
        ]);

        $book = $service->createBook($payload, Auth::user());

        Alert::success('ثبت شد', 'دسته چک ' . ($book->book_number ?: $book->id) . ' با ' . $book->leaf_count . ' برگ ثبت شد.');

        return redirect()->route('Accounting.treasury.chequeBooks');
    }

    public function bankReconciliation(Request $request)
    {
        $user = Auth::user();
        $accounts = $this->treasuryAccounts();
        $query = BankStatementLine::with(['account', 'voucher'])
            ->orderByRaw('statement_date IS NULL')
            ->orderByDesc('statement_date')
            ->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->currentTenantId($user));
        }

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->get('account_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('statement_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('statement_date', '<=', $request->get('date_to'));
        }

        $lines = $query->paginate(30)->withQueryString();
        $candidateVouchers = $this->treasuryVoucherCandidates($request->get('account_id'));
        $today = now()->toDateString();

        return view('Accounting.treasury.bank_reconciliation', [
            'accounts' => $accounts,
            'lines' => $lines,
            'candidateVouchers' => $candidateVouchers,
            'statuses' => $this->bankStatementStatuses(),
            'today' => $today,
        ]);
    }

    public function storeBankStatementLine(Request $request)
    {
        $request->validate([
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'statement_date' => ['required', 'date'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'debit_amount' => ['nullable', 'numeric', 'min:0'],
            'credit_amount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $debit = $this->money($request->get('debit_amount'));
        $credit = $this->money($request->get('credit_amount'));

        if (($debit <= 0 && $credit <= 0) || ($debit > 0 && $credit > 0)) {
            return back()->withErrors(['amount' => 'در هر ردیف صورت حساب فقط یکی از مبلغ های بدهکار یا بستانکار باید پر شود.'])->withInput();
        }

        $user = Auth::user();

        BankStatementLine::create([
            'tenant_id' => $this->currentTenantId($user),
            'organization_id' => $this->currentOrganizationId($user),
            'account_id' => $request->get('account_id'),
            'statement_date' => $request->get('statement_date'),
            'reference_no' => $request->get('reference_no'),
            'debit_amount' => $debit,
            'credit_amount' => $credit,
            'amount' => max($debit, $credit),
            'status' => 'imported',
            'description' => $request->get('description'),
            'created_by' => $user?->id,
        ]);

        Alert::success('ثبت شد', 'ردیف صورت حساب بانکی ثبت شد.');

        return redirect()->route('Accounting.treasury.bankReconciliation');
    }

    public function reconcileBankStatementLine(Request $request, BankStatementLine $line)
    {
        $this->authorizeBankStatementLineTenant($line);

        $request->validate([
            'action' => ['required', 'in:match,ignore,reset'],
            'voucher_id' => ['nullable', 'integer', 'exists:vouchers,id'],
        ]);

        $action = $request->get('action');
        $user = Auth::user();

        if ($action === 'match') {
            if (!$request->filled('voucher_id')) {
                return back()->withErrors(['voucher_id' => 'برای تطبیق، انتخاب سند خزانه الزامی است.']);
            }

            $voucher = Voucher::with('items')->findOrFail($request->get('voucher_id'));
            $this->authorizeVoucherTenant($voucher);

            $voucherItem = $voucher->items->firstWhere('account_id', (int) $line->account_id);

            $line->update([
                'voucher_id' => $voucher->id,
                'voucher_item_id' => $voucherItem?->id,
                'status' => 'matched',
                'matched_at' => now(),
                'matched_by' => $user?->id,
                'updated_by' => $user?->id,
            ]);

            Alert::success('تطبیق شد', 'ردیف بانک به سند خزانه متصل شد.');

            return back();
        }

        $line->update([
            'voucher_id' => null,
            'voucher_item_id' => null,
            'status' => $action === 'ignore' ? 'ignored' : 'imported',
            'matched_at' => null,
            'matched_by' => null,
            'updated_by' => $user?->id,
        ]);

        Alert::success('بروزرسانی شد', 'وضعیت ردیف صورت حساب بروزرسانی شد.');

        return back();
    }

    public function liquidityReport(Request $request)
    {
        $accounts = $this->treasuryAccounts();
        $user = Auth::user();
        $rows = $accounts->map(function (Accounts $account) use ($request, $user) {
            $totalsQuery = VoucherItems::query()
                ->join('vouchers', 'voucher_items.voucher_id', '=', 'vouchers.id')
                ->where('voucher_items.account_id', $account->id)
                ->whereNull('voucher_items.deleted_at')
                ->whereNull('vouchers.deleted_at');

            if ((int) $user->isGod !== 1) {
                $totalsQuery->where('vouchers.tenant_id', $this->currentTenantId($user));
            }

            if ($request->filled('date_from')) {
                $totalsQuery->whereDate('vouchers.voucher_date_en', '>=', $request->get('date_from'));
            }

            if ($request->filled('date_to')) {
                $totalsQuery->whereDate('vouchers.voucher_date_en', '<=', $request->get('date_to'));
            }

            $totals = $totalsQuery->selectRaw('COALESCE(SUM(voucher_items.debit_amount), 0) as debit, COALESCE(SUM(voucher_items.credit_amount), 0) as credit')->first();

            $statementQuery = BankStatementLine::query()->where('account_id', $account->id);

            if ((int) $user->isGod !== 1) {
                $statementQuery->where('tenant_id', $this->currentTenantId($user));
            }

            if ($request->filled('date_from')) {
                $statementQuery->whereDate('statement_date', '>=', $request->get('date_from'));
            }

            if ($request->filled('date_to')) {
                $statementQuery->whereDate('statement_date', '<=', $request->get('date_to'));
            }

            $statement = (clone $statementQuery)->selectRaw('COALESCE(SUM(debit_amount), 0) as debit, COALESCE(SUM(credit_amount), 0) as credit')->first();

            $ledgerBalance = round((float) $totals->debit - (float) $totals->credit, 2);
            $statementBalance = round((float) $statement->debit - (float) $statement->credit, 2);

            return [
                'account' => $account,
                'ledger_debit' => (float) $totals->debit,
                'ledger_credit' => (float) $totals->credit,
                'ledger_balance' => $ledgerBalance,
                'statement_balance' => $statementBalance,
                'difference' => round($statementBalance - $ledgerBalance, 2),
                'matched_count' => (clone $statementQuery)->where('status', 'matched')->count(),
                'unmatched_count' => (clone $statementQuery)->where('status', 'imported')->count(),
            ];
        });

        return view('Accounting.treasury.liquidity_report', compact('rows'));
    }

    public function treasuryCashForecast(Request $request, TreasuryCashForecastService $service)
    {
        $accounts = $this->treasuryAccounts();
        $report = $service->build(Auth::user(), $accounts, $request->only(['base_date', 'days']));
        $statuses = $this->treasuryInstrumentStatuses();

        return view('Accounting.treasury.cash_forecast', compact('accounts', 'report', 'statuses'));
    }

    public function pettyCash(Request $request, PettyCashService $service)
    {
        $user = Auth::user();
        $funds = PettyCashFund::with(['account', 'custodian'])
            ->when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $this->currentTenantId($user)))
            ->orderBy('status')
            ->orderBy('title')
            ->get();
        $report = $service->report($user, $funds, $request->only(['fund_id', 'type']));
        $accounts = $this->accountingAccounts();
        $treasuryAccounts = $this->treasuryAccounts();
        $costCenters = $this->costCenters();
        $expenseTypes = $this->expenseTypes();
        $stores = $this->accountingStores();
        $users = $this->accountingUsers();
        $today = now()->toDateString();

        return view('Accounting.treasury.petty_cash', compact('funds', 'report', 'accounts', 'treasuryAccounts', 'costCenters', 'expenseTypes', 'stores', 'users', 'today'));
    }

    public function storePettyCashFund(Request $request, PettyCashService $service)
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'fund_code' => ['nullable', 'string', 'max:60'],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'custodian_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'custodian_name' => ['nullable', 'string', 'max:255'],
            'ceiling_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive,closed'],
            'description' => ['nullable', 'string'],
        ]);

        $fund = $service->createFund($payload, Auth::user());

        Alert::success('ثبت شد', 'تنخواه ' . $fund->title . ' ثبت شد.');

        return redirect()->route('Accounting.treasury.pettyCash');
    }

    public function chargePettyCash(Request $request, PettyCashFund $fund, PettyCashService $service)
    {
        $this->authorizePettyCashFundTenant($fund);

        $payload = $request->validate([
            'transaction_date_en' => ['nullable', 'date'],
            'counter_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
        ]);

        $transaction = $service->charge($fund, $payload, Auth::user());

        Alert::success('ثبت شد', 'شارژ تنخواه با سند ' . optional($transaction->voucher)->voucher_number . ' ثبت شد.');

        return redirect()->route('Accounting.treasury.pettyCash');
    }

    public function spendPettyCash(Request $request, PettyCashFund $fund, PettyCashService $service)
    {
        $this->authorizePettyCashFundTenant($fund);

        $payload = $request->validate([
            'transaction_date_en' => ['nullable', 'date'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'cost_center_id' => ['required', 'integer', 'exists:cost_centers,id'],
            'expense_type_id' => ['required', 'integer', 'exists:expense_types,id'],
            'expense_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
        ]);

        $transaction = $service->spend($fund, $payload, Auth::user());

        Alert::success('ثبت شد', 'هزینه تنخواه با سند ' . optional($transaction->voucher)->voucher_number . ' ثبت شد.');

        return redirect()->route('Accounting.treasury.pettyCash');
    }

    public function settlePettyCash(Request $request, PettyCashFund $fund, PettyCashService $service)
    {
        $this->authorizePettyCashFundTenant($fund);

        $payload = $request->validate([
            'transaction_date_en' => ['nullable', 'date'],
            'counter_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
        ]);

        $transaction = $service->settle($fund, $payload, Auth::user());

        Alert::success('ثبت شد', 'تسویه تنخواه با سند ' . optional($transaction->voucher)->voucher_number . ' ثبت شد.');

        return redirect()->route('Accounting.treasury.pettyCash');
    }

    public function updateTreasuryInstrumentStatus(Request $request, TreasuryInstrument $instrument, AccountingPostingService $postingService)
    {
        $this->authorizeTreasuryInstrumentTenant($instrument);

        $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys($this->treasuryInstrumentStatuses()))],
            'settlement_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'current_holder_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'current_holder_name' => ['nullable', 'string', 'max:255'],
            'status_date' => ['nullable', 'date'],
            'status_note' => ['nullable', 'string'],
        ]);

        $postingService->updateTreasuryInstrumentStatus(
            $instrument,
            $request->get('status'),
            Auth::user(),
            $request->integer('settlement_account_id') ?: null,
            $request->only(['current_holder_account_id', 'current_holder_name', 'status_date', 'status_note'])
        );

        Alert::success('ثبت شد', 'وضعیت چک با موفقیت بروزرسانی شد.');

        return redirect()->route('Accounting.treasury');
    }

    public function makeVoucherPermanent(Voucher $voucher, AccountingPostingService $postingService)
    {
        $this->authorizeVoucherTenant($voucher);

        $postingService->makePermanent($voucher, Auth::user());

        Alert::success('دائمی شد', 'سند حسابداری با موفقیت دائمی شد.');

        return redirect()->route('Accounting.vouchers');
    }

    public function reverseVoucher(Request $request, Voucher $voucher, AccountingPostingService $postingService)
    {
        $this->authorizeVoucherTenant($voucher);

        $payload = $request->validate([
            'voucher_date_en' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $reversal = $postingService->reverseVoucher($voucher, $payload, Auth::user());

        Alert::success('برگشت ثبت شد', 'سند برگشتی با شماره ' . $reversal->voucher_number . ' ثبت شد.');

        return redirect()->route('Accounting.vouchers');
    }

    public function cancelVoucher(Request $request, Voucher $voucher, AccountingPostingService $postingService)
    {
        $this->authorizeVoucherTenant($voucher);

        $payload = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $postingService->cancelDraftVoucher($voucher, $payload, Auth::user());

        Alert::success('ابطال شد', 'سند موقت با موفقیت ابطال شد.');

        return redirect()->route('Accounting.vouchers');
    }

    public function copyVoucher(Request $request, Voucher $voucher, AccountingPostingService $postingService)
    {
        $this->authorizeVoucherTenant($voucher);

        $payload = $request->validate([
            'voucher_date_en' => ['nullable', 'date'],
        ]);

        $copy = $postingService->copyVoucherToDraft($voucher, $payload, Auth::user());

        Alert::success('کپی شد', 'سند موقت جدید با شماره ' . $copy->voucher_number . ' ساخته شد.');

        return redirect()->route('Accounting.vouchers');
    }


    public function ProductsSales(Request $request)
    {
        $startDate   = $request->input('from_date') ? $this->toGregorianDate($request->input('from_date')) : null;
        $endDate     = $request->input('to_date') ? $this->toGregorianDate($request->input('to_date')) : null;

        $delivery_from_date = $request->input('delivery_from_date') ? $this->toGregorianDate($request->input('delivery_from_date')) : null;
        $delivery_to_date   = $request->input('delivery_to_date') ? $this->toGregorianDate($request->input('delivery_to_date')) : null;


        $user = Auth::user();
        $products = Product::withoutGlobalScope('withCurrentStock') // حذف موجودی پیش‌فرض
            ->forOrganizations($user, 'products.organization_id')
            ->where('products.isMaterial', 0)
            ->withSalesReport(
                $this->toGregorianDate($request->input('from_date')),
                $this->toGregorianDate($request->input('to_date')),
                $this->toGregorianDate($request->input('delivery_from_date')),
                $this->toGregorianDate($request->input('delivery_to_date'))
            )
            ->get();

        return view('Accounting.productsList', compact('products'));
    }
    public function PrFilter(Request $request)
    {

        $Products = Product::all();

        $pr_id = $request->get('pr_id');
        $Product = Product::find($request->get('pr_id'));
        echo $Product->title . " - " . $Product->display_name . " <hr />";

        // گرفتن آی‌دی فاکتورهایی که این محصول را دارند
        $factorIds = PishFactorItems::where('pr_id', $request->get('pr_id'))
            ->pluck('pishfactor_id')
            ->unique()
            ->toArray();

        // فقط فاکتورهای مرتبط را بگیر
        $pishfactors = Pishfactor::whereIn('id', $factorIds)
            ->whereIn('status', [1, 4])
            ->get();

        // رکوردهای Depot با pr_id مورد نظر
        $depots = Depot::where('pr_id', $request->get('pr_id'))->get();

        // ترکیب هر دو کالکشن و سورت بر اساس created_at
        $merged = $pishfactors->concat($depots)->sortByDesc(function ($item) {
            return $item->created_at;
        });

        // گروه‌بندی بر اساس روز و مرتب‌سازی صعودی تاریخ (قدیمی‌ترین اول)
        $grouped = $merged->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        })->sortKeys();

        $CARDEX = array();
        $Cardex_item = array();
        foreach ($grouped as $date => $items) {
            // echo "<h3>".$date."</h3><ul>";
            $endate = explode("-", $date);
            $Jalali = Verta::GregorianToJalali($endate[0], $endate[1], $endate[2]);
            $export = 0;
            $import = 0;
            foreach ($items as $item) {
                // تشخیص نوع رکورد
                if ($item instanceof \App\Models\Pishfactor) {
                    $cur_item_info = PishFactorItems::where('pr_id', $request->get('pr_id'))->where('pishfactor_id', $item->id)->first();
                    $tedadkol = intval($cur_item_info->pack * $Product->pack_items) + intval($cur_item_info->tedad);
                    $export += $tedadkol;
                } else {
                    $import += $item->entity;
                }
            }
            if ($import > 0) {
                // echo "<li>ورود: .$import.</li>";
                $Cardex_item['date'] = $Jalali[0] . "/" . $Jalali[1] . "/" . $Jalali[2];
                $Cardex_item['import'] = $import;
                $Cardex_item['export'] = 0;
                $Cardex_item['price'] = $item->price;
            }
            if ($export > 0) {
                //  echo "<li>خروج: .$export.</li>";
                $Cardex_item['date'] = $Jalali[0] . "/" . $Jalali[1] . "/" . $Jalali[2];
                $Cardex_item['export'] = $export;
                $Cardex_item['import'] = 0;
                $Cardex_item['price'] = $cur_item_info->price;
            }

            array_push($CARDEX, $Cardex_item);


            //  echo "</ul>";
        }

        return view('Accounting.Report_cardex', compact('CARDEX', 'Products'));
    }

    public function payed()
    {

        $PishFactors = Pishfactor::whereIn('status', [1, 4])->whereIn('payment_type', [1, 2])->orderBy('id', 'desc')->get();

        return view('Accounting.index', compact('PishFactors'));
    }

    public function unpayed()
    {

        $PishFactors = Pishfactor::whereIn('status', [1, 4])->whereIn('payment_type', [3, null])->orderBy('id', 'desc')->get();

        return view('Accounting.index', compact('PishFactors'));
    }

    private function toGregorianDate($jalaliDate)
    {
        // مثلا ورودی: 1403/07/01 یا 1403-07-01
        $jalaliDate = str_replace("/", "-", $jalaliDate);
        $parts = explode("-", $jalaliDate); // [سال, ماه, روز]
        if (count($parts) !== 3) return null;

        $miladi = Verta::jalaliToGregorian($parts[0], $parts[1], $parts[2]);
        $year = $miladi[0];
        $month = str_pad($miladi[1], 2, "0", STR_PAD_LEFT);
        $day = str_pad($miladi[2], 2, "0", STR_PAD_LEFT);

        return "{$year}-{$month}-{$day}";
    }

    private function costCenters()
    {
        $user = Auth::user();
        $query = CostCenter::with('store')->where('is_active', 1)->orderBy('code')->orderBy('name');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->currentTenantId($user));
        }

        return $query->get();
    }

    private function revenueCenterOptions()
    {
        $user = Auth::user();
        $query = RevenueCenter::where('is_active', 1)->orderBy('code')->orderBy('name');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->currentTenantId($user));
        }

        return $query->get();
    }

    private function revenueCenterTypes(): array
    {
        return [
            'branch' => 'شعبه',
            'store' => 'انبار/محل فروش',
            'product_group' => 'گروه کالا',
            'sales_channel' => 'کانال فروش',
            'salesperson' => 'ویزیتور/فروشنده',
            'route' => 'مسیر پخش',
            'project' => 'پروژه',
            'contract' => 'قرارداد',
            'customer_group' => 'گروه مشتری',
            'other' => 'سایر',
        ];
    }

    private function incomeTypes()
    {
        $user = Auth::user();
        $query = IncomeType::with('account')->where('is_active', 1)->orderBy('code')->orderBy('name');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->currentTenantId($user));
        }

        return $query->get();
    }

    private function incomeGroups(): array
    {
        return [
            'operational' => 'عملیاتی',
            'service' => 'خدمات',
            'rent' => 'اجاره',
            'contract' => 'پیمان/قرارداد',
            'commission' => 'کارمزد',
            'non_operational' => 'غیرعملیاتی',
            'other' => 'سایر',
        ];
    }

    private function expenseTypes()
    {
        $user = Auth::user();
        $query = ExpenseType::with('account')->where('is_active', 1)->orderBy('code')->orderBy('name');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->currentTenantId($user));
        }

        return $query->get();
    }

    private function expenseAllocationTargetLabel($allocation): string
    {
        $type = $allocation->allocation_target_type ?: $allocation->target_type ?: 'manual';

        return match ($type) {
            'product' => 'کالا: ' . (optional($allocation->product)->title ?: optional($allocation->product)->name ?: $allocation->product_id ?: $allocation->allocation_target_id),
            'order' => 'سفارش/خرید: ' . ($allocation->allocation_target_id ?: $allocation->target_id ?: '-'),
            'project' => 'پروژه: ' . ($allocation->project_code ?: $allocation->allocation_target_id ?: '-'),
            'contract' => 'قرارداد: ' . ($allocation->contract_code ?: $allocation->allocation_target_id ?: '-'),
            'cost_center' => 'مرکز هزینه: ' . ($allocation->allocation_target_id ?: $allocation->cost_center_id ?: '-'),
            'asset' => 'دارایی: ' . ($allocation->allocation_target_id ?: $allocation->target_id ?: '-'),
            default => 'دستی / بدون هدف',
        };
    }

    private function accountingStores()
    {
        $user = Auth::user();
        $query = Store::query()->where('isActive', 1)->orderBy('title');

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->get();
    }

    private function accountingUsers()
    {
        $user = Auth::user();
        $query = User::query()->where('isActive', 1)->orderBy('name')->orderBy('username');

        if ((int) $user->isGod !== 1) {
            $tenantId = $this->currentTenantId($user);
            $query->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)->orWhere('tenants_id', $tenantId);
            });
        }

        return $query->get();
    }

    private function voucherDimensionOptions(): array
    {
        return [
            'costCenters' => $this->costCenters(),
            'revenueCenters' => $this->revenueCenterOptions(),
            'branches' => $this->accountingStores(),
            'products' => $this->analyticProducts(),
            'customers' => $this->analyticCustomers(),
            'employees' => $this->payrollEmployees(),
            'currencies' => $this->accountingCurrencies(),
        ];
    }

    private function accountingCurrencies()
    {
        $user = Auth::user();
        $query = Currency::query()->where('isActive', 1)->orderByDesc('is_default')->orderBy('code');

        if ((int) $user->isGod !== 1) {
            $tenantId = $this->currentTenantId($user);
            $query->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            });
        }

        return $query->get();
    }

    private function analyticProducts()
    {
        return collect();
    }

    private function analyticCustomers()
    {
        return collect();
    }

    private function payrollEmployees()
    {
        return collect();
    }

    private function nextExpenseNumber(?int $tenantId): string
    {
        $year = verta()->format('Y');
        $base = 'EXP-' . $year . '-';
        $query = OperationalExpense::where('expense_number', 'like', $base . '%');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $lastNumber = $query->orderByDesc('id')->value('expense_number');
        $next = 1;

        if ($lastNumber && preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $base . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function nextIncomeNumber(?int $tenantId): string
    {
        $year = verta()->format('Y');
        $base = 'INC-' . $year . '-';
        $query = OperationalIncome::where('income_number', 'like', $base . '%');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $lastNumber = $query->orderByDesc('id')->value('income_number');
        $next = 1;

        if ($lastNumber && preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $base . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function jalaliDate(string $date): string
    {
        try {
            return verta($date)->format('Y/m/d');
        } catch (\Throwable $exception) {
            return $date;
        }
    }

    private function storeOrganizationId(int $storeId): ?int
    {
        $organizationId = Store::whereKey($storeId)->value('organization_id');
        $decoded = is_string($organizationId) ? json_decode($organizationId, true) : null;

        if (is_array($decoded)) {
            return isset($decoded[0]) ? (int) $decoded[0] : null;
        }

        return $organizationId ? (int) $organizationId : null;
    }

    private function accountingAccounts()
    {
        $user = Auth::user();
        $query = Accounts::query()->where('isActive', 1)->orderBy('code')->orderBy('name');

        if ((int) $user->isGod !== 1) {
            $tenantId = $user->tenant_id ?: $user->tenants_id;
            $query->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)->orWhere('tenants_id', $tenantId);
            });
        }

        return $query->get();
    }

    private function fiscalYearsForCurrentUser()
    {
        $user = Auth::user();
        $query = FiscalYear::query()->orderByDesc('starts_at')->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->currentTenantId($user));
        }

        return $query->get();
    }

    private function paymentTerminals()
    {
        $user = Auth::user();
        $query = PaymentTerminal::query()->orderBy('title');

        if ((int) $user->isGod !== 1) {
            $tenantId = $user->tenant_id ?: $user->tenants_id;
            $query->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)->orWhere('tenants_id', $tenantId);
            });
        }

        return $query->get();
    }

    private function paymentMethods()
    {
        return PaymentMethod::where('isActive', 1)
            ->whereNotNull('legacy_code')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }

    private function specializedExpenseKinds(): array
    {
        return [
            'insurance' => 'بیمه',
            'customs' => 'گمرک و ترخیص',
            'waste' => 'ضایعات',
            'commission' => 'کارمزد و پورسانت',
            'production_payroll' => 'حقوق تولید',
            'depreciation' => 'استهلاک',
        ];
    }

    private function companyAssetCategories(): array
    {
        return [
            'building' => 'ساختمان و تاسیسات',
            'vehicle' => 'خودرو و وسایل نقلیه',
            'machinery' => 'ماشین آلات و تجهیزات تولید',
            'office_equipment' => 'تجهیزات اداری',
            'computer' => 'رایانه و تجهیزات فناوری',
            'furniture' => 'اثاثه و منصوبات',
            'tool' => 'ابزار و تجهیزات کارگاهی',
            'other' => 'سایر اموال',
        ];
    }

    private function companyAssetStatuses(): array
    {
        return [
            'active' => 'فعال در بهره برداری',
            'idle' => 'بلااستفاده موقت',
            'under_repair' => 'در تعمیر',
            'sold' => 'فروخته شده',
            'scrapped' => 'اسقاط شده',
        ];
    }

    private function companyAssetAttachmentTypes(): array
    {
        return [
            'purchase_invoice' => 'فاکتور خرید',
            'image' => 'تصویر دارایی',
            'insurance' => 'بیمه نامه',
            'warranty' => 'گارانتی',
            'repair' => 'مدرک تعمیر',
            'contract' => 'قرارداد',
            'other' => 'سایر مدارک',
        ];
    }

    private function companyAssetEventTypes(): array
    {
        return [
            'registration' => 'ثبت اولیه',
            'custody' => 'تحویل گیرنده',
            'transfer' => 'جابجایی محل',
            'repair' => 'تعمیر',
            'maintenance' => 'نگهداری',
            'insurance' => 'بیمه',
            'valuation' => 'تغییر ارزش',
            'status_change' => 'تغییر وضعیت',
            'sale' => 'فروش دارایی',
            'scrap' => 'اسقاط',
            'other' => 'سایر رخدادها',
        ];
    }

    private function companyAssetCapitalAdditionTypes(): array
    {
        return [
            'major_repair' => 'تعمیرات اساسی سرمایه ای',
            'expansion' => 'الحاق/گسترش دارایی',
            'component' => 'افزودن قطعه سرمایه ای',
            'upgrade' => 'ارتقای سرمایه ای',
        ];
    }

    private function companyAssetTaxInvoiceStatuses(): array
    {
        return [
            'draft' => 'پیش نویس آماده ارسال',
            'sent' => 'ارسال شده / در انتظار پاسخ',
            'failed' => 'خطای ارسال',
            'accepted' => 'تایید شده در سامانه',
            'rejected' => 'رد شده در سامانه',
        ];
    }

    private function nextCompanyAssetCode(?int $tenantId): string
    {
        $year = verta()->format('Y');
        $base = 'AST-' . $year . '-';
        $query = CompanyAsset::where('asset_code', 'like', $base . '%');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $lastNumber = $query->orderByDesc('id')->value('asset_code');
        $next = 1;

        if ($lastNumber && preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $base . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function authorizeOperationalExpenseTenant(OperationalExpense $expense): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        if ((int) $expense->tenant_id !== (int) $this->currentTenantId($user)) {
            abort(403);
        }
    }

    private function authorizeCompanyAssetTenant(CompanyAsset $asset): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        if ((int) $asset->tenant_id !== (int) $this->currentTenantId($user)) {
            abort(403);
        }
    }

    private function authorizeOperationalIncomeTenant(OperationalIncome $income): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        if ((int) $income->tenant_id !== (int) $this->currentTenantId($user)) {
            abort(403);
        }
    }

    private function storeFinancialAttachment(Request $request, $attachable, ?Voucher $voucher = null): ?FinancialAttachment
    {
        if (!$request->hasFile('attachment_file')) {
            return null;
        }

        $file = $request->file('attachment_file');
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $path = $file->storeAs(
            'financial-attachments/' . now()->format('Y/m'),
            (string) Str::uuid() . '.' . $extension,
            'public'
        );

        return $attachable->financialAttachments()->create([
            'tenant_id' => $attachable->tenant_id,
            'organization_id' => $attachable->organization_id,
            'voucher_id' => $voucher?->id ?: $attachable->voucher_id,
            'attachment_kind' => 'document',
            'disk' => 'public',
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'note' => $request->input('attachment_note'),
            'created_by' => Auth::id(),
        ]);
    }

    private function treasuryAccounts()
    {
        $user = Auth::user();
        $tenantId = $this->currentTenantId($user);
        $transferAccountIds = VoucherItems::query()
            ->join('vouchers', 'voucher_items.voucher_id', '=', 'vouchers.id')
            ->where('vouchers.document_type', 'treasury_transfer')
            ->when((int) $user->isGod !== 1, fn($query) => $query->where('vouchers.tenant_id', $tenantId))
            ->distinct()
            ->pluck('voucher_items.account_id')
            ->filter()
            ->values();
        $statementAccountIds = BankStatementLine::query()
            ->when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $tenantId))
            ->distinct()
            ->pluck('account_id')
            ->filter()
            ->values();
        $terminalAccountIds = PaymentTerminal::query()
            ->when((int) $user->isGod !== 1, fn($query) => $query->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)->orWhere('tenants_id', $tenantId);
            }))
            ->pluck('account_id')
            ->filter()
            ->values();
        $treasuryAccountIds = $transferAccountIds
            ->merge($statementAccountIds)
            ->merge($terminalAccountIds)
            ->unique()
            ->values();

        $query = Accounts::query()->where('isActive', 1)
            ->where(function ($query) use ($treasuryAccountIds) {
                $query->whereIn('code', ['SYS-1101', 'SYS-1102', 'SYS-1103', 'SYS-1104']);

                if ($treasuryAccountIds->isNotEmpty()) {
                    $query->orWhereIn('id', $treasuryAccountIds->all());
                }

                $query->orWhereNotNull('account_number')
                    ->orWhereNotNull('card_number')
                    ->orWhereNotNull('iban');
            })
            ->orderBy('code')
            ->orderBy('name');

        if ((int) $user->isGod !== 1) {
            $query->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)->orWhere('tenants_id', $tenantId);
            });
        }

        return $query->get();
    }

    private function treasuryVoucherCandidates($accountId)
    {
        $user = Auth::user();
        $tenantId = $this->currentTenantId($user);
        $query = Voucher::with(['items.account'])
            ->whereIn('document_type', ['treasury_receipt', 'treasury_payment', 'treasury_transfer'])
            ->orderByDesc('id')
            ->limit(200);

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $tenantId);
        }

        if ($accountId) {
            $query->whereHas('items', fn($query) => $query->where('account_id', $accountId));
        }

        return $query->get();
    }

    private function treasuryInstrumentStatuses(): array
    {
        return [
            'received' => 'دریافت شده',
            'issued' => 'صادر شده',
            'deposited' => 'واگذار به بانک',
            'collected' => 'وصول شده',
            'spent' => 'خرج شده',
            'returned' => 'برگشتی',
            'endorsed' => 'پشت نمره / واگذار شده',
            'refunded' => 'مسترد شده',
            'replaced' => 'تعویض شده',
            'canceled' => 'باطل شده',
        ];
    }

    private function bankStatementStatuses(): array
    {
        return [
            'imported' => 'تطبیق نشده',
            'matched' => 'تطبیق شده',
            'ignored' => 'نادیده گرفته شده',
        ];
    }

    private function money($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 2);
    }

    private function accountantReviewReport($user, array $filters): array
    {
        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;
        $tenantId = $this->currentTenantId($user);

        $voucherBase = DB::table('vouchers')->whereNull('deleted_at');
        $this->applyAccountantReviewVoucherScope($voucherBase, $user, $fromDate, $toDate);

        $voucherTotals = (clone $voucherBase)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'draft' OR is_permanent = 0 THEN 1 ELSE 0 END) as draft_count")
            ->selectRaw("SUM(CASE WHEN status = 'permanent' OR is_permanent = 1 THEN 1 ELSE 0 END) as permanent_count")
            ->selectRaw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count")
            ->first();

        $itemTotalsQuery = DB::table('voucher_items')
            ->join('vouchers', 'voucher_items.voucher_id', '=', 'vouchers.id')
            ->whereNull('voucher_items.deleted_at')
            ->whereNull('vouchers.deleted_at');
        $this->applyAccountantReviewVoucherScope($itemTotalsQuery, $user, $fromDate, $toDate);
        $itemTotals = $itemTotalsQuery
            ->selectRaw('COALESCE(SUM(voucher_items.debit_amount), 0) as debit')
            ->selectRaw('COALESCE(SUM(voucher_items.credit_amount), 0) as credit')
            ->first();

        $unbalancedQuery = DB::table('vouchers')
            ->leftJoin('voucher_items', function ($join) {
                $join->on('voucher_items.voucher_id', '=', 'vouchers.id')
                    ->whereNull('voucher_items.deleted_at');
            })
            ->whereNull('vouchers.deleted_at')
            ->select('vouchers.id', 'vouchers.voucher_number', 'vouchers.voucher_date_en', 'vouchers.document_type', 'vouchers.status')
            ->selectRaw('COALESCE(SUM(voucher_items.debit_amount), 0) as debit')
            ->selectRaw('COALESCE(SUM(voucher_items.credit_amount), 0) as credit')
            ->groupBy('vouchers.id', 'vouchers.voucher_number', 'vouchers.voucher_date_en', 'vouchers.document_type', 'vouchers.status')
            ->havingRaw('ROUND(COALESCE(SUM(voucher_items.debit_amount), 0) - COALESCE(SUM(voucher_items.credit_amount), 0), 2) <> 0');
        $this->applyAccountantReviewVoucherScope($unbalancedQuery, $user, $fromDate, $toDate);

        $unbalancedCount = DB::query()->fromSub(clone $unbalancedQuery, 'unbalanced_vouchers')->count();
        $unbalancedVouchers = (clone $unbalancedQuery)->orderByDesc('vouchers.id')->limit(20)->get();

        $voucherWithoutItemsQuery = DB::table('vouchers')
            ->leftJoin('voucher_items', function ($join) {
                $join->on('voucher_items.voucher_id', '=', 'vouchers.id')
                    ->whereNull('voucher_items.deleted_at');
            })
            ->whereNull('vouchers.deleted_at')
            ->whereNull('voucher_items.id')
            ->select('vouchers.id', 'vouchers.voucher_number', 'vouchers.voucher_date_en', 'vouchers.document_type', 'vouchers.status');
        $this->applyAccountantReviewVoucherScope($voucherWithoutItemsQuery, $user, $fromDate, $toDate);

        $missingAccountQuery = DB::table('voucher_items')
            ->join('vouchers', 'voucher_items.voucher_id', '=', 'vouchers.id')
            ->leftJoin('accounts', 'voucher_items.account_id', '=', 'accounts.id')
            ->whereNull('voucher_items.deleted_at')
            ->whereNull('vouchers.deleted_at')
            ->where(function ($query) {
                $query->whereNull('voucher_items.account_id')->orWhereNull('accounts.id');
            })
            ->select('voucher_items.id', 'voucher_items.voucher_id', 'voucher_items.account_id', 'vouchers.voucher_number', 'vouchers.voucher_date_en', 'vouchers.document_type');
        $this->applyAccountantReviewVoucherScope($missingAccountQuery, $user, $fromDate, $toDate);

        $receiptWithoutVoucherQuery = DB::table('receipts')
            ->leftJoin('vouchers', function ($join) {
                $join->on('vouchers.source_id', '=', 'receipts.id')
                    ->where('vouchers.source_type', Receipt::class)
                    ->where('vouchers.document_type', 'inventory_receipt')
                    ->whereNull('vouchers.deleted_at');
            })
            ->whereNull('receipts.deleted_at')
            ->where('receipts.document_status', 'approved')
            ->whereNull('vouchers.id')
            ->select('receipts.id', 'receipts.store_id', 'receipts.number', 'receipts.date_en', 'receipts.type', 'receipts.return_source_receipt_id');
        $this->applyAccountantReviewTenantScope($receiptWithoutVoucherQuery, $user, 'receipts.tenant_id');
        if ($fromDate) {
            $receiptWithoutVoucherQuery->whereDate('receipts.date_en', '>=', $fromDate);
        }
        if ($toDate) {
            $receiptWithoutVoucherQuery->whereDate('receipts.date_en', '<=', $toDate);
        }

        $negativeBalanceQuery = DB::table('inventory_balances')
            ->leftJoin('products', 'inventory_balances.product_id', '=', 'products.id')
            ->leftJoin('stores', 'inventory_balances.store_id', '=', 'stores.id')
            ->where(function ($query) {
                $query->where('inventory_balances.quantity', '<', 0)
                    ->orWhere('inventory_balances.quantity_sub_unit', '<', 0)
                    ->orWhereRaw('COALESCE(inventory_balances.reserved_quantity, 0) > COALESCE(inventory_balances.quantity, 0)');
            })
            ->select('inventory_balances.*')
            ->selectRaw('COALESCE(products.display_name, products.title) as product_name, stores.title as store_name');
        $this->applyAccountantReviewTenantScope($negativeBalanceQuery, $user, 'inventory_balances.tenant_id');

        $movementSums = DB::table('inventory_movements')
            ->select('tenant_id', 'store_id', 'warehouse_location_id', 'product_id')
            ->selectRaw("SUM(CASE WHEN direction = 'in' THEN quantity ELSE -quantity END) as ledger_quantity")
            ->selectRaw("SUM(CASE WHEN direction = 'in' THEN quantity_sub_unit ELSE -quantity_sub_unit END) as ledger_sub_unit")
            ->groupBy('tenant_id', 'store_id', 'warehouse_location_id', 'product_id');
        $this->applyAccountantReviewTenantScope($movementSums, $user, 'tenant_id');

        $ledgerMismatchQuery = DB::table('inventory_balances')
            ->joinSub($movementSums, 'movement_sums', function ($join) use ($tenantId, $user) {
                $join->on('movement_sums.store_id', '=', 'inventory_balances.store_id')
                    ->on('movement_sums.warehouse_location_id', '=', 'inventory_balances.warehouse_location_id')
                    ->on('movement_sums.product_id', '=', 'inventory_balances.product_id');

                if ((int) $user->isGod === 1) {
                    $join->whereRaw('COALESCE(movement_sums.tenant_id, 0) = COALESCE(inventory_balances.tenant_id, 0)');
                } else {
                    $join->where('movement_sums.tenant_id', $tenantId);
                }
            })
            ->leftJoin('products', 'inventory_balances.product_id', '=', 'products.id')
            ->leftJoin('stores', 'inventory_balances.store_id', '=', 'stores.id')
            ->where(function ($query) {
                $query->whereRaw('ABS(COALESCE(inventory_balances.quantity, 0) - COALESCE(movement_sums.ledger_quantity, 0)) > 0.001')
                    ->orWhereRaw('ABS(COALESCE(inventory_balances.quantity_sub_unit, 0) - COALESCE(movement_sums.ledger_sub_unit, 0)) > 0.001');
            })
            ->select('inventory_balances.product_id', 'inventory_balances.store_id', 'inventory_balances.warehouse_location_id', 'inventory_balances.quantity', 'inventory_balances.quantity_sub_unit')
            ->selectRaw('movement_sums.ledger_quantity, movement_sums.ledger_sub_unit')
            ->selectRaw('COALESCE(products.display_name, products.title) as product_name, stores.title as store_name');
        $this->applyAccountantReviewTenantScope($ledgerMismatchQuery, $user, 'inventory_balances.tenant_id');

        $returnReceiptsQuery = DB::table('receipts')
            ->whereNull('deleted_at')
            ->whereNotNull('return_source_receipt_id')
            ->select('id', 'store_id', 'number', 'date_en', 'type', 'return_source_receipt_id', 'return_reason', 'document_status')
            ->orderByDesc('id');
        $this->applyAccountantReviewTenantScope($returnReceiptsQuery, $user, 'tenant_id');

        return [
            'summary' => [
                'vouchers' => (int) ($voucherTotals->total ?? 0),
                'draft_vouchers' => (int) ($voucherTotals->draft_count ?? 0),
                'permanent_vouchers' => (int) ($voucherTotals->permanent_count ?? 0),
                'cancelled_vouchers' => (int) ($voucherTotals->cancelled_count ?? 0),
                'debit' => round((float) ($itemTotals->debit ?? 0), 2),
                'credit' => round((float) ($itemTotals->credit ?? 0), 2),
                'difference' => round((float) ($itemTotals->debit ?? 0) - (float) ($itemTotals->credit ?? 0), 2),
                'unbalanced_vouchers' => (int) $unbalancedCount,
                'vouchers_without_items' => (clone $voucherWithoutItemsQuery)->count(),
                'missing_account_items' => (clone $missingAccountQuery)->count(),
                'approved_receipts_without_voucher' => (clone $receiptWithoutVoucherQuery)->count(),
                'negative_balances' => (clone $negativeBalanceQuery)->count(),
                'ledger_balance_mismatches' => (clone $ledgerMismatchQuery)->count(),
                'return_receipts' => (clone $returnReceiptsQuery)->count(),
            ],
            'unbalanced_vouchers' => $unbalancedVouchers,
            'vouchers_without_items' => (clone $voucherWithoutItemsQuery)->orderByDesc('vouchers.id')->limit(20)->get(),
            'missing_account_items' => (clone $missingAccountQuery)->orderByDesc('voucher_items.id')->limit(20)->get(),
            'receipt_without_voucher' => (clone $receiptWithoutVoucherQuery)->orderByDesc('receipts.id')->limit(20)->get(),
            'negative_balances' => (clone $negativeBalanceQuery)->orderBy('inventory_balances.quantity')->limit(20)->get(),
            'ledger_mismatches' => (clone $ledgerMismatchQuery)->orderByDesc('inventory_balances.id')->limit(20)->get(),
            'return_receipts' => (clone $returnReceiptsQuery)->limit(20)->get(),
        ];
    }

    private function applyAccountantReviewVoucherScope($query, $user, ?string $fromDate, ?string $toDate): void
    {
        $this->applyAccountantReviewTenantScope($query, $user, 'vouchers.tenant_id');

        if ($fromDate) {
            $query->whereDate('vouchers.voucher_date_en', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('vouchers.voucher_date_en', '<=', $toDate);
        }
    }

    private function applyAccountantReviewTenantScope($query, $user, string $column): void
    {
        if ((int) $user->isGod !== 1) {
            $query->where($column, $this->currentTenantId($user));
        }
    }

    private function currentTenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }

    private function currentOrganizationId($user): ?int
    {
        $organizationId = $user?->organization_id;
        $decoded = is_string($organizationId) ? json_decode($organizationId, true) : null;

        if (is_array($decoded)) {
            return isset($decoded[0]) ? (int) $decoded[0] : null;
        }

        return $organizationId ? (int) $organizationId : null;
    }

    private function authorizeVoucherTenant(Voucher $voucher): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        if ((int) $voucher->tenant_id !== (int) ($user->tenant_id ?: $user->tenants_id)) {
            abort(403);
        }
    }

    private function authorizeVoucherTemplateTenant(VoucherTemplate $voucherTemplate): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        if ((int) $voucherTemplate->tenant_id !== (int) $this->currentTenantId($user)) {
            abort(403);
        }
    }

    private function authorizePayrollRunTenant(PayrollRun $payrollRun): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        if ((int) $payrollRun->tenant_id !== (int) $this->currentTenantId($user)) {
            abort(403);
        }
    }

    private function authorizeTreasuryInstrumentTenant(TreasuryInstrument $instrument): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        if ((int) $instrument->tenant_id !== (int) ($user->tenant_id ?: $user->tenants_id)) {
            abort(403);
        }
    }

    private function authorizePettyCashFundTenant(PettyCashFund $fund): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        if ((int) $fund->tenant_id !== (int) $this->currentTenantId($user)) {
            abort(403);
        }
    }

    private function authorizeBankStatementLineTenant(BankStatementLine $line): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        if ((int) $line->tenant_id !== (int) $this->currentTenantId($user)) {
            abort(403);
        }
    }
}

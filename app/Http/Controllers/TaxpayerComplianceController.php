<?php

namespace App\Http\Controllers;

use App\Models\CompanyAssetTaxInvoice;
use App\Models\ContractingProgressStatement;
use App\Models\Pishfactor;
use App\Models\Product;
use App\Models\TaxpayerInvoice;
use App\Models\TaxpayerItemMapping;
use App\Models\TaxpayerSetting;
use App\Services\TaxpayerComplianceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class TaxpayerComplianceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $this->currentTenantId($user);
        $scope = fn($query) => (int) $user->isGod !== 1 ? $query->where('tenant_id', $tenantId) : $query;

        $settings = TaxpayerSetting::query()
            ->when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $tenantId))
            ->latest('id')
            ->limit(10)
            ->get();
        $activeSetting = $settings->firstWhere('is_active', true) ?: $settings->first();
        $mappings = TaxpayerItemMapping::with('product')
            ->when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $tenantId))
            ->latest('id')
            ->limit(30)
            ->get();
        $invoices = TaxpayerInvoice::with(['items', 'logs', 'customer'])
            ->when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $tenantId))
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->get('status')))
            ->latest('id')
            ->paginate(15);
        $invoices->appends($request->query());
        $salesCandidates = Pishfactor::with('customer')
            ->whereIn('status', [1, 4])
            ->when((int) $user->isGod !== 1, fn($query) => $query->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)->orWhere('tenants_id', $tenantId);
            }))
            ->latest('id')
            ->limit(25)
            ->get();
        $contractingCandidates = ContractingProgressStatement::with('project.customer')
            ->whereIn('status', ['posted', 'approved', 'paid'])
            ->when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $tenantId))
            ->latest('id')
            ->limit(25)
            ->get();
        $assetCandidates = CompanyAssetTaxInvoice::with('asset')
            ->when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $tenantId))
            ->latest('id')
            ->limit(25)
            ->get();
        $products = Product::forOrganizations($user)->where('isActive', 1)->orderBy('title')->limit(200)->get(['id', 'title', 'display_name', 'sku', 'tax']);
        $totals = [
            'draft' => (clone $this->invoiceBaseQuery($user, $tenantId))->where('status', 'draft')->count(),
            'sent' => (clone $this->invoiceBaseQuery($user, $tenantId))->where('status', 'sent')->count(),
            'accepted' => (clone $this->invoiceBaseQuery($user, $tenantId))->where('status', 'accepted')->count(),
            'failed' => (clone $this->invoiceBaseQuery($user, $tenantId))->whereIn('status', ['failed', 'rejected'])->count(),
        ];

        return view('taxpayer.index', compact('settings', 'activeSetting', 'mappings', 'invoices', 'salesCandidates', 'contractingCandidates', 'assetCandidates', 'products', 'totals'));
    }

    public function storeSetting(Request $request, TaxpayerComplianceService $service)
    {
        $payload = $request->validate([
            'id' => ['nullable', 'integer', 'exists:taxpayer_settings,id'],
            'title' => ['nullable', 'string', 'max:191'],
            'send_mode' => ['required', 'in:direct,trusted_company,manual'],
            'environment' => ['required', 'in:sandbox,production'],
            'memory_id' => ['nullable', 'string', 'max:120'],
            'branch_tax_code' => ['nullable', 'string', 'max:80'],
            'economic_number' => ['nullable', 'string', 'max:80'],
            'seller_national_id' => ['nullable', 'string', 'max:80'],
            'seller_postal_code' => ['nullable', 'string', 'max:30'],
            'endpoint_url' => ['nullable', 'string', 'max:191'],
            'trusted_company_name' => ['nullable', 'string', 'max:191'],
            'certificate_alias' => ['nullable', 'string', 'max:191'],
            'auto_send' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $setting = $service->saveSetting($payload, Auth::user());

        Alert::success('ثبت شد', 'تنظیمات سامانه مودیان ' . $setting->title . ' ذخیره شد.');

        return redirect()->route('taxpayer.index');
    }

    public function storeMapping(Request $request, TaxpayerComplianceService $service)
    {
        $payload = $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'local_type' => ['required', 'in:product,service,fixed_asset'],
            'local_code' => ['nullable', 'string', 'max:120'],
            'local_title' => ['nullable', 'string', 'max:191'],
            'tax_item_id' => ['required', 'string', 'max:120'],
            'tax_item_title' => ['nullable', 'string', 'max:191'],
            'measurement_unit_code' => ['nullable', 'string', 'max:40'],
            'invoice_pattern' => ['required', 'in:sales,sales_return,service,contracting,fixed_asset'],
            'default_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $mapping = $service->saveMapping($payload, Auth::user());

        Alert::success('ثبت شد', 'نگاشت مالیاتی ' . $mapping->local_title . ' ذخیره شد.');

        return redirect()->route('taxpayer.index');
    }

    public function prepareSales(Pishfactor $factor, Request $request, TaxpayerComplianceService $service)
    {
        $this->authorizeTenant($factor->tenant_id ?: $factor->tenants_id);

        $invoice = $service->prepareFromSales($factor, $request->only(['invoice_number', 'issue_date_en', 'description']), Auth::user());

        Alert::success('آماده شد', 'پیش نویس مودیان ' . $invoice->invoice_number . ' از فاکتور فروش ساخته شد.');

        return redirect()->route('taxpayer.index');
    }

    public function prepareContracting(ContractingProgressStatement $statement, Request $request, TaxpayerComplianceService $service)
    {
        $this->authorizeTenant($statement->tenant_id);

        $invoice = $service->prepareFromContracting($statement, $request->only(['invoice_number', 'issue_date_en', 'description']), Auth::user());

        Alert::success('آماده شد', 'پیش نویس مودیان ' . $invoice->invoice_number . ' از صورت وضعیت ساخته شد.');

        return redirect()->route('taxpayer.index');
    }

    public function prepareAsset(CompanyAssetTaxInvoice $assetTaxInvoice, Request $request, TaxpayerComplianceService $service)
    {
        $this->authorizeTenant($assetTaxInvoice->tenant_id);

        $invoice = $service->prepareFromAssetInvoice($assetTaxInvoice, $request->only(['invoice_number', 'issue_date_en', 'buyer_name', 'buyer_economic_number', 'buyer_national_id', 'buyer_postal_code', 'buyer_address', 'description']), Auth::user());

        Alert::success('آماده شد', 'پیش نویس مودیان عمومی ' . $invoice->invoice_number . ' از فروش دارایی ساخته شد.');

        return redirect()->route('taxpayer.index');
    }

    public function updateStatus(TaxpayerInvoice $taxpayerInvoice, Request $request, TaxpayerComplianceService $service)
    {
        $this->authorizeTenant($taxpayerInvoice->tenant_id);

        $payload = $request->validate([
            'status' => ['required', 'in:sent,failed,accepted,rejected'],
            'tax_id' => ['nullable', 'string', 'max:120'],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'error_message' => ['nullable', 'string'],
        ]);

        $invoice = $service->updateStatus($taxpayerInvoice, $payload, Auth::user());

        Alert::success('بروزرسانی شد', 'وضعیت صورت حساب ' . $invoice->invoice_number . ' به ' . $invoice->status . ' تغییر کرد.');

        return redirect()->route('taxpayer.index');
    }

    private function invoiceBaseQuery($user, ?int $tenantId)
    {
        return TaxpayerInvoice::query()->when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $tenantId));
    }

    private function authorizeTenant(?int $tenantId): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        if ((int) $tenantId !== (int) $this->currentTenantId($user)) {
            abort(403);
        }
    }

    private function currentTenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }
}

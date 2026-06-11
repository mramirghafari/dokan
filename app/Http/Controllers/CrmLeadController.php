<?php

namespace App\Http\Controllers;

use App\Models\CrmLead;
use App\Models\CrmOpportunity;
use App\Models\Customers;
use App\Models\DataExchangeRun;
use App\Models\User;
use App\Services\CrmLeadBulkImportService;
use App\Services\DataExchangeService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CrmLeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:customers,user')->only(['index', 'store', 'convert', 'reject', 'import', 'importTemplate', 'importStatus']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $baseQuery = CrmLead::query()->with(['owner', 'customer', 'opportunity', 'duplicateCustomer', 'duplicateLead']);

        if ((int) $user->isGod !== 1) {
            $baseQuery->forOrganizations($user);
        }

        $filteredQuery = (clone $baseQuery)
            ->when($request->filled('stage'), fn($query) => $query->where('stage', $request->stage))
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->status))
            ->when($request->filled('source'), fn($query) => $query->where('source', $request->source))
            ->when($request->filled('owner_user_id'), fn($query) => $query->where('owner_user_id', $request->owner_user_id))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . trim($request->search) . '%';
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', $search)
                        ->orWhere('company_name', 'like', $search)
                        ->orWhere('mobile', 'like', $search)
                        ->orWhere('code', 'like', $search)
                        ->orWhere('campaign', 'like', $search);
                });
            });

        $leads = $filteredQuery->orderByRaw("FIELD(status, 'open', 'duplicate', 'converted', 'rejected')")
            ->orderByDesc('score')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $stats = [
            'open' => (clone $baseQuery)->where('status', 'open')->count(),
            'converted' => (clone $baseQuery)->where('status', 'converted')->count(),
            'duplicate' => (clone $baseQuery)->where('status', 'duplicate')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        $userColumns = array_values(array_filter(['id', 'name', Schema::hasColumn('users', 'tenant_id') ? 'tenant_id' : null, 'organization_id', 'isActive']));
        $usersQuery = User::query()->select($userColumns)->where('isActive', 1)->orderBy('name')->limit(200);
        if ((int) $user->isGod !== 1) {
            $usersQuery->forOrganizations($user);
        }

        return view('crm.leads.index', [
            'leads' => $leads,
            'stats' => $stats,
            'users' => $usersQuery->get(),
            'sources' => CrmLead::SOURCES,
            'stages' => CrmLead::STAGES,
            'statuses' => CrmLead::STATUSES,
            'priorities' => CrmLead::PRIORITIES,
            'opportunityStages' => CrmOpportunity::STAGES,
            'filters' => $request->only(['stage', 'status', 'source', 'owner_user_id', 'search']),
            'recentImports' => DataExchangeRun::query()
                ->where('entity_type', 'crm_leads')
                ->when((int) $user->isGod !== 1, function ($query) use ($user) {
                    $orgId = app(\App\Services\TenantContextService::class)->organizationId($user);
                    if ($orgId) {
                        $query->where('organization_id', $orgId);
                    }
                })
                ->latest('id')
                ->limit(5)
                ->get(),
        ]);
    }

    public function importTemplate(CrmLeadBulkImportService $importService): StreamedResponse
    {
        $headers = $importService->templateHeaders();

        return response()->streamDownload(function () use ($headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            fputcsv($out, ['علی نمونه', '09120000000', '', 'ali@example.com', 'شرکت نمونه', 'تهران', 'campaign', 'کمپین بهار', '50', 'normal', 'یادداشت نمونه']);
            fclose($out);
        }, 'crm-leads-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request, DataExchangeService $exchangeService)
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'default_source' => ['nullable', 'in:' . implode(',', array_keys(CrmLead::SOURCES))],
            'default_campaign' => ['nullable', 'string', 'max:160'],
            'update_existing' => ['nullable', 'boolean'],
        ]);

        $path = $request->file('file')->store('imports/crm-leads', 'local');
        $run = $exchangeService->dispatchLeadImport(Auth::user(), $path, [
            'default_source' => $data['default_source'] ?? 'campaign',
            'default_campaign' => $data['default_campaign'] ?? null,
            'update_existing' => $request->boolean('update_existing'),
        ]);

        Alert::success('Import در صف', 'فایل سرنخ در صف پردازش قرار گرفت. run #' . $run->id);

        return redirect()->route('crm.leads.index');
    }

    public function importStatus(DataExchangeRun $run)
    {
        $user = Auth::user();

        if ((int) $user->isGod !== 1 && (int) $run->organization_id !== (int) app(\App\Services\TenantContextService::class)->organizationId($user)) {
            abort(403);
        }

        return response()->json([
            'id' => $run->id,
            'status' => $run->status,
            'total_rows' => $run->total_rows,
            'success_rows' => $run->success_rows,
            'failed_rows' => $run->failed_rows,
            'summary' => $run->summary_json,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:160'],
            'city' => ['nullable', 'string', 'max:120'],
            'source' => ['required', 'in:' . implode(',', array_keys(CrmLead::SOURCES))],
            'campaign' => ['nullable', 'string', 'max:160'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'priority' => ['required', 'in:' . implode(',', array_keys(CrmLead::PRIORITIES))],
            'owner_user_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $tenantId = $this->tenantId($user);
        $organizationId = $this->organizationId($user);
        $duplicateCustomer = $this->duplicateCustomer($data['mobile'] ?? null, $tenantId, $organizationId);
        $duplicateLead = $this->duplicateLead($data['mobile'] ?? null, $tenantId, $organizationId);
        $status = $duplicateCustomer || $duplicateLead ? 'duplicate' : 'open';
        $nextId = (int) CrmLead::withTrashed()->max('id') + 1;

        CrmLead::create([
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'owner_user_id' => $data['owner_user_id'] ?: $user->id,
            'code' => 'LEAD-' . now()->format('Y') . '-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT),
            'name' => $data['name'],
            'company_name' => $data['company_name'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'city' => $data['city'] ?? null,
            'source' => $data['source'],
            'campaign' => $data['campaign'] ?? null,
            'score' => $data['score'] ?? 0,
            'stage' => $status === 'duplicate' ? 'new' : 'new',
            'status' => $status,
            'priority' => $data['priority'],
            'duplicate_status' => $duplicateCustomer ? 'customer' : ($duplicateLead ? 'lead' : 'none'),
            'duplicate_customer_id' => optional($duplicateCustomer)->id,
            'duplicate_lead_id' => optional($duplicateLead)->id,
            'notes' => $data['notes'] ?? null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        Alert::success('ثبت شد', $status === 'duplicate' ? 'سرنخ ثبت شد و به عنوان تکراری علامت خورد.' : 'سرنخ CRM ثبت شد.');

        return redirect()->route('crm.leads.index');
    }

    public function convert(Request $request, CrmLead $lead)
    {
        $this->authorizeLead($lead);
        abort_if($lead->status === 'converted', 422);

        $data = $request->validate([
            'customer_id' => ['nullable', 'integer'],
            'create_opportunity' => ['nullable', 'boolean'],
            'opportunity_title' => ['nullable', 'string', 'max:180'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'probability_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_close_date_en' => ['nullable', 'date'],
            'next_action_date_en' => ['nullable', 'date'],
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($lead, $data, $user) {
            $customer = !empty($data['customer_id'])
                ? $this->resolveCustomer($data['customer_id'], $user)
                : $this->createCustomerFromLead($lead, $user);

            $opportunity = null;

            if (($data['create_opportunity'] ?? true)) {
                $opportunity = $this->createOpportunityFromLead($lead, $customer, $data, $user);
            }

            $lead->update([
                'customer_id' => $customer->id,
                'opportunity_id' => optional($opportunity)->id,
                'stage' => 'converted',
                'status' => 'converted',
                'converted_at' => now(),
                'converted_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        });

        Alert::success('تبدیل شد', 'سرنخ به مشتری و در صورت انتخاب به فرصت فروش تبدیل شد.');

        return redirect()->route('crm.leads.index');
    }

    public function reject(Request $request, CrmLead $lead)
    {
        $this->authorizeLead($lead);

        $data = $request->validate([
            'reject_reason' => ['required', 'string', 'max:2000'],
        ]);

        $lead->update([
            'stage' => 'rejected',
            'status' => 'rejected',
            'reject_reason' => $data['reject_reason'],
            'updated_by' => Auth::id(),
        ]);

        Alert::warning('رد شد', 'سرنخ بدون حذف داده رد شد.');

        return redirect()->route('crm.leads.index');
    }

    private function createCustomerFromLead(CrmLead $lead, $user): Customers
    {
        return Customers::create([
            'tenant_id' => $lead->tenant_id ?: $this->tenantId($user),
            'organization_id' => $lead->organization_id ?: $this->organizationId($user),
            'name' => $lead->company_name ?: $lead->name,
            'national_id' => '',
            'mobile' => $lead->mobile,
            'phone' => $lead->phone ?: $lead->mobile ?: '-',
            'region_id' => 0,
            'area' => 0,
            'address' => $lead->city ?: '',
            'status' => 1,
            'leader_id' => $lead->owner_user_id ?: $user->id,
            'created_by' => $user->id,
        ]);
    }

    private function createOpportunityFromLead(CrmLead $lead, Customers $customer, array $data, $user): CrmOpportunity
    {
        $nextId = (int) CrmOpportunity::withTrashed()->max('id') + 1;
        $expectedCloseDate = !empty($data['expected_close_date_en']) ? Carbon::parse($data['expected_close_date_en'])->toDateString() : null;
        $nextActionDate = !empty($data['next_action_date_en']) ? Carbon::parse($data['next_action_date_en'])->toDateString() : null;

        return CrmOpportunity::create([
            'tenant_id' => $customer->tenant_id ?: $lead->tenant_id,
            'organization_id' => $customer->organization_id ?: $lead->organization_id,
            'customer_id' => $customer->id,
            'assigned_user_id' => $lead->owner_user_id ?: $user->id,
            'source_lead_id' => $lead->id,
            'code' => 'OPP-' . now()->format('Y') . '-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT),
            'title' => $data['opportunity_title'] ?: ('فرصت فروش ' . $lead->name),
            'stage' => 'qualified',
            'priority' => $lead->priority,
            'status' => 'open',
            'amount' => $data['amount'] ?? 0,
            'probability_percent' => $data['probability_percent'] ?? max(20, min(90, $lead->score)),
            'expected_close_date_en' => $expectedCloseDate,
            'expected_close_date_fa' => $expectedCloseDate ? verta($expectedCloseDate)->format('Y/m/d') : null,
            'next_action_date_en' => $nextActionDate,
            'next_action_date_fa' => $nextActionDate ? verta($nextActionDate)->format('Y/m/d') : null,
            'description' => trim(($lead->notes ?: '') . "\n" . 'منبع سرنخ: ' . $lead->sourceText() . ($lead->campaign ? ' / کمپین: ' . $lead->campaign : '')),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    private function duplicateCustomer(?string $mobile, ?int $tenantId, ?int $organizationId): ?Customers
    {
        if (!$mobile) {
            return null;
        }

        return Customers::query()
            ->where('mobile', $mobile)
            ->when($tenantId, fn($query) => $query->where('tenant_id', $tenantId))
            ->when($organizationId, fn($query) => $query->where('organization_id', $organizationId))
            ->first();
    }

    private function duplicateLead(?string $mobile, ?int $tenantId, ?int $organizationId): ?CrmLead
    {
        if (!$mobile) {
            return null;
        }

        return CrmLead::query()
            ->where('mobile', $mobile)
            ->whereIn('status', ['open', 'duplicate'])
            ->when($tenantId, fn($query) => $query->where('tenant_id', $tenantId))
            ->when($organizationId, fn($query) => $query->where('organization_id', $organizationId))
            ->latest('id')
            ->first();
    }

    private function resolveCustomer($customerId, $user): Customers
    {
        $query = Customers::query();

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->findOrFail($customerId);
    }

    private function authorizeLead(CrmLead $lead): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        abort_unless(CrmLead::query()->whereKey($lead->id)->forOrganizations($user)->exists(), 403);
    }

    private function tenantId($user): ?int
    {
        return $user ? (int) ($user->tenant_id ?: $user->tenants_id) ?: null : null;
    }

    private function organizationId($user): ?int
    {
        if (!$user || empty($user->organization_id)) {
            return null;
        }

        $decoded = json_decode((string) $user->organization_id, true);

        if (is_array($decoded)) {
            return (int) ($decoded[0] ?? 0) ?: null;
        }

        return (int) $user->organization_id ?: null;
    }
}

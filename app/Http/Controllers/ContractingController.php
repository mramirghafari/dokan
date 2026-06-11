<?php

namespace App\Http\Controllers;

use App\Models\Accounts;
use App\Models\ContractingProject;
use App\Models\CostCenter;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ContractingProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class ContractingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $this->currentTenantId($user);

        $projectsQuery = ContractingProject::with([
            'customer',
            'manager',
            'costCenter',
            'items',
            'progressStatements.voucher',
            'guarantees.voucher',
            'costEntries.voucher',
            'costEntries.supplier',
        ])->latest('id');

        if ((int) $user->isGod !== 1) {
            $projectsQuery->where('tenant_id', $tenantId);
        }

        if ($request->filled('status')) {
            $projectsQuery->where('status', $request->get('status'));
        }

        $projects = $projectsQuery->paginate(10);
        $projects->appends($request->query());
        $projectItems = collect($projects->items());
        $managers = User::query()->orderBy('name')->limit(200)->get(['id', 'name', 'username']);
        $accounts = $this->accountingAccounts($user);
        $costCenters = CostCenter::query()
            ->when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $tenantId))
            ->where('is_active', 1)
            ->orderBy('code')
            ->orderBy('name')
            ->limit(200)
            ->get();
        $suppliers = Supplier::query()->orderBy('title')->orderBy('name')->limit(200)->get();
        $today = now()->toDateString();
        $baseTotals = ContractingProject::query()->when((int) $user->isGod !== 1, fn($query) => $query->where('tenant_id', $tenantId));
        $totals = [
            'projects' => (clone $baseTotals)->count(),
            'active' => (clone $baseTotals)->where('status', 'active')->count(),
            'contract_amount' => (clone $baseTotals)->sum('contract_amount'),
            'statements' => $projectItems->sum(fn($project) => $project->progressStatements->sum('current_amount')),
            'costs' => $projectItems->sum(fn($project) => $project->costEntries->sum('total_amount')),
            'guarantees' => $projectItems->sum(fn($project) => $project->guarantees->where('status', 'active')->sum('amount')),
        ];

        return view('contracting.projects', compact('projects', 'managers', 'accounts', 'costCenters', 'suppliers', 'today', 'totals'));
    }

    public function storeProject(Request $request, ContractingProjectService $service)
    {
        $payload = $request->validate([
            'project_code' => ['nullable', 'string', 'max:60'],
            'title' => ['required', 'string', 'max:191'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'contract_number' => ['nullable', 'string', 'max:80'],
            'contract_type' => ['required', 'in:construction,service,maintenance,supply,other'],
            'status' => ['required', 'in:draft,active,suspended,closed'],
            'start_date_en' => ['nullable', 'date'],
            'end_date_en' => ['nullable', 'date'],
            'contract_amount' => ['nullable', 'numeric', 'min:0'],
            'approved_budget' => ['nullable', 'numeric', 'min:0'],
            'retention_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'advance_payment_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'performance_bond_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vat_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'receivable_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'revenue_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'advance_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'retention_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'tax_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'cost_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'payable_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'cost_center_id' => ['nullable', 'integer', 'exists:cost_centers,id'],
            'project_manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'description' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_code' => ['nullable', 'string', 'max:80'],
            'items.*.title' => ['nullable', 'string', 'max:191'],
            'items.*.unit' => ['nullable', 'string', 'max:40'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.description' => ['nullable', 'string'],
        ]);

        $project = $service->createProject($payload, Auth::user());

        Alert::success('ثبت شد', 'پروژه پیمانکاری ' . $project->project_code . ' با فهرست بها ساخته شد.');

        return redirect()->route('contracting.projects');
    }

    public function storeProgressStatement(Request $request, ContractingProject $project, ContractingProjectService $service)
    {
        $this->authorizeProjectTenant($project);

        $payload = $request->validate([
            'statement_number' => ['nullable', 'string', 'max:80'],
            'statement_date_en' => ['nullable', 'date'],
            'period_from_en' => ['nullable', 'date'],
            'period_to_en' => ['nullable', 'date'],
            'retention_amount' => ['nullable', 'numeric', 'min:0'],
            'advance_deduction_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,posted,approved,paid'],
            'description' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.contracting_project_item_id' => ['required', 'integer', 'exists:contracting_project_items,id'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.description' => ['nullable', 'string'],
        ]);

        $statement = $service->createProgressStatement($project, $payload, Auth::user());

        Alert::success('ثبت شد', 'صورت وضعیت ' . $statement->statement_number . ' و سند حسابداری آن ثبت شد.');

        return redirect()->route('contracting.projects');
    }

    public function storeGuarantee(Request $request, ContractingProject $project, ContractingProjectService $service)
    {
        $this->authorizeProjectTenant($project);

        $payload = $request->validate([
            'guarantee_number' => ['nullable', 'string', 'max:80'],
            'guarantee_type' => ['required', 'in:bid,performance,advance,retention,other'],
            'issuer' => ['nullable', 'string', 'max:191'],
            'beneficiary' => ['nullable', 'string', 'max:191'],
            'amount' => ['required', 'numeric', 'min:1'],
            'issue_date_en' => ['nullable', 'date'],
            'expiry_date_en' => ['nullable', 'date'],
            'status' => ['required', 'in:active,released,expired,confiscated'],
            'description' => ['nullable', 'string'],
        ]);

        $guarantee = $service->createGuarantee($project, $payload, Auth::user());

        Alert::success('ثبت شد', 'ضمانت نامه ' . $guarantee->guarantee_number . ' و سند انتظامی آن ثبت شد.');

        return redirect()->route('contracting.projects');
    }

    public function storeCostEntry(Request $request, ContractingProject $project, ContractingProjectService $service)
    {
        $this->authorizeProjectTenant($project);

        $payload = $request->validate([
            'cost_number' => ['nullable', 'string', 'max:80'],
            'cost_date_en' => ['nullable', 'date'],
            'cost_type' => ['required', 'in:direct,material,labor,equipment,subcontract,overhead,other'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'cost_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'tax_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'payable_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'status' => ['required', 'in:draft,posted,approved,paid'],
            'description' => ['nullable', 'string'],
        ]);

        $costEntry = $service->createCostEntry($project, $payload, Auth::user());

        Alert::success('ثبت شد', 'هزینه پروژه ' . $costEntry->cost_number . ' و سند حسابداری آن ثبت شد.');

        return redirect()->route('contracting.projects');
    }

    private function accountingAccounts($user)
    {
        $query = Accounts::query()->where('isActive', 1)->orderBy('code')->orderBy('name');

        if ((int) $user->isGod !== 1) {
            $tenantId = $this->currentTenantId($user);
            $query->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)->orWhere('tenants_id', $tenantId);
            });
        }

        return $query->limit(300)->get();
    }

    private function authorizeProjectTenant(ContractingProject $project): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        if ((int) $project->tenant_id !== (int) $this->currentTenantId($user)) {
            abort(403);
        }
    }

    private function currentTenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }
}

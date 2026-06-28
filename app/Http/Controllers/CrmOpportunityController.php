<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use App\Models\CrmOpportunity;
use App\Services\CrmAdvancedAutomationService;
use App\Models\Customers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RealRashid\SweetAlert\Facades\Alert;

class CrmOpportunityController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:customers,user')->only(['index', 'store', 'updateStage']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $baseQuery = CrmOpportunity::query()->with(['customer', 'assignedUser', 'creator']);

        if ((int) $user->isGod !== 1) {
            $baseQuery->forOrganizations($user);
        }

        $filteredQuery = (clone $baseQuery)
            ->when($request->filled('stage'), fn($query) => $query->where('stage', $request->stage))
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->status))
            ->when($request->filled('priority'), fn($query) => $query->where('priority', $request->priority))
            ->when($request->filled('assigned_user_id'), fn($query) => $query->where('assigned_user_id', $request->assigned_user_id))
            ->when($request->boolean('overdue'), fn($query) => $query->where('status', 'open')->whereDate('next_action_date_en', '<', now()->toDateString()))
            ->when($request->get('close_month') === 'current', fn($query) => $query->where('status', 'open')->whereBetween('expected_close_date_en', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]))
            ->when($request->boolean('stale'), fn($query) => $query->where('status', 'open')->whereRaw('DATEDIFF(CURDATE(), COALESCE(updated_at, created_at)) > 30'))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . trim($request->search) . '%';
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', $search)
                        ->orWhere('code', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhereHas('customer', fn($customerQuery) => $customerQuery->where('name', 'like', $search));
                });
            });

        $opportunities = $filteredQuery->orderByRaw("FIELD(status, 'open', 'won', 'lost', 'canceled')")
            ->orderByRaw("FIELD(stage, 'negotiation', 'proposal', 'qualified', 'new', 'won', 'lost')")
            ->orderByRaw('next_action_date_en IS NULL')
            ->orderBy('next_action_date_en')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $stats = [
            'open_count' => (clone $baseQuery)->where('status', 'open')->count(),
            'open_amount' => (clone $baseQuery)->where('status', 'open')->sum('amount'),
            'weighted_amount' => (clone $baseQuery)->where('status', 'open')->selectRaw('COALESCE(SUM(amount * probability_percent / 100), 0) as weighted_amount')->value('weighted_amount'),
            'overdue' => (clone $baseQuery)->where('status', 'open')->whereDate('next_action_date_en', '<', now()->toDateString())->count(),
            'won_month' => (clone $baseQuery)->where('status', 'won')->whereBetween('closed_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
        ];

        $stageSummaries = (clone $baseQuery)
            ->select('stage', DB::raw('COUNT(*) as count'), DB::raw('COALESCE(SUM(amount), 0) as amount'))
            ->where('status', 'open')
            ->groupBy('stage')
            ->get()
            ->keyBy('stage');

        $userColumns = array_values(array_filter(['id', 'name', Schema::hasColumn('users', 'tenant_id') ? 'tenant_id' : null, 'organization_id', 'isActive']));
        $usersQuery = User::query()->select($userColumns)->where('isActive', 1)->orderBy('name')->limit(200);
        if ((int) $user->isGod !== 1) {
            $usersQuery->forOrganizations($user);
        }

        return view('crm.opportunities.index', [
            'opportunities' => $opportunities,
            'stats' => $stats,
            'stageSummaries' => $stageSummaries,
            'users' => $usersQuery->get(),
            'stages' => CrmOpportunity::STAGES,
            'statuses' => CrmOpportunity::STATUSES,
            'priorities' => CrmOpportunity::PRIORITIES,
            'filters' => $request->only(['stage', 'status', 'priority', 'assigned_user_id', 'search', 'overdue', 'close_month', 'stale']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'integer'],
            'assigned_user_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:180'],
            'stage' => ['required', 'in:' . implode(',', array_keys(CrmOpportunity::STAGES))],
            'priority' => ['required', 'in:' . implode(',', array_keys(CrmOpportunity::PRIORITIES))],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'probability_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'expected_close_date_en' => ['nullable', 'date'],
            'next_action_date_en' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $customer = $this->resolveCustomer($data['customer_id'], $user);
        $expectedCloseDate = !empty($data['expected_close_date_en']) ? Carbon::parse($data['expected_close_date_en'])->toDateString() : null;
        $nextActionDate = !empty($data['next_action_date_en']) ? Carbon::parse($data['next_action_date_en'])->toDateString() : null;

        DB::transaction(function () use ($data, $user, $customer, $expectedCloseDate, $nextActionDate) {
            $nextId = (int) CrmOpportunity::withTrashed()->max('id') + 1;

            CrmOpportunity::create([
                'tenant_id' => $customer->tenant_id ?: $user->tenant_id,
                'organization_id' => $customer->organization_id ?: $user->organization_id,
                'customer_id' => $customer->id,
                'assigned_user_id' => $data['assigned_user_id'] ?: $user->id,
                'code' => 'OPP-' . now()->format('Y') . '-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT),
                'title' => $data['title'],
                'stage' => $data['stage'],
                'priority' => $data['priority'],
                'status' => in_array($data['stage'], ['won', 'lost'], true) ? $data['stage'] : 'open',
                'amount' => $data['amount'] ?? 0,
                'probability_percent' => $data['probability_percent'],
                'expected_close_date_en' => $expectedCloseDate,
                'expected_close_date_fa' => $expectedCloseDate ? verta($expectedCloseDate)->format('Y/m/d') : null,
                'next_action_date_en' => $nextActionDate,
                'next_action_date_fa' => $nextActionDate ? verta($nextActionDate)->format('Y/m/d') : null,
                'description' => $data['description'] ?? null,
                'closed_at' => in_array($data['stage'], ['won', 'lost'], true) ? now() : null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        });

        ActivityLogService::safeLog('create', 'CRM: store', null, ['section' => 'crm', 'event_key' => 'crm.store']);

        Alert::success('ثبت شد', 'فرصت فروش با موفقیت در pipeline ثبت شد.');

        return redirect()->route('crm.opportunities.index');
    }

    public function updateStage(Request $request, CrmOpportunity $opportunity)
    {
        $this->authorizeOpportunity($opportunity);

        $data = $request->validate([
            'stage' => ['required', 'in:' . implode(',', array_keys(CrmOpportunity::STAGES))],
            'status' => ['required', 'in:' . implode(',', array_keys(CrmOpportunity::STATUSES))],
            'probability_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'next_action_date_en' => ['nullable', 'date'],
            'outcome' => ['nullable', 'string'],
            'lost_reason' => ['nullable', 'string'],
        ]);

        if (in_array($data['stage'], ['won', 'lost'], true)) {
            $data['status'] = $data['stage'];
        }

        if ($data['status'] === 'won') {
            $data['stage'] = 'won';
        }

        if ($data['status'] === 'lost') {
            $data['stage'] = 'lost';
            app(CrmAdvancedAutomationService::class)->assertLostReason($data['lost_reason'] ?? null, $opportunity->tenant_id);
        }

        $nextActionDate = !empty($data['next_action_date_en']) ? Carbon::parse($data['next_action_date_en'])->toDateString() : null;
        $closed = in_array($data['status'], ['won', 'lost', 'canceled'], true);

        $opportunity->update([
            'stage' => $data['stage'],
            'status' => $data['status'],
            'probability_percent' => $data['probability_percent'],
            'next_action_date_en' => $nextActionDate,
            'next_action_date_fa' => $nextActionDate ? verta($nextActionDate)->format('Y/m/d') : null,
            'outcome' => $data['outcome'] ?? $opportunity->outcome,
            'lost_reason' => $data['lost_reason'] ?? $opportunity->lost_reason,
            'closed_at' => $closed ? ($opportunity->closed_at ?: now()) : null,
            'updated_by' => Auth::id(),
        ]);

        ActivityLogService::safeLog('update', 'CRM: Stage', null, ['section' => 'crm', 'event_key' => 'crm.updateStage']);

        Alert::success('بروزرسانی شد', 'مرحله فرصت فروش تغییر کرد.');

        return redirect()->route('crm.opportunities.index');
    }

    private function resolveCustomer($customerId, $user): Customers
    {
        $query = Customers::query();

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->findOrFail($customerId);
    }

    private function authorizeOpportunity(CrmOpportunity $opportunity): void
    {
        $user = Auth::user();

        if ((int) $user->isGod === 1) {
            return;
        }

        $allowed = CrmOpportunity::query()->whereKey($opportunity->id)->forOrganizations($user)->exists();

        abort_unless($allowed, 403);
    }
}

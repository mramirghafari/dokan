<?php

namespace App\Http\Controllers;

use App\Models\CrmCampaign;
use App\Models\CrmCampaignAudience;
use App\Models\CustomerLoyaltyAccount;
use App\Models\CustomerLoyaltyTransaction;
use App\Models\CustomerSegment;
use App\Models\Customers;
use App\Models\Pishfactor;
use App\Models\User;
use App\Services\CrmCampaignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use RealRashid\SweetAlert\Facades\Alert;

class CrmCampaignController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:customers,user')->only(['index', 'store', 'syncAudience', 'activateCampaign', 'recordResult', 'storeLoyaltyTransaction']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $baseQuery = CrmCampaign::query()->with(['targetSegment', 'owner']);

        if ((int) $user->isGod !== 1) {
            $baseQuery->forOrganizations($user);
        }

        $filteredQuery = (clone $baseQuery)
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->status))
            ->when($request->filled('channel'), fn($query) => $query->where('channel', $request->channel))
            ->when($request->filled('goal'), fn($query) => $query->where('goal', $request->goal))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . trim($request->search) . '%';
                $query->where(function ($inner) use ($search) {
                    $inner->where('code', 'like', $search)
                        ->orWhere('title', 'like', $search)
                        ->orWhere('discount_code', 'like', $search)
                        ->orWhere('description', 'like', $search);
                });
            });

        $campaigns = $filteredQuery->orderByRaw("FIELD(status, 'active', 'draft', 'paused', 'completed')")
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'active' => (clone $baseQuery)->where('status', 'active')->count(),
            'audience' => (clone $baseQuery)->sum('audience_count'),
            'conversions' => (clone $baseQuery)->sum('conversion_count'),
            'revenue' => (clone $baseQuery)->sum('actual_revenue'),
            'loyalty_accounts' => $this->loyaltyAccountQuery($user)->count(),
            'points_balance' => $this->loyaltyAccountQuery($user)->sum('points_balance'),
        ];

        $ordersQuery = Pishfactor::query()
            ->select(array_values(array_filter(['id', 'customer_id', 'invoiceID', 'fullPrice', 'pat_price', Schema::hasColumn('pishfactors', 'tenant_id') ? 'tenant_id' : null, 'organization_id', 'created_at'])))
            ->orderByDesc('id')
            ->limit(200);

        if ((int) $user->isGod !== 1) {
            $ordersQuery->forOrganizations($user);
        }

        $orders = $ordersQuery->get();

        return view('crm.campaigns.index', [
            'campaigns' => $campaigns,
            'stats' => $stats,
            'orders' => $orders,
            'segments' => $this->segmentsQuery($user)->get(),
            'users' => $this->usersQuery($user)->get(),
            'loyaltyAccounts' => $this->loyaltyAccountQuery($user)->with('customer')->latest('id')->limit(20)->get(),
            'recentTransactions' => $this->loyaltyTransactionQuery($user)->with(['customer', 'campaign'])->latest('id')->limit(20)->get(),
            'channels' => CrmCampaign::CHANNELS,
            'goals' => CrmCampaign::GOALS,
            'statuses' => CrmCampaign::STATUSES,
            'audienceStatuses' => CrmCampaignAudience::STATUSES,
            'tiers' => CustomerLoyaltyAccount::TIERS,
            'retentionStatuses' => CustomerLoyaltyAccount::RETENTION_STATUSES,
            'transactionTypes' => CustomerLoyaltyTransaction::TYPES,
            'filters' => $request->only(['status', 'channel', 'goal', 'search']),
        ]);
    }

    public function store(Request $request, CrmCampaignService $campaignService)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'channel' => ['required', 'in:' . implode(',', array_keys(CrmCampaign::CHANNELS))],
            'goal' => ['required', 'in:' . implode(',', array_keys(CrmCampaign::GOALS))],
            'status' => ['required', 'in:' . implode(',', array_keys(CrmCampaign::STATUSES))],
            'target_segment_id' => ['nullable', 'integer'],
            'owner_user_id' => ['nullable', 'integer'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'expected_revenue' => ['nullable', 'numeric', 'min:0'],
            'discount_code' => ['nullable', 'string', 'max:80'],
            'message_template' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $nextId = (int) CrmCampaign::withTrashed()->max('id') + 1;

        $draft = new CrmCampaign([
            'title' => $data['title'],
            'goal' => $data['goal'],
            'discount_code' => $data['discount_code'] ?? null,
        ]);

        CrmCampaign::create([
            'tenant_id' => $this->tenantId($user),
            'organization_id' => $this->organizationId($user),
            'target_segment_id' => $data['target_segment_id'] ?? null,
            'owner_user_id' => ($data['owner_user_id'] ?? null) ?: $user->id,
            'code' => 'CMP-' . now()->format('Y') . '-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT),
            'title' => $data['title'],
            'channel' => $data['channel'],
            'goal' => $data['goal'],
            'status' => $data['status'],
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'budget_amount' => $data['budget_amount'] ?? 0,
            'expected_revenue' => $data['expected_revenue'] ?? 0,
            'discount_code' => $data['discount_code'] ?? null,
            'message_template' => $campaignService->fixedMessageFor($draft),
            'description' => $data['description'] ?? null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        Alert::success('ثبت شد', 'کمپین CRM ثبت شد.');

        return redirect()->route('crm.campaigns.index');
    }

    public function syncAudience(Request $request, CrmCampaign $campaign, CrmCampaignService $campaignService)
    {
        $this->authorizeCampaign($campaign);

        $data = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
            'only_active' => ['nullable', 'boolean'],
        ]);

        $count = $campaignService->syncAudience($campaign, Auth::user(), $data + ['only_active' => $request->boolean('only_active', true)]);

        Alert::success('مخاطبان بروزرسانی شدند', number_format($count) . ' مشتری جدید به کمپین اضافه شد.');

        return redirect()->route('crm.campaigns.index');
    }

    public function activateCampaign(Request $request, CrmCampaign $campaign, CrmCampaignService $campaignService)
    {
        $this->authorizeCampaign($campaign);

        $data = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $result = $campaignService->dispatch($campaign, Auth::user(), $data);

        if (($result['mode'] ?? '') === 'fixed_template_only') {
            Alert::success('کمپین ثبت شد', number_format($result['audience_count']) . ' مخاطب با پیام ثابت علامت‌گذاری شد (بدون ارسال SMS).');
        } else {
            Alert::success('ارسال در صف قرار گرفت', number_format($result['audience_count']) . ' مخاطب در ' . ($result['queued_batches'] ?? 0) . ' batch');
        }

        return redirect()->route('crm.campaigns.index');
    }

    public function recordResult(Request $request, CrmCampaign $campaign, CrmCampaignService $campaignService)
    {
        $this->authorizeCampaign($campaign);

        $data = $request->validate([
            'customer_id' => ['required', 'integer'],
            'status' => ['required', 'in:' . implode(',', array_keys(CrmCampaignAudience::STATUSES))],
            'pishfactor_id' => ['nullable', 'integer'],
            'revenue_amount' => ['nullable', 'numeric', 'min:0'],
            'loyalty_points_awarded' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $campaignService->recordAudienceResult($campaign, Auth::user(), $data);

        Alert::success('نتیجه ثبت شد', 'نتیجه مخاطب، فروش منتسب و امتیاز وفاداری بروزرسانی شد.');

        return redirect()->route('crm.campaigns.index');
    }

    public function storeLoyaltyTransaction(Request $request, CrmCampaignService $campaignService)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'integer'],
            'crm_campaign_id' => ['nullable', 'integer'],
            'pishfactor_id' => ['nullable', 'integer'],
            'type' => ['required', 'in:' . implode(',', array_keys(CustomerLoyaltyTransaction::TYPES))],
            'points' => ['required', 'integer', 'min:-100000', 'max:100000'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $campaignService->addManualLoyaltyTransaction(Auth::user(), $data);

        Alert::success('امتیاز ثبت شد', 'تراکنش باشگاه مشتریان ثبت و مانده امتیاز بروزرسانی شد.');

        return redirect()->route('crm.campaigns.index');
    }

    private function authorizeCampaign(CrmCampaign $campaign): void
    {
        $user = Auth::user();
        if ((int) $user->isGod === 1) {
            return;
        }

        abort_unless(CrmCampaign::query()->whereKey($campaign->id)->forOrganizations($user)->exists(), 403);
    }

    private function customersQuery($user)
    {
        $columns = array_values(array_filter(['id', 'name', 'mobile', Schema::hasColumn('customers', 'tenant_id') ? 'tenant_id' : null, 'organization_id']));
        $query = Customers::query()->select($columns)->orderByDesc('id');
        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function segmentsQuery($user)
    {
        $query = CustomerSegment::query()->where('isActive', true)->orderBy('type')->orderBy('sort_order')->orderBy('title');
        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function usersQuery($user)
    {
        $columns = array_values(array_filter(['id', 'name', Schema::hasColumn('users', 'tenant_id') ? 'tenant_id' : null, 'organization_id', 'isActive']));
        $query = User::query()->select($columns)->where('isActive', 1)->orderBy('name')->limit(200);
        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function loyaltyAccountQuery($user)
    {
        $query = CustomerLoyaltyAccount::query();
        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function loyaltyTransactionQuery($user)
    {
        $query = CustomerLoyaltyTransaction::query();
        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
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

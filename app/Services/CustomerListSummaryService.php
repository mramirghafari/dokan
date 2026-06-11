<?php

namespace App\Services;

use App\Models\Area;
use App\Models\CustomerListScopeSummary;
use App\Models\Customers;
use App\Models\Pishfactor;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class CustomerListSummaryService
{
    public function __construct(
        private readonly CustomerListService $customerListService
    ) {
    }

    public function getIndexMetrics(User $user, Request $request): array
    {
        $summary = $this->resolveScopeSummary($user);

        return [
            'customers_total' => $this->filteredTotal($user, $request),
            'customers_with_purchase' => (int) $summary->customers_with_purchase,
            'restricted_customers' => (int) $summary->restricted_customers,
            'banned_customers' => (int) $summary->banned_customers,
            'summary_computed_at' => $summary->computed_at,
        ];
    }

    public function scopedTotal(User $user): int
    {
        return (int) $this->resolveScopeSummary($user)->total_customers;
    }

    public function filteredTotal(User $user, Request $request): int
    {
        if (!$this->customerListService->hasListFilters($request)) {
            return $this->scopedTotal($user);
        }

        $context = $this->customerListService->scopeContext($user);
        $cacheKey = $this->filteredCacheKey($context, $request);

        return (int) Cache::remember(
            $cacheKey,
            $this->filteredCacheTtl(),
            function () use ($user, $request) {
                $query = $this->customerListService->scopedQuery($user);

                return (int) $this->customerListService
                    ->applyFilters(clone $query, $request, $user)
                    ->count('customers.id');
            }
        );
    }

    public function refreshForUser(User $user): CustomerListScopeSummary
    {
        $context = $this->customerListService->scopeContext($user);
        $summary = $this->computeAndPersist($context);
        $this->forgetScopeCaches($context);

        return $summary;
    }

    public function refreshAllScopes(): int
    {
        $refreshed = 0;

        $contexts = [
            [
                'scope_type' => 'global',
                'scope_key' => 'global',
                'tenant_id' => null,
                'organization_id' => null,
            ],
        ];

        $organizationRows = Customers::query()
            ->select('tenant_id', 'organization_id')
            ->whereNotNull('organization_id')
            ->distinct()
            ->get();

        foreach ($organizationRows as $row) {
            $contexts[] = [
                'scope_type' => 'organization',
                'scope_key' => 'org:' . (int) $row->organization_id,
                'tenant_id' => $row->tenant_id ? (int) $row->tenant_id : null,
                'organization_id' => (int) $row->organization_id,
            ];
        }

        $leaderIds = Region::query()
            ->whereNotNull('leader_id')
            ->distinct()
            ->pluck('leader_id');

        foreach ($leaderIds as $leaderId) {
            $leader = User::query()->find($leaderId);
            $contexts[] = [
                'scope_type' => 'leader',
                'scope_key' => 'leader:' . (int) $leaderId,
                'tenant_id' => $leader?->tenant_id ? (int) $leader->tenant_id : null,
                'organization_id' => $leader?->organization_id ? (int) $leader->organization_id : null,
            ];
        }

        $uniqueContexts = collect($contexts)->unique(fn (array $context) => implode('|', [
            $context['scope_type'],
            $context['scope_key'],
            (string) ($context['tenant_id'] ?? ''),
            (string) ($context['organization_id'] ?? ''),
        ]));

        foreach ($uniqueContexts as $context) {
            $this->computeAndPersist($context);
            $this->forgetScopeCaches($context);
            $refreshed++;
        }

        return $refreshed;
    }

    public function invalidateForCustomer(Customers $customer): void
    {
        $this->forgetScopeCaches([
            'scope_type' => 'global',
            'scope_key' => 'global',
            'tenant_id' => null,
            'organization_id' => null,
        ]);

        if ($customer->organization_id) {
            $this->forgetScopeCaches([
                'scope_type' => 'organization',
                'scope_key' => 'org:' . (int) $customer->organization_id,
                'tenant_id' => $customer->tenant_id ? (int) $customer->tenant_id : null,
                'organization_id' => (int) $customer->organization_id,
            ]);
        }

        $leaderId = $this->resolveLeaderIdForCustomer($customer);
        if ($leaderId) {
            $leader = User::query()->find($leaderId);
            $this->forgetScopeCaches([
                'scope_type' => 'leader',
                'scope_key' => 'leader:' . $leaderId,
                'tenant_id' => $leader?->tenant_id ? (int) $leader->tenant_id : null,
                'organization_id' => $leader?->organization_id ? (int) $leader->organization_id : null,
            ]);
        }
    }

    public function invalidateForPishfactor(Pishfactor $pishfactor): void
    {
        if ($pishfactor->customer_id) {
            $customer = Customers::query()->find($pishfactor->customer_id);
            if ($customer) {
                $this->invalidateForCustomer($customer);
            }
        }
    }

    private function resolveScopeSummary(User $user): CustomerListScopeSummary
    {
        $context = $this->customerListService->scopeContext($user);
        $cacheKey = $this->scopeCacheKey($context);

        $cached = Cache::get($cacheKey);
        if ($cached instanceof CustomerListScopeSummary) {
            return $cached;
        }

        $summary = $this->findPersistedSummary($context);
        if ($summary && $summary->computed_at && $summary->computed_at->gte(now()->subSeconds($this->scopeCacheTtl()))) {
            Cache::put($cacheKey, $summary, $this->scopeCacheTtl());

            return $summary;
        }

        $summary = $this->computeAndPersist($context);
        Cache::put($cacheKey, $summary, $this->scopeCacheTtl());

        return $summary;
    }

    private function findPersistedSummary(array $context): ?CustomerListScopeSummary
    {
        return CustomerListScopeSummary::query()
            ->where('scope_type', $context['scope_type'])
            ->where('scope_key', $context['scope_key'])
            ->when($context['tenant_id'], fn (Builder $query) => $query->where('tenant_id', $context['tenant_id']))
            ->when(!$context['tenant_id'], fn (Builder $query) => $query->whereNull('tenant_id'))
            ->when($context['organization_id'], fn (Builder $query) => $query->where('organization_id', $context['organization_id']))
            ->when(!$context['organization_id'], fn (Builder $query) => $query->whereNull('organization_id'))
            ->first();
    }

    private function computeAndPersist(array $context): CustomerListScopeSummary
    {
        $baseQuery = $this->baseQueryForContext($context);

        $metrics = [
            'total_customers' => (clone $baseQuery)->count('customers.id'),
            'customers_with_purchase' => (clone $baseQuery)
                ->whereHas('pishfactors', function (Builder $query) {
                    $query->whereIn('status', [1, 4]);
                })
                ->count('customers.id'),
            'restricted_customers' => 0,
            'banned_customers' => (clone $baseQuery)->where('customers.status', 0)->count('customers.id'),
            'computed_at' => Carbon::now(),
        ];

        return CustomerListScopeSummary::query()->updateOrCreate(
            [
                'tenant_id' => $context['tenant_id'],
                'organization_id' => $context['organization_id'],
                'scope_type' => $context['scope_type'],
                'scope_key' => $context['scope_key'],
            ],
            $metrics
        );
    }

    private function baseQueryForContext(array $context): Builder
    {
        if ($context['scope_type'] === 'global') {
            return Customers::query()->select('customers.*');
        }

        if ($context['scope_type'] === 'leader') {
            return $this->customerListService->scopedQueryForContext($context);
        }

        $user = User::query()
            ->where('organization_id', $context['organization_id'])
            ->when($context['tenant_id'], fn (Builder $query) => $query->where('tenant_id', $context['tenant_id']))
            ->orderByDesc('isAdmin')
            ->orderByDesc('isActive')
            ->first();

        if ($user) {
            return $this->customerListService->scopedQuery($user);
        }

        return $this->customerListService->scopedQueryForContext($context);
    }

    private function resolveLeaderIdForCustomer(Customers $customer): ?int
    {
        if (!$customer->area) {
            return null;
        }

        $area = Area::query()->with('region:id,leader_id')->find($customer->area);

        return $area?->region?->leader_id ? (int) $area->region->leader_id : null;
    }

    private function scopeCacheKey(array $context): string
    {
        return 'customer_list_summary:' . md5(json_encode([
            $context['scope_type'],
            $context['scope_key'],
            $context['tenant_id'],
            $context['organization_id'],
        ]));
    }

    private function filteredCacheKey(array $context, Request $request): string
    {
        return 'customer_list_filtered:' . md5(json_encode([
            $context['scope_type'],
            $context['scope_key'],
            $context['tenant_id'],
            $context['organization_id'],
            $this->customerListService->filterFingerprint($request),
        ]));
    }

    private function forgetScopeCaches(array $context): void
    {
        Cache::forget($this->scopeCacheKey($context));
    }

    private function scopeCacheTtl(): int
    {
        return max((int) config('erp_scale.customer_list_summary.cache_ttl', 300), 60);
    }

    private function filteredCacheTtl(): int
    {
        return max((int) config('erp_scale.customer_list_summary.filtered_cache_ttl', 60), 15);
    }
}

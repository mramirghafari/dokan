<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class ErpLoadTestService
{
    public function __construct(
        private CustomerListService $customerListService,
        private CustomerListSummaryService $customerListSummaryService,
        private PishFactorListService $pishFactorListService,
        private BiSelfServiceReportService $biReportService,
    ) {}

    public function run(?User $user = null, bool $includeBi = true): array
    {
        $user = $this->resolveUser($user);
        $thresholds = (array) config('erp_scale.load_test.thresholds', []);

        $scenarios = [
            'customer_list_page_1' => $this->benchCustomerPage($user, 0, 50),
            'customer_list_page_50' => $this->benchCustomerPage($user, 2450, 50),
            'customer_search' => $this->benchCustomerSearch($user, (string) config('erp_scale.load_test.marker', 'STSCALE')),
            'pishfactor_list_page_1' => $this->benchPishfactorPage($user, 0, 50),
        ];

        if ($includeBi) {
            $scenarios['bi_refresh'] = $this->benchBiRefresh($user);
        }

        $checks = [];

        foreach ($scenarios as $key => $scenario) {
            $limit = (int) ($thresholds[$key . '_ms'] ?? $thresholds['default_ms'] ?? 2000);
            $checks[$key] = [
                'ms' => $scenario['ms'],
                'rows' => $scenario['rows'] ?? null,
                'limit_ms' => $limit,
                'passed' => $scenario['ms'] <= $limit,
            ];
        }

        return [
            'user_id' => $user->id,
            'scenarios' => $scenarios,
            'checks' => $checks,
            'passed' => collect($checks)->every(fn (array $check) => $check['passed']),
            'generated_at' => now(),
        ];
    }

    private function resolveUser(?User $user): User
    {
        if ($user) {
            return $user;
        }

        $configured = config('erp_scale.load_test.user_id');
        if ($configured) {
            return User::query()->findOrFail((int) $configured);
        }

        return User::query()->where('isGod', 1)->orderBy('id')->first()
            ?: User::query()->orderBy('id')->firstOrFail();
    }

    private function benchCustomerPage(User $user, int $start, int $length): array
    {
        $request = Request::create('/customers/datatable', 'GET', [
            'start' => $start,
            'length' => $length,
            'draw' => 1,
        ]);

        $started = microtime(true);
        $scopedQuery = $this->customerListService->scopedQuery($user);
        $this->customerListSummaryService->scopedTotal($user);
        $filteredQuery = $this->customerListService->applyFilters(clone $scopedQuery, $request, $user);
        $this->customerListSummaryService->filteredTotal($user, $request);

        $rows = (clone $filteredQuery)
            ->with(['region:id,name', 'Area:id,name', 'leader:id,name'])
            ->withCount('activeOrders as active_orders_count')
            ->withSum('activeOrders as active_orders_sum', 'fullPrice')
            ->orderByDesc('customers.id')
            ->skip($start)
            ->take($length)
            ->get();

        return [
            'ms' => (int) round((microtime(true) - $started) * 1000),
            'rows' => $rows->count(),
        ];
    }

    private function benchCustomerSearch(User $user, string $term): array
    {
        $request = Request::create('/customers/datatable', 'GET', [
            'start' => 0,
            'length' => 50,
            'draw' => 1,
            'codename' => $term,
            'search' => ['value' => $term],
        ]);

        $started = microtime(true);
        $query = $this->customerListService->applyFilters(
            $this->customerListService->scopedQuery($user),
            $request,
            $user
        );

        $rows = $query->orderByDesc('customers.id')->limit(50)->get(['customers.id', 'customers.name', 'customers.customer_code']);

        return [
            'ms' => (int) round((microtime(true) - $started) * 1000),
            'rows' => $rows->count(),
        ];
    }

    private function benchPishfactorPage(User $user, int $start, int $length): array
    {
        $started = microtime(true);

        $rows = $this->pishFactorListService
            ->scopedQuery($user, PishFactorListService::LIST_ACTIVE, [
                'isVisitor' => false,
                'isManager' => false,
                'isLeader' => false,
            ])
            ->orderByDesc('pishfactors.id')
            ->skip($start)
            ->take($length)
            ->get(['pishfactors.id', 'pishfactors.invoiceID', 'pishfactors.customer_id', 'pishfactors.fullPrice']);

        return [
            'ms' => (int) round((microtime(true) - $started) * 1000),
            'rows' => $rows->count(),
        ];
    }

    private function benchBiRefresh(User $user): array
    {
        $started = microtime(true);
        $this->biReportService->refreshEnterpriseDataMart($user);

        return [
            'ms' => (int) round((microtime(true) - $started) * 1000),
            'rows' => null,
        ];
    }
}

<?php

namespace App\Services;

use App\Models\Customers;
use App\Models\Employee;
use App\Models\Log;
use App\Models\Pishfactor;
use App\Models\PishFactorItems;
use App\Models\Product;
use App\Models\Region;
use App\Models\Area;
use App\Models\User;
use App\Models\Targets;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardService
{
    /**
     * کارت‌های آمار کلی بالای داشبورد
     */
    public function getStatCards(User $user, array $visitorIds, array $leaderIds): array
    {
        $orgId = $this->resolveOrgId($user);

        $activeCustomers = Customers::where('organization_id', $orgId)
            ->where('status', 1)
            ->count();

        $totalCustomers = Customers::where('organization_id', $orgId)->count();

        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd   = Carbon::now()->endOfMonth();

        $thisMonthFactors = Pishfactor::where('organization_id', $orgId)
            ->whereIn('status', [1, 4])
            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->count();

        $thisMonthAmount = Pishfactor::where('organization_id', $orgId)
            ->whereIn('status', [1, 4])
            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->sum(DB::raw("CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED)"));

        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd   = Carbon::now()->subMonth()->endOfMonth();

        $lastMonthAmount = Pishfactor::where('organization_id', $orgId)
            ->whereIn('status', [1, 4])
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum(DB::raw("CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED)"));

        $activeEmployees = Employee::count();

        $warehouseStock = 0;
        if (Schema::hasTable('stocks') && \App\Services\TenantSettings::enabled('feature_warehouse_management')) {
            $warehouseStock = DB::table('stocks')
                ->where('organization_id', $orgId)
                ->sum('quantity');
        }

        $salesTrend = $lastMonthAmount > 0
            ? round((($thisMonthAmount - $lastMonthAmount) / $lastMonthAmount) * 100, 1)
            : 0;

        return [
            'active_customers'   => $activeCustomers,
            'total_customers'    => $totalCustomers,
            'this_month_factors' => $thisMonthFactors,
            'this_month_amount'  => $thisMonthAmount,
            'last_month_amount'  => $lastMonthAmount,
            'sales_trend'        => $salesTrend,
            'active_employees'   => $activeEmployees,
            'warehouse_stock'    => $warehouseStock,
        ];
    }

    /**
     * سلسله مراتب تیم فروش با آمار
     */
    public function getSalesTeamHierarchy(User $user, array $leaderIds, array $visitorIds): array
    {
        $orgId = $this->resolveOrgId($user);

        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd   = Carbon::now()->endOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd   = Carbon::now()->subMonth()->endOfMonth();

        $leaders = User::whereIn('id', $leaderIds)
            ->select('id', 'name', 'username', 'leader_id', 'isActive')
            ->get();

        $visitorMetrics = Pishfactor::whereIn('visitor_id', $visitorIds)
            ->whereIn('status', [1, 4])
            ->select(
                'visitor_id',
                DB::raw('COUNT(DISTINCT customer_id) as customer_count'),
                DB::raw('COUNT(*) as factor_count'),
                DB::raw("SUM(CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED)) as total_amount"),
                DB::raw("SUM(CASE WHEN created_at >= '{$thisMonthStart}' AND created_at <= '{$thisMonthEnd}' THEN CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED) ELSE 0 END) as this_month_amount"),
                DB::raw("SUM(CASE WHEN created_at >= '{$lastMonthStart}' AND created_at <= '{$lastMonthEnd}' THEN CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED) ELSE 0 END) as last_month_amount")
            )
            ->groupBy('visitor_id')
            ->get()
            ->keyBy('visitor_id');

        $leaderMetrics = Pishfactor::whereIn('sarparast_id', $leaderIds)
            ->whereIn('status', [1, 4])
            ->select(
                'sarparast_id',
                DB::raw('COUNT(DISTINCT customer_id) as customer_count'),
                DB::raw('COUNT(*) as factor_count'),
                DB::raw("SUM(CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED)) as total_amount"),
                DB::raw("SUM(CASE WHEN created_at >= '{$thisMonthStart}' AND created_at <= '{$thisMonthEnd}' THEN CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED) ELSE 0 END) as this_month_amount"),
                DB::raw("SUM(CASE WHEN created_at >= '{$lastMonthStart}' AND created_at <= '{$lastMonthEnd}' THEN CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED) ELSE 0 END) as last_month_amount")
            )
            ->groupBy('sarparast_id')
            ->get()
            ->keyBy('sarparast_id');

        $visitors = User::whereIn('id', $visitorIds)
            ->select('id', 'name', 'username', 'leader_id', 'isActive')
            ->get()
            ->groupBy('leader_id');

        $team = [];

        foreach ($leaders as $leader) {
            $lm = $leaderMetrics->get($leader->id);
            $leaderVisitors = $visitors->get($leader->id, collect());

            $visitorsData = $leaderVisitors->map(function ($visitor) use ($visitorMetrics) {
                $vm = $visitorMetrics->get($visitor->id);
                $thisMo = $vm->this_month_amount ?? 0;
                $lastMo = $vm->last_month_amount ?? 0;
                $trend = $lastMo > 0 ? round((($thisMo - $lastMo) / $lastMo) * 100, 1) : 0;

                return [
                    'id'                => $visitor->id,
                    'name'              => $visitor->name,
                    'role'              => 'visitor',
                    'role_label'        => 'ویزیتور',
                    'is_active'         => $visitor->isActive,
                    'customer_count'    => $vm->customer_count ?? 0,
                    'factor_count'      => $vm->factor_count ?? 0,
                    'total_amount'      => $vm->total_amount ?? 0,
                    'this_month_amount' => $thisMo,
                    'last_month_amount' => $lastMo,
                    'trend'             => $trend,
                ];
            })->values()->toArray();

            $thisMoLeader = $lm->this_month_amount ?? 0;
            $lastMoLeader = $lm->last_month_amount ?? 0;
            $leaderTrend = $lastMoLeader > 0 ? round((($thisMoLeader - $lastMoLeader) / $lastMoLeader) * 100, 1) : 0;

            $team[] = [
                'id'                => $leader->id,
                'name'              => $leader->name,
                'role'              => 'leader',
                'role_label'        => 'سرپرست فروش',
                'is_active'         => $leader->isActive,
                'customer_count'    => $lm->customer_count ?? 0,
                'factor_count'      => $lm->factor_count ?? 0,
                'total_amount'      => $lm->total_amount ?? 0,
                'this_month_amount' => $thisMoLeader,
                'last_month_amount' => $lastMoLeader,
                'trend'             => $leaderTrend,
                'visitors'          => $visitorsData,
            ];
        }

        usort($team, fn($a, $b) => $b['total_amount'] <=> $a['total_amount']);

        return $team;
    }

    /**
     * نمودار فروش ماهانه ۶ ماه اخیر
     */
    public function getMonthlyChartData(User $user, int $months = 6): array
    {
        $orgId = $this->resolveOrgId($user);
        $labels = [];
        $amounts = [];

        $persianMonths = [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
            4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
            7 => 'مهر', 8 => 'آبان', 9 => 'آذر',
            10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
        ];

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end   = Carbon::now()->subMonths($i)->endOfMonth();

            $amount = Pishfactor::where('organization_id', $orgId)
                ->whereIn('status', [1, 4])
                ->whereBetween('created_at', [$start, $end])
                ->sum(DB::raw("CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED)"));

            $jalaali = \Hekmatinasser\Verta\Verta::instance($start);
            $labels[] = $persianMonths[$jalaali->month] ?? $start->format('M Y');
            $amounts[] = (int) $amount;
        }

        return [
            'labels'  => $labels,
            'amounts' => $amounts,
        ];
    }

    /**
     * آمار محصولات و موجودی
     */
    public function getProductSummary(User $user, array $visitorIds): array
    {
        $orgId = $this->resolveOrgId($user);

        $factorIds = Pishfactor::where('organization_id', $orgId)
            ->whereIn('status', [1, 4])
            ->whereBetween('created_at', [Carbon::now()->subDays(30), Carbon::now()])
            ->pluck('id');

        $topProducts = PishFactorItems::whereIn('pishfactor_id', $factorIds)
            ->select('pr_id', DB::raw('SUM(tedad + (pack * COALESCE(pack_items, 1))) as total_qty'), DB::raw('COUNT(*) as times_sold'))
            ->groupBy('pr_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->with('product:id,name,code')
            ->get()
            ->map(function ($item) {
                return [
                    'product_name' => $item->product->name ?? 'نامشخص',
                    'product_code' => $item->product->code ?? '-',
                    'total_qty'    => (int) $item->total_qty,
                    'times_sold'   => (int) $item->times_sold,
                ];
            });

        $lowStockProducts = collect();
        $totalStockItems  = 0;

        if (Schema::hasTable('stocks') && \App\Services\TenantSettings::enabled('feature_warehouse_management')) {
            $lowStockProducts = DB::table('stocks')
                ->join('products', 'stocks.product_id', '=', 'products.id')
                ->where('stocks.organization_id', $orgId)
                ->where('stocks.quantity', '>', 0)
                ->where('stocks.quantity', '<=', DB::raw('COALESCE(products.min_stock, 5)'))
                ->where('products.isMaterial', 0)
                ->select('products.id', 'products.name', 'products.code', 'stocks.quantity', 'products.min_stock')
                ->orderBy('stocks.quantity')
                ->take(5)
                ->get();

            $totalStockItems = DB::table('stocks')
                ->where('organization_id', $orgId)
                ->where('quantity', '>', 0)
                ->sum('quantity');
        }

        $totalProducts = Product::where('isMaterial', 0)->count();

        return [
            'top_products'      => $topProducts,
            'low_stock_products' => $lowStockProducts,
            'total_stock_items' => $totalStockItems,
            'total_products'    => $totalProducts,
        ];
    }

    /**
     * آمار مناطق و مسیرها
     */
    public function getRoutesSummary(User $user, array $leaderIds): array
    {
        if (!\App\Services\TenantSettings::enabled('feature_route_management')) {
            return ['enabled' => false];
        }

        $orgId = $this->resolveOrgId($user);

        $myRegions = Region::whereIn('leader_id', $leaderIds)->pluck('id');
        $activeRegions = Region::whereIn('leader_id', $leaderIds)->where('isActive', 1)->count();
        $totalRegions  = Region::whereIn('leader_id', $leaderIds)->count();

        $areas = Area::whereIn('region_id', $myRegions)->get();
        $totalAreas = $areas->count();

        $customersPerRegion = Region::whereIn('id', $myRegions)
            ->select('regions.id', 'regions.title')
            ->withCount(['areas as customer_count' => function ($q) {
                $q->join('customers', 'areas.id', '=', 'customers.area')
                  ->where('customers.status', 1);
            }])
            ->take(5)
            ->get()
            ->map(fn($r) => ['title' => $r->title, 'count' => $r->customer_count]);

        return [
            'enabled'             => true,
            'active_regions'      => $activeRegions,
            'total_regions'       => $totalRegions,
            'total_areas'         => $totalAreas,
            'customers_per_region' => $customersPerRegion,
        ];
    }

    /**
     * لاگ فعالیت سیستم
     */
    public function getActivityLog(User $user, int $limit = 20): array
    {
        $orgId    = $this->resolveOrgId($user);
        $tenantId = $this->resolveTenantId($user);

        $orgUserIds = User::where('organization_id', $orgId)->pluck('id');

        $rawLogs = DB::table('logs')
            ->whereIn('user_id', $orgUserIds)
            ->orderByDesc('id')
            ->take($limit)
            ->get();

        $userNames = User::whereIn('id', $rawLogs->pluck('user_id')->unique())
            ->pluck('name', 'id');

        $logs = $rawLogs->map(function ($log) use ($userNames) {
            $actionLabels = [
                'create'  => 'ایجاد',
                'update'  => 'ویرایش',
                'delete'  => 'حذف',
                'login'   => 'ورود',
                'logout'  => 'خروج',
                'approve' => 'تایید',
                'reject'  => 'رد',
                'view'    => 'مشاهده',
            ];

            $actionColors = [
                'create'  => 'success',
                'update'  => 'info',
                'delete'  => 'danger',
                'login'   => 'primary',
                'logout'  => 'secondary',
                'approve' => 'success',
                'reject'  => 'warning',
            ];

            $action = strtolower($log->action ?? '');

            $createdAt = $log->created_at ? Carbon::parse($log->created_at) : null;

            return [
                'id'           => $log->id,
                'user_name'    => $userNames[$log->user_id] ?? 'کاربر ناشناس',
                'action'       => $action,
                'action_label' => $actionLabels[$action] ?? $log->action,
                'action_color' => $actionColors[$action] ?? 'secondary',
                'description'  => $log->description ?? '',
                'ip'           => $log->ip ?? '',
                'created_at'   => $createdAt ? $createdAt->format('Y/m/d H:i') : '',
                'time_ago'     => $createdAt ? $createdAt->diffForHumans() : '',
            ];
        });

        return $logs->toArray();
    }

    /**
     * خلاصه مالی
     */
    public function getFinancialSummary(User $user): array
    {
        $orgId = $this->resolveOrgId($user);

        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd   = Carbon::now()->endOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd   = Carbon::now()->subMonth()->endOfMonth();

        $thisMonthRevenue = Pishfactor::where('organization_id', $orgId)
            ->whereIn('status', [1, 4])
            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->sum(DB::raw("CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED)"));

        $lastMonthRevenue = Pishfactor::where('organization_id', $orgId)
            ->whereIn('status', [1, 4])
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum(DB::raw("CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED)"));

        $pendingFactors = Pishfactor::where('organization_id', $orgId)
            ->whereIn('status', [0, 2, 3])
            ->count();

        $pendingAmount = Pishfactor::where('organization_id', $orgId)
            ->whereIn('status', [0, 2, 3])
            ->sum(DB::raw("CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED)"));

        $receivable = 0;
        $payable    = 0;

        if (Schema::hasTable('vouchers') && Schema::hasColumn('vouchers', 'type') && \App\Services\TenantSettings::enabled('feature_double_entry_accounting')) {
            $receivable = DB::table('vouchers')
                ->where('organization_id', $orgId)
                ->where('type', 'receivable')
                ->where('status', '!=', 'settled')
                ->sum('amount');

            $payable = DB::table('vouchers')
                ->where('organization_id', $orgId)
                ->where('type', 'payable')
                ->where('status', '!=', 'settled')
                ->sum('amount');
        }

        return [
            'this_month_revenue' => (int) $thisMonthRevenue,
            'last_month_revenue' => (int) $lastMonthRevenue,
            'revenue_trend'      => $lastMonthRevenue > 0
                ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
                : 0,
            'pending_factors'    => $pendingFactors,
            'pending_amount'     => (int) $pendingAmount,
            'receivable'         => (int) $receivable,
            'payable'            => (int) $payable,
        ];
    }

    /**
     * آخرین مشتریان ثبت‌شده با اطلاعات بازاریاب و کال‌سنتر
     */
    public function getRecentCustomers(User $user, int $limit = 10): array
    {
        $orgId = $this->resolveOrgId($user);

        $customers = Customers::where('organization_id', $orgId)
            ->orderByDesc('id')
            ->take($limit)
            ->get();

        $addedByIds = $customers->pluck('added_by')->filter()->unique();
        $visitorIds  = $customers->pluck('visitor_id')->filter()->unique();
        $mergedIds   = $addedByIds->merge($visitorIds)->unique();

        $userNames = User::whereIn('id', $mergedIds)
            ->pluck('name', 'id');

        $factorCounts = Pishfactor::whereIn('customer_id', $customers->pluck('id'))
            ->whereIn('status', [1, 4])
            ->select('customer_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('customer_id')
            ->pluck('cnt', 'customer_id');

        $balances = [];
        if (Schema::hasTable('customer_accounts')) {
            $balances = DB::table('customer_accounts')
                ->whereIn('customer_id', $customers->pluck('id'))
                ->where('organization_id', $orgId)
                ->select('customer_id', DB::raw('SUM(debit) - SUM(credit) as balance'))
                ->groupBy('customer_id')
                ->pluck('balance', 'customer_id');
        }

        return $customers->map(function ($c) use ($userNames, $factorCounts, $balances) {
            $jDate = null;
            try {
                $jDate = \Hekmatinasser\Verta\Verta::instance($c->created_at)->format('Y/m/d');
            } catch (\Exception $e) {
                $jDate = '-';
            }
            return [
                'id'            => $c->id,
                'name'          => $c->name ?? $c->company_name ?? 'نامشخص',
                'mobile'        => $c->mobile ?? '-',
                'marketer'      => $userNames[$c->visitor_id ?? 0] ?? '-',
                'added_by'      => $userNames[$c->added_by ?? 0] ?? '-',
                'factor_count'  => $factorCounts[$c->id] ?? 0,
                'balance'       => (int) ($balances[$c->id] ?? 0),
                'registered_at' => $jDate,
                'status'        => $c->status ?? 0,
            ];
        })->toArray();
    }

    /**
     * داده‌های هفتگی برای تب‌های earning reports (۷ روز)
     */
    public function getEarningTabsData(User $user): array
    {
        $orgId = $this->resolveOrgId($user);
        $days = [];
        $ordersData    = [];
        $salesData     = [];
        $customersData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date  = Carbon::now()->subDays($i)->toDateString();
            $days[] = Carbon::now()->subDays($i)->format('D');

            $ordersData[] = (int) Pishfactor::where('organization_id', $orgId)
                ->whereIn('status', [1, 4])
                ->whereDate('created_at', $date)
                ->count();

            $salesData[] = (int) round(Pishfactor::where('organization_id', $orgId)
                ->whereIn('status', [1, 4])
                ->whereDate('created_at', $date)
                ->sum(DB::raw("CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED)")) / 10);

            $customersData[] = (int) Customers::where('organization_id', $orgId)
                ->whereDate('created_at', $date)
                ->count();
        }

        return [
            'days'      => $days,
            'orders'    => $ordersData,
            'sales'     => $salesData,
            'customers' => $customersData,
        ];
    }

    /**
     * داده نمودار revenue report گروهی (فروش vs فاکتورها) ۶ ماه
     */
    public function getRevenueReportData(User $user, int $months = 6): array
    {
        $orgId = $this->resolveOrgId($user);
        $labels  = [];
        $sales   = [];
        $factors = [];

        $persianMonths = [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
            4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
            7 => 'مهر', 8 => 'آبان', 9 => 'آذر',
            10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
        ];

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end   = Carbon::now()->subMonths($i)->endOfMonth();

            $amount = (int) Pishfactor::where('organization_id', $orgId)
                ->whereIn('status', [1, 4])
                ->whereBetween('created_at', [$start, $end])
                ->sum(DB::raw("CAST(REPLACE(fullPrice, ',', '') AS UNSIGNED)"));

            $count = (int) Pishfactor::where('organization_id', $orgId)
                ->whereIn('status', [1, 4])
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $jalaali   = \Hekmatinasser\Verta\Verta::instance($start);
            $labels[]  = $persianMonths[$jalaali->month] ?? $start->format('M');
            $sales[]   = (int) round($amount / 10);
            $factors[] = $count;
        }

        return [
            'labels'  => $labels,
            'sales'   => $sales,
            'factors' => $factors,
        ];
    }

    /**
     * برترین محصولات با درصد فروش نسبی برای progress bar
     */
    public function getTopProductsWithPercent(User $user, int $limit = 5): array
    {
        $orgId = $this->resolveOrgId($user);

        $factorIds = Pishfactor::where('organization_id', $orgId)
            ->whereIn('status', [1, 4])
            ->whereBetween('created_at', [Carbon::now()->subDays(30), Carbon::now()])
            ->pluck('id');

        $items = PishFactorItems::whereIn('pishfactor_id', $factorIds)
            ->select('pr_id', DB::raw('SUM(tedad + (pack * COALESCE(pack_items, 1))) as total_qty'))
            ->groupBy('pr_id')
            ->orderByDesc('total_qty')
            ->take($limit)
            ->with('product:id,name,code')
            ->get();

        $max = $items->max('total_qty') ?: 1;

        return $items->map(function ($item) use ($max) {
            return [
                'name'    => $item->product->name ?? 'نامشخص',
                'code'    => $item->product->code ?? '-',
                'qty'     => (int) $item->total_qty,
                'percent' => min(100, (int) round($item->total_qty / $max * 100)),
            ];
        })->toArray();
    }

    private function resolveOrgId(User $user): int
    {
        $raw = $user->organization_id;
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && !empty($decoded[0])) {
                return (int) $decoded[0];
            }
        }
        return (int) $raw;
    }

    private function resolveTenantId(User $user): int
    {
        return (int) ($user->tenant_id ?: $user->tenants_id ?: 0);
    }
}

<?php

namespace App\Services;

use App\Models\CommissionSettlement;
use App\Models\ManagementReportSnapshot;
use App\Models\Pishfactor;
use App\Models\PishFactorItems;
use App\Models\Shipments;
use App\Models\Targets;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ManagementReportService
{
    public function __construct(private CommissionCalculationService $commissionService) {}

    public function build(User $user, Carbon $startDate, Carbon $endDate, array $options = []): array
    {
        $previousEndDate = $startDate->copy()->subDay();
        $previousStartDate = $previousEndDate->copy()->subDays($startDate->diffInDays($endDate));
        $salesQuery = $this->scopedSalesQuery($user, $startDate, $endDate);
        $salesInvoices = (clone $salesQuery)->with('items')->get();
        $salesAmount = $salesInvoices->sum(fn($invoice) => $this->money($invoice->fullPrice));
        $taxAmount = $salesInvoices->sum(fn($invoice) => $this->money($invoice->pat_price));
        $ordersCount = $salesInvoices->count();
        $averageOrder = $ordersCount > 0 ? round($salesAmount / $ordersCount, 2) : 0;

        $targets = $this->scopedTargets($user, $startDate, $endDate)->with(['user.roles', 'commissionPlan'])->get();
        $targetReports = $this->commissionService->calculateMany($targets);
        $targetAmount = $targetReports->sum('target_amount');
        $commissionPayable = $targetReports->sum('payable_amount');
        $achievementPercent = $targetAmount > 0 ? round(($salesAmount / $targetAmount) * 100, 2) : 0;

        $sales = [
            'orders_count' => $ordersCount,
            'sales_amount' => round($salesAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'net_amount' => round(max(0, $salesAmount - $taxAmount), 2),
            'average_order' => $averageOrder,
        ];
        $targetsSummary = [
            'targets_count' => $targets->count(),
            'target_amount' => round($targetAmount, 2),
            'achievement_percent' => $achievementPercent,
            'commission_payable' => round($commissionPayable, 2),
            'items' => $targetReports,
        ];
        $financial = $this->financialMetrics($user, $startDate, $endDate);
        $warehouse = $this->warehouseMetrics($user);
        $production = $this->productionMetrics($user, $startDate, $endDate);
        $distribution = $this->distributionMetrics($user, $startDate, $endDate);
        $ecommerce = $this->ecommerceMetrics($user, $startDate, $endDate);
        $previous = $this->summaryForPeriod($user, $previousStartDate, $previousEndDate);

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
                'previous_start' => $previousStartDate,
                'previous_end' => $previousEndDate,
            ],
            'template' => [
                'key' => $options['template_key'] ?? 'executive_summary',
                'title' => $options['template_title'] ?? 'داشبورد مدیریتی یکپارچه',
                'export_formats' => ['html', 'excel', 'pdf'],
            ],
            'sales' => $sales,
            'targets' => $targetsSummary,
            'financial' => $financial,
            'warehouse' => $warehouse,
            'production' => $production,
            'distribution' => $distribution,
            'ecommerce' => $ecommerce,
            'previous_period' => $previous,
            'comparison' => $this->comparisonMetrics([
                'sales_amount' => $sales['sales_amount'],
                'net_amount' => $sales['net_amount'],
                'orders_count' => $sales['orders_count'],
                'gross_profit' => $financial['gross_profit'],
                'inventory_value' => $warehouse['inventory_value'],
                'production_cost' => $production['material_cost'],
                'ecommerce_orders' => $ecommerce['orders_count'],
            ], $previous),
            'top_visitors' => $this->topVisitors($user, $startDate, $endDate),
            'top_products' => $this->topProducts($user, $startDate, $endDate),
            'settlements' => $this->settlementMetrics($user, $startDate, $endDate),
            'schedule' => [
                'supported_frequencies' => ['daily', 'weekly', 'monthly'],
                'default_frequency' => 'monthly',
            ],
        ];
    }

    public function saveSnapshot(User $user, array $report, array $filters = []): ManagementReportSnapshot
    {
        return ManagementReportSnapshot::create([
            'tenant_id' => $user->tenant_id ?: $user->tenants_id,
            'organization_id' => is_numeric($user->organization_id) ? $user->organization_id : null,
            'report_key' => 'management_summary',
            'title' => 'گزارش مدیریتی',
            'period_start' => $report['period']['start'],
            'period_end' => $report['period']['end'],
            'created_by' => $user->id,
            'filters' => $filters,
            'metrics' => $report,
        ]);
    }

    private function scopedSalesQuery(User $user, Carbon $startDate, Carbon $endDate): Builder
    {
        $query = Pishfactor::query()
            ->whereIn('status', [1, 4])
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function scopedTargets(User $user, Carbon $startDate, Carbon $endDate): Builder
    {
        $query = Targets::query()
            ->where('start_date_en', '<=', $endDate->copy()->endOfDay())
            ->where('end_date_en', '>=', $startDate->copy()->startOfDay());

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function distributionMetrics(User $user, Carbon $startDate, Carbon $endDate): array
    {
        if (!Schema::hasTable('shipments')) {
            return ['shipments_count' => 0, 'active_shipments_count' => 0, 'completed_shipments_count' => 0];
        }

        $query = Shipments::query()->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return [
            'shipments_count' => (clone $query)->count(),
            'active_shipments_count' => Schema::hasColumn('shipments', 'workflow_status') ? (clone $query)->whereIn('workflow_status', ['planned', 'loaded', 'dispatched'])->count() : 0,
            'completed_shipments_count' => Schema::hasColumn('shipments', 'workflow_status') ? (clone $query)->where('workflow_status', 'completed')->count() : 0,
        ];
    }

    private function financialMetrics(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $sales = $this->summaryForPeriod($user, $startDate, $endDate);
        $vouchers = $this->scopedTableQuery('vouchers', $user, $startDate, $endDate, 'voucher_date_en');

        if (!$vouchers) {
            return [
                'vouchers_count' => 0,
                'permanent_vouchers_count' => 0,
                'draft_vouchers_count' => 0,
                'total_debit' => 0,
                'total_credit' => 0,
                'balance_gap' => 0,
                'gross_profit' => $sales['net_amount'],
            ];
        }

        $totalDebit = Schema::hasColumn('vouchers', 'total_debit') ? (float) (clone $vouchers)->sum('total_debit') : 0;
        $totalCredit = Schema::hasColumn('vouchers', 'total_credit') ? (float) (clone $vouchers)->sum('total_credit') : 0;

        return [
            'vouchers_count' => (clone $vouchers)->count(),
            'permanent_vouchers_count' => Schema::hasColumn('vouchers', 'is_permanent') ? (clone $vouchers)->where('is_permanent', 1)->count() : 0,
            'draft_vouchers_count' => Schema::hasColumn('vouchers', 'is_permanent') ? (clone $vouchers)->where('is_permanent', 0)->count() : 0,
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'balance_gap' => round($totalDebit - $totalCredit, 2),
            'gross_profit' => round($sales['net_amount'], 2),
        ];
    }

    private function warehouseMetrics(User $user): array
    {
        $balances = $this->scopedTableQuery('inventory_balances', $user);

        if (!$balances) {
            return ['products_count' => 0, 'available_quantity' => 0, 'reserved_quantity' => 0, 'inventory_value' => 0, 'low_stock_count' => 0];
        }

        $quantityExpression = 'COALESCE(quantity, 0)';
        $reservedExpression = Schema::hasColumn('inventory_balances', 'reserved_quantity') ? 'COALESCE(reserved_quantity, 0)' : '0';
        $valueExpression = Schema::hasColumn('inventory_balances', 'total_cost') ? 'COALESCE(total_cost, 0)' : '0';

        return [
            'products_count' => (clone $balances)->distinct('product_id')->count('product_id'),
            'available_quantity' => round((float) (clone $balances)->sum(DB::raw($quantityExpression)), 3),
            'reserved_quantity' => round((float) (clone $balances)->sum(DB::raw($reservedExpression)), 3),
            'inventory_value' => round((float) (clone $balances)->sum(DB::raw($valueExpression)), 2),
            'low_stock_count' => Schema::hasColumn('inventory_balances', 'minimum_quantity')
                ? (clone $balances)->whereNotNull('minimum_quantity')->whereColumn('quantity', '<=', 'minimum_quantity')->count()
                : 0,
        ];
    }

    private function productionMetrics(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $orders = $this->scopedTableQuery('production_orders', $user, $startDate, $endDate, 'date_en');

        if (!$orders) {
            return ['orders_count' => 0, 'approved_count' => 0, 'planned_quantity' => 0, 'actual_quantity' => 0, 'material_cost' => 0];
        }

        return [
            'orders_count' => (clone $orders)->count(),
            'approved_count' => Schema::hasColumn('production_orders', 'status') ? (clone $orders)->where('status', 'approved')->count() : 0,
            'planned_quantity' => Schema::hasColumn('production_orders', 'planned_quantity') ? round((float) (clone $orders)->sum('planned_quantity'), 3) : 0,
            'actual_quantity' => Schema::hasColumn('production_orders', 'actual_quantity') ? round((float) (clone $orders)->sum('actual_quantity'), 3) : 0,
            'material_cost' => Schema::hasColumn('production_orders', 'material_cost') ? round((float) (clone $orders)->sum('material_cost'), 2) : 0,
        ];
    }

    private function ecommerceMetrics(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $orders = $this->scopedTableQuery('ecommerce_order_mappings', $user, $startDate, $endDate, 'received_at');

        if (!$orders) {
            return ['orders_count' => 0, 'processed_count' => 0, 'failed_count' => 0, 'net_amount' => 0];
        }

        return [
            'orders_count' => (clone $orders)->count(),
            'processed_count' => Schema::hasColumn('ecommerce_order_mappings', 'sync_status') ? (clone $orders)->where('sync_status', 'processed')->count() : 0,
            'failed_count' => Schema::hasColumn('ecommerce_order_mappings', 'sync_status') ? (clone $orders)->whereIn('sync_status', ['failed', 'conflict'])->count() : 0,
            'net_amount' => Schema::hasColumn('ecommerce_order_mappings', 'net_amount') ? round((float) (clone $orders)->sum('net_amount'), 2) : 0,
        ];
    }

    private function summaryForPeriod(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $query = $this->scopedSalesQuery($user, $startDate, $endDate);
        $salesAmount = (float) (clone $query)->sum(DB::raw("CAST(REPLACE(COALESCE(fullPrice, '0'), ',', '') AS DECIMAL(18,2))"));
        $taxAmount = (float) (clone $query)->sum(DB::raw("CAST(REPLACE(COALESCE(pat_price, '0'), ',', '') AS DECIMAL(18,2))"));
        $ordersCount = (clone $query)->count();

        return [
            'sales_amount' => round($salesAmount, 2),
            'net_amount' => round(max(0, $salesAmount - $taxAmount), 2),
            'orders_count' => $ordersCount,
            'gross_profit' => round(max(0, $salesAmount - $taxAmount), 2),
            'inventory_value' => $this->warehouseMetrics($user)['inventory_value'],
            'production_cost' => $this->productionMetrics($user, $startDate, $endDate)['material_cost'],
            'ecommerce_orders' => $this->ecommerceMetrics($user, $startDate, $endDate)['orders_count'],
        ];
    }

    private function comparisonMetrics(array $current, array $previous): array
    {
        return collect($current)->mapWithKeys(function ($value, $key) use ($previous) {
            $previousValue = (float) ($previous[$key] ?? 0);
            $delta = (float) $value - $previousValue;

            return [$key => [
                'current' => round((float) $value, 2),
                'previous' => round($previousValue, 2),
                'delta' => round($delta, 2),
                'percent' => $previousValue != 0.0 ? round(($delta / abs($previousValue)) * 100, 2) : null,
            ]];
        })->all();
    }

    private function scopedTableQuery(string $table, User $user, ?Carbon $startDate = null, ?Carbon $endDate = null, ?string $dateColumn = null): ?\Illuminate\Database\Query\Builder
    {
        if (!Schema::hasTable($table)) {
            return null;
        }

        $query = DB::table($table);

        if ($dateColumn && Schema::hasColumn($table, $dateColumn) && $startDate && $endDate) {
            $query->whereBetween($dateColumn, [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);
        } elseif (Schema::hasColumn($table, 'created_at') && $startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);
        }

        if ((int) $user->isGod !== 1) {
            $tenantId = $user->tenant_id ?: $user->tenants_id;
            if ($tenantId && Schema::hasColumn($table, 'tenant_id')) {
                $query->where('tenant_id', $tenantId);
            } elseif (is_numeric($user->organization_id) && Schema::hasColumn($table, 'organization_id')) {
                $query->where('organization_id', $user->organization_id);
            }
        }

        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        return $query;
    }

    private function topVisitors(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $query = $this->scopedSalesQuery($user, $startDate, $endDate)
            ->select('visitor_id', DB::raw('COUNT(*) as orders_count'), DB::raw("SUM(CAST(REPLACE(COALESCE(fullPrice, '0'), ',', '') AS DECIMAL(18,2))) as sales_amount"))
            ->whereNotNull('visitor_id')
            ->groupBy('visitor_id')
            ->orderByDesc('sales_amount')
            ->limit(10)
            ->get();

        $users = User::whereIn('id', $query->pluck('visitor_id'))->get()->keyBy('id');

        return $query->map(fn($row) => [
            'user_id' => $row->visitor_id,
            'name' => optional($users->get($row->visitor_id))->name ?: '-',
            'orders_count' => (int) $row->orders_count,
            'sales_amount' => (float) $row->sales_amount,
        ])->all();
    }

    private function topProducts(User $user, Carbon $startDate, Carbon $endDate): array
    {
        if (!Schema::hasTable('pish_factor_items')) {
            return [];
        }

        $salesIds = $this->scopedSalesQuery($user, $startDate, $endDate)->pluck('id');

        if ($salesIds->isEmpty()) {
            return [];
        }

        return PishFactorItems::query()
            ->with('product')
            ->select('pr_id', DB::raw('SUM(COALESCE(tedad, 0)) as quantity'), DB::raw('SUM(COALESCE(line_total, price * tedad, 0)) as amount'))
            ->whereIn('pishfactor_id', $salesIds)
            ->groupBy('pr_id')
            ->orderByDesc('amount')
            ->limit(10)
            ->get()
            ->map(fn($row) => [
                'product_id' => $row->pr_id,
                'title' => optional($row->product)->title ?: optional($row->product)->name ?: '-',
                'quantity' => (float) $row->quantity,
                'amount' => (float) $row->amount,
            ])->all();
    }

    private function settlementMetrics(User $user, Carbon $startDate, Carbon $endDate): array
    {
        if (!Schema::hasTable('commission_settlements')) {
            return ['calculated_count' => 0, 'payable_amount' => 0];
        }

        $query = CommissionSettlement::query()
            ->whereBetween('period_start', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);

        if ((int) $user->isGod !== 1) {
            $tenantId = $user->tenant_id ?: $user->tenants_id;
            $query->where(function (Builder $scope) use ($tenantId, $user) {
                $scope->where('tenant_id', $tenantId)
                    ->orWhere('organization_id', is_numeric($user->organization_id) ? $user->organization_id : null);
            });
        }

        return [
            'calculated_count' => (clone $query)->count(),
            'payable_amount' => (float) (clone $query)->sum('payable_amount'),
        ];
    }

    private function money($value): float
    {
        return (float) str_replace(',', '', (string) ($value ?? 0));
    }
}

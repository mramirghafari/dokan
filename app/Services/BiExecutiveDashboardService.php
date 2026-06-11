<?php

namespace App\Services;

use App\Models\BiDailySummary;
use App\Models\BiMetricDefinition;
use App\Models\Targets;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BiExecutiveDashboardService
{
    public function executiveForUser($user): array
    {
        return $this->buildDashboard($user, 'executive', [
            ['key' => 'sales', 'title' => 'فروش ناخالص', 'metric_key' => 'sales_gross_amount', 'unit' => 'rial', 'icon' => 'ti-chart-bar'],
            ['key' => 'orders', 'title' => 'تعداد سفارش', 'metric_key' => 'sales_order_count', 'unit' => 'count', 'icon' => 'ti-shopping-cart'],
            ['key' => 'profit', 'title' => 'سود تخمینی', 'metric_key' => '_derived_gross_profit', 'unit' => 'rial', 'icon' => 'ti-trending-up'],
            ['key' => 'crm_pipeline', 'title' => 'Pipeline CRM', 'metric_key' => 'crm_weighted_pipeline', 'unit' => 'rial', 'icon' => 'ti-briefcase'],
            ['key' => 'crm_open', 'title' => 'کارت باز CRM', 'metric_key' => 'crm_open_cards', 'unit' => 'count', 'icon' => 'ti-layout-kanban'],
            ['key' => 'inventory_alert', 'title' => 'کمبود موجودی', 'metric_key' => 'inventory_low_stock_items', 'unit' => 'count', 'icon' => 'ti-package'],
            ['key' => 'tickets', 'title' => 'تیکت باز (SLA)', 'metric_key' => 'crm_open_service_tickets', 'unit' => 'count', 'icon' => 'ti-headset'],
        ]);
    }

    public function cfoForUser($user): array
    {
        return $this->buildDashboard($user, 'cfo', [
            ['key' => 'cash', 'title' => 'پیش‌بینی نقدینگی', 'metric_key' => 'finance_cash_forecast', 'unit' => 'rial', 'icon' => 'ti-cash'],
            ['key' => 'treasury', 'title' => 'اسناد خزانه', 'metric_key' => 'treasury_instrument_amount', 'unit' => 'rial', 'icon' => 'ti-building-bank'],
            ['key' => 'receivables', 'title' => 'گردش بدهکار مالی', 'metric_key' => 'finance_debit_total', 'unit' => 'rial', 'icon' => 'ti-receipt'],
            ['key' => 'inventory_value', 'title' => 'ارزش موجودی', 'metric_key' => 'inventory_stock_value', 'unit' => 'rial', 'icon' => 'ti-box'],
            ['key' => 'payroll', 'title' => 'خالص حقوق', 'metric_key' => 'payroll_net_pay_amount', 'unit' => 'rial', 'icon' => 'ti-users'],
            ['key' => 'crm_overdue', 'title' => 'پیگیری معوق CRM', 'metric_key' => 'crm_overdue_followups', 'unit' => 'count', 'icon' => 'ti-clock-exclamation'],
            ['key' => 'tickets', 'title' => 'تیکت باز', 'metric_key' => 'crm_open_service_tickets', 'unit' => 'count', 'icon' => 'ti-alert-circle'],
        ]);
    }

    private function buildDashboard($user, string $mode, array $cardDefinitions): array
    {
        $latestDate = $this->summaryQuery($user)->max('summary_date');
        $compareDays = max(1, (int) config('erp_scale.bi_executive.comparison_days', 7));
        $previousDate = $latestDate
            ? $this->summaryQuery($user)->whereDate('summary_date', '<', $latestDate)->max('summary_date')
            : null;

        if ($latestDate && !$previousDate) {
            $previousDate = Carbon::parse($latestDate)->subDays($compareDays)->toDateString();
        }

        $current = $latestDate
            ? $this->summariesForDate($user, $latestDate)->keyBy('metric_key')
            : collect();

        $previous = $previousDate
            ? $this->summariesForDate($user, $previousDate)->keyBy('metric_key')
            : collect();

        $cards = collect($cardDefinitions)->map(function (array $def) use ($current, $previous, $user) {
            $value = $this->resolveMetricValue($def['metric_key'], $current, $previous);
            $prevValue = $this->metricValue($def['metric_key'], $previous);
            $budget = $this->budgetForMetric($user, $def['metric_key']);

            return [
                'key' => $def['key'],
                'title' => $def['title'],
                'metric_key' => $def['metric_key'],
                'icon' => $def['icon'],
                'unit' => $def['unit'],
                'value' => $value,
                'previous_value' => $prevValue,
                'delta_percent' => $this->deltaPercent($value, $prevValue),
                'budget' => $budget,
                'budget_percent' => $budget > 0 ? round(($value / $budget) * 100, 1) : null,
                'status' => $current->has($def['metric_key']) || $def['metric_key'] === '_derived_gross_profit' ? 'fresh' : 'missing',
            ];
        })->values()->all();

        return [
            'mode' => $mode,
            'latest_date' => $latestDate,
            'previous_date' => $previousDate,
            'cards' => $cards,
            'trend' => $this->trendSeries($user, 'sales_gross_amount', 14),
            'domains' => $this->domainBreakdown($user, $latestDate),
            'metrics_catalog' => BiMetricDefinition::query()->where('is_active', true)->orderBy('domain')->get(),
            'budget_summary' => $this->budgetSummary($user),
        ];
    }

    private function resolveMetricValue(string $metricKey, Collection $current, Collection $previous): float
    {
        if ($metricKey === '_derived_gross_profit') {
            $sales = $this->metricValue('sales_gross_amount', $current);
            $purchase = $this->metricValue('purchase_order_amount', $current);
            $payroll = $this->metricValue('payroll_net_pay_amount', $current);

            return max(0, $sales - $purchase - $payroll);
        }

        return $this->metricValue($metricKey, $current);
    }

    private function metricValue(string $metricKey, Collection $summaries): float
    {
        $row = $summaries->get($metricKey);

        return $row ? (float) $row->value : 0.0;
    }

    private function deltaPercent(float $current, float $previous): ?float
    {
        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function budgetForMetric($user, string $metricKey): float
    {
        if ($metricKey !== 'sales_gross_amount' && $metricKey !== '_derived_gross_profit') {
            return 0.0;
        }

        return (float) $this->budgetSummary($user)['sales_target'];
    }

    private function budgetSummary($user): array
    {
        $query = Targets::query()->where('status', 1);

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        $today = now()->toDateString();
        $query->where(function ($inner) use ($today) {
            $inner->whereNull('start_date_en')->orWhereDate('start_date_en', '<=', $today);
        })->where(function ($inner) use ($today) {
            $inner->whereNull('end_date_en')->orWhereDate('end_date_en', '>=', $today);
        });

        $salesTarget = (float) (clone $query)->sum('target_price');

        return [
            'sales_target' => $salesTarget,
            'active_targets' => (clone $query)->count(),
        ];
    }

    private function trendSeries($user, string $metricKey, int $days): array
    {
        $from = now()->subDays($days)->toDateString();

        return $this->summaryQuery($user)
            ->where('metric_key', $metricKey)
            ->whereDate('summary_date', '>=', $from)
            ->orderBy('summary_date')
            ->get(['summary_date', 'value'])
            ->map(fn ($row) => [
                'date' => $row->summary_date->format('Y-m-d'),
                'label' => verta($row->summary_date)->format('m/d'),
                'value' => (float) $row->value,
            ])
            ->values()
            ->all();
    }

    private function domainBreakdown($user, ?string $date): array
    {
        if (!$date) {
            return [];
        }

        return $this->summaryQuery($user)
            ->whereDate('summary_date', $date)
            ->selectRaw('domain, COUNT(*) as metrics_count, COALESCE(SUM(value), 0) as total_value')
            ->groupBy('domain')
            ->orderByDesc('total_value')
            ->get()
            ->map(fn ($row) => [
                'domain' => $row->domain,
                'metrics_count' => (int) $row->metrics_count,
                'total_value' => (float) $row->total_value,
            ])
            ->values()
            ->all();
    }

    private function summariesForDate($user, string $date): Collection
    {
        return $this->summaryQuery($user)->whereDate('summary_date', $date)->get();
    }

    private function summaryQuery($user)
    {
        $query = BiDailySummary::query();

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $user->tenant_id ?: $user->tenants_id);
            if (is_numeric($user->organization_id)) {
                $query->where('organization_id', (int) $user->organization_id);
            }
        }

        return $query;
    }
}

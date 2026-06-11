<?php

namespace App\Services;

use App\Models\BiDailySummary;
use App\Models\BiRefreshLog;
use Illuminate\Support\Carbon;

class BiReconciliationService
{
    public function __construct(
        private BiSelfServiceReportService $reportService,
    ) {}

    public function dashboardForUser($user): array
    {
        $latestDate = $this->summaryQuery($user)->max('summary_date');
        $reconciliation = $latestDate
            ? $this->reconcileForDate($user, $latestDate, true)
            : $this->emptyReconciliation();

        return [
            'latest_summary_date' => $latestDate,
            'reconciliation' => $reconciliation,
            'coverage' => $this->coverageStats($user),
            'recent_logs' => $this->recentLogsForUser($user),
            'backfill_presets' => $this->backfillPresets(),
        ];
    }

    public function runReconciliation($user, ?string $date = null, bool $audit = true): array
    {
        $date = $date ?: $this->summaryQuery($user)->max('summary_date');

        if (!$date) {
            return $this->emptyReconciliation('هنوز summary روزانه‌ای در data mart نیست.');
        }

        $result = $this->reconcileForDate($user, $date, true);

        if ($audit) {
            BiRefreshLog::create([
                'dataset_key' => 'bi_reconciliation',
                'tenant_id' => $this->tenantId($user),
                'organization_id' => $this->organizationId($user),
                'status' => $result['health_status'] === 'critical' ? 'warning' : 'success',
                'started_at' => now(),
                'finished_at' => now(),
                'rows_count' => count($result['checks']),
                'message' => 'BI reconciliation: ' . $result['aligned_count'] . '/' . count($result['checks']) . ' aligned.',
                'metadata' => [
                    'summary_date' => $date,
                    'health_score' => $result['health_score'],
                    'health_status' => $result['health_status'],
                    'checks' => collect($result['checks'])->map(fn ($row) => [
                        'key' => $row['key'],
                        'status' => $row['status'],
                        'delta_percent' => $row['delta_percent'],
                    ])->all(),
                ],
            ]);
        }

        return $result;
    }

    public function queueBackfill($user, string $from, string $to): array
    {
        $fromDate = Carbon::parse($from)->toDateString();
        $toDate = Carbon::parse($to)->toDateString();

        $log = BiRefreshLog::create([
            'dataset_key' => 'bi_backfill',
            'tenant_id' => $this->tenantId($user),
            'organization_id' => $this->organizationId($user),
            'status' => 'processing',
            'started_at' => now(),
            'message' => 'Historical backfill queued.',
            'metadata' => ['from' => $fromDate, 'to' => $toDate, 'queued' => true],
        ]);

        \App\Jobs\BackfillBiDataMartJob::dispatch($user->id, $fromDate, $toDate, $log->id);

        return [
            'log_id' => $log->id,
            'from' => $fromDate,
            'to' => $toDate,
            'queued' => true,
        ];
    }

    private function reconcileForDate($user, string $date, bool $withOperational): array
    {
        $summaries = $this->summaryQuery($user)
            ->whereDate('summary_date', $date)
            ->get()
            ->keyBy('metric_key');

        $definitions = $this->checkDefinitions();
        $checks = [];

        foreach ($definitions as $definition) {
            $summaryValue = $summaries->has($definition['metric_key'])
                ? (float) $summaries->get($definition['metric_key'])->value
                : null;

            $sourceValue = null;
            if ($withOperational) {
                $sourceValue = $this->reportService->operationalMetricValue(
                    $user,
                    $definition['metric_key'],
                    $definition['snapshot'] ? $date : $date
                );
            }

            $checks[] = $this->buildCheckRow($definition, $summaryValue, $sourceValue);
        }

        $scored = collect($checks)->filter(fn ($row) => $row['status'] !== 'missing');
        $aligned = $scored->where('status', 'aligned')->count();
        $total = max(1, $scored->count());
        $healthScore = round(($aligned / $total) * 100, 1);

        $healthStatus = 'healthy';
        if ($scored->contains(fn ($row) => $row['status'] === 'critical')) {
            $healthStatus = 'critical';
        } elseif ($scored->contains(fn ($row) => in_array($row['status'], ['warning', 'missing'], true))) {
            $healthStatus = 'warning';
        }

        return [
            'summary_date' => $date,
            'checks' => $checks,
            'aligned_count' => $aligned,
            'warning_count' => $scored->where('status', 'warning')->count(),
            'critical_count' => $scored->where('status', 'critical')->count(),
            'missing_count' => collect($checks)->where('status', 'missing')->count(),
            'health_score' => $healthScore,
            'health_status' => $healthStatus,
            'message' => null,
        ];
    }

    private function buildCheckRow(array $definition, ?float $summaryValue, ?float $sourceValue): array
    {
        if ($summaryValue === null && $sourceValue === null) {
            return array_merge($definition, [
                'summary_value' => null,
                'source_value' => null,
                'delta' => null,
                'delta_percent' => null,
                'status' => 'missing',
                'status_label' => 'بدون داده',
            ]);
        }

        $summaryValue = $summaryValue ?? 0.0;
        $sourceValue = $sourceValue ?? 0.0;
        $delta = round($summaryValue - $sourceValue, 4);
        $deltaPercent = $this->deltaPercent($summaryValue, $sourceValue);
        $status = $this->statusForDelta($deltaPercent, $summaryValue, $sourceValue);

        return array_merge($definition, [
            'summary_value' => $summaryValue,
            'source_value' => $sourceValue,
            'delta' => $delta,
            'delta_percent' => $deltaPercent,
            'status' => $status,
            'status_label' => $this->statusLabel($status),
        ]);
    }

    private function statusForDelta(?float $deltaPercent, float $summary, float $source): string
    {
        if ($summary == 0.0 && $source == 0.0) {
            return 'aligned';
        }

        if ($deltaPercent === null) {
            return abs($summary - $source) < 0.01 ? 'aligned' : 'warning';
        }

        $warning = (float) config('erp_scale.bi_reconciliation.warning_delta_percent', 2);
        $critical = (float) config('erp_scale.bi_reconciliation.critical_delta_percent', 10);
        $abs = abs($deltaPercent);

        if ($abs <= $warning) {
            return 'aligned';
        }

        return $abs <= $critical ? 'warning' : 'critical';
    }

    private function deltaPercent(float $summary, float $source): ?float
    {
        if (abs($source) < 0.0001) {
            return $summary == 0.0 ? 0.0 : 100.0;
        }

        return round((($summary - $source) / abs($source)) * 100, 2);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'aligned' => 'هم‌خوان',
            'warning' => 'اختلاف جزئی',
            'critical' => 'اختلاف مهم',
            default => 'نامشخص',
        };
    }

    private function checkDefinitions(): array
    {
        return [
            [
                'key' => 'sales_gross',
                'metric_key' => 'sales_gross_amount',
                'title' => 'فروش ناخالص',
                'domain' => 'sales',
                'source' => 'pishfactors',
                'unit' => 'rial',
                'icon' => 'ti-chart-bar',
                'snapshot' => false,
            ],
            [
                'key' => 'sales_orders',
                'metric_key' => 'sales_order_count',
                'title' => 'تعداد سفارش',
                'domain' => 'sales',
                'source' => 'pishfactors',
                'unit' => 'count',
                'icon' => 'ti-shopping-cart',
                'snapshot' => false,
            ],
            [
                'key' => 'finance_debit',
                'metric_key' => 'finance_debit_total',
                'title' => 'گردش بدهکار مالی',
                'domain' => 'finance',
                'source' => 'vouchers',
                'unit' => 'rial',
                'icon' => 'ti-receipt',
                'snapshot' => false,
            ],
            [
                'key' => 'inventory_value',
                'metric_key' => 'inventory_stock_value',
                'title' => 'ارزش موجودی',
                'domain' => 'inventory',
                'source' => 'inventory_balances',
                'unit' => 'rial',
                'icon' => 'ti-package',
                'snapshot' => true,
            ],
            [
                'key' => 'crm_tickets',
                'metric_key' => 'crm_open_service_tickets',
                'title' => 'تیکت باز CRM',
                'domain' => 'crm',
                'source' => 'crm_service_tickets',
                'unit' => 'count',
                'icon' => 'ti-headset',
                'snapshot' => true,
            ],
            [
                'key' => 'purchase_amount',
                'metric_key' => 'purchase_order_amount',
                'title' => 'خرید',
                'domain' => 'purchase',
                'source' => 'purchase_orders',
                'unit' => 'rial',
                'icon' => 'ti-truck',
                'snapshot' => false,
            ],
        ];
    }

    private function coverageStats($user): array
    {
        $query = $this->summaryQuery($user);
        $first = $query->min('summary_date');
        $last = $query->max('summary_date');
        $days = $first && $last
            ? Carbon::parse($first)->diffInDays(Carbon::parse($last)) + 1
            : 0;

        return [
            'first_date' => $first,
            'last_date' => $last,
            'distinct_days' => (int) (clone $query)->distinct()->count('summary_date'),
            'span_days' => $days,
            'metric_rows' => (int) (clone $query)->count(),
        ];
    }

    private function recentLogsForUser($user)
    {
        $query = BiRefreshLog::query()
            ->whereIn('dataset_key', ['bi_reconciliation', 'bi_backfill', 'enterprise_data_mart'])
            ->latest('id')
            ->limit(12);

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
            if ($this->organizationId($user)) {
                $query->where('organization_id', $this->organizationId($user));
            }
        }

        return $query->get();
    }

    private function backfillPresets(): array
    {
        $months = max(1, (int) config('erp_scale.bi_reconciliation.default_backfill_months', 12));

        return [
            ['label' => '۳ ماه اخیر', 'months' => 3],
            ['label' => '۶ ماه اخیر', 'months' => 6],
            ['label' => $months . ' ماه اخیر (پیش‌فرض)', 'months' => $months],
        ];
    }

    private function emptyReconciliation(?string $message = null): array
    {
        return [
            'summary_date' => null,
            'checks' => [],
            'aligned_count' => 0,
            'warning_count' => 0,
            'critical_count' => 0,
            'missing_count' => 0,
            'health_score' => 0,
            'health_status' => 'warning',
            'message' => $message ?? 'ابتدا data mart را refresh یا backfill کنید.',
        ];
    }

    private function summaryQuery($user)
    {
        $query = BiDailySummary::query();

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
            if ($this->organizationId($user)) {
                $query->where('organization_id', $this->organizationId($user));
            }
        }

        return $query;
    }

    private function tenantId($user): ?int
    {
        return $user->tenant_id ?: $user->tenants_id ?: null;
    }

    private function organizationId($user): ?int
    {
        return is_numeric($user->organization_id) ? (int) $user->organization_id : null;
    }
}

<?php

namespace App\Services;

use App\Models\BiDailySummary;
use App\Models\BiDatasetDefinition;
use App\Models\BiReportDelivery;
use App\Models\BiRefreshLog;
use App\Models\BiReportRun;
use App\Models\BiReportSchedule;
use App\Models\BiReportTemplate;
use App\Models\DataExchangeRun;
use App\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class BiSelfServiceReportService
{
    public function builderState($user, array $input = [], bool $auditRun = false): array
    {
        $datasets = BiDatasetDefinition::query()
            ->where('is_active', true)
            ->orderBy('domain')
            ->orderBy('dataset_key')
            ->get();

        $datasetKey = $input['dataset_key'] ?? null;
        $dataset = $datasetKey ? $datasets->firstWhere('dataset_key', $datasetKey) : $datasets->first();

        return [
            'datasets' => $datasets,
            'selected_dataset' => $dataset,
            'result' => $dataset ? $this->runDataset($user, $dataset, $input, $auditRun) : $this->emptyResult(),
            'templates' => $this->templatesForUser($user),
            'recent_runs' => $this->recentRunsForUser($user),
            'schedules' => $this->schedulesForUser($user),
            'recent_deliveries' => $this->recentDeliveriesForUser($user),
            'recent_exports' => $this->recentExportsForUser($user),
            'roles' => $this->shareableRolesForUser($user),
            'chart_types' => $this->chartTypes(),
            'view_modes' => $this->viewModes(),
        ];
    }

    public function chartTypes(): array
    {
        return [
            'table' => 'فقط جدول',
            'bar' => 'نمودار میله‌ای',
            'line' => 'نمودار خطی',
        ];
    }

    public function viewModes(): array
    {
        return [
            'table' => 'جدول ساده',
            'pivot' => 'Pivot (سطر × ستون)',
        ];
    }

    public function runForUser($user, array $input, bool $auditRun = true): array
    {
        $dataset = BiDatasetDefinition::query()
            ->where('is_active', true)
            ->where('dataset_key', $input['dataset_key'] ?? '')
            ->firstOrFail();

        return $this->runDataset($user, $dataset, $input, $auditRun);
    }

    public function queueExportForUser($user, array $input, string $format = 'csv'): DataExchangeRun
    {
        return app(DataExchangeService::class)->dispatchReportExport($user, $input, $format);
    }

    public function storeTemplate($user, array $input): BiReportTemplate
    {
        $dataset = BiDatasetDefinition::query()
            ->where('is_active', true)
            ->where('dataset_key', $input['dataset_key'] ?? '')
            ->firstOrFail();

        $dimensionMap = $this->definitionMap($dataset->dimensions ?? []);
        $measureMap = $this->definitionMap($dataset->measures ?? []);
        $chartType = $input['chart_type'] ?? 'table';
        $visibility = $input['visibility'] ?? 'private';
        $sharedRoleId = isset($input['shared_role_id']) && is_numeric($input['shared_role_id'])
            ? (int) $input['shared_role_id']
            : null;

        if ($visibility === 'role' && !$sharedRoleId) {
            throw ValidationException::withMessages(['shared_role_id' => 'برای اشتراک با نقش، یک نقش انتخاب کنید.']);
        }

        $filters = array_merge($this->allowedFilters($dataset, $input), array_filter([
            'view_mode' => $input['view_mode'] ?? null,
            'pivot_row' => $input['pivot_row'] ?? null,
            'pivot_col' => $input['pivot_col'] ?? null,
            'analysis_mode' => $input['analysis_mode'] ?? null,
        ]));

        return BiReportTemplate::create([
            'tenant_id' => $this->tenantId($user),
            'organization_id' => $this->organizationId($user),
            'title' => trim($input['title'] ?? '') ?: $dataset->title,
            'dataset_key' => $dataset->dataset_key,
            'dimensions' => $this->selectedKeys($input['dimensions'] ?? [], $dimensionMap, $dataset->default_dimensions ?: ['summary_date', 'metric_key'], 5),
            'measures' => $this->selectedKeys($input['measures'] ?? [], $measureMap, $dataset->default_measures ?: ['value_sum'], 6),
            'filters' => $filters,
            'chart_type' => in_array($chartType, ['table', 'bar', 'line', 'kpi'], true) ? $chartType : 'table',
            'visibility' => in_array($visibility, ['private', 'organization', 'tenant', 'role'], true) ? $visibility : 'private',
            'shared_role_id' => $visibility === 'role' ? $sharedRoleId : null,
            'created_by' => $user->id,
            'is_active' => true,
        ]);
    }

    public function refreshEnterpriseDataMart($user): BiRefreshLog
    {
        $startedAt = now();
        $summaryDate = $startedAt->toDateString();
        $rows = $this->metricsRowsForDate($user, $summaryDate);
        $written = $this->persistMetricsRows($user, $summaryDate, $rows);

        return BiRefreshLog::create([
            'dataset_key' => 'enterprise_data_mart',
            'tenant_id' => $this->tenantId($user),
            'organization_id' => $this->organizationId($user),
            'status' => 'success',
            'started_at' => $startedAt,
            'finished_at' => now(),
            'rows_count' => $written,
            'message' => 'Enterprise BI data mart refreshed from sales, finance, inventory, CRM, procurement, production, treasury, payroll, assets, distribution, ecommerce and contracting sources.',
            'metadata' => ['metrics' => $rows->pluck('metric_key')->values()->all(), 'summary_date' => $summaryDate],
        ]);
    }

    public function metricsRowsForDate($user, string $summaryDate)
    {
        return collect()
            ->merge($this->salesMetrics($user, $summaryDate))
            ->merge($this->financeMetrics($user, $summaryDate))
            ->merge($this->inventoryMetrics($user, $summaryDate))
            ->merge($this->crmOperationalMetrics($user, $summaryDate))
            ->merge($this->purchaseMetrics($user, $summaryDate))
            ->merge($this->productionMetrics($user, $summaryDate))
            ->merge($this->treasuryMetrics($user))
            ->merge($this->payrollMetrics($user, $summaryDate))
            ->merge($this->assetMetrics($user))
            ->merge($this->distributionMetrics($user, $summaryDate))
            ->merge($this->ecommerceMetrics($user, $summaryDate))
            ->merge($this->contractingMetrics($user));
    }

    public function operationalMetricValue($user, string $metricKey, string $summaryDate): ?float
    {
        $row = $this->metricsRowsForDate($user, $summaryDate)->firstWhere('metric_key', $metricKey);

        return $row ? (float) $row['value'] : null;
    }

    public function persistMetricsRows($user, string $summaryDate, $rows): int
    {
        $tenantId = $this->tenantId($user);
        $organizationId = $this->organizationId($user);
        $written = 0;

        foreach ($rows as $row) {
            BiDailySummary::updateOrCreate([
                'summary_date' => $summaryDate,
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'domain' => $row['domain'],
                'metric_key' => $row['metric_key'],
                'dimension_type' => null,
                'dimension_id' => null,
            ], [
                'value' => $row['value'],
                'comparison_value' => $row['comparison_value'] ?? null,
                'metadata' => $row['metadata'] ?? [],
                'refreshed_at' => now(),
            ]);
            $written++;
        }

        return $written;
    }

    public function backfillEnterpriseDataMart($user, string $from, string $to, bool $writeLog = true): array
    {
        $startedAt = now();
        $fromDate = \Illuminate\Support\Carbon::parse($from)->startOfDay();
        $toDate = \Illuminate\Support\Carbon::parse($to)->startOfDay();

        if ($fromDate->gt($toDate)) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        $maxDays = max(1, (int) config('erp_scale.bi_reconciliation.max_backfill_days', 400));
        $daysSpan = $fromDate->diffInDays($toDate) + 1;

        if ($daysSpan > $maxDays) {
            throw ValidationException::withMessages([
                'to' => 'بازه backfill حداکثر ' . $maxDays . ' روز مجاز است.',
            ]);
        }

        $daysProcessed = 0;
        $rowsWritten = 0;
        $cursor = $fromDate->copy();

        while ($cursor->lte($toDate)) {
            $date = $cursor->toDateString();
            $rows = $this->metricsRowsForDate($user, $date);
            $rowsWritten += $this->persistMetricsRows($user, $date, $rows);
            $daysProcessed++;
            $cursor->addDay();
        }

        $logId = null;

        if ($writeLog) {
            $log = BiRefreshLog::create([
                'dataset_key' => 'bi_backfill',
                'tenant_id' => $this->tenantId($user),
                'organization_id' => $this->organizationId($user),
                'status' => 'success',
                'started_at' => $startedAt,
                'finished_at' => now(),
                'rows_count' => $rowsWritten,
                'message' => 'Historical BI data mart backfill completed.',
                'metadata' => [
                    'from' => $fromDate->toDateString(),
                    'to' => $toDate->toDateString(),
                    'days_processed' => $daysProcessed,
                ],
            ]);
            $logId = $log->id;
        }

        return [
            'log_id' => $logId,
            'from' => $fromDate->toDateString(),
            'to' => $toDate->toDateString(),
            'days_processed' => $daysProcessed,
            'rows_written' => $rowsWritten,
        ];
    }

    private function runDataset($user, BiDatasetDefinition $dataset, array $input, bool $auditRun): array
    {
        if ($dataset->source_type !== 'bi_summary') {
            throw ValidationException::withMessages(['dataset_key' => 'این dataset هنوز موتور اجرایی ندارد.']);
        }

        $startedAt = now();
        $dimensionMap = $this->definitionMap($dataset->dimensions ?? []);
        $measureMap = $this->definitionMap($dataset->measures ?? []);
        $dimensions = $this->selectedKeys($input['dimensions'] ?? [], $dimensionMap, $dataset->default_dimensions ?: ['summary_date', 'metric_key'], 5);
        $measures = $this->selectedKeys($input['measures'] ?? [], $measureMap, $dataset->default_measures ?: ['value_sum'], 6);
        $limit = max(10, min(500, (int) ($input['limit'] ?? 100)));
        $analysisMode = $this->analysisMode($input);

        $query = BiDailySummary::query();
        $this->applySummaryScope($query, $user);
        $this->applyFixedFilters($query, $dataset->fixed_filters ?? []);
        $this->applyUserFilters($query, $dataset, $input);

        $selects = [];
        $groupColumns = [];
        foreach ($dimensions as $dimensionKey) {
            $column = $dimensionMap[$dimensionKey]['column'];
            $this->assertSafeColumn($column);
            $selects[] = DB::raw($column . ' as ' . $dimensionKey);
            $groupColumns[] = $column;
        }

        foreach ($measures as $measureKey) {
            $measure = $measureMap[$measureKey];
            $column = $measure['column'];
            $aggregate = strtolower($measure['aggregate'] ?? 'sum');
            $this->assertSafeColumn($column);
            $selects[] = DB::raw($this->aggregateExpression($aggregate, $column) . ' as ' . $measureKey);
        }

        if (in_array($analysisMode, ['budget_vs_actual', 'target_vs_performance'], true)) {
            $selects[] = DB::raw('COALESCE(SUM(comparison_value), 0) as comparison_value_sum');
        }

        $query->select($selects);
        if ($groupColumns) {
            $query->groupBy($groupColumns);
        }

        $sort = $this->sortConfig($input, $dataset, $dimensions, $measures);
        if ($sort) {
            $query->orderBy($sort['column'], $sort['direction']);
        }

        $rows = $query->limit($limit)->get();
        $columns = $this->resultColumns($dimensions, $measures, $dimensionMap, $measureMap);
        $analysis = $this->applyAnalysisMode($rows, $columns, $measures, $dimensions, $analysisMode);
        $rows = $analysis['rows'];
        $columns = $analysis['columns'];
        $security = $this->applySecurityPolicy($user, $rows, $columns, $measures, $dimensions, $this->allowedFilters($dataset, $input));
        $rows = $security['rows'];
        $viewMode = $this->viewMode($input);
        $chartType = $this->chartType($input);

        $result = [
            'dataset_key' => $dataset->dataset_key,
            'columns' => $columns,
            'rows' => $rows,
            'totals' => $this->totals($rows, $measures),
            'selected_dimensions' => $dimensions,
            'selected_measures' => $measures,
            'analysis_mode' => $analysisMode,
            'analysis_modes' => $this->analysisModes(),
            'analysis_insights' => $analysis['insights'],
            'security' => $security['policy'],
            'filters' => $this->allowedFilters($dataset, $input),
            'limit' => $limit,
            'view_mode' => $viewMode,
            'chart_type' => $chartType,
            'pivot' => $viewMode === 'pivot'
                ? $this->buildPivotTable($rows, $input, $dimensions, $measures, $dimensionMap, $measureMap)
                : null,
            'chart' => in_array($chartType, ['bar', 'line'], true)
                ? $this->buildChartPayload($rows, $dimensions, $measures, $dimensionMap, $measureMap, $chartType)
                : null,
        ];

        if ($auditRun) {
            BiReportRun::create([
                'tenant_id' => $this->tenantId($user),
                'organization_id' => $this->organizationId($user),
                'dataset_key' => $dataset->dataset_key,
                'requested_by' => $user->id,
                'status' => 'success',
                'row_count' => $rows->count(),
                'filters' => $result['filters'],
                'output_summary' => ['dimensions' => $dimensions, 'measures' => $measures, 'analysis_mode' => $analysisMode, 'view_mode' => $viewMode, 'chart_type' => $chartType, 'security' => $security['policy'], 'limit' => $limit],
                'started_at' => $startedAt,
                'finished_at' => now(),
                'message' => 'Self-service BI report executed from whitelist dataset contract.',
            ]);
        }

        return $result;
    }

    private function templatesForUser($user)
    {
        $query = BiReportTemplate::query()->where('is_active', true);

        if ((int) $user->isGod !== 1) {
            $tenantId = $this->tenantId($user);
            $organizationId = $this->organizationId($user);
            $roleIds = $this->userRoleIds($user);
            $query->where(function ($scope) use ($user, $tenantId, $organizationId, $roleIds) {
                $scope->where('created_by', $user->id)
                    ->orWhere(function ($tenantScope) use ($tenantId) {
                        $tenantScope->where('visibility', 'tenant')->where('tenant_id', $tenantId);
                    });

                if ($organizationId) {
                    $scope->orWhere(function ($organizationScope) use ($organizationId) {
                        $organizationScope->where('visibility', 'organization')->where('organization_id', $organizationId);
                    });
                }

                if ($roleIds !== []) {
                    $scope->orWhere(function ($roleScope) use ($roleIds) {
                        $roleScope->where('visibility', 'role')->whereIn('shared_role_id', $roleIds);
                    });
                }
            });
        }

        return $query->latest('id')->limit(20)->get();
    }

    private function recentExportsForUser($user)
    {
        $query = DataExchangeRun::query()
            ->where('direction', 'export')
            ->where('entity_type', 'bi_report')
            ->latest('id')
            ->limit(8);

        if ((int) $user->isGod !== 1) {
            $query->where('user_id', $user->id);
        }

        return $query->get();
    }

    private function shareableRolesForUser($user)
    {
        $query = Role::query()->where('isActive', 1)->orderBy('title');

        if ((int) $user->isGod !== 1 && $this->tenantId($user)) {
            $query->where('tenant_id', $this->tenantId($user));
        }

        return $query->limit(50)->get(['id', 'title']);
    }

    private function userRoleIds($user): array
    {
        if (!method_exists($user, 'roles')) {
            return [];
        }

        return $user->roles()->pluck('roles.id')->map(fn ($id) => (int) $id)->all();
    }

    public function buildPivotTable($rows, array $input, array $dimensions, array $measures, array $dimensionMap, array $measureMap): array
    {
        $rowKey = (string) ($input['pivot_row'] ?? $dimensions[0] ?? '');
        $colKey = (string) ($input['pivot_col'] ?? $dimensions[1] ?? '');
        $valueKey = (string) ($measures[0] ?? '');

        if ($rowKey === '' || $colKey === '' || $valueKey === '' || $rowKey === $colKey) {
            return [
                'ready' => false,
                'message' => 'برای pivot حداقل دو بعد متفاوت و یک measure لازم است.',
            ];
        }

        $maxColumns = max(3, (int) config('erp_scale.bi_report_builder.pivot_max_columns', 12));
        $matrix = [];
        $columnTotals = [];
        $rowTotals = [];

        foreach ($rows as $row) {
            $rowLabel = (string) ($row->{$rowKey} ?? '-');
            $colLabel = (string) ($row->{$colKey} ?? '-');
            $value = $this->rowNumber($row, $valueKey);

            $matrix[$rowLabel][$colLabel] = ($matrix[$rowLabel][$colLabel] ?? 0) + $value;
            $columnTotals[$colLabel] = ($columnTotals[$colLabel] ?? 0) + $value;
            $rowTotals[$rowLabel] = ($rowTotals[$rowLabel] ?? 0) + $value;
        }

        arsort($columnTotals);
        $columns = array_slice(array_keys($columnTotals), 0, $maxColumns);
        $pivotRows = [];

        foreach ($matrix as $rowLabel => $cells) {
            $line = ['label' => $rowLabel, 'cells' => [], 'total' => round($rowTotals[$rowLabel] ?? 0, 4)];
            foreach ($columns as $column) {
                $line['cells'][$column] = round((float) ($cells[$column] ?? 0), 4);
            }
            $pivotRows[] = $line;
        }

        usort($pivotRows, fn ($a, $b) => $b['total'] <=> $a['total']);

        return [
            'ready' => true,
            'row_key' => $rowKey,
            'col_key' => $colKey,
            'value_key' => $valueKey,
            'row_label' => $dimensionMap[$rowKey]['label'] ?? $rowKey,
            'col_label' => $dimensionMap[$colKey]['label'] ?? $colKey,
            'value_label' => $measureMap[$valueKey]['label'] ?? $valueKey,
            'columns' => $columns,
            'rows' => $pivotRows,
            'column_totals' => collect($columns)->mapWithKeys(fn ($col) => [$col => round($columnTotals[$col] ?? 0, 4)])->all(),
            'grand_total' => round(array_sum($rowTotals), 4),
        ];
    }

    public function buildChartPayload($rows, array $dimensions, array $measures, array $dimensionMap, array $measureMap, string $chartType): ?array
    {
        $categoryKey = $dimensions[0] ?? null;
        if (!$categoryKey || $measures === []) {
            return null;
        }

        $maxPoints = max(5, (int) config('erp_scale.bi_report_builder.chart_max_points', 30));
        $sorted = collect($rows)->take($maxPoints);
        $categories = $sorted->map(fn ($row) => (string) ($row->{$categoryKey} ?? '-'))->values()->all();
        $series = [];

        foreach (array_slice($measures, 0, 3) as $measureKey) {
            $series[] = [
                'key' => $measureKey,
                'name' => $measureMap[$measureKey]['label'] ?? $measureKey,
                'data' => $sorted->map(fn ($row) => $this->rowNumber($row, $measureKey))->values()->all(),
            ];
        }

        return [
            'type' => $chartType,
            'category_key' => $categoryKey,
            'category_label' => $dimensionMap[$categoryKey]['label'] ?? $categoryKey,
            'categories' => $categories,
            'series' => $series,
        ];
    }

    private function viewMode(array $input): string
    {
        $mode = (string) ($input['view_mode'] ?? 'table');

        return array_key_exists($mode, $this->viewModes()) ? $mode : 'table';
    }

    private function chartType(array $input): string
    {
        $type = (string) ($input['chart_type'] ?? 'table');

        return array_key_exists($type, $this->chartTypes()) ? $type : 'table';
    }

    private function recentRunsForUser($user)
    {
        $query = BiReportRun::query()->latest('finished_at')->limit(10);

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
            if ($this->organizationId($user)) {
                $query->where('organization_id', $this->organizationId($user));
            }
        }

        return $query->get();
    }

    private function schedulesForUser($user)
    {
        $query = BiReportSchedule::query()->with('template')->where('is_active', true)->latest('id')->limit(10);

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
            if ($this->organizationId($user)) {
                $query->where('organization_id', $this->organizationId($user));
            }
        }

        return $query->get();
    }

    private function recentDeliveriesForUser($user)
    {
        $query = BiReportDelivery::query()->latest('generated_at')->limit(10);

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
            if ($this->organizationId($user)) {
                $query->where('organization_id', $this->organizationId($user));
            }
        }

        return $query->get();
    }

    private function salesMetrics($user, string $summaryDate): array
    {
        if (!Schema::hasTable('pishfactors')) {
            return [];
        }

        $base = DB::table('pishfactors');
        $this->applyDeletedFilter($base, 'pishfactors');
        $this->applyOperationalScope($base, 'pishfactors', $user);
        $this->applyDateFilter($base, 'pishfactors', ['created_at', 'recive_date_en'], $summaryDate);

        $gross = Schema::hasColumn('pishfactors', 'fullPrice')
            ? (float) (clone $base)->selectRaw($this->numericSumExpression('fullPrice') . ' as aggregate')->value('aggregate')
            : 0.0;

        return [
            $this->metricRow('sales', 'sales_order_count', (clone $base)->count(), ['source' => 'pishfactors']),
            $this->metricRow('sales', 'sales_gross_amount', $gross, ['source' => 'pishfactors.fullPrice']),
        ];
    }

    private function financeMetrics($user, string $summaryDate): array
    {
        if (!Schema::hasTable('vouchers')) {
            return [];
        }

        $base = DB::table('vouchers');
        $this->applyDeletedFilter($base, 'vouchers');
        $this->applyOperationalScope($base, 'vouchers', $user);
        $this->applyDateFilter($base, 'vouchers', ['voucher_date_en', 'created_at'], $summaryDate);

        if (Schema::hasColumn('vouchers', 'total_debit')) {
            $debit = (float) (clone $base)->selectRaw('COALESCE(SUM(total_debit), 0) as aggregate')->value('aggregate');
        } elseif (Schema::hasColumn('vouchers', 'amount')) {
            $debit = (float) (clone $base)->selectRaw($this->numericSumExpression('amount') . ' as aggregate')->value('aggregate');
        } else {
            $debit = 0.0;
        }

        return [
            $this->metricRow('finance', 'finance_voucher_count', (clone $base)->count(), ['source' => 'vouchers']),
            $this->metricRow('finance', 'finance_debit_total', $debit, ['source' => 'vouchers.total_debit']),
        ];
    }

    private function inventoryMetrics($user, string $summaryDate): array
    {
        if (!Schema::hasTable('inventory_balances')) {
            return [];
        }

        $base = DB::table('inventory_balances');
        $this->applyOperationalScope($base, 'inventory_balances', $user);

        if (Schema::hasColumn('inventory_balances', 'total_cost')) {
            $stockValue = (float) (clone $base)->selectRaw('COALESCE(SUM(total_cost), 0) as aggregate')->value('aggregate');
        } elseif (Schema::hasColumn('inventory_balances', 'unit_cost')) {
            $stockValue = (float) (clone $base)->selectRaw('COALESCE(SUM(quantity * unit_cost), 0) as aggregate')->value('aggregate');
        } else {
            $stockValue = 0.0;
        }

        $lowStock = 0;
        if (Schema::hasColumn('inventory_balances', 'minimum_quantity')) {
            $lowStock = (clone $base)
                ->whereNotNull('minimum_quantity')
                ->whereRaw('(quantity - COALESCE(reserved_quantity, 0)) <= minimum_quantity')
                ->count();
        }

        return [
            $this->metricRow('inventory', 'inventory_stock_value', $stockValue, ['source' => 'inventory_balances']),
            $this->metricRow('inventory', 'inventory_low_stock_items', $lowStock, ['source' => 'inventory_balances.minimum_quantity']),
        ];
    }

    private function crmOperationalMetrics($user, string $summaryDate): array
    {
        $rows = [];

        if (Schema::hasTable('crm_service_tickets')) {
            $tickets = DB::table('crm_service_tickets');
            $this->applyDeletedFilter($tickets, 'crm_service_tickets');
            $this->applyOperationalScope($tickets, 'crm_service_tickets', $user);
            if (Schema::hasColumn('crm_service_tickets', 'status')) {
                $tickets->whereIn('status', ['open', 'in_progress', 'waiting_customer', 'needs_followup']);
            }
            $rows[] = $this->metricRow('crm', 'crm_open_service_tickets', $tickets->count(), ['source' => 'crm_service_tickets']);
        }

        if (Schema::hasTable('crm_call_logs')) {
            $calls = DB::table('crm_call_logs');
            $this->applyDeletedFilter($calls, 'crm_call_logs');
            $this->applyOperationalScope($calls, 'crm_call_logs', $user);
            $this->applyDateFilter($calls, 'crm_call_logs', ['call_started_at', 'created_at'], $summaryDate);
            $rows[] = $this->metricRow('crm', 'crm_calls_today', $calls->count(), ['source' => 'crm_call_logs']);
        }

        return $rows;
    }

    private function purchaseMetrics($user, string $summaryDate): array
    {
        return $this->tableCountAndSumMetrics($user, 'purchase_orders', 'purchase', 'purchase_order_count', 'purchase_order_amount', 'total_amount', ['order_date_en', 'created_at'], $summaryDate);
    }

    private function productionMetrics($user, string $summaryDate): array
    {
        return $this->tableCountAndSumMetrics($user, 'production_orders', 'production', 'production_order_count', 'production_material_cost', 'material_cost', ['date_en', 'created_at'], $summaryDate);
    }

    private function treasuryMetrics($user): array
    {
        return $this->tableCountAndSumMetrics($user, 'treasury_instruments', 'treasury', 'treasury_instrument_count', 'treasury_instrument_amount', 'amount', [], null, ['status' => ['open', 'issued', 'in_hand', 'deposited', 'pending']]);
    }

    private function payrollMetrics($user, string $summaryDate): array
    {
        return $this->tableCountAndSumMetrics($user, 'payroll_runs', 'payroll', 'payroll_run_count', 'payroll_net_pay_amount', 'net_pay_amount', ['payroll_date_en', 'created_at'], $summaryDate);
    }

    private function assetMetrics($user): array
    {
        return $this->tableCountAndSumMetrics($user, 'company_assets', 'assets', 'asset_active_count', 'asset_acquisition_cost', 'acquisition_cost', [], null, ['status' => ['active', 'in_service']]);
    }

    private function distributionMetrics($user, string $summaryDate): array
    {
        return $this->tableCountAndSumMetrics($user, 'distribution_mobile_orders', 'distribution', 'distribution_mobile_order_count', 'distribution_net_amount', 'net_amount', ['synced_at', 'offline_created_at', 'created_at'], $summaryDate);
    }

    private function ecommerceMetrics($user, string $summaryDate): array
    {
        return $this->tableCountAndSumMetrics($user, 'ecommerce_order_mappings', 'ecommerce', 'ecommerce_order_count', 'ecommerce_net_amount', 'net_amount', ['received_at', 'created_at'], $summaryDate);
    }

    private function contractingMetrics($user): array
    {
        return $this->tableCountAndSumMetrics($user, 'contracting_projects', 'contracting', 'contracting_project_count', 'contracting_contract_amount', 'contract_amount', [], null, ['status' => ['active', 'approved', 'in_progress']]);
    }

    private function tableCountAndSumMetrics($user, string $table, string $domain, string $countMetric, ?string $sumMetric, ?string $sumColumn, array $dateColumns, ?string $summaryDate, array $whereIn = []): array
    {
        if (!Schema::hasTable($table)) {
            return [];
        }

        $base = DB::table($table);
        $this->applyDeletedFilter($base, $table);
        $this->applyOperationalScope($base, $table, $user);
        if ($summaryDate) {
            $this->applyDateFilter($base, $table, $dateColumns, $summaryDate);
        }
        foreach ($whereIn as $column => $values) {
            if (Schema::hasColumn($table, $column)) {
                $base->whereIn($column, $values);
            }
        }

        $rows = [$this->metricRow($domain, $countMetric, (clone $base)->count(), ['source' => $table])];

        if ($sumMetric && $sumColumn && Schema::hasColumn($table, $sumColumn)) {
            $rows[] = $this->metricRow($domain, $sumMetric, (float) (clone $base)->selectRaw('COALESCE(SUM(' . $sumColumn . '), 0) as aggregate')->value('aggregate'), ['source' => $table . '.' . $sumColumn]);
        }

        return $rows;
    }

    private function metricRow(string $domain, string $metricKey, float $value, array $metadata = []): array
    {
        return ['domain' => $domain, 'metric_key' => $metricKey, 'value' => $value, 'metadata' => $metadata];
    }

    private function definitionMap(array $definitions): array
    {
        return collect($definitions)->filter(fn($definition) => isset($definition['key'], $definition['column']))->keyBy('key')->all();
    }

    private function selectedKeys($selected, array $allowedMap, array $defaults, int $max): array
    {
        $selected = $this->normaliseArray($selected) ?: $defaults;

        return collect($selected)
            ->filter(fn($key) => isset($allowedMap[$key]))
            ->unique()
            ->take($max)
            ->values()
            ->all() ?: array_slice(array_keys($allowedMap), 0, 1);
    }

    private function normaliseArray($value): array
    {
        if (is_null($value) || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return array_values(array_filter($value, fn($item) => $item !== null && $item !== ''));
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }

    private function applySummaryScope($query, $user): void
    {
        if ((int) $user->isGod === 1) {
            return;
        }

        $query->where('tenant_id', $this->tenantId($user));
        if ($this->organizationId($user)) {
            $query->where('organization_id', $this->organizationId($user));
        }
    }

    private function applyFixedFilters($query, array $fixedFilters): void
    {
        foreach ($fixedFilters as $column => $value) {
            $this->assertSafeColumn($column);
            $query->where($column, $value);
        }
    }

    private function applyUserFilters($query, BiDatasetDefinition $dataset, array $input): void
    {
        $filterMap = collect($dataset->filters ?? [])->keyBy('key');
        foreach ($filterMap as $filterKey => $filter) {
            $value = Arr::get($input, $filterKey, Arr::get($input, 'filters.' . $filterKey));
            if ($value === null || $value === '') {
                continue;
            }

            $column = $filter['column'];
            $operator = $filter['operator'] ?? '=';
            $this->assertSafeColumn($column);

            if (($filter['type'] ?? null) === 'date') {
                $query->whereDate($column, $operator, $value);
            } elseif ($operator === 'in') {
                $values = $this->normaliseArray($value);
                if ($values) {
                    $query->whereIn($column, $values);
                }
            } else {
                $query->where($column, $operator, $value);
            }
        }
    }

    private function allowedFilters(BiDatasetDefinition $dataset, array $input): array
    {
        $filters = [];
        foreach (($dataset->filters ?? []) as $filter) {
            $key = $filter['key'];
            $value = Arr::get($input, $key, Arr::get($input, 'filters.' . $key));
            if ($value !== null && $value !== '') {
                $filters[$key] = is_array($value) ? array_values($value) : $value;
            }
        }

        return $filters;
    }

    private function aggregateExpression(string $aggregate, string $column): string
    {
        return match ($aggregate) {
            'avg' => 'AVG(' . $column . ')',
            'max' => 'MAX(' . $column . ')',
            'min' => 'MIN(' . $column . ')',
            'count' => 'COUNT(' . $column . ')',
            default => 'SUM(' . $column . ')',
        };
    }

    private function sortConfig(array $input, BiDatasetDefinition $dataset, array $dimensions, array $measures): ?array
    {
        $allowed = array_merge($dimensions, $measures);
        $sortBy = $input['sort_by'] ?? Arr::get($dataset->default_sort ?? [], 'column');
        $direction = strtolower($input['sort_direction'] ?? Arr::get($dataset->default_sort ?? [], 'direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (!$sortBy || !in_array($sortBy, $allowed, true)) {
            return null;
        }

        return ['column' => $sortBy, 'direction' => $direction];
    }

    private function resultColumns(array $dimensions, array $measures, array $dimensionMap, array $measureMap): array
    {
        $columns = [];
        foreach ($dimensions as $key) {
            $columns[] = ['key' => $key, 'label' => $dimensionMap[$key]['label'] ?? $key, 'type' => 'dimension'];
        }
        foreach ($measures as $key) {
            $columns[] = ['key' => $key, 'label' => $measureMap[$key]['label'] ?? $key, 'type' => 'measure'];
        }

        return $columns;
    }

    private function totals($rows, array $measures): array
    {
        $totals = [];
        foreach ($measures as $measure) {
            $totals[$measure] = round((float) $rows->sum($measure), 4);
        }

        return $totals;
    }

    private function applySecurityPolicy($user, $rows, array $columns, array $measures, array $dimensions, array $filters): array
    {
        $rows = collect($rows)->values();
        $sensitiveRows = 0;
        $maskMeasures = !$this->canViewSensitiveBi($user);

        if ($maskMeasures) {
            $rows->each(function ($row) use ($measures, $dimensions, $filters, &$sensitiveRows) {
                if (!$this->rowIsSensitive($row, $dimensions, $filters)) {
                    return;
                }

                $sensitiveRows++;
                foreach ($measures as $measure) {
                    if (property_exists($row, $measure)) {
                        $row->{$measure} = null;
                    }
                }

                foreach (['comparison_value_sum', 'previous_value', 'delta_value', 'delta_percent', 'variance_value', 'achievement_percent', 'cumulative_value', 'rolling_3_avg'] as $analysisMeasure) {
                    if (property_exists($row, $analysisMeasure)) {
                        $row->{$analysisMeasure} = null;
                    }
                }

                $row->security_masked = true;
            });
        }

        $hasSensitiveResult = $sensitiveRows > 0 || $this->filtersAreSensitive($filters);

        return [
            'rows' => $rows,
            'policy' => [
                'sensitive_result' => $hasSensitiveResult,
                'masked_rows' => $sensitiveRows,
                'masking_applied' => $maskMeasures && $hasSensitiveResult,
                'export_allowed' => !$hasSensitiveResult || $this->canExportSensitiveBi($user),
                'policy' => $hasSensitiveResult ? 'sensitive_bi_summary' : 'standard_bi_summary',
            ],
        ];
    }

    public function canExportSensitiveBi($user): bool
    {
        return (int) ($user->isGod ?? 0) === 1 || (int) ($user->isAdmin ?? 0) === 1;
    }

    private function canViewSensitiveBi($user): bool
    {
        return $this->canExportSensitiveBi($user);
    }

    private function rowIsSensitive($row, array $dimensions, array $filters): bool
    {
        $domain = in_array('domain', $dimensions, true) ? ($row->domain ?? null) : ($filters['domain'] ?? null);
        $metric = in_array('metric_key', $dimensions, true) ? ($row->metric_key ?? null) : ($filters['metric_key'] ?? null);

        return $this->domainIsSensitive($domain) || $this->metricIsSensitive($metric);
    }

    private function filtersAreSensitive(array $filters): bool
    {
        return $this->domainIsSensitive($filters['domain'] ?? null) || $this->metricIsSensitive($filters['metric_key'] ?? null);
    }

    private function domainIsSensitive($domain): bool
    {
        return in_array((string) $domain, ['finance', 'treasury', 'payroll'], true);
    }

    private function metricIsSensitive($metric): bool
    {
        foreach ($this->normaliseArray($metric) as $value) {
            foreach (['amount', 'debit', 'credit', 'pay', 'salary', 'cash', 'voucher', 'balance'] as $needle) {
                if (str_contains(strtolower((string) $value), $needle)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function analysisModes(): array
    {
        return [
            'none' => 'بدون تحلیل تکمیلی',
            'ranking' => 'رتبه بندی نزولی',
            'month_over_month' => 'ماه به ماه',
            'year_over_year' => 'سال به سال',
            'share_of_total' => 'سهم از کل',
            'budget_vs_actual' => 'بودجه در برابر واقعیت',
            'target_vs_performance' => 'هدف در برابر عملکرد',
            'cohort' => 'Cohort خلاصه',
            'retention' => 'Retention خلاصه',
            'funnel' => 'Funnel خلاصه',
            'pareto' => 'Pareto / ABC',
            'period_compare' => 'مقایسه دوره ای',
            'trend' => 'Trend و جهت تغییر',
            'rolling_average' => 'میانگین متحرک 3 ردیفی',
            'waterfall' => 'Waterfall تجمعی',
            'heatmap' => 'Heatmap شدت شاخص',
        ];
    }

    private function analysisMode(array $input): string
    {
        $mode = (string) ($input['analysis_mode'] ?? 'none');

        return array_key_exists($mode, $this->analysisModes()) ? $mode : 'none';
    }

    private function applyAnalysisMode($rows, array $columns, array $measures, array $dimensions, string $mode): array
    {
        $rows = collect($rows)->values();
        $primaryMeasure = $measures[0] ?? null;

        if ($mode === 'none' || !$primaryMeasure || $rows->isEmpty()) {
            return ['rows' => $rows, 'columns' => $columns, 'insights' => []];
        }

        $insights = [];
        $addColumn = function (string $key, string $label, string $type = 'measure') use (&$columns): void {
            if (!collect($columns)->contains('key', $key)) {
                $columns[] = ['key' => $key, 'label' => $label, 'type' => $type];
            }
        };

        if (in_array($mode, ['ranking', 'share_of_total', 'pareto', 'heatmap'], true)) {
            $rows = $rows->sortByDesc(fn($row) => $this->rowNumber($row, $primaryMeasure))->values();
        } elseif (in_array($mode, ['period_compare', 'month_over_month', 'year_over_year', 'trend', 'rolling_average', 'waterfall', 'cohort', 'retention', 'funnel'], true) && in_array('summary_date', $dimensions, true)) {
            $rows = $rows->sortBy(fn($row) => (string) ($row->summary_date ?? ''))->values();
        }

        if ($mode === 'ranking') {
            $addColumn('analysis_rank', 'رتبه', 'measure');
            $rows->each(fn($row, $index) => $row->analysis_rank = $index + 1);
            $insights[] = 'ردیف ها بر اساس ' . $primaryMeasure . ' رتبه بندی شدند.';
        }

        if ($mode === 'share_of_total') {
            $total = max(0.0001, (float) $rows->sum(fn($row) => $this->rowNumber($row, $primaryMeasure)));
            $addColumn('share_percent', 'سهم از کل %', 'measure');
            $rows->each(fn($row) => $row->share_percent = round(($this->rowNumber($row, $primaryMeasure) / $total) * 100, 2));
            $insights[] = 'سهم هر ردیف از جمع کل محاسبه شد.';
        }

        if ($mode === 'pareto') {
            $total = max(0.0001, (float) $rows->sum(fn($row) => $this->rowNumber($row, $primaryMeasure)));
            $running = 0.0;
            $addColumn('cumulative_percent', 'درصد تجمعی', 'measure');
            $addColumn('pareto_bucket', 'گروه Pareto', 'dimension');
            $rows->each(function ($row) use (&$running, $primaryMeasure, $total) {
                $running += $this->rowNumber($row, $primaryMeasure);
                $row->cumulative_percent = round(($running / $total) * 100, 2);
                $row->pareto_bucket = $row->cumulative_percent <= 80 ? 'A' : ($row->cumulative_percent <= 95 ? 'B' : 'C');
            });
            $insights[] = 'تحلیل Pareto با گروه های A/B/C روی measure اصلی ساخته شد.';
        }

        if (in_array($mode, ['period_compare', 'month_over_month', 'year_over_year', 'trend'], true)) {
            $addColumn('previous_value', 'مقدار دوره قبل', 'measure');
            $addColumn('delta_value', 'اختلاف', 'measure');
            $addColumn('delta_percent', 'درصد تغییر', 'measure');
            if ($mode === 'trend') {
                $addColumn('trend_direction', 'جهت روند', 'dimension');
            }

            $previous = null;
            $rows->each(function ($row) use (&$previous, $primaryMeasure, $mode) {
                $current = $this->rowNumber($row, $primaryMeasure);
                $row->previous_value = $previous;
                $row->delta_value = $previous === null ? null : round($current - $previous, 4);
                $row->delta_percent = $previous === null || abs((float) $previous) < 0.0001 ? null : round((($current - $previous) / abs((float) $previous)) * 100, 2);
                if ($mode === 'trend') {
                    $row->trend_direction = $row->delta_value === null ? '-' : ($row->delta_value > 0 ? 'up' : ($row->delta_value < 0 ? 'down' : 'flat'));
                }
                $previous = $current;
            });
            $insights[] = 'تغییر هر ردیف نسبت به ردیف قبلی محاسبه شد؛ برای MoM/YoY دقیق، data mart باید دوره های تاریخی همان شاخص را داشته باشد.';
        }

        if (in_array($mode, ['budget_vs_actual', 'target_vs_performance'], true)) {
            $addColumn('comparison_value_sum', $mode === 'budget_vs_actual' ? 'بودجه/مبنای مقایسه' : 'هدف/مبنای مقایسه', 'measure');
            $addColumn('variance_value', 'انحراف', 'measure');
            $addColumn('achievement_percent', 'درصد تحقق', 'measure');
            $rows->each(function ($row) use ($primaryMeasure) {
                $actual = $this->rowNumber($row, $primaryMeasure);
                $comparison = $this->rowNumber($row, 'comparison_value_sum');
                $row->variance_value = round($actual - $comparison, 4);
                $row->achievement_percent = abs($comparison) < 0.0001 ? null : round(($actual / abs($comparison)) * 100, 2);
            });
            $insights[] = 'واقعیت با فیلد comparison_value data mart مقایسه شد؛ اگر هدف/بودجه ثبت نشده باشد مبنای مقایسه صفر می ماند.';
        }

        if ($mode === 'cohort') {
            $addColumn('cohort_period', 'دوره Cohort', 'dimension');
            $rows->each(function ($row) {
                $row->cohort_period = $this->cohortPeriod($row->summary_date ?? null);
            });
            $insights[] = 'Cohort در سطح summary_date ساخته شد؛ cohort رفتاری دقیق به backfill رویدادهای مشتری نیاز دارد.';
        }

        if ($mode === 'retention') {
            $firstValue = null;
            $addColumn('retention_percent', 'Retention %', 'measure');
            $rows->each(function ($row) use (&$firstValue, $primaryMeasure) {
                $current = $this->rowNumber($row, $primaryMeasure);
                if ($firstValue === null && abs($current) > 0.0001) {
                    $firstValue = $current;
                }
                $row->retention_percent = $firstValue === null || abs($firstValue) < 0.0001 ? null : round(($current / abs($firstValue)) * 100, 2);
            });
            $insights[] = 'Retention نسبت به اولین مقدار غیرصفر در خروجی فعلی محاسبه شد.';
        }

        if ($mode === 'funnel') {
            $addColumn('funnel_step', 'مرحله Funnel', 'measure');
            $addColumn('conversion_from_previous', 'تبدیل از مرحله قبل %', 'measure');
            $previous = null;
            $rows->each(function ($row, $index) use (&$previous, $primaryMeasure) {
                $current = $this->rowNumber($row, $primaryMeasure);
                $row->funnel_step = $index + 1;
                $row->conversion_from_previous = $previous === null || abs($previous) < 0.0001 ? null : round(($current / abs($previous)) * 100, 2);
                $previous = $current;
            });
            $insights[] = 'Funnel خلاصه از ترتیب ردیف های خروجی ساخته شد؛ funnel دقیق مرحله ای به رویدادهای stage-level نیاز دارد.';
        }

        if ($mode === 'rolling_average') {
            $addColumn('rolling_3_avg', 'میانگین متحرک 3 ردیفی', 'measure');
            $values = [];
            $rows->each(function ($row) use (&$values, $primaryMeasure) {
                $values[] = $this->rowNumber($row, $primaryMeasure);
                $window = array_slice($values, -3);
                $row->rolling_3_avg = round(array_sum($window) / max(1, count($window)), 4);
            });
            $insights[] = 'میانگین متحرک 3 ردیفی برای تشخیص نوسان نرم محاسبه شد.';
        }

        if ($mode === 'waterfall') {
            $addColumn('cumulative_value', 'مقدار تجمعی', 'measure');
            $running = 0.0;
            $rows->each(function ($row) use (&$running, $primaryMeasure) {
                $running += $this->rowNumber($row, $primaryMeasure);
                $row->cumulative_value = round($running, 4);
            });
            $insights[] = 'مقدار تجمعی برای تحلیل waterfall ساخته شد.';
        }

        if ($mode === 'heatmap') {
            $max = max(0.0001, (float) $rows->max(fn($row) => $this->rowNumber($row, $primaryMeasure)));
            $addColumn('heatmap_intensity', 'شدت Heatmap %', 'measure');
            $rows->each(fn($row) => $row->heatmap_intensity = round(($this->rowNumber($row, $primaryMeasure) / $max) * 100, 2));
            $insights[] = 'شدت نسبی هر ردیف نسبت به بیشترین مقدار همان خروجی محاسبه شد.';
        }

        return ['rows' => $rows, 'columns' => $columns, 'insights' => $insights];
    }

    private function rowNumber($row, string $key): float
    {
        return is_numeric($row->{$key} ?? null) ? (float) $row->{$key} : 0.0;
    }

    private function cohortPeriod($date): string
    {
        if (!$date) {
            return 'summary';
        }

        $timestamp = strtotime((string) $date);

        return $timestamp ? date('Y-m', $timestamp) : 'summary';
    }

    private function applyDeletedFilter($query, string $table): void
    {
        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }
    }

    private function applyOperationalScope($query, string $table, $user): void
    {
        if ((int) $user->isGod === 1) {
            return;
        }

        if (Schema::hasColumn($table, 'tenant_id')) {
            $query->where('tenant_id', $this->tenantId($user));
        } elseif (Schema::hasColumn($table, 'tenants_id')) {
            $query->where('tenants_id', $this->tenantId($user));
        }

        if ($this->organizationId($user) && Schema::hasColumn($table, 'organization_id')) {
            $query->where('organization_id', $this->organizationId($user));
        }
    }

    private function applyDateFilter($query, string $table, array $columns, string $date): void
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                $query->whereDate($column, $date);
                return;
            }
        }
    }

    private function numericSumExpression(string $column): string
    {
        $this->assertSafeColumn($column);

        return "COALESCE(SUM(CAST(REPLACE(REPLACE(COALESCE($column, 0), ',', ''), ' ', '') AS DECIMAL(24,4))), 0)";
    }

    private function assertSafeColumn(string $column): void
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $column)) {
            throw ValidationException::withMessages(['dataset_key' => 'تعریف dataset معتبر نیست.']);
        }
    }

    private function tenantId($user): ?int
    {
        return $user->tenant_id ?: $user->tenants_id ?: null;
    }

    private function organizationId($user): ?int
    {
        return is_numeric($user->organization_id) ? (int) $user->organization_id : null;
    }

    private function emptyResult(): array
    {
        return [
            'dataset_key' => null,
            'columns' => [],
            'rows' => collect(),
            'totals' => [],
            'selected_dimensions' => [],
            'selected_measures' => [],
            'analysis_mode' => 'none',
            'analysis_modes' => $this->analysisModes(),
            'analysis_insights' => [],
            'security' => ['sensitive_result' => false, 'masked_rows' => 0, 'masking_applied' => false, 'export_allowed' => true, 'policy' => 'standard_bi_summary'],
            'filters' => [],
            'limit' => 100,
            'view_mode' => 'table',
            'chart_type' => 'table',
            'pivot' => null,
            'chart' => null,
        ];
    }
}

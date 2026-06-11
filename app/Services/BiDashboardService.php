<?php

namespace App\Services;

use App\Models\BiDailySummary;
use App\Models\BiMetricDefinition;
use App\Models\BiRefreshLog;

class BiDashboardService
{
    public function __construct(private CrmDashboardService $crmDashboardService) {}

    public function dashboardForUser($user): array
    {
        $latestDate = $this->summaryQuery($user)->max('summary_date');
        $summaries = $latestDate ? $this->summaryQuery($user)->whereDate('summary_date', $latestDate)->get()->keyBy('metric_key') : collect();

        return [
            'metrics' => BiMetricDefinition::query()->where('is_active', true)->orderBy('domain')->orderBy('metric_key')->get(),
            'summaries' => $summaries,
            'latest_date' => $latestDate,
            'refresh_logs' => $this->refreshLogQuery($user)->latest('finished_at')->limit(10)->get(),
            'health' => $this->healthRows($summaries, $latestDate),
        ];
    }

    public function refreshCrmSummary($user): BiRefreshLog
    {
        $startedAt = now();
        $tenantId = $this->tenantId($user);
        $organizationId = $this->organizationId($user);
        $summary = $this->crmDashboardService->forUser($user)['summary'];
        $metricValues = [
            'crm_open_cards' => $summary['open_cards'],
            'crm_overdue_followups' => $summary['overdue_followups'],
            'crm_weighted_pipeline' => $summary['weighted_pipeline'],
            'crm_win_rate' => $summary['win_rate'],
        ];

        foreach ($metricValues as $metricKey => $value) {
            BiDailySummary::updateOrCreate([
                'summary_date' => $startedAt->toDateString(),
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'domain' => 'crm',
                'metric_key' => $metricKey,
                'dimension_type' => null,
                'dimension_id' => null,
            ], [
                'value' => $value,
                'metadata' => ['source' => 'crm_dashboard_service'],
                'refreshed_at' => now(),
            ]);
        }

        return BiRefreshLog::create([
            'dataset_key' => 'crm_daily_summary',
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'status' => 'success',
            'started_at' => $startedAt,
            'finished_at' => now(),
            'rows_count' => count($metricValues),
            'message' => 'CRM BI summary refreshed from operational CRM dashboard.',
            'metadata' => ['metrics' => array_keys($metricValues)],
        ]);
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

    private function refreshLogQuery($user)
    {
        $query = BiRefreshLog::query();

        if ((int) $user->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
            if ($this->organizationId($user)) {
                $query->where('organization_id', $this->organizationId($user));
            }
        }

        return $query;
    }

    private function healthRows($summaries, $latestDate)
    {
        return collect(['crm_open_cards', 'crm_overdue_followups', 'crm_weighted_pipeline', 'crm_win_rate'])->map(function ($metricKey) use ($summaries, $latestDate) {
            $row = $summaries->get($metricKey);

            return [
                'metric_key' => $metricKey,
                'status' => $row ? 'fresh' : 'missing',
                'value' => $row ? (float) $row->value : null,
                'refreshed_at' => $row ? $row->refreshed_at : null,
                'summary_date' => $latestDate,
            ];
        });
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

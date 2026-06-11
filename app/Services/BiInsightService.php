<?php

namespace App\Services;

use App\Models\BiAlertRule;
use App\Models\BiDailySummary;
use App\Models\BiInsightAlert;
use App\Models\BiMetricForecast;
use Illuminate\Support\Facades\DB;

class BiInsightService
{
    public function dashboardState($user): array
    {
        return [
            'rules' => $this->ruleQuery($user)->latest('id')->limit(30)->get(),
            'alerts' => $this->alertQuery($user)->latest('detected_at')->limit(40)->get(),
            'forecasts' => $this->forecastQuery($user)->latest('forecast_date')->limit(40)->get(),
            'metrics' => BiDailySummary::query()->select('domain', 'metric_key')->distinct()->orderBy('domain')->orderBy('metric_key')->get(),
            'stats' => [
                'open_alerts' => (clone $this->alertQuery($user))->where('status', 'open')->count(),
                'critical_alerts' => (clone $this->alertQuery($user))->whereIn('severity', ['high', 'critical'])->where('status', 'open')->count(),
                'rules' => (clone $this->ruleQuery($user))->where('is_active', true)->count(),
                'forecasts' => (clone $this->forecastQuery($user))->count(),
            ],
        ];
    }

    public function createRule($user, array $data): BiAlertRule
    {
        return BiAlertRule::create([
            'tenant_id' => $this->tenantId($user),
            'organization_id' => $this->organizationId($user),
            'domain' => $data['domain'],
            'metric_key' => $data['metric_key'],
            'rule_type' => $data['rule_type'],
            'operator' => $data['operator'] ?? null,
            'threshold_value' => $data['threshold_value'],
            'severity' => $data['severity'],
            'lookback_days' => $data['lookback_days'] ?? 7,
            'comparison_days' => $data['comparison_days'] ?? 7,
            'title' => $data['title'],
            'suggestion' => $data['suggestion'] ?? null,
            'is_active' => true,
            'created_by' => $user->id,
        ]);
    }

    public function runAnalysis($user): array
    {
        return DB::transaction(function () use ($user) {
            $alerts = 0;
            $forecasts = 0;
            $evaluated = 0;
            foreach ($this->ruleQuery($user)->where('is_active', true)->get() as $rule) {
                foreach ($this->latestSummaries($rule, $user) as $summary) {
                    $evaluated++;
                    $baseline = $this->baseline($rule, $summary);
                    $alertPayload = $this->alertPayload($rule, $summary, $baseline);
                    if ($alertPayload) {
                        BiInsightAlert::updateOrCreate([
                            'summary_date' => $summary->summary_date->toDateString(),
                            'tenant_id' => $summary->tenant_id,
                            'organization_id' => $summary->organization_id,
                            'domain' => $summary->domain,
                            'metric_key' => $summary->metric_key,
                            'alert_type' => $rule->rule_type,
                        ], $alertPayload);
                        $alerts++;
                    }

                    $forecasts += $this->buildForecasts($rule, $summary, $baseline);
                }
            }

            return ['alerts' => $alerts, 'forecasts' => $forecasts, 'evaluated' => $evaluated];
        });
    }

    public function updateAlertStatus($user, BiInsightAlert $alert, string $status): BiInsightAlert
    {
        abort_unless($this->alertQuery($user)->whereKey($alert->id)->exists(), 403);

        $alert->update([
            'status' => $status,
            'acknowledged_at' => $status === 'acknowledged' ? now() : $alert->acknowledged_at,
            'acknowledged_by' => $status === 'acknowledged' ? $user->id : $alert->acknowledged_by,
            'resolved_at' => $status === 'resolved' ? now() : $alert->resolved_at,
            'resolved_by' => $status === 'resolved' ? $user->id : $alert->resolved_by,
        ]);

        return $alert->refresh();
    }

    public function openAlertsFor($user, int $limit = 100)
    {
        return $this->alertQuery($user)
            ->where('status', 'open')
            ->latest('detected_at')
            ->limit(max(1, min(500, $limit)))
            ->get();
    }

    private function latestSummaries(BiAlertRule $rule, $user)
    {
        $query = BiDailySummary::query()->where('domain', $rule->domain)->where('metric_key', $rule->metric_key);
        $this->applyScope($query, $user);

        return $query
            ->select('tenant_id', 'organization_id', DB::raw('MAX(summary_date) as latest_summary_date'))
            ->groupBy('tenant_id', 'organization_id')
            ->orderByDesc('latest_summary_date')
            ->limit(500)
            ->get()
            ->map(function ($scope) use ($rule) {
                $summaryQuery = BiDailySummary::query()
                    ->where('domain', $rule->domain)
                    ->where('metric_key', $rule->metric_key)
                    ->whereDate('summary_date', $scope->latest_summary_date);

                $this->applyNullableScopeValue($summaryQuery, 'tenant_id', $scope->tenant_id);
                $this->applyNullableScopeValue($summaryQuery, 'organization_id', $scope->organization_id);

                return $summaryQuery->latest('id')->first();
            })
            ->filter()
            ->values();
    }

    private function baseline(BiAlertRule $rule, BiDailySummary $summary): float
    {
        $query = BiDailySummary::query()
            ->where('domain', $summary->domain)
            ->where('metric_key', $summary->metric_key)
            ->where('summary_date', '<', $summary->summary_date)
            ->where('summary_date', '>=', $summary->summary_date->copy()->subDays(max(1, (int) $rule->lookback_days)));
        $this->applyNullableScopeValue($query, 'tenant_id', $summary->tenant_id);
        $this->applyNullableScopeValue($query, 'organization_id', $summary->organization_id);

        $value = (float) $query->avg('value');

        return $value > 0 ? $value : (float) $summary->comparison_value;
    }

    private function alertPayload(BiAlertRule $rule, BiDailySummary $summary, float $baseline): ?array
    {
        $actual = (float) $summary->value;
        $threshold = (float) $rule->threshold_value;
        $deviation = $baseline > 0 ? (($actual - $baseline) / $baseline) * 100 : null;
        $triggered = false;

        if ($rule->rule_type === 'threshold') {
            $triggered = ($rule->operator === 'lte' || $rule->operator === 'below') ? $actual <= $threshold : $actual >= $threshold;
        } elseif ($rule->rule_type === 'drop' && $baseline > 0) {
            $triggered = $deviation <= -abs($threshold);
        } elseif ($rule->rule_type === 'spike' && $baseline > 0) {
            $triggered = $deviation >= abs($threshold);
        }

        if (!$triggered) {
            return null;
        }

        return [
            'tenant_id' => $summary->tenant_id,
            'organization_id' => $summary->organization_id,
            'bi_alert_rule_id' => $rule->id,
            'summary_date' => $summary->summary_date,
            'domain' => $summary->domain,
            'metric_key' => $summary->metric_key,
            'alert_type' => $rule->rule_type,
            'severity' => $rule->severity,
            'status' => 'open',
            'title' => $rule->title,
            'message' => $this->message($rule, $actual, $baseline, $deviation),
            'current_value' => $actual,
            'baseline_value' => $baseline,
            'deviation_percent' => $deviation,
            'suggestion' => $rule->suggestion,
            'metadata' => ['rule' => $rule->rule_type, 'operator' => $rule->operator],
            'detected_at' => now(),
        ];
    }

    private function buildForecasts(BiAlertRule $rule, BiDailySummary $summary, float $baseline): int
    {
        $actual = (float) $summary->value;
        $base = $baseline > 0 ? $baseline : $actual;
        $trend = $actual < $base ? 'down' : ($actual > $base ? 'up' : 'flat');
        $forecastValue = max(0, ($base * 0.6) + ($actual * 0.4));

        BiMetricForecast::updateOrCreate([
            'forecast_date' => $summary->summary_date->copy()->addDays(max(1, (int) $rule->comparison_days))->toDateString(),
            'tenant_id' => $summary->tenant_id,
            'organization_id' => $summary->organization_id,
            'domain' => $summary->domain,
            'metric_key' => $summary->metric_key,
        ], [
            'horizon_days' => max(1, (int) $rule->comparison_days),
            'method' => 'moving_average_weighted',
            'actual_value' => $actual,
            'forecast_value' => $forecastValue,
            'lower_bound' => max(0, $forecastValue * 0.85),
            'upper_bound' => $forecastValue * 1.15,
            'confidence_score' => $base > 0 ? 75 : 45,
            'trend_direction' => $trend,
            'metadata' => ['baseline' => $base, 'rule_id' => $rule->id],
            'generated_at' => now(),
        ]);

        return 1;
    }

    private function message(BiAlertRule $rule, float $actual, float $baseline, ?float $deviation): string
    {
        $change = $deviation === null ? '-' : number_format($deviation, 2) . '%';

        return $rule->title . '؛ مقدار فعلی ' . number_format($actual, 2) . '، مبنا ' . number_format($baseline, 2) . '، تغییر ' . $change;
    }

    private function ruleQuery($user)
    {
        $query = BiAlertRule::query();
        if ((int) $user->isGod !== 1) {
            $query->where(function ($scope) use ($user) {
                $scope->whereNull('tenant_id')->orWhere('tenant_id', $this->tenantId($user));
            });
        }

        return $query;
    }

    private function alertQuery($user)
    {
        $query = BiInsightAlert::query();
        $this->applyScope($query, $user);

        return $query;
    }

    private function forecastQuery($user)
    {
        $query = BiMetricForecast::query();
        $this->applyScope($query, $user);

        return $query;
    }

    private function applyScope($query, $user): void
    {
        if ((int) $user->isGod === 1) {
            return;
        }

        $query->where('tenant_id', $this->tenantId($user));
        if ($this->organizationId($user)) {
            $query->where('organization_id', $this->organizationId($user));
        }
    }

    private function applyNullableScopeValue($query, string $column, $value): void
    {
        is_null($value) ? $query->whereNull($column) : $query->where($column, $value);
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

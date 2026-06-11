<?php

namespace App\Services;

use App\Models\CrmCampaign;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class ErpPhaseGateService
{
    public function __construct(
        private ErpScaleHardeningService $scaleService,
        private CrmHealthAuditService $healthAuditService,
        private ErpLoadTestService $loadTestService,
        private ErpColdDataArchiveService $archiveService,
    ) {}

    public function evaluate(?User $user = null, bool $runLoadTest = false, bool $includeBi = false): array
    {
        $scale = $this->scaleService->audit($user);
        $tenantIsolation = $this->healthAuditService->tenantIsolationAudit($user);
        $health = $this->healthAuditService->audit($user);
        $criticalIssues = collect($health['issues'] ?? [])
            ->filter(fn (array $issue) => ($issue['severity'] ?? '') === 'critical' && (int) ($issue['count'] ?? 0) > 0);

        $thresholds = (array) config('erp_scale.load_test.gate', []);
        $minScore = (int) ($thresholds['scale_score_min'] ?? 85);
        $slowQueryLimitMs = (int) ($thresholds['slow_query_spike_ms'] ?? 2000);

        $checks = [
            'scale_score' => [
                'passed' => (int) $scale['readiness_score'] >= $minScore,
                'value' => (int) $scale['readiness_score'],
                'expected' => '>= ' . $minScore,
            ],
            'tenant_isolation' => [
                'passed' => (bool) $tenantIsolation['passed'],
                'value' => (int) $tenantIsolation['total'],
                'expected' => '0 unscoped',
            ],
            'critical_health_issues' => [
                'passed' => $criticalIssues->isEmpty(),
                'value' => $criticalIssues->count(),
                'expected' => '0 critical',
            ],
            'slow_query_spikes' => $this->slowQuerySpikeCheck($slowQueryLimitMs),
            'archive_policy_ready' => [
                'passed' => (bool) config('erp_scale.archive.enabled', true),
                'value' => $this->archiveService->candidateCount(),
                'expected' => 'policy enabled',
            ],
        ];

        $loadTest = null;

        if ($runLoadTest) {
            $loadTest = $this->loadTestService->run($user, $includeBi);
            $checks['load_test'] = [
                'passed' => (bool) $loadTest['passed'],
                'value' => collect($loadTest['checks'])->map(fn (array $row) => $row['ms'] . 'ms')->all(),
                'expected' => 'all scenarios under threshold',
            ];
        }

        $passed = collect($checks)->every(fn (array $check) => $check['passed']);

        return [
            'gate' => 'phase_0_1',
            'passed' => $passed,
            'checks' => $checks,
            'scale' => ['score' => $scale['readiness_score'], 'risk' => $scale['risk_level']],
            'tenant_isolation' => $tenantIsolation,
            'load_test' => $loadTest,
            'generated_at' => now(),
        ];
    }

    private function slowQuerySpikeCheck(int $limitMs): array
    {
        $path = $this->latestSlowQueryLog();
        $spikes = 0;

        if ($path && File::exists($path)) {
            $lines = array_slice(file($path, FILE_IGNORE_NEW_LINES) ?: [], -500);

            foreach ($lines as $line) {
                if (!str_contains($line, 'slow_query_detected')) {
                    continue;
                }

                if (preg_match('/"time_ms":([0-9.]+)/', $line, $matches)) {
                    if ((float) $matches[1] > $limitMs && $this->isListRouteLine($line)) {
                        $spikes++;
                    }
                }
            }
        }

        return [
            'passed' => $spikes === 0,
            'value' => $spikes,
            'expected' => '0 list spikes > ' . $limitMs . 'ms',
            'log' => $path,
        ];
    }

    private function latestSlowQueryLog(): ?string
    {
        $configured = storage_path('logs/slow-query.log');
        if (File::exists($configured)) {
            return $configured;
        }

        $files = File::glob(storage_path('logs/slow-query-*.log')) ?: [];

        return $files === [] ? null : end($files);
    }

    private function isListRouteLine(string $line): bool
    {
        return str_contains($line, 'customers')
            || str_contains($line, 'datatable')
            || str_contains($line, 'pishfactor')
            || str_contains($line, 'products');
    }

    public function evaluatePhase2(?User $user = null): array
    {
        $p0p1 = $this->evaluate($user);

        $checks = [
            'customer_360_route' => [
                'passed' => Route::has('customers.360'),
                'value' => Route::has('customers.360') ? 'customers.360' : 'missing',
                'expected' => 'route exists',
            ],
            'crm_forecast_dashboard' => [
                'passed' => class_exists(CrmDashboardService::class) && method_exists(CrmDashboardService::class, 'forUser'),
                'value' => 'CrmDashboardService::forUser',
                'expected' => 'forecast payload',
            ],
            'campaign_live_dispatch' => $this->campaignDispatchCheck($user),
            'lead_import_pipeline' => [
                'passed' => class_exists(\App\Services\CrmLeadBulkImportService::class)
                    && class_exists(\App\Jobs\ImportCrmLeadsJob::class),
                'value' => 'CrmLeadBulkImportService',
                'expected' => 'queue import ready',
            ],
        ];

        $passed = $p0p1['passed'] && collect($checks)->every(fn (array $check) => $check['passed']);

        return [
            'gate' => 'phase_2',
            'passed' => $passed,
            'checks' => $checks,
            'phase_0_1' => $p0p1,
            'generated_at' => now(),
        ];
    }

    public function evaluatePhase3(?User $user = null): array
    {
        $p0p1 = $this->evaluate($user);
        $minScore = (int) config('erp_scale.load_test.gate.scale_score_min', 85);

        $checks = [
            'scale_score' => [
                'passed' => (int) ($p0p1['scale']['score'] ?? 0) >= $minScore,
                'value' => (int) ($p0p1['scale']['score'] ?? 0),
                'expected' => '>= ' . $minScore,
            ],
            'bi_executive_routes' => [
                'passed' => Route::has('bi.executive.index') && Route::has('bi.cfo.index'),
                'value' => 'executive+cfo',
                'expected' => 'routes exist',
            ],
            'bi_reconciliation' => [
                'passed' => class_exists(BiReconciliationService::class)
                    && Route::has('bi.reconciliation.index'),
                'value' => 'BiReconciliationService',
                'expected' => '/bi/reconciliation',
            ],
            'bi_backfill_command' => [
                'passed' => class_exists(\App\Console\Commands\BackfillBiDataMart::class),
                'value' => 'bi:backfill-data-mart',
                'expected' => 'command registered',
            ],
            'report_builder_export' => [
                'passed' => Route::has('bi.report-builder.export'),
                'value' => 'bi.report-builder.export',
                'expected' => 'queued export',
            ],
        ];

        $passed = $p0p1['passed'] && collect($checks)->every(fn (array $check) => $check['passed']);

        return [
            'gate' => 'phase_3',
            'passed' => $passed,
            'checks' => $checks,
            'phase_0_1' => $p0p1,
            'generated_at' => now(),
        ];
    }

    private function campaignDispatchCheck(?User $user): array
    {
        $query = CrmCampaign::query()
            ->where('dispatch_status', 'completed')
            ->where(function ($scope) {
                $scope->where('sent_count', '>', 0)
                    ->orWhereHas('audiences', fn ($audience) => $audience->where('sms_status', 'fixed_template'));
            });

        if ($user && (int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        $count = (clone $query)->count();
        $fixedTemplateReady = !config('erp_scale.crm_campaign.sms_enabled', false)
            && class_exists(CrmCampaignService::class);

        return [
            'passed' => $count > 0 || $fixedTemplateReady,
            'value' => $count,
            'expected' => $count > 0
                ? '>=1 campaign completed'
                : 'fixed_template mode ready (sms_enabled=false)',
        ];
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ErpPhaseGateService;
use Illuminate\Console\Command;

class EvaluateErpPhaseGate extends Command
{
    protected $signature = 'erp:phase-gate
                            {--user= : Scope audits to one user}
                            {--phase=1 : Gate phase: 1, 2 or 3}
                            {--with-load-test : Run erp load benchmarks inside gate}
                            {--with-bi : Include BI refresh in load test}
                            {--json : JSON output}';

    protected $description = 'Evaluate ERP phase gate (P0/P1 or P2 CRM).';

    public function handle(ErpPhaseGateService $service): int
    {
        $user = $this->option('user') ? User::query()->find((int) $this->option('user')) : null;
        $phase = (int) $this->option('phase');
        $report = match ($phase) {
            2 => $service->evaluatePhase2($user),
            3 => $service->evaluatePhase3($user),
            default => $service->evaluate($user, (bool) $this->option('with-load-test'), (bool) $this->option('with-bi')),
        };

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            return $report['passed'] ? self::SUCCESS : self::FAILURE;
        }

        $this->info('erp_phase_gate=' . ($report['passed'] ? 'passed' : 'failed'));

        foreach ($report['checks'] as $name => $check) {
            $status = $check['passed'] ? 'ok' : 'fail';
            $value = is_array($check['value']) ? json_encode($check['value']) : $check['value'];
            $this->line("  {$name}: {$status} ({$value})");
        }

        return $report['passed'] ? self::SUCCESS : self::FAILURE;
    }
}

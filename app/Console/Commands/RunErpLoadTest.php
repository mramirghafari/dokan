<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ErpLoadTestService;
use Illuminate\Console\Command;

class RunErpLoadTest extends Command
{
    protected $signature = 'erp:load-test {--user= : User id for scoped benchmarks} {--with-bi : Include BI data mart refresh} {--json : JSON output}';

    protected $description = 'Benchmark customer list, search, invoice list and optional BI refresh.';

    public function handle(ErpLoadTestService $service): int
    {
        $user = $this->option('user') ? User::query()->find((int) $this->option('user')) : null;
        $report = $service->run($user, (bool) $this->option('with-bi'));

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            return $report['passed'] ? self::SUCCESS : self::FAILURE;
        }

        $this->info('erp_load_test=' . ($report['passed'] ? 'passed' : 'failed'));

        foreach ($report['checks'] as $name => $check) {
            $status = $check['passed'] ? 'ok' : 'fail';
            $this->line("  {$name}: {$status} {$check['ms']}ms / {$check['limit_ms']}ms");
        }

        return $report['passed'] ? self::SUCCESS : self::FAILURE;
    }
}

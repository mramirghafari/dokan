<?php

namespace App\Console\Commands;

use App\Services\CrmHealthAuditService;
use Illuminate\Console\Command;

class AuditTenantIsolation extends Command
{
    protected $signature = 'erp:audit-tenant-isolation {--json : Output JSON breakdown}';

    protected $description = 'Audit CRM/ERP/BI tables for records missing tenant scope metadata.';

    public function handle(CrmHealthAuditService $service): int
    {
        $report = $service->tenantIsolationAudit(null);

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            return $report['passed'] ? self::SUCCESS : self::FAILURE;
        }

        $this->info('tenant_isolation_scope=' . $report['scope_label']);
        $this->info('tenant_isolation_total=' . $report['total']);
        $this->info('tenant_isolation_passed=' . ($report['passed'] ? 'yes' : 'no'));

        foreach ($report['breakdown'] as $domain => $tables) {
            if ($tables === []) {
                continue;
            }

            $this->warn(strtoupper($domain) . ':');
            foreach ($tables as $table => $count) {
                $this->line("  {$table}={$count}");
            }
        }

        return $report['passed'] ? self::SUCCESS : self::FAILURE;
    }
}

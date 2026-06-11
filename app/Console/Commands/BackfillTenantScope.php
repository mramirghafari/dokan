<?php

namespace App\Console\Commands;

use App\Services\CrmHealthAuditService;
use App\Services\TenantBackfillService;
use Illuminate\Console\Command;

class BackfillTenantScope extends Command
{
    protected $signature = 'erp:backfill-tenant {--dry-run : Preview updates only} {--table= : Limit to one table}';

    protected $description = 'Backfill missing tenant_id values on ERP/CRM core tables.';

    public function handle(TenantBackfillService $backfill, CrmHealthAuditService $audit): int
    {
        $report = $backfill->run((bool) $this->option('dry-run'), $this->option('table') ?: null);
        $isolation = $audit->tenantIsolationAudit(null);

        $this->info('erp_backfill_tenant=' . ($report['dry_run'] ? 'dry_run' : 'done') . ' updated=' . $report['updated']);

        foreach ($report['tables'] as $table => $tableReport) {
            if (($tableReport['updated'] ?? 0) === 0) {
                continue;
            }

            $this->line("  {$table}: {$tableReport['updated']}");
        }

        $this->info('tenant_isolation_total=' . $isolation['total'] . ' passed=' . ($isolation['passed'] ? 'yes' : 'no'));

        return $isolation['passed'] || $report['dry_run'] ? self::SUCCESS : self::FAILURE;
    }
}

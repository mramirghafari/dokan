<?php

namespace App\Console\Commands;

use App\Services\CrmHealthAuditService;
use Illuminate\Console\Command;

class RefreshCrmHealth extends Command
{
    protected $signature = 'crm:refresh-health';

    protected $description = 'Calculate and persist CRM data quality, permission scope and integration health snapshot.';

    public function handle(CrmHealthAuditService $service): int
    {
        $snapshot = $service->persist(null);
        $isolation = $service->tenantIsolationAudit(null);

        $this->info('crm_health_refresh=ok score=' . $snapshot->health_score . ' risk=' . $snapshot->risk_level . ' issues=' . count($snapshot->issues ?: []));
        $this->info('tenant_isolation_total=' . $isolation['total'] . ' passed=' . ($isolation['passed'] ? 'yes' : 'no'));

        return $isolation['passed'] ? self::SUCCESS : self::FAILURE;
    }
}

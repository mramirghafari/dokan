<?php

namespace App\Console\Commands;

use App\Services\ErpScaleHardeningService;
use Illuminate\Console\Command;

class RefreshErpScaleAudit extends Command
{
    protected $signature = 'erp:refresh-scale-audit';

    protected $description = 'Calculate and persist ERP scale hardening readiness snapshot.';

    public function handle(ErpScaleHardeningService $service): int
    {
        $snapshot = $service->persist(null);

        $this->info('erp_scale_audit=ok score=' . $snapshot->readiness_score . ' risk=' . $snapshot->risk_level . ' checks=' . count($snapshot->checks ?: []));

        return self::SUCCESS;
    }
}

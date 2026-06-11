<?php

namespace App\Console\Commands;

use App\Services\BiReportDeliveryService;
use Illuminate\Console\Command;

class SendScheduledBiReports extends Command
{
    protected $signature = 'bi:send-scheduled-reports {--user_id= : Run schedules created by one user} {--dry-run : Count due schedules without delivery} {--limit=100 : Maximum due schedules to process}';

    protected $description = 'Generate scheduled BI report snapshots, secure links and panel delivery notifications.';

    public function handle(BiReportDeliveryService $service): int
    {
        $result = $service->runDue($this->option('user_id') ? (int) $this->option('user_id') : null, (int) $this->option('limit'), (bool) $this->option('dry-run'));
        $mode = $this->option('dry-run') ? 'dry_run' : 'sent';

        $this->info('bi_scheduled_reports_' . $mode . '=ok due=' . $result['due'] . ' delivered=' . $result['delivered'] . ' failed=' . $result['failed']);

        return 0;
    }
}

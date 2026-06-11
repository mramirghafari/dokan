<?php

namespace App\Console\Commands;

use App\Services\CrmIntegrationService;
use Illuminate\Console\Command;

class SyncCrmIntegrations extends Command
{
    protected $signature = 'crm:sync-integrations {--limit=200}';

    protected $description = 'Sync CRM integration queue for calendar, VoIP and external file providers.';

    public function handle(CrmIntegrationService $service): int
    {
        $calendarEvents = $service->syncDueCalendarEvents(null, (int) $this->option('limit'));

        $this->info('crm_integrations_sync=ok calendar_events=' . $calendarEvents);

        return self::SUCCESS;
    }
}

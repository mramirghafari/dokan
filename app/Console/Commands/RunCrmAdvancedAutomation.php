<?php

namespace App\Console\Commands;

use App\Services\CrmAdvancedAutomationService;
use Illuminate\Console\Command;

class RunCrmAdvancedAutomation extends Command
{
    protected $signature = 'crm:run-advanced-automation';

    protected $description = 'Run scheduled CRM automation for important leads, overdue tickets, and due opportunities.';

    public function handle(CrmAdvancedAutomationService $automationService): int
    {
        $result = $automationService->run();

        $this->info(
            'crm_advanced_automation=ok lead_followups=' . $result['lead_followups']
            . ' ticket_escalations=' . $result['ticket_escalations']
            . ' opportunity_followups=' . $result['opportunity_followups']
            . ' followup_escalations=' . $result['followup_escalations']
        );

        return 0;
    }
}

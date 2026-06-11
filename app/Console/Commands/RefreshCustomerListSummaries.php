<?php

namespace App\Console\Commands;

use App\Services\CustomerListSummaryService;
use Illuminate\Console\Command;

class RefreshCustomerListSummaries extends Command
{
    protected $signature = 'erp:refresh-customer-list-summaries';

    protected $description = 'Refresh cached customer list scope summaries for ERP list KPI cards.';

    public function handle(CustomerListSummaryService $service): int
    {
        $count = $service->refreshAllScopes();

        $this->info('customer_list_summaries=ok scopes=' . $count);

        return self::SUCCESS;
    }
}

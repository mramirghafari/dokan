<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\BiSelfServiceReportService;
use Illuminate\Console\Command;

class RefreshBiDataMart extends Command
{
    protected $signature = 'bi:refresh-data-mart {--user= : User id used for tenant and organization scope}';

    protected $description = 'Refresh BI enterprise data mart summaries from whitelisted operational sources.';

    public function handle(BiSelfServiceReportService $reportService): int
    {
        $user = $this->option('user')
            ? User::query()->find($this->option('user'))
            : User::query()->where('isGod', 1)->orderBy('id')->first();

        $user = $user ?: User::query()->orderBy('id')->first();
        if (!$user) {
            $this->error('No user found for BI data mart scope.');
            return self::FAILURE;
        }

        $log = $reportService->refreshEnterpriseDataMart($user);

        $this->info('bi_data_mart_refreshed rows=' . $log->rows_count . ' dataset=' . $log->dataset_key);

        return self::SUCCESS;
    }
}

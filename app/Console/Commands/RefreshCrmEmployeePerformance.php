<?php

namespace App\Console\Commands;

use App\Models\CrmEmployeePerformanceSnapshot;
use App\Models\User;
use App\Services\CrmEmployeePerformanceService;
use Illuminate\Console\Command;

class RefreshCrmEmployeePerformance extends Command
{
    protected $signature = 'crm:refresh-employee-performance {--period-start=} {--period-end=} {--role-scope=mixed} {--user-id=}';

    protected $description = 'Refresh CRM employee performance snapshots and coaching scorecards.';

    public function handle(CrmEmployeePerformanceService $service): int
    {
        $viewer = User::where('isGod', 1)->first() ?: User::where('isActive', 1)->first();

        if (!$viewer) {
            $this->warn('crm_employee_performance_refresh=skipped reason=no_user');
            return self::SUCCESS;
        }

        $roleScope = (string) $this->option('role-scope');
        if (!array_key_exists($roleScope, CrmEmployeePerformanceSnapshot::ROLE_SCOPES)) {
            $roleScope = 'mixed';
        }

        $count = $service->refresh($viewer, [
            'period_start' => $this->option('period-start') ?: now()->subDays(29)->toDateString(),
            'period_end' => $this->option('period-end') ?: now()->toDateString(),
            'role_scope' => $roleScope,
            'user_id' => $this->option('user-id'),
        ]);

        $this->info('crm_employee_performance_refresh=ok snapshots=' . $count . ' role_scope=' . $roleScope);

        return self::SUCCESS;
    }
}

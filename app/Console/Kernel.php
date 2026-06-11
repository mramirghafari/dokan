<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('treasury:send-alert-notifications')->dailyAt('08:00')->withoutOverlapping();
        $schedule->command('bi:refresh-data-mart')->dailyAt('02:30')->withoutOverlapping();
        $schedule->command('bi:send-insight-notifications --run-analysis')->dailyAt('03:00')->withoutOverlapping();
        $schedule->command('bi:send-scheduled-reports')->dailyAt('03:20')->withoutOverlapping();
        $schedule->command('crm:run-advanced-automation')->everyThirtyMinutes()->withoutOverlapping();
        $schedule->command('crm:sync-integrations')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('crm:refresh-employee-performance')->dailyAt('03:30')->withoutOverlapping();
        $schedule->command('crm:refresh-health')->dailyAt('04:00')->withoutOverlapping();
        $schedule->command('erp:refresh-scale-audit')->dailyAt('04:30')->withoutOverlapping();
        $schedule->command('erp:refresh-customer-list-summaries')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('erp:archive-cold-data')->dailyAt('05:00')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

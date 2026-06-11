<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\BiSelfServiceReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class BackfillBiDataMart extends Command
{
    protected $signature = 'bi:backfill-data-mart
                            {--from= : Start date Y-m-d}
                            {--to= : End date Y-m-d}
                            {--months= : Backfill last N months when --from omitted}
                            {--user= : User id for tenant scope}
                            {--sync : Run inline instead of queue}';

    protected $description = 'Backfill bi_daily_summaries for a historical date range.';

    public function handle(BiSelfServiceReportService $reportService): int
    {
        $user = $this->resolveUser();
        if (!$user) {
            $this->error('No user found for BI backfill scope.');

            return self::FAILURE;
        }

        $to = $this->option('to') ? Carbon::parse($this->option('to'))->toDateString() : now()->toDateString();
        $months = (int) ($this->option('months') ?: config('erp_scale.bi_reconciliation.default_backfill_months', 12));
        $from = $this->option('from')
            ? Carbon::parse($this->option('from'))->toDateString()
            : Carbon::parse($to)->subMonths(max(1, $months))->toDateString();

        if ($this->option('sync')) {
            $result = $reportService->backfillEnterpriseDataMart($user, $from, $to);
            $this->info('bi_backfill_sync days=' . $result['days_processed'] . ' rows=' . $result['rows_written']);

            return self::SUCCESS;
        }

        $log = \App\Models\BiRefreshLog::create([
            'dataset_key' => 'bi_backfill',
            'tenant_id' => $user->tenant_id ?: $user->tenants_id,
            'organization_id' => is_numeric($user->organization_id) ? (int) $user->organization_id : null,
            'status' => 'processing',
            'started_at' => now(),
            'message' => 'CLI backfill queued.',
            'metadata' => ['from' => $from, 'to' => $to, 'queued' => true],
        ]);

        \App\Jobs\BackfillBiDataMartJob::dispatch($user->id, $from, $to, $log->id);
        $this->info('bi_backfill_queued log=' . $log->id . ' from=' . $from . ' to=' . $to);

        return self::SUCCESS;
    }

    private function resolveUser(): ?User
    {
        if ($this->option('user')) {
            return User::query()->find((int) $this->option('user'));
        }

        return User::query()->where('isGod', 1)->orderBy('id')->first()
            ?: User::query()->orderBy('id')->first();
    }
}

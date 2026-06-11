<?php

namespace App\Jobs;

use App\Models\BiRefreshLog;
use App\Models\User;
use App\Scopes\TenantScope;
use App\Services\BiSelfServiceReportService;
use App\Services\TenantContextService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BackfillBiDataMartJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public int $tries = 1;

    public function __construct(
        public int $userId,
        public string $from,
        public string $to,
        public ?int $logId = null,
    ) {
        $queue = config('erp_scale.queue.heavy_queue', 'heavy');
        $connection = config('erp_scale.queue.heavy_connection');

        $this->onQueue($queue);

        if ($connection) {
            $this->onConnection($connection);
        }
    }

    public function handle(
        BiSelfServiceReportService $reportService,
        TenantContextService $tenantContext
    ): void {
        $user = User::query()->findOrFail($this->userId);
        $tenantId = $tenantContext->tenantId($user);
        $log = $this->logId ? BiRefreshLog::query()->find($this->logId) : null;

        TenantScope::forTenant($tenantId, function () use ($reportService, $user, $log) {
            try {
                $result = $reportService->backfillEnterpriseDataMart($user, $this->from, $this->to, false);

                if ($log) {
                    $log->update([
                        'status' => 'success',
                        'finished_at' => now(),
                        'rows_count' => $result['rows_written'],
                        'message' => 'Historical backfill completed (queued).',
                        'metadata' => array_merge($log->metadata ?? [], $result, ['queued' => true]),
                    ]);
                }
            } catch (\Throwable $exception) {
                if ($log) {
                    $log->update([
                        'status' => 'failed',
                        'finished_at' => now(),
                        'message' => $exception->getMessage(),
                    ]);
                }

                throw $exception;
            }
        });
    }
}

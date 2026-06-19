<?php

namespace App\Jobs;

use App\Models\DataExchangeRun;
use App\Models\User;
use App\Scopes\TenantScope;
use App\Services\CustomerBulkImportService;
use App\Services\DataExchangeService;
use App\Services\SpreadsheetImportReader;
use App\Services\TenantContextService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ImportCustomersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1200;

    public int $tries = 2;

    public function __construct(
        public int $exchangeRunId,
        public int $userId,
        public string $storagePath,
        public array $options = []
    ) {
        $queue = config('erp_scale.queue.heavy_queue', 'heavy');
        $connection = config('erp_scale.queue.heavy_connection');

        $this->onQueue($queue);

        if ($connection) {
            $this->onConnection($connection);
        }
    }

    public function handle(
        CustomerBulkImportService $importService,
        DataExchangeService $exchangeService,
        TenantContextService $tenantContext,
        SpreadsheetImportReader $spreadsheetReader
    ): void {
        $run = DataExchangeRun::query()->findOrFail($this->exchangeRunId);
        $user = User::query()->findOrFail($this->userId);
        $tenantId = $run->tenant_id ?: $tenantContext->tenantId($user);

        TenantScope::forTenant($tenantId, function () use ($importService, $exchangeService, $spreadsheetReader, $run, $user) {
            try {
                if (!Storage::disk('local')->exists($this->storagePath)) {
                    throw new \RuntimeException('Import file not found: ' . $this->storagePath);
                }

                $absolutePath = Storage::disk('local')->path($this->storagePath);
                $rows = $spreadsheetReader->readAssocRows($absolutePath);
                $importService->validateImportStructure($rows);
                $summary = $importService->importRows($rows, $user, array_merge($this->options, [
                    'organization_id' => $run->organization_id,
                    'exchange_run_id' => $run->id,
                ]));

                $successRows = (int) $summary['created'] + (int) $summary['updated'];
                $failedRows = (int) $summary['failed'] + (int) $summary['skipped'];

                if ($successRows === 0 && $failedRows > 0) {
                    $exchangeService->fail(
                        $run,
                        $importService->buildResultMessage($summary, $rows),
                        $summary
                    );

                    return;
                }

                $exchangeService->finish(
                    $run,
                    (int) $summary['total'],
                    $successRows,
                    $failedRows,
                    $summary
                );
            } catch (\Throwable $exception) {
                $exchangeService->fail($run, $exception->getMessage());
            } finally {
                Storage::disk('local')->delete($this->storagePath);
            }
        });
    }
}

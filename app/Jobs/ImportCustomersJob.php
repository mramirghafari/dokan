<?php

namespace App\Jobs;

use App\Models\DataExchangeRun;
use App\Models\User;
use App\Scopes\TenantScope;
use App\Services\CustomerBulkImportService;
use App\Services\DataExchangeService;
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
        TenantContextService $tenantContext
    ): void {
        $run = DataExchangeRun::query()->findOrFail($this->exchangeRunId);
        $user = User::query()->findOrFail($this->userId);
        $tenantId = $run->tenant_id ?: $tenantContext->tenantId($user);

        TenantScope::forTenant($tenantId, function () use ($importService, $exchangeService, $run, $user) {
            try {
                $rows = $this->readRows($this->storagePath);
                $summary = $importService->importRows($rows, $user, array_merge($this->options, [
                    'organization_id' => $run->organization_id,
                ]));

                $exchangeService->finish(
                    $run,
                    (int) $summary['total'],
                    (int) $summary['created'] + (int) $summary['updated'],
                    (int) $summary['failed'] + (int) $summary['skipped'],
                    $summary
                );
            } catch (\Throwable $exception) {
                $exchangeService->fail($run, $exception->getMessage());
                throw $exception;
            }
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readRows(string $storagePath): array
    {
        if (!Storage::disk('local')->exists($storagePath)) {
            throw new \RuntimeException('Import file not found: ' . $storagePath);
        }

        $absolutePath = Storage::disk('local')->path($storagePath);
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        if ($extension === 'json') {
            $decoded = json_decode((string) file_get_contents($absolutePath), true);

            return is_array($decoded['rows'] ?? null) ? $decoded['rows'] : (is_array($decoded) ? $decoded : []);
        }

        $handle = fopen($absolutePath, 'r');

        if ($handle === false) {
            throw new \RuntimeException('Unable to read import file.');
        }

        $header = fgetcsv($handle);

        if (!is_array($header)) {
            fclose($handle);

            return [];
        }

        $header = array_map(fn ($column) => trim((string) $column), $header);
        $rows = [];

        while (($line = fgetcsv($handle)) !== false) {
            if ($line === [null] || $line === false) {
                continue;
            }

            $rows[] = array_combine($header, array_pad($line, count($header), null));
        }

        fclose($handle);

        return $rows;
    }
}

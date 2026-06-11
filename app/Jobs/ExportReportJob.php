<?php

namespace App\Jobs;

use App\Models\BiReportRun;
use App\Models\DataExchangeRun;
use App\Models\User;
use App\Scopes\TenantScope;
use App\Services\BiSelfServiceReportService;
use App\Services\DataExchangeService;
use App\Services\TenantContextService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExportReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public int $tries = 2;

    public function __construct(
        public int $exchangeRunId,
        public int $userId,
        public array $input,
        public string $format = 'csv'
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
        DataExchangeService $exchangeService,
        TenantContextService $tenantContext
    ): void {
        $run = DataExchangeRun::query()->findOrFail($this->exchangeRunId);
        $user = User::query()->findOrFail($this->userId);
        $tenantId = $run->tenant_id ?: $tenantContext->tenantId($user);
        $format = $this->normaliseFormat($this->format);

        TenantScope::forTenant($tenantId, function () use ($reportService, $exchangeService, $run, $user, $format) {
            $startedAt = now();

            try {
                $result = $reportService->runForUser($user, $this->input, false);
                $rows = collect($result['rows'] ?? []);
                $columns = collect($result['columns'] ?? [])->pluck('key')->filter()->values()->all();

                if ($columns === [] && $rows->isNotEmpty()) {
                    $columns = array_keys((array) $rows->first());
                }

                $relativePath = 'erp/exports/report-' . $run->id . '.' . $format;
                $this->writeExport(Storage::disk('local')->path($relativePath), $columns, $rows, $format, $result);

                BiReportRun::create([
                    'tenant_id' => $run->tenant_id,
                    'organization_id' => $run->organization_id,
                    'dataset_key' => (string) ($result['dataset_key'] ?? $this->input['dataset_key'] ?? 'bi_export'),
                    'requested_by' => $user->id,
                    'status' => 'success',
                    'row_count' => $rows->count(),
                    'filters' => $result['filters'] ?? [],
                    'output_summary' => [
                        'exchange_run_id' => $run->id,
                        'format' => $format,
                        'storage_path' => $relativePath,
                        'queued' => true,
                        'view_mode' => $result['view_mode'] ?? 'table',
                        'chart_type' => $result['chart_type'] ?? 'table',
                    ],
                    'started_at' => $startedAt,
                    'finished_at' => now(),
                    'message' => 'Queued BI report export completed.',
                ]);

                $exchangeService->finish($run, $rows->count(), $rows->count(), 0, [
                    'storage_path' => $relativePath,
                    'format' => $format,
                    'dataset_key' => $result['dataset_key'] ?? null,
                    'download_name' => 'bi-report-' . $run->id . '.' . $format,
                ]);
            } catch (\Throwable $exception) {
                $exchangeService->fail($run, $exception->getMessage(), [
                    'dataset_key' => $this->input['dataset_key'] ?? null,
                ]);

                throw $exception;
            }
        });
    }

    private function normaliseFormat(string $format): string
    {
        $format = strtolower($format);

        return in_array($format, ['csv', 'xlsx', 'pdf', 'json'], true) ? $format : 'csv';
    }

    private function writeExport(string $absolutePath, array $columns, $rows, string $format, array $result = []): void
    {
        $directory = dirname($absolutePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        if ($format === 'json') {
            file_put_contents($absolutePath, json_encode([
                'columns' => $columns,
                'rows' => $rows->values()->all(),
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            return;
        }

        if ($format === 'pdf') {
            file_put_contents($absolutePath, $this->renderPrintableHtml($columns, $rows, $result));

            return;
        }

        if ($format === 'xlsx') {
            file_put_contents($absolutePath, $this->renderExcelHtml($columns, $rows, $result));

            return;
        }

        $handle = fopen($absolutePath, 'w');

        if ($handle === false) {
            throw new \RuntimeException('Unable to open export file for writing.');
        }

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $columns);

        foreach ($rows as $row) {
            $line = [];

            foreach ($columns as $column) {
                $line[] = is_array($row) ? ($row[$column] ?? null) : ($row->{$column} ?? null);
            }

            fputcsv($handle, $line);
        }

        fclose($handle);
    }

    private function renderPrintableHtml(array $columns, $rows, array $result): string
    {
        $columnMeta = collect($result['columns'] ?? [])->keyBy('key');
        $header = collect($columns)->map(function ($key) use ($columnMeta) {
            $label = $columnMeta->get($key)['label'] ?? $key;

            return '<th>' . e($label) . '</th>';
        })->implode('');

        $body = $rows->map(function ($row) use ($columns) {
            $cells = collect($columns)->map(function ($column) use ($row) {
                $value = is_array($row) ? ($row[$column] ?? '-') : ($row->{$column} ?? '-');

                return '<td>' . e(is_numeric($value) ? number_format((float) $value, 2) : (string) $value) . '</td>';
            })->implode('');

            return '<tr>' . $cells . '</tr>';
        })->implode('');

        $title = 'گزارش BI — ' . e((string) ($result['dataset_key'] ?? 'export'));

        return '<!doctype html><html dir="rtl" lang="fa"><head><meta charset="utf-8"><title>' . $title . '</title>'
            . '<style>@page{margin:16mm}body{font-family:Tahoma,sans-serif;margin:24px;color:#333}'
            . 'h1{font-size:18px;margin:0 0 8px}table{width:100%;border-collapse:collapse;font-size:12px}'
            . 'td,th{border:1px solid #ccc;padding:6px;text-align:right}th{background:#f0f0f0}'
            . '.meta{color:#666;font-size:12px;margin-bottom:16px}</style></head><body>'
            . '<h1>' . $title . '</h1><p class="meta">تاریخ: ' . e(now()->format('Y-m-d H:i')) . ' — ' . number_format($rows->count()) . ' ردیف</p>'
            . '<table><thead><tr>' . $header . '</tr></thead><tbody>' . $body . '</tbody></table></body></html>';
    }

    private function renderExcelHtml(array $columns, $rows, array $result): string
    {
        $columnMeta = collect($result['columns'] ?? [])->keyBy('key');
        $header = collect($columns)->map(function ($key) use ($columnMeta) {
            $label = $columnMeta->get($key)['label'] ?? $key;

            return '<th>' . e($label) . '</th>';
        })->implode('');

        $body = $rows->map(function ($row) use ($columns) {
            $cells = collect($columns)->map(function ($column) use ($row) {
                $value = is_array($row) ? ($row[$column] ?? '') : ($row->{$column} ?? '');

                return '<td>' . e((string) $value) . '</td>';
            })->implode('');

            return '<tr>' . $cells . '</tr>';
        })->implode('');

        return "\xEF\xBB\xBF" . '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" dir="rtl" lang="fa">'
            . '<head><meta charset="utf-8"></head><body><table border="1"><thead><tr>' . $header . '</tr></thead><tbody>'
            . $body . '</tbody></table></body></html>';
    }
}

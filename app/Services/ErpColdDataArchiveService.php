<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ErpColdDataArchiveService
{
    public function sources(): array
    {
        return (array) config('erp_scale.archive.sources', []);
    }

    public function retentionDays(?int $override = null): int
    {
        return max(1, $override ?? (int) config('erp_scale.archive.retention_days', 180));
    }

    public function candidateCount(?string $table = null, ?int $retentionDays = null): int
    {
        $total = 0;

        foreach ($this->resolvedSources($table) as $sourceTable => $definition) {
            $total += $this->countCandidates($sourceTable, $definition, $retentionDays);
        }

        return $total;
    }

    public function run(bool $dryRun = false, ?string $onlyTable = null, ?int $retentionDays = null): array
    {
        if (!config('erp_scale.archive.enabled', true)) {
            return [
                'enabled' => false,
                'dry_run' => $dryRun,
                'retention_days' => $this->retentionDays($retentionDays),
                'tables' => [],
                'total_candidates' => 0,
                'total_processed' => 0,
            ];
        }

        $retentionDays = $this->retentionDays($retentionDays);
        $chunkSize = max(50, (int) config('erp_scale.archive.chunk_size', 500));
        $maxRows = max($chunkSize, (int) config('erp_scale.archive.max_rows_per_table', 50000));
        $cutoff = now()->subDays($retentionDays);
        $tables = [];

        foreach ($this->resolvedSources($onlyTable) as $table => $definition) {
            $tables[$table] = $this->archiveTable(
                $table,
                $definition,
                $cutoff,
                $chunkSize,
                $maxRows,
                $dryRun
            );
        }

        $report = [
            'enabled' => true,
            'dry_run' => $dryRun,
            'retention_days' => $retentionDays,
            'cutoff' => $cutoff->toDateTimeString(),
            'tables' => $tables,
            'total_candidates' => collect($tables)->sum('candidates'),
            'total_processed' => collect($tables)->sum('processed'),
        ];

        if (!$dryRun && $report['total_processed'] > 0) {
            Log::channel('single')->info('erp_cold_data_archived', $report);
        }

        return $report;
    }

    private function resolvedSources(?string $onlyTable = null): array
    {
        $sources = collect($this->sources())
            ->filter(fn (array $definition, string $table) => Schema::hasTable($table))
            ->when($onlyTable, fn (Collection $items) => $items->only([$onlyTable]));

        return $sources->all();
    }

    private function countCandidates(string $table, array $definition, ?int $retentionDays = null): int
    {
        $dateColumn = $definition['date_column'] ?? 'created_at';

        if (!Schema::hasColumn($table, $dateColumn)) {
            return 0;
        }

        $query = DB::table($table)->where($dateColumn, '<', now()->subDays($this->retentionDays($retentionDays)));
        $this->applySourceFilters($query, $table, $definition);

        return (int) $query->count();
    }

    private function archiveTable(
        string $table,
        array $definition,
        Carbon $cutoff,
        int $chunkSize,
        int $maxRows,
        bool $dryRun
    ): array {
        $dateColumn = $definition['date_column'] ?? 'created_at';
        $primaryKey = $definition['primary_key'] ?? 'id';
        $mode = $definition['mode'] ?? 'purge';

        if (!Schema::hasColumn($table, $dateColumn) || !Schema::hasColumn($table, $primaryKey)) {
            return [
                'mode' => $mode,
                'candidates' => 0,
                'processed' => 0,
                'skipped' => true,
                'reason' => 'missing_date_or_primary_key',
            ];
        }

        $baseQuery = DB::table($table)->where($dateColumn, '<', $cutoff);
        $this->applySourceFilters($baseQuery, $table, $definition);
        $candidates = (int) (clone $baseQuery)->count();

        if ($candidates === 0) {
            return [
                'mode' => $mode,
                'candidates' => 0,
                'processed' => 0,
                'skipped' => false,
            ];
        }

        $processed = 0;
        $archiveTable = $table . '_archive';
        $canArchive = $mode === 'archive' && Schema::hasTable($archiveTable);

        while ($processed < min($candidates, $maxRows)) {
            $ids = (clone $baseQuery)
                ->orderBy($primaryKey)
                ->limit(min($chunkSize, $maxRows - $processed))
                ->pluck($primaryKey);

            if ($ids->isEmpty()) {
                break;
            }

            if (!$dryRun) {
                if ($canArchive) {
                    $this->moveRowsToArchive($table, $archiveTable, $primaryKey, $ids->all());
                } else {
                    DB::table($table)->whereIn($primaryKey, $ids->all())->delete();
                }
            }

            $processed += $ids->count();
        }

        return [
            'mode' => $canArchive ? 'archive' : 'purge',
            'candidates' => $candidates,
            'processed' => $processed,
            'skipped' => false,
        ];
    }

    private function applySourceFilters($query, string $table, array $definition): void
    {
        if (!empty($definition['only_status']) && Schema::hasColumn($table, 'status')) {
            $query->whereIn($table . '.status', (array) $definition['only_status']);
        }

        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->whereNull($table . '.deleted_at');
        }
    }

    private function moveRowsToArchive(string $sourceTable, string $archiveTable, string $primaryKey, array $ids): void
    {
        DB::transaction(function () use ($sourceTable, $archiveTable, $primaryKey, $ids) {
            $rows = DB::table($sourceTable)->whereIn($primaryKey, $ids)->get();

            foreach ($rows as $row) {
                DB::table($archiveTable)->updateOrInsert(
                    [$primaryKey => $row->{$primaryKey}],
                    (array) $row
                );
            }

            DB::table($sourceTable)->whereIn($primaryKey, $ids)->delete();
        });
    }
}

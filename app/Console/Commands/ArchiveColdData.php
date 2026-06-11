<?php

namespace App\Console\Commands;

use App\Services\ErpColdDataArchiveService;
use Illuminate\Console\Command;

class ArchiveColdData extends Command
{
    protected $signature = 'erp:archive-cold-data
                            {--dry-run : Count and preview without deleting rows}
                            {--table= : Limit archive to one configured source table}
                            {--retention-days= : Override configured retention days}';

    protected $description = 'Archive or purge cold ERP/CRM/BI rows based on erp_scale.archive policy.';

    public function handle(ErpColdDataArchiveService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $table = $this->option('table') ?: null;
        $retentionDays = $this->option('retention-days') !== null
            ? (int) $this->option('retention-days')
            : null;

        $report = $service->run($dryRun, $table, $retentionDays);

        if (!$report['enabled']) {
            $this->warn('erp_archive_disabled=1');

            return self::SUCCESS;
        }

        $mode = $dryRun ? 'dry_run' : 'executed';
        $this->info('erp_archive_' . $mode . '=ok retention_days=' . $report['retention_days']);
        $this->info('candidates=' . $report['total_candidates'] . ' processed=' . $report['total_processed']);

        foreach ($report['tables'] as $tableName => $tableReport) {
            $suffix = !empty($tableReport['skipped']) ? ' skipped' : '';
            $this->line(sprintf(
                '  %s: candidates=%d processed=%d mode=%s%s',
                $tableName,
                $tableReport['candidates'] ?? 0,
                $tableReport['processed'] ?? 0,
                $tableReport['mode'] ?? '-',
                $suffix
            ));
        }

        return self::SUCCESS;
    }
}

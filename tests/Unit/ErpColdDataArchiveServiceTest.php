<?php

namespace Tests\Unit;

use App\Services\ErpColdDataArchiveService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ErpColdDataArchiveServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('erp_scale.archive.enabled', true);
        Config::set('erp_scale.archive.retention_days', 30);
        Config::set('erp_scale.archive.chunk_size', 2);
        Config::set('erp_scale.archive.max_rows_per_table', 10);
        Config::set('erp_scale.archive.sources', [
            'erp_archive_probe' => [
                'date_column' => 'created_at',
                'mode' => 'purge',
            ],
        ]);

        Schema::dropIfExists('erp_archive_probe');
        Schema::create('erp_archive_probe', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('erp_archive_probe');
        parent::tearDown();
    }

    public function test_dry_run_counts_without_deleting_rows(): void
    {
        DB::table('erp_archive_probe')->insert([
            ['label' => 'old', 'created_at' => now()->subDays(60)],
            ['label' => 'fresh', 'created_at' => now()],
        ]);

        $service = app(ErpColdDataArchiveService::class);
        $report = $service->run(true);

        $this->assertSame(1, $report['total_candidates']);
        $this->assertSame(1, $report['total_processed']);
        $this->assertSame(2, DB::table('erp_archive_probe')->count());
    }

    public function test_run_purges_old_rows_in_chunks(): void
    {
        DB::table('erp_archive_probe')->insert([
            ['label' => 'old-1', 'created_at' => now()->subDays(60)],
            ['label' => 'old-2', 'created_at' => now()->subDays(45)],
            ['label' => 'fresh', 'created_at' => now()],
        ]);

        $service = app(ErpColdDataArchiveService::class);
        $report = $service->run(false);

        $this->assertSame(2, $report['total_processed']);
        $this->assertSame(1, DB::table('erp_archive_probe')->count());
        $this->assertSame('fresh', DB::table('erp_archive_probe')->value('label'));
    }
}

<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\CustomerBulkImportService;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerBulkImportServiceTest extends TestCase
{
    public function test_duplicate_mobile_is_skipped_when_update_disabled(): void
    {
        if (!Schema::hasTable('customers')) {
            $this->markTestSkipped('customers table is not available.');
        }

        $user = User::query()->first();

        if (!$user) {
            $this->markTestSkipped('No users available for import test.');
        }

        $service = app(CustomerBulkImportService::class);
        $mobile = '0912' . random_int(1000000, 9999999);

        $first = $service->importRows([
            ['name' => 'اول', 'mobile' => $mobile],
        ], $user);

        $second = $service->importRows([
            ['name' => 'دوم', 'mobile' => $mobile],
        ], $user);

        if (($first['created'] ?? 0) === 0) {
            $this->markTestSkipped('Unable to create baseline customer in this environment.');
        }

        $this->assertSame(1, $second['skipped']);
    }

    public function test_blank_name_and_mobile_rows_are_skipped(): void
    {
        if (!Schema::hasTable('customers')) {
            $this->markTestSkipped('customers table is not available.');
        }

        $user = User::query()->first();

        if (!$user) {
            $this->markTestSkipped('No users available for import test.');
        }

        $service = app(CustomerBulkImportService::class);
        $summary = $service->importRows([
            ['name' => '', 'mobile' => '', 'مبلغ' => '1000'],
            ['name' => 'فقط نام', 'mobile' => ''],
            ['name' => '', 'mobile' => '09123334444'],
        ], $user, ['update_existing' => true]);

        $this->assertSame(3, $summary['total']);
        $this->assertSame(1, $summary['skipped']);
        $this->assertSame(2, $summary['failed']);
        $this->assertSame(0, $summary['created'] + $summary['updated']);
    }

    public function test_name_and_mobile_are_required_in_column_guide(): void
    {
        $service = app(CustomerBulkImportService::class);
        $guide = collect($service->columnGuide())->keyBy('header');

        $this->assertTrue($guide['نام']['required']);
        $this->assertTrue($guide['موبایل']['required']);
    }

    public function test_template_headers_include_all_guide_columns(): void
    {
        $service = app(CustomerBulkImportService::class);
        $headers = $service->templateHeaders();
        $guideHeaders = array_column($service->columnGuide(), 'header');

        $this->assertSame($guideHeaders, $headers);
        $this->assertCount(14, $headers);
    }

    public function test_template_sample_rows_match_header_count(): void
    {
        $service = app(CustomerBulkImportService::class);
        $headerCount = count($service->templateHeaders());

        $this->assertSame(14, $headerCount);

        foreach ($service->templateSampleRows() as $index => $row) {
            $this->assertCount(
                $headerCount,
                $row,
                sprintf('Sample row %d must have %d columns.', $index + 1, $headerCount)
            );
        }
    }

    public function test_import_with_name_and_mobile_only_sets_legacy_defaults(): void
    {
        if (!Schema::hasTable('customers')) {
            $this->markTestSkipped('customers table is not available.');
        }

        $user = User::query()->first();

        if (!$user) {
            $this->markTestSkipped('No users available for import test.');
        }

        $service = app(CustomerBulkImportService::class);
        $mobile = '09' . random_int(100000000, 999999999);

        $summary = $service->importRows([
            ['name' => 'مشتری تست import', 'mobile' => $mobile],
        ], $user);

        if (($summary['failed'] ?? 0) > 0) {
            $this->fail($summary['errors'][0]['messages'][0] ?? 'Import failed unexpectedly.');
        }

        $this->assertSame(1, $summary['created']);
    }

    public function test_template_filename_is_versioned_for_cache_busting(): void
    {
        $this->assertSame('customers-import-template-v2.csv', CustomerBulkImportService::TEMPLATE_FILENAME);
    }

    public function test_summarize_row_errors_groups_messages(): void
    {
        $service = app(CustomerBulkImportService::class);
        $summary = $service->summarizeRowErrors([
            'errors' => [
                ['line' => 1, 'messages' => ['فیلد نام الزامی است.']],
                ['line' => 2, 'messages' => ['فیلد نام الزامی است.']],
                ['line' => 5, 'messages' => ['فیلد شماره همراه الزامی است.']],
            ],
        ]);

        $this->assertCount(2, $summary);
        $this->assertSame('فیلد نام الزامی است.', $summary[0]['message']);
        $this->assertSame(2, $summary[0]['count']);
        $this->assertSame([1, 2], $summary[0]['lines']);
    }
}

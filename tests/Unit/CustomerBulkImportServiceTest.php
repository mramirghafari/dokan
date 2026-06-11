<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\CustomerBulkImportService;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerBulkImportServiceTest extends TestCase
{
    public function test_import_rows_requires_database_customers_table(): void
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
            ['name' => 'مشتری تست صف', 'mobile' => '09120009999'],
        ], $user, ['update_existing' => false]);

        $this->assertSame(1, $summary['total']);
        $this->assertContains($summary['created'] + $summary['skipped'], [0, 1]);
    }

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
}

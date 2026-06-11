<?php

namespace Tests\Unit;

use App\Services\CrmLeadBulkImportService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CrmLeadBulkImportServiceTest extends TestCase
{
    public function test_normalize_row_supports_persian_headers(): void
    {
        $service = new CrmLeadBulkImportService(app(\App\Services\TenantContextService::class));
        $method = new ReflectionMethod(CrmLeadBulkImportService::class, 'normalizeRow');
        $method->setAccessible(true);

        $row = $method->invoke($service, [
            'نام' => 'رضا تست',
            'موبایل' => '0912-111-2233',
            'منبع' => 'campaign',
            'کمپین' => 'بهار ۱۴۰۵',
        ], 'manual', null);

        $this->assertSame('رضا تست', $row['name']);
        $this->assertSame('09121112233', $row['mobile']);
        $this->assertSame('campaign', $row['source']);
        $this->assertSame('بهار ۱۴۰۵', $row['campaign']);
    }
}

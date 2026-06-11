<?php

namespace Tests\Unit;

use App\Services\CrmDashboardService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CrmDashboardServiceTest extends TestCase
{
    public function test_forecast_reconcile_flags_aligned_pipeline(): void
    {
        $service = new CrmDashboardService();
        $method = new ReflectionMethod(CrmDashboardService::class, 'forecastReconcile');
        $method->setAccessible(true);

        $result = $method->invoke($service, 1500000.0, 1500000.0, [
            'raw_closing_month' => 2000000,
            'weighted_closing_month' => 1200000,
        ]);

        $this->assertTrue($result['pipeline_aligned']);
        $this->assertSame(0.0, $result['pipeline_delta']);
        $this->assertSame(60.0, $result['month_coverage_percent']);
    }

    public function test_forecast_reconcile_detects_stage_mismatch(): void
    {
        $service = new CrmDashboardService();
        $method = new ReflectionMethod(CrmDashboardService::class, 'forecastReconcile');
        $method->setAccessible(true);

        $result = $method->invoke($service, 2000000.0, 1500000.0, [
            'raw_closing_month' => 0,
            'weighted_closing_month' => 0,
        ]);

        $this->assertFalse($result['pipeline_aligned']);
        $this->assertSame(500000.0, $result['pipeline_delta']);
    }
}

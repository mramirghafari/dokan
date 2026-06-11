<?php

namespace Tests\Unit;

use App\Services\BiReconciliationService;
use ReflectionMethod;
use Tests\TestCase;

class BiReconciliationServiceTest extends TestCase
{
    public function test_delta_percent_when_source_zero(): void
    {
        $service = new BiReconciliationService(new \App\Services\BiSelfServiceReportService());
        $method = new ReflectionMethod(BiReconciliationService::class, 'deltaPercent');
        $method->setAccessible(true);

        $this->assertSame(0.0, $method->invoke($service, 0.0, 0.0));
        $this->assertSame(100.0, $method->invoke($service, 50.0, 0.0));
    }

    public function test_status_for_delta_uses_thresholds(): void
    {
        $service = new BiReconciliationService(new \App\Services\BiSelfServiceReportService());
        $method = new ReflectionMethod(BiReconciliationService::class, 'statusForDelta');
        $method->setAccessible(true);

        $this->assertSame('aligned', $method->invoke($service, 1.0, 1000.0, 990.0));
        $this->assertSame('warning', $method->invoke($service, 5.0, 1000.0, 950.0));
        $this->assertSame('critical', $method->invoke($service, 15.0, 1000.0, 850.0));
    }
}

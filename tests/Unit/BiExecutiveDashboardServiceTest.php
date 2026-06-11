<?php

namespace Tests\Unit;

use App\Services\BiExecutiveDashboardService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class BiExecutiveDashboardServiceTest extends TestCase
{
    public function test_delta_percent_when_previous_is_zero_and_current_positive(): void
    {
        $service = new BiExecutiveDashboardService();
        $method = new ReflectionMethod(BiExecutiveDashboardService::class, 'deltaPercent');
        $method->setAccessible(true);

        $this->assertSame(100.0, $method->invoke($service, 500.0, 0.0));
    }

    public function test_delta_percent_calculates_change(): void
    {
        $service = new BiExecutiveDashboardService();
        $method = new ReflectionMethod(BiExecutiveDashboardService::class, 'deltaPercent');
        $method->setAccessible(true);

        $this->assertSame(25.0, $method->invoke($service, 1250.0, 1000.0));
        $this->assertSame(-20.0, $method->invoke($service, 800.0, 1000.0));
    }

    public function test_resolve_gross_profit_derives_from_summaries(): void
    {
        $service = new BiExecutiveDashboardService();
        $method = new ReflectionMethod(BiExecutiveDashboardService::class, 'resolveMetricValue');
        $method->setAccessible(true);

        $current = collect([
            (object) ['metric_key' => 'sales_gross_amount', 'value' => 10000],
            (object) ['metric_key' => 'purchase_order_amount', 'value' => 3000],
            (object) ['metric_key' => 'payroll_net_pay_amount', 'value' => 2000],
        ])->keyBy('metric_key');

        $profit = $method->invoke($service, '_derived_gross_profit', $current, collect());

        $this->assertSame(5000.0, $profit);
    }
}

<?php

namespace Tests\Unit;

use App\Services\BiSelfServiceReportService;
use Tests\TestCase;

class BiReportBuilderAdvancedTest extends TestCase
{
    public function test_build_pivot_table_aggregates_row_and_column(): void
    {
        $service = new BiSelfServiceReportService();
        $rows = collect([
            (object) ['domain' => 'sales', 'metric_key' => 'a', 'value_sum' => 100],
            (object) ['domain' => 'sales', 'metric_key' => 'b', 'value_sum' => 50],
            (object) ['domain' => 'crm', 'metric_key' => 'a', 'value_sum' => 30],
        ]);

        $pivot = $service->buildPivotTable(
            $rows,
            ['pivot_row' => 'domain', 'pivot_col' => 'metric_key'],
            ['domain', 'metric_key'],
            ['value_sum'],
            ['domain' => ['label' => 'دامنه'], 'metric_key' => ['label' => 'شاخص']],
            ['value_sum' => ['label' => 'جمع']]
        );

        $this->assertTrue($pivot['ready']);
        $this->assertSame(180.0, $pivot['grand_total']);
        $this->assertContains('a', $pivot['columns']);
    }

    public function test_build_pivot_requires_distinct_dimensions(): void
    {
        $service = new BiSelfServiceReportService();
        $rows = collect([(object) ['domain' => 'sales', 'value_sum' => 10]]);

        $pivot = $service->buildPivotTable(
            $rows,
            ['pivot_row' => 'domain', 'pivot_col' => 'domain'],
            ['domain'],
            ['value_sum'],
            ['domain' => ['label' => 'دامنه']],
            ['value_sum' => ['label' => 'جمع']]
        );

        $this->assertFalse($pivot['ready']);
    }

    public function test_build_chart_payload_for_bar_chart(): void
    {
        $service = new BiSelfServiceReportService();
        $rows = collect([
            (object) ['summary_date' => '2026-06-01', 'value_sum' => 100],
            (object) ['summary_date' => '2026-06-02', 'value_sum' => 150],
        ]);

        $chart = $service->buildChartPayload(
            $rows,
            ['summary_date'],
            ['value_sum'],
            ['summary_date' => ['label' => 'تاریخ']],
            ['value_sum' => ['label' => 'مقدار']],
            'bar'
        );

        $this->assertSame('bar', $chart['type']);
        $this->assertCount(2, $chart['categories']);
        $this->assertSame([100.0, 150.0], $chart['series'][0]['data']);
    }
}

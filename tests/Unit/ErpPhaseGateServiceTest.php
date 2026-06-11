<?php

namespace Tests\Unit;

use App\Services\ErpPhaseGateService;
use Tests\TestCase;

class ErpPhaseGateServiceTest extends TestCase
{
    public function test_phase_gate_returns_structured_report(): void
    {
        $report = app(ErpPhaseGateService::class)->evaluate(null, false, false);

        $this->assertArrayHasKey('passed', $report);
        $this->assertArrayHasKey('checks', $report);
        $this->assertArrayHasKey('scale_score', $report['checks']);
        $this->assertArrayHasKey('tenant_isolation', $report['checks']);
    }
}

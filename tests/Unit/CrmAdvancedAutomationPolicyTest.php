<?php

namespace Tests\Unit;

use App\Services\CrmAdvancedAutomationService;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CrmAdvancedAutomationPolicyTest extends TestCase
{
    public function test_assert_lost_reason_skips_when_policy_disabled(): void
    {
        $service = $this->getMockBuilder(CrmAdvancedAutomationService::class)
            ->onlyMethods(['requiresLostReason'])
            ->getMock();

        $service->method('requiresLostReason')->willReturn(false);
        $service->assertLostReason(null, 1);

        $this->assertTrue(true);
    }

}

<?php

namespace Tests\Unit;

use App\Models\CrmCampaign;
use App\Services\CrmCampaignService;
use Tests\TestCase;

class CrmCampaignFixedMessageTest extends TestCase
{
    public function test_fixed_message_uses_goal_template(): void
    {
        $campaign = new CrmCampaign([
            'title' => 'جشنواره بهار',
            'goal' => 'retention',
            'discount_code' => 'SPRING10',
        ]);

        $message = (new CrmCampaignService())->fixedMessageFor($campaign);

        $this->assertStringContainsString('جشنواره بهار', $message);
        $this->assertStringContainsString('{name}', $message);
    }

    public function test_fixed_message_falls_back_to_default(): void
    {
        $campaign = new CrmCampaign([
            'title' => 'کمپین تست',
            'goal' => 'unknown_goal',
        ]);

        $message = (new CrmCampaignService())->fixedMessageFor($campaign);

        $this->assertNotSame('', trim($message));
        $this->assertStringContainsString('کمپین تست', $message);
    }
}

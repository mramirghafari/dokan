<?php

namespace App\Jobs;

use App\Models\CrmCampaign;
use App\Models\User;
use App\Scopes\TenantScope;
use App\Services\CrmCampaignService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchCampaignSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public int $tries = 2;

    public function __construct(
        public int $campaignId,
        public int $userId,
        public array $audienceIds = [],
        public bool $finalize = false
    ) {
        $queue = config('erp_scale.queue.heavy_queue', 'heavy');
        $connection = config('erp_scale.queue.heavy_connection');

        $this->onQueue($queue);

        if ($connection) {
            $this->onConnection($connection);
        }
    }

    public function handle(CrmCampaignService $campaignService): void
    {
        $campaign = CrmCampaign::query()->findOrFail($this->campaignId);
        $user = User::query()->findOrFail($this->userId);

        TenantScope::forTenant($campaign->tenant_id, function () use ($campaignService, $campaign, $user) {
            $campaignService->processSmsBatch($campaign, $user, $this->audienceIds, $this->finalize);
        });
    }
}

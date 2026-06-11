<?php

namespace App\Console\Commands;

use App\Models\BiInsightAlert;
use App\Models\Notifs;
use App\Models\User;
use App\Services\BiInsightService;
use Illuminate\Console\Command;

class SendBiInsightNotifications extends Command
{
    protected $signature = 'bi:send-insight-notifications {--user_id= : Send only for one user} {--run-analysis : Run BI analysis before notifying users} {--dry-run : Count notifications without creating records} {--limit=200 : Maximum open alerts to process}';

    protected $description = 'Run BI insight analysis if requested and create idempotent header notifications for high-priority BI alerts.';

    public function handle(BiInsightService $insightService): int
    {
        if ($this->option('run-analysis')) {
            $result = $insightService->runAnalysis($this->systemContext());
            $this->info('analysis alerts=' . $result['alerts'] . ' forecasts=' . $result['forecasts'] . ' evaluated=' . $result['evaluated']);
        }

        $created = 0;
        $skipped = 0;
        $candidates = 0;
        $dryRun = (bool) $this->option('dry-run');

        $alerts = BiInsightAlert::query()
            ->where('status', 'open')
            ->whereIn('severity', ['high', 'critical'])
            ->latest('detected_at')
            ->limit(max(1, min(1000, (int) $this->option('limit'))))
            ->get();

        foreach ($alerts as $alert) {
            foreach ($this->recipientsFor($alert) as $user) {
                if ($this->option('user_id') && (int) $this->option('user_id') !== (int) $user->id) {
                    continue;
                }

                $candidates++;
                if ($dryRun) {
                    continue;
                }

                $notification = Notifs::firstOrCreate([
                    'user_id' => $user->id,
                    'alert_key' => 'bi:insight:' . $alert->id . ':' . optional($alert->detected_at)->format('Y-m-d'),
                ], [
                    'tenant_id' => $alert->tenant_id,
                    'title' => 'هشدار BI: ' . $alert->title,
                    'content' => trim($alert->message . ' ' . ($alert->suggestion ?: '')),
                    'status' => false,
                    'source' => 'bi_insights',
                    'severity' => in_array($alert->severity, ['high', 'critical'], true) ? 'danger' : 'warning',
                    'reference_type' => 'bi_insight_alert',
                    'reference_id' => $alert->id,
                    'scheduled_for' => now()->toDateString(),
                    'sent_at' => now(),
                ]);

                $notification->wasRecentlyCreated ? $created++ : $skipped++;
            }
        }

        $mode = $dryRun ? 'dry_run' : 'sent';
        $this->info('bi_insight_notifications_' . $mode . '=ok candidates=' . $candidates . ' created=' . $created . ' skipped=' . $skipped . ' alerts=' . $alerts->count());

        return 0;
    }

    private function recipientsFor(BiInsightAlert $alert)
    {
        return User::query()
            ->where('isActive', 1)
            ->where(function ($query) use ($alert) {
                $query->where('isGod', 1);

                if ($alert->tenant_id) {
                    $query->orWhere(function ($tenantQuery) use ($alert) {
                        $tenantQuery->where('isAdmin', 1);

                        $tenantQuery->where(function ($scope) use ($alert) {
                            $scope->where('tenant_id', $alert->tenant_id)->orWhere('tenants_id', $alert->tenant_id);
                        });

                        if ($alert->organization_id) {
                            $tenantQuery->where(function ($scope) use ($alert) {
                                $scope->where('organization_id', $alert->organization_id)
                                    ->orWhere('organization_id', 'like', '%"' . $alert->organization_id . '"%')
                                    ->orWhere('organization_id', 'like', '%[' . $alert->organization_id . ']%');
                            });
                        }
                    });
                }
            })
            ->limit(50)
            ->get();
    }

    private function systemContext(): object
    {
        return (object) [
            'id' => 0,
            'isGod' => 1,
            'tenant_id' => null,
            'tenants_id' => null,
            'organization_id' => null,
        ];
    }
}

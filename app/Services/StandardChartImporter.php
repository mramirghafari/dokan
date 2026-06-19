<?php

namespace App\Services;

use App\Models\Accounts;
use Illuminate\Support\Facades\Auth;

class StandardChartImporter
{
    private const NATURE_MAP = [
        'debit' => 1,
        'credit' => 2,
        'neutral' => 0,
    ];

    /**
     * ساخت سرفصل‌های استاندارد (مدل سپیدار) برای پنل جاری به‌صورت idempotent.
     *
     * @return array{created:int, skipped:int}
     */
    public function import(): array
    {
        $user = Auth::user();
        $tenantId = $user->tenants_id;
        $groups = config('sepidar_chart_of_accounts.groups', []);

        $existingCodes = Accounts::where('tenants_id', $tenantId)
            ->pluck('code')
            ->filter()
            ->map(fn ($code) => (string) $code)
            ->flip();

        $created = 0;
        $skipped = 0;

        foreach ($groups as $group) {
            foreach ($group['totals'] ?? [] as $total) {
                $totalCode = (string) $total['code'];

                if ($existingCodes->has($totalCode)) {
                    $skipped++;
                    $totalModel = Accounts::where('tenants_id', $tenantId)
                        ->where('code', $totalCode)
                        ->first();
                } else {
                    $totalModel = Accounts::create([
                        'code' => $totalCode,
                        'name' => $total['title'],
                        'level' => 1,
                        'parent_id' => 0,
                        'nature' => self::NATURE_MAP[$total['nature'] ?? $group['nature']] ?? 0,
                        'account_category' => $group['category'],
                        'asset_class' => $group['asset_class'] ?? null,
                        'asset_type' => $total['title'],
                        'isActive' => 1,
                        'tenants_id' => $tenantId,
                        'created_by' => $user->id,
                    ]);
                    $existingCodes->put($totalCode, true);
                    $created++;
                }

                if (!$totalModel) {
                    continue;
                }

                foreach ($total['subsidiaries'] ?? [] as $sub) {
                    $subCode = (string) $sub['code'];

                    if ($existingCodes->has($subCode)) {
                        $skipped++;
                        continue;
                    }

                    Accounts::create([
                        'code' => $subCode,
                        'name' => $sub['title'],
                        'level' => 2,
                        'parent_id' => $totalModel->id,
                        'nature' => self::NATURE_MAP[$total['nature'] ?? $group['nature']] ?? 0,
                        'account_category' => $group['category'],
                        'asset_class' => $group['asset_class'] ?? null,
                        'isActive' => 1,
                        'tenants_id' => $tenantId,
                        'created_by' => $user->id,
                    ]);
                    $existingCodes->put($subCode, true);
                    $created++;
                }
            }
        }

        return ['created' => $created, 'skipped' => $skipped];
    }
}

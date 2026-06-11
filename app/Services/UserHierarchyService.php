<?php

namespace App\Services;

use App\Models\Targets;
use App\Models\User;
use App\Models\Pishfactor;
use Carbon\Carbon;

class UserHierarchyService
{
    /**
     * دریافت کل زیرمجموعه یک کاربر با بازهٔ تاریخی
     */
    public function getSubtree($rootUserId, $start = null, $end = null)
    {
        $rootUser = User::findOrFail($rootUserId);




        // ------------------------------
        // تعیین بازه تاریخ (Start / End)
        // ------------------------------
        if (!$start || !$end) {

            $target = Targets::where('user_id', $rootUserId)
                ->where('status', 1)
                ->first();

            if ($target) {
                // ستون‌های معتبر: start_date_en / end_date_en
                $start = $target->start_date_en;
                $end   = $target->end_date_en;
            } else {
                $start = $rootUser->created_at;
                $end   = Carbon::today();
            }
        }

        // تبدیل Carbon به فرمت yyyy-mm-dd 00:00:00 / 23:59:59
        $start = Carbon::parse($start)->format('Y-m-d 00:00:00');
        $org_end = $end;
        $end   = Carbon::parse($end)->format('Y-m-d 23:59:59');

        return $this->buildTree($rootUserId, $rootUser, $start, $end,$org_end);
    }

    /**
     * ساخت ساختار درختی
     */
    private function buildTree($leaderId, $rootUser, $start, $end,$org_end)
    {
        $subs = User::where('leader_id', $leaderId)
            ->where('isActive', 1)
            ->get();


        $result = [];

        foreach ($subs as $sub) {

            $roleTitle = optional($sub->roles()->first())->title;
            $roleDesc = optional($sub->roles()->first())->description;


            // فاکتورهای یوزر در این بازه
            $factors = $this->getUserFactorsInRange($sub, $roleTitle, $rootUser, $start, $end);


            $totalFactorPrice = $factors->sum(fn($f) => intval(str_replace(',', '', $f->fullPrice)));
            $totalPatPrice    = $factors->sum(fn($f) => intval(str_replace(',', '', $f->pat_price)));


            $Target = Targets::where('user_id', $sub->id)
                ->where('start_date_en', $start)
                ->where('end_date_en', $org_end)
                ->first();


            $children = $this->buildTree($sub->id, $rootUser, $start, $end,$org_end);

            $result[] = [
                'id'             => $sub->id,
                'name'           => $sub->name,
                'personalID'     => $sub->personalID,
                'role'           => $roleDesc,
                'factors_count'  => $factors->count(),
                'FactorPrices'   => $totalFactorPrice,
                'PatPrices'      => $totalPatPrice,
                'children'       => $children,
                'isActive'       => $sub->isActive,
                'targetID'   => $Target->id ?? '-',
                'target_price'   => $Target->target_price ?? '-',
                'target_period'  => $Target ? $Target->start_date_fa . ' تا ' . $Target->end_date_fa : '-',
                'factors'  => $factors,
            ];
        }

        usort($result, fn($a, $b) => $b['factors_count'] <=> $a['factors_count']);

        return $result;
    }

    /**
     * فاکتورهای یک یوزر بر اساس نقش و بازهٔ زمانی
     */
    private function getUserFactorsInRange($user, $roleTitle, $rootUser, $start, $end)
    {
        switch ($roleTitle) {

            case 'leader':
                return Pishfactor::whereIn('status', [1, 4])
                    ->where('sarparast_id', $user->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->get();

            case 'visitor':
                return Pishfactor::whereIn('status', [1, 4])
                    ->where('visitor_id', $user->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->get();

            case 'expert':
                // مخصوص نقش expert → استفاده از rootUser
                return Pishfactor::forOrganizations($rootUser)
                    ->whereIn('status', [1, 4])
                    ->whereBetween('created_at', [$start, $end])
                    ->get();

            default:
                return collect([]);
        }
    }
}

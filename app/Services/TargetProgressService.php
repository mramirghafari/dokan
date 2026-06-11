<?php

namespace App\Services;

use App\Models\User;
use App\Models\Pishfactor;
use App\Models\Tasks;

class TargetProgressService
{
    public function getSubUsersWithFactors($leaderId, $target, $includeSelf = true)
    {
        $result = [];

        // ------------------------------------------------------
        // تعریف اولیه متغیرها برای جلوگیری از Undefined variable
        // ------------------------------------------------------
        $selfFactors = collect([]);
        $selfTasks   = collect([]);
        $roleTitle   = '-';
        $user        = User::find($leaderId);
        // ------------------------------------------------------

        // ==============================
        //     1) محاسبه یوزر اصلی (Self)
        // ==============================
        if ($includeSelf && $user) {

            $role = $user->roles()->first();
            $roleTitle = $role ? $role->title : '-';

            if ($roleTitle === 'leader') {
                $selfFactors = Pishfactor::where('sarparast_id', $user->id)
                    ->whereIn('status', [1, 4])
                    ->whereBetween('created_at', [$target->start_date_en, $target->end_date_en])
                    ->get();

                $selfTasks = Tasks::where('leader_id', $user->id)
                    ->where('status', 1)
                    ->get();
            }

            if ($roleTitle === 'visitor') {
                $selfFactors = Pishfactor::where('visitor_id', $user->id)
                    ->whereIn('status', [1, 4])
                    ->whereBetween('created_at', [$target->start_date_en, $target->end_date_en])
                    ->get();

                $selfTasks = Tasks::where('user_id', $user->id)
                    ->where('status', 1)
                    ->get();
            }

            // محاسبه قیمت‌ها (حتی اگر selfFactors خالی باشد → بدون ارور)
            $selfFactorPrices = 0;
            $selfPatPrices = 0;

            foreach ($selfFactors as $f) {
                $selfFactorPrices += intval(str_replace(',', '', $f->fullPrice));
                $selfPatPrices    += intval(str_replace(',', '', $f->pat_price));
            }

            $result[] = [
                'id'           => $user->id,
                'name'         => $user->name,
                'username'     => $user->username,
                'role'         => $roleTitle,
                'factors_count'=> count($selfFactors),
                'children'     => [],
                'FactorPrices' => $selfFactorPrices,
                'PatPrices'    => $selfPatPrices,
                'ActiveTasks'  => $selfTasks,
                'isActive'     => $user->isActive,
            ];
        }

        // ==============================
        //         2) محاسبه SubUsers
        // ==============================
        $subs = User::where('leader_id', $leaderId)->where('isActive', 1)->get();

        foreach ($subs as $sub) {

            // ساختار بازگشتی
            $children = $this->getSubUsersWithFactors($sub->id, $target, true);

            $role = $sub->roles()->first();
            $roleTitle = $role ? $role->title : '-';

            // تعریف اولیه
            $factors = collect([]);
            $tasks   = collect([]);

            if ($roleTitle === 'leader') {
                $factors = Pishfactor::where('sarparast_id', $sub->id)
                    ->whereIn('status', [1,4])
                    ->whereBetween('created_at', [$target->start_date_en, $target->end_date_en])
                    ->get();

                $tasks = Tasks::where('leader_id', $sub->id)
                    ->where('status', 1)->get();
            }

            if ($roleTitle === 'visitor') {
                $factors = Pishfactor::where('visitor_id', $sub->id)
                    ->whereIn('status', [1,4])
                    ->whereBetween('created_at', [$target->start_date_en, $target->end_date_en])
                    ->get();

                $tasks = Tasks::where('user_id', $sub->id)
                    ->where('status', 1)->get();
            }

            $FactorPrices = 0;
            $PatPrices = 0;

            foreach ($factors as $f) {
                $FactorPrices += intval(str_replace(',', '', $f->fullPrice));
                $PatPrices    += intval(str_replace(',', '', $f->pat_price));
            }

            $result[] = [
                'id'           => $sub->id,
                'name'         => $sub->name,
                'username'     => $sub->username,
                'role'         => $roleTitle,
                'factors_count'=> count($factors),
                'children'     => $children,
                'FactorPrices' => $FactorPrices,
                'PatPrices'    => $PatPrices,
                'ActiveTasks'  => $tasks,
                'isActive'     => $sub->isActive,
            ];
        }

        // مرتب‌سازی خروجی
        usort($result, fn($a, $b) => $b['factors_count'] <=> $a['factors_count']);

        return $result;
    }




    public function totalFactorPrice($tree)
    {
        $sum = 0;

        foreach ($tree as $node) {
            $sum += $node['FactorPrices'];
            if (!empty($node['children'])) {
                $sum += $this->totalFactorPrice($node['children']);
            }
        }

        return $sum;
    }
}

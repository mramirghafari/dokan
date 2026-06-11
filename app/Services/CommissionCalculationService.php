<?php

namespace App\Services;

use App\Models\CommissionPlan;
use App\Models\CommissionSettlement;
use App\Models\CommissionSettlementLine;
use App\Models\Pishfactor;
use App\Models\Targets;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CommissionCalculationService
{
    public function calculateTarget(Targets $target, ?CommissionPlan $plan = null, bool $persist = false): array
    {
        $target->loadMissing(['user.roles', 'commissionPlan', 'products']);
        $plan = $plan ?: $target->commissionPlan ?: $this->defaultPlan($target);

        $invoices = $this->targetInvoices($target, $plan)->with('items')->get();
        $totals = $this->invoiceTotals($invoices, $plan);
        $targetAmount = $this->money($target->target_price);
        $achievementPercent = $targetAmount > 0 ? round(($totals['sales_amount'] / $targetAmount) * 100, 2) : 0.0;
        $baseCommission = $this->commissionBase($totals, $plan) * ((float) $plan->base_rate_percent / 100);
        $tierCommission = $this->tierCommission($plan, $achievementPercent, $totals['sales_amount']);
        $bonusAmount = $achievementPercent >= (float) $target->achievement_threshold_percent ? $this->money($target->bonus_amount) : 0.0;
        $penaltyAmount = $achievementPercent < (float) $target->achievement_threshold_percent ? $this->money($target->penalty_amount) : 0.0;
        $payableAmount = max(0, $baseCommission + $tierCommission + $bonusAmount - $penaltyAmount + (float) $plan->fixed_amount);

        if ($plan->cap_amount !== null) {
            $payableAmount = min($payableAmount, (float) $plan->cap_amount);
        }

        $result = [
            'target' => $target,
            'plan' => $plan,
            'user' => $target->user,
            'period_start' => $target->start_date_en,
            'period_end' => $target->end_date_en,
            'invoices_count' => $invoices->count(),
            'sales_amount' => round($totals['sales_amount'], 2),
            'tax_amount' => round($totals['tax_amount'], 2),
            'discount_amount' => round($totals['discount_amount'], 2),
            'net_amount' => round($totals['net_amount'], 2),
            'collected_amount' => round($totals['collected_amount'], 2),
            'gross_profit_amount' => round($totals['gross_profit_amount'], 2),
            'target_amount' => round($targetAmount, 2),
            'achievement_percent' => $achievementPercent,
            'base_commission_amount' => round($baseCommission, 2),
            'tier_commission_amount' => round($tierCommission, 2),
            'bonus_amount' => round($bonusAmount, 2),
            'penalty_amount' => round($penaltyAmount, 2),
            'payable_amount' => round($payableAmount, 2),
        ];

        if ($persist) {
            $result['settlement'] = $this->persistSettlement($target, $plan, $invoices, $result);
        }

        return $result;
    }

    public function calculateMany(Collection $targets, bool $persist = false): Collection
    {
        return $targets->map(function (Targets $target) use ($persist) {
            return $this->calculateTarget($target, null, $persist);
        });
    }

    public function targetInvoices(Targets $target, CommissionPlan $plan): Builder
    {
        $user = $target->user ?: User::find($target->user_id);
        $query = Pishfactor::query()
            ->whereBetween('created_at', [$target->start_date_en, $target->end_date_en]);

        $this->applyTriggerStatus($query, $plan->trigger_status);

        if ($user) {
            $roleTitle = optional($user->roles()->first())->title;

            if ($roleTitle === 'visitor') {
                $query->where('visitor_id', $user->id);
            } elseif ($roleTitle === 'leader') {
                $query->where(function (Builder $scope) use ($user) {
                    $scope->where('sarparast_id', $user->id)
                        ->orWhereIn('visitor_id', $this->childUserIds($user->id));
                });
            } elseif ((int) $user->isGod !== 1) {
                $query->forOrganizations($user);
            }
        }

        return $query;
    }

    private function defaultPlan(Targets $target): CommissionPlan
    {
        $user = $target->user;
        $roleTitle = $user ? optional($user->roles()->first())->title : null;

        $plan = CommissionPlan::query()
            ->where('is_active', true)
            ->when($target->tenant_id, fn(Builder $query) => $query->where(function (Builder $scope) use ($target) {
                $scope->whereNull('tenant_id')->orWhere('tenant_id', $target->tenant_id);
            }))
            ->when($target->organization_id, fn(Builder $query) => $query->where(function (Builder $scope) use ($target) {
                $scope->whereNull('organization_id')->orWhere('organization_id', $target->organization_id);
            }))
            ->when($roleTitle, fn(Builder $query) => $query->where(function (Builder $scope) use ($roleTitle) {
                $scope->whereNull('applies_to_role')->orWhere('applies_to_role', $roleTitle);
            }))
            ->orderByDesc('tenant_id')
            ->orderByDesc('organization_id')
            ->first();

        if ($plan) {
            return $plan;
        }

        return new CommissionPlan([
            'title' => 'پلن پیش فرض پورسانت',
            'calculation_base' => 'invoice_total',
            'trigger_status' => 'approved',
            'base_rate_percent' => 0,
            'fixed_amount' => 0,
            'include_tax' => true,
            'include_discount' => true,
            'is_active' => true,
        ]);
    }

    private function invoiceTotals(Collection $invoices, CommissionPlan $plan): array
    {
        $totals = [
            'sales_amount' => 0.0,
            'tax_amount' => 0.0,
            'discount_amount' => 0.0,
            'net_amount' => 0.0,
            'collected_amount' => 0.0,
            'gross_profit_amount' => 0.0,
        ];

        foreach ($invoices as $invoice) {
            $invoiceAmount = $this->money($invoice->fullPrice);
            $taxAmount = $this->money($invoice->pat_price);
            $discountAmount = (float) $invoice->items->sum(fn($item) => $this->money($item->discount_amount ?? 0));
            $netAmount = $invoiceAmount;

            if (!$plan->include_tax) {
                $netAmount -= $taxAmount;
            }

            if (!$plan->include_discount) {
                $netAmount -= $discountAmount;
            }

            $totals['sales_amount'] += $invoiceAmount;
            $totals['tax_amount'] += $taxAmount;
            $totals['discount_amount'] += $discountAmount;
            $totals['net_amount'] += max(0, $netAmount);
            $totals['collected_amount'] += in_array($invoice->settlement_status, ['settled', 'paid'], true) ? $invoiceAmount : 0;
            $totals['gross_profit_amount'] += max(0, $invoiceAmount - $taxAmount);
        }

        return $totals;
    }

    private function commissionBase(array $totals, CommissionPlan $plan): float
    {
        return match ($plan->calculation_base) {
            'net_amount' => $totals['net_amount'],
            'collected_amount' => $totals['collected_amount'],
            'gross_profit' => $totals['gross_profit_amount'],
            default => $totals['sales_amount'],
        };
    }

    private function tierCommission(CommissionPlan $plan, float $achievementPercent, float $salesAmount): float
    {
        if (!$plan->exists) {
            return 0.0;
        }

        $tier = $plan->tiers()
            ->where('is_active', true)
            ->where('from_achievement_percent', '<=', $achievementPercent)
            ->where(function (Builder $query) use ($achievementPercent) {
                $query->whereNull('to_achievement_percent')
                    ->orWhere('to_achievement_percent', '>=', $achievementPercent);
            })
            ->orderByDesc('from_achievement_percent')
            ->first();

        if (!$tier) {
            return 0.0;
        }

        return ($salesAmount * ((float) $tier->rate_percent / 100)) + (float) $tier->fixed_bonus_amount;
    }

    private function persistSettlement(Targets $target, CommissionPlan $plan, Collection $invoices, array $result): CommissionSettlement
    {
        return DB::transaction(function () use ($target, $plan, $invoices, $result) {
            $settlement = CommissionSettlement::updateOrCreate(
                [
                    'target_id' => $target->id,
                    'user_id' => $target->user_id,
                    'period_start' => $target->start_date_en,
                    'period_end' => $target->end_date_en,
                ],
                [
                    'tenant_id' => $target->tenant_id,
                    'organization_id' => $target->organization_id,
                    'commission_plan_id' => $plan->exists ? $plan->id : null,
                    'sales_amount' => $result['sales_amount'],
                    'tax_amount' => $result['tax_amount'],
                    'discount_amount' => $result['discount_amount'],
                    'net_amount' => $result['net_amount'],
                    'collected_amount' => $result['collected_amount'],
                    'gross_profit_amount' => $result['gross_profit_amount'],
                    'target_amount' => $result['target_amount'],
                    'achievement_percent' => $result['achievement_percent'],
                    'base_commission_amount' => $result['base_commission_amount'],
                    'tier_commission_amount' => $result['tier_commission_amount'],
                    'bonus_amount' => $result['bonus_amount'],
                    'penalty_amount' => $result['penalty_amount'],
                    'payable_amount' => $result['payable_amount'],
                    'status' => 'calculated',
                    'calculated_at' => now(),
                ]
            );

            $settlement->lines()->delete();

            foreach ($invoices as $invoice) {
                $lineAmount = $this->money($invoice->fullPrice);
                CommissionSettlementLine::create([
                    'commission_settlement_id' => $settlement->id,
                    'pishfactor_id' => $invoice->id,
                    'customer_id' => $invoice->customer_id,
                    'area_id' => $invoice->area_id,
                    'region_id' => $invoice->region_id,
                    'quantity' => $invoice->items->sum(fn($item) => (float) ($item->tedad ?? 0)),
                    'invoice_amount' => $lineAmount,
                    'net_amount' => $lineAmount,
                    'calculation_base_amount' => $lineAmount,
                    'rate_percent' => (float) $plan->base_rate_percent,
                    'commission_amount' => $lineAmount * ((float) $plan->base_rate_percent / 100),
                    'reason' => 'invoice',
                ]);
            }

            $target->forceFill(['settlement_status' => 'calculated'])->save();

            return $settlement->load('lines');
        });
    }

    private function applyTriggerStatus(Builder $query, string $triggerStatus): void
    {
        match ($triggerStatus) {
            'delivered' => $query->where(function (Builder $scope) {
                $scope->where('delivery_status', 'delivered')->orWhere('status', 4);
            }),
            'settled' => $query->where('settlement_status', 'settled'),
            default => $query->whereIn('status', [1, 4]),
        };
    }

    private function childUserIds(int $leaderId): array
    {
        $childIds = User::where('leader_id', $leaderId)->pluck('id')->all();

        foreach ($childIds as $childId) {
            $childIds = array_merge($childIds, $this->childUserIds((int) $childId));
        }

        return array_values(array_unique($childIds));
    }

    private function money($value): float
    {
        return (float) str_replace(',', '', (string) ($value ?? 0));
    }
}

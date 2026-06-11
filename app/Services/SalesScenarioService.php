<?php

namespace App\Services;

class SalesScenarioService
{
    public function initialInvoicePayload($user = null, ?int $tenantId = null, array $context = []): array
    {
        $decision = $this->approvalDecision($tenantId, $context);
        $requiresApproval = $decision['decision'] !== 'approved';

        if ($requiresApproval) {
            return [
                'status' => 0,
                'sales_status' => 'pending_approval',
                'approval_status' => 'pending_approval',
                'approval_level' => $decision['approval_level'],
                'approval_requested_at' => now(),
                'approval_requested_by' => $user?->id,
                'approval_note' => $decision['note'],
                'credit_status' => $decision['credit_status'],
                'reserve_status' => $decision['reserve_status'],
                'warehouse_issue_status' => 'pending',
                'delivery_status' => 'not_ready',
            ];
        }

        return [
            'status' => 1,
            'sales_status' => 'approved',
            'approval_status' => 'approved',
            'approval_level' => 0,
            'approval_reviewed_at' => now(),
            'approval_reviewed_by' => $user?->id,
            'approval_note' => $decision['note'],
            'credit_status' => $decision['credit_status'],
            'reserve_status' => $decision['reserve_status'],
            'warehouse_issue_status' => 'pending',
            'delivery_status' => 'not_ready',
        ];
    }

    public function invoiceApprovalRequired(?int $tenantId = null, array $context = []): bool
    {
        return $this->approvalDecision($tenantId, $context)['decision'] !== 'approved';
    }

    public function approvalDecision(?int $tenantId = null, array $context = []): array
    {
        $policy = (string) TenantSettings::get('invoice_approval_policy', $tenantId, 'legacy_toggle');
        $reasons = [];
        $approvalLevel = 0;

        if ($policy === 'legacy_toggle') {
            if (TenantSettings::enabled('feature_manager_order_approval', $tenantId)) {
                $reasons[] = 'تایید مدیریتی سفارش ها فعال است';
                $approvalLevel = max($approvalLevel, 1);
            }
        } elseif ($policy !== 'auto') {
            $reasons[] = $this->approvalPolicyLabel($policy);
            $approvalLevel = max($approvalLevel, $policy === 'multi_step' ? 2 : 1);
        }

        $amount = (float) ($context['amount'] ?? 0);
        $supervisorThreshold = (float) TenantSettings::get('amount_supervisor_threshold', $tenantId, 0);
        $managerThreshold = (float) TenantSettings::get('amount_manager_threshold', $tenantId, 0);

        if ($managerThreshold > 0 && $amount >= $managerThreshold) {
            $reasons[] = 'مبلغ فاکتور از آستانه تایید مدیر عبور کرده است';
            $approvalLevel = max($approvalLevel, 2);
        } elseif ($supervisorThreshold > 0 && $amount >= $supervisorThreshold) {
            $reasons[] = 'مبلغ فاکتور از آستانه تایید سرپرست عبور کرده است';
            $approvalLevel = max($approvalLevel, 1);
        }

        $discountPercent = (float) ($context['discount_percent'] ?? 0);
        $maxDiscount = (float) TenantSettings::get('max_discount_without_approval_percent', $tenantId, 0);

        if ($maxDiscount > 0 && $discountPercent > $maxDiscount) {
            $reasons[] = 'تخفیف از سقف بدون تایید بیشتر است';
            $approvalLevel = max($approvalLevel, 1);
        }

        $marginPercent = $context['profit_margin_percent'] ?? null;
        $minMargin = (float) TenantSettings::get('min_profit_margin_percent', $tenantId, 0);

        if ($marginPercent !== null && $minMargin > 0 && (float) $marginPercent < $minMargin) {
            $reasons[] = 'حاشیه سود از حداقل مجاز کمتر است';
            $approvalLevel = max($approvalLevel, 2);
        }

        $creditStatus = (string) ($context['credit_status'] ?? 'not_checked');
        $creditPolicy = (string) TenantSettings::get('customer_credit_policy', $tenantId, 'warning');

        if (in_array($creditStatus, ['over_limit', 'overdue', 'blocked'], true)) {
            if ($creditPolicy === 'block_invoice') {
                $reasons[] = 'اعتبار مشتری اجازه تایید خودکار نمی دهد';
                $approvalLevel = max($approvalLevel, 2);
            } elseif (in_array($creditPolicy, ['accountant_approval', 'manager_approval'], true)) {
                $reasons[] = 'وضعیت اعتبار مشتری نیازمند تایید است';
                $approvalLevel = max($approvalLevel, $creditPolicy === 'manager_approval' ? 2 : 1);
            }
        }

        $inventoryStatus = (string) ($context['inventory_status'] ?? 'not_checked');
        $shortagePolicy = (string) TenantSettings::get('shortage_handling_policy', $tenantId, 'manager_approval');

        if ($inventoryStatus === 'shortage') {
            if ($shortagePolicy === 'block_invoice') {
                $reasons[] = 'کمبود موجودی اجازه تایید خودکار نمی دهد';
                $approvalLevel = max($approvalLevel, 2);
            } elseif (in_array($shortagePolicy, ['warehouse_approval', 'manager_approval'], true)) {
                $reasons[] = 'کمبود موجودی نیازمند تایید انبار یا مدیر است';
                $approvalLevel = max($approvalLevel, $shortagePolicy === 'manager_approval' ? 2 : 1);
            }
        }

        $paymentType = (string) ($context['payment_type'] ?? '');
        $chequePolicy = (string) TenantSettings::get('cheque_approval_policy', $tenantId, 'accountant_approval');

        if ($paymentType === 'cheque' && $chequePolicy !== 'auto') {
            $reasons[] = 'فروش چکی نیازمند کنترل مالی است';
            $approvalLevel = max($approvalLevel, $chequePolicy === 'manager_approval' ? 2 : 1);
        }

        $decision = empty($reasons) ? 'approved' : 'pending_approval';

        return [
            'decision' => $decision,
            'approval_level' => $decision === 'approved' ? 0 : max(1, $approvalLevel),
            'reasons' => $reasons,
            'note' => empty($reasons) ? null : implode('؛ ', $reasons),
            'credit_status' => $creditStatus,
            'reserve_status' => (string) ($context['reserve_status'] ?? 'not_reserved'),
        ];
    }

    private function approvalPolicyLabel(string $policy): string
    {
        return [
            'supervisor' => 'تایید سرپرست فروش لازم است',
            'sales_expert' => 'تایید کارشناس فروش لازم است',
            'sales_manager' => 'تایید مدیر فروش لازم است',
            'general_manager' => 'تایید مدیر کل پنل لازم است',
            'multi_step' => 'تایید چند مرحله ای فروش لازم است',
        ][$policy] ?? 'تایید فروش لازم است';
    }
}

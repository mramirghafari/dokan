<?php

namespace App\Services;

use App\Models\PurchaseApprovalEvent;
use App\Models\PurchaseBudget;
use App\Models\PurchaseOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseApprovalService
{
    public function requestApproval(PurchaseOrder $purchaseOrder, $user): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $user) {
            $purchaseOrder = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();
            $fromStatus = $purchaseOrder->approval_status ?: 'draft';

            if (!in_array($purchaseOrder->status, ['draft', 'pending_approval'], true) || $purchaseOrder->receipt_id) {
                throw ValidationException::withMessages([
                    'purchase_order' => 'فقط سفارش خرید پیش نویس قابل ارسال برای تایید است.',
                ]);
            }

            $snapshot = $this->budgetSnapshot($purchaseOrder);
            $purchaseOrder->update(array_merge($snapshot, [
                'status' => 'pending_approval',
                'approval_status' => 'pending_approval',
                'approval_level' => 1,
                'approval_requested_at' => now(),
                'approval_requested_by' => $user?->id,
                'approval_reviewed_at' => null,
                'approval_reviewed_by' => null,
                'updated_by' => $user?->id,
            ]));

            $this->recordEvent($purchaseOrder->fresh() ?: $purchaseOrder, 'requested', $fromStatus, 'pending_approval', $snapshot, null, $user);

            return $purchaseOrder->fresh(['items.product', 'supplier', 'store', 'receipt', 'returns']) ?: $purchaseOrder;
        });
    }

    public function approve(PurchaseOrder $purchaseOrder, ?string $note, $user): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $note, $user) {
            $purchaseOrder = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();
            $fromStatus = $purchaseOrder->approval_status ?: 'draft';

            if ($purchaseOrder->approval_status !== 'pending_approval') {
                throw ValidationException::withMessages([
                    'purchase_order' => 'برای تایید مدیریتی، سفارش باید در وضعیت در انتظار تایید باشد.',
                ]);
            }

            $snapshot = $this->budgetSnapshot($purchaseOrder);
            $purchaseOrder->update(array_merge($snapshot, [
                'status' => 'approved',
                'approval_status' => 'approved',
                'approval_reviewed_at' => now(),
                'approval_reviewed_by' => $user?->id,
                'approval_note' => $note,
                'updated_by' => $user?->id,
            ]));

            $this->recordEvent($purchaseOrder->fresh() ?: $purchaseOrder, 'approved', $fromStatus, 'approved', $snapshot, $note, $user);

            return $purchaseOrder->fresh(['items.product', 'supplier', 'store', 'receipt', 'returns']) ?: $purchaseOrder;
        });
    }

    public function reject(PurchaseOrder $purchaseOrder, ?string $note, $user): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $note, $user) {
            $purchaseOrder = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();
            $fromStatus = $purchaseOrder->approval_status ?: 'draft';

            if ($purchaseOrder->approval_status !== 'pending_approval') {
                throw ValidationException::withMessages([
                    'purchase_order' => 'فقط سفارش در انتظار تایید قابل برگشت یا رد است.',
                ]);
            }

            $snapshot = $this->budgetSnapshot($purchaseOrder);
            $purchaseOrder->update(array_merge($snapshot, [
                'status' => 'draft',
                'approval_status' => 'rejected',
                'approval_reviewed_at' => now(),
                'approval_reviewed_by' => $user?->id,
                'approval_note' => $note,
                'updated_by' => $user?->id,
            ]));

            $this->recordEvent($purchaseOrder->fresh() ?: $purchaseOrder, 'rejected', $fromStatus, 'rejected', $snapshot, $note, $user);

            return $purchaseOrder->fresh(['items.product', 'supplier', 'store', 'receipt', 'returns']) ?: $purchaseOrder;
        });
    }

    public function budgetSnapshot(PurchaseOrder $purchaseOrder): array
    {
        $period = $this->period($purchaseOrder);
        $budget = PurchaseBudget::query()
            ->where('period', $period)
            ->where('store_id', $purchaseOrder->store_id)
            ->when($purchaseOrder->tenant_id, fn($query) => $query->where('tenant_id', $purchaseOrder->tenant_id))
            ->orderByDesc('id')
            ->first();
        $consumed = $this->consumedAmount($purchaseOrder, $period);
        $budgetAmount = $budget ? $this->money($budget->budget_amount) : null;
        $orderAmount = $this->money($purchaseOrder->total_amount);
        $remaining = $budgetAmount === null ? 0 : round($budgetAmount - $consumed - $orderAmount, 2);
        $budgetStatus = match (true) {
            !$budget => 'no_budget',
            $remaining < 0 => 'over_budget',
            default => 'within_budget',
        };

        return [
            'budget_status' => $budgetStatus,
            'budget_period' => $period,
            'budget_amount' => $budgetAmount,
            'budget_consumed_amount' => $consumed,
            'budget_remaining_amount' => $remaining,
        ];
    }

    private function consumedAmount(PurchaseOrder $purchaseOrder, string $period): float
    {
        [$start, $end] = $this->periodRange($period);

        return $this->money(PurchaseOrder::query()
            ->where('store_id', $purchaseOrder->store_id)
            ->when($purchaseOrder->tenant_id, fn($query) => $query->where('tenant_id', $purchaseOrder->tenant_id))
            ->whereKeyNot($purchaseOrder->id)
            ->whereBetween('order_date_en', [$start, $end])
            ->whereIn('approval_status', ['pending_approval', 'approved'])
            ->sum('total_amount'));
    }

    private function recordEvent(PurchaseOrder $purchaseOrder, string $eventType, ?string $fromStatus, string $toStatus, array $snapshot, ?string $note, $user): void
    {
        PurchaseApprovalEvent::create([
            'tenant_id' => $purchaseOrder->tenant_id,
            'organization_id' => $purchaseOrder->organization_id,
            'purchase_order_id' => $purchaseOrder->id,
            'event_type' => $eventType,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'order_amount' => $this->money($purchaseOrder->total_amount),
            'budget_amount' => $snapshot['budget_amount'],
            'budget_consumed_amount' => $snapshot['budget_consumed_amount'],
            'budget_status' => $snapshot['budget_status'],
            'description' => $note,
            'created_by' => $user?->id,
        ]);
    }

    private function period(PurchaseOrder $purchaseOrder): string
    {
        $date = $purchaseOrder->order_date_en ? Carbon::parse($purchaseOrder->order_date_en) : now();

        return $date->format('Y-m');
    }

    private function periodRange(string $period): array
    {
        $start = Carbon::createFromFormat('Y-m-d', $period . '-01')->startOfMonth();

        return [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()];
    }

    private function money($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 2);
    }
}

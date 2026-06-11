<?php

namespace App\Services;

use App\Models\Pishfactor;
use App\Models\PishFactorItems;
use App\Models\Product;
use App\Models\SalesInventoryReservation;
use App\Models\SalesWorkflowEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class SalesWorkflowService
{
    public function requestApproval(Pishfactor $factor, $user = null): Pishfactor
    {
        return DB::transaction(function () use ($factor, $user) {
            $factor = Pishfactor::whereKey($factor->id)->lockForUpdate()->firstOrFail();
            $fromStatus = $factor->approval_status ?: 'draft';

            if (!in_array($fromStatus, ['draft', 'rejected'], true)) {
                throw ValidationException::withMessages(['sales_order' => 'Sales order is not ready for approval request.']);
            }

            $creditStatus = $this->creditStatus($factor);
            $factor->update([
                'sales_status' => 'pending_approval',
                'approval_status' => 'pending_approval',
                'approval_level' => 1,
                'approval_requested_at' => now(),
                'approval_requested_by' => $user?->id,
                'approval_reviewed_at' => null,
                'approval_reviewed_by' => null,
                'credit_status' => $creditStatus,
                'updated_by' => $user?->id,
            ]);

            $this->recordEvent($factor->fresh() ?: $factor, 'requested', $fromStatus, 'pending_approval', null, $user);

            return $factor->fresh(['items.product', 'customer', 'reservations']) ?: $factor;
        });
    }

    public function approve(Pishfactor $factor, ?string $note = null, $user = null): Pishfactor
    {
        return DB::transaction(function () use ($factor, $note, $user) {
            $factor = Pishfactor::whereKey($factor->id)->lockForUpdate()->firstOrFail();
            $fromStatus = $factor->approval_status ?: 'draft';

            if (!in_array($fromStatus, ['pending_approval', 'draft'], true)) {
                throw ValidationException::withMessages(['sales_order' => 'Sales order cannot be approved from the current state.']);
            }

            $factor->update([
                'status' => 1,
                'sales_status' => 'approved',
                'approval_status' => 'approved',
                'approval_reviewed_at' => now(),
                'approval_reviewed_by' => $user?->id,
                'approval_note' => $note,
                'delivery_status' => 'not_ready',
                'updated_by' => $user?->id,
            ]);

            $this->recordEvent($factor->fresh() ?: $factor, 'approved', $fromStatus, 'approved', $note, $user);

            return $factor->fresh(['items.product', 'customer', 'reservations']) ?: $factor;
        });
    }

    public function reject(Pishfactor $factor, ?string $note = null, $user = null): Pishfactor
    {
        return DB::transaction(function () use ($factor, $note, $user) {
            $factor = Pishfactor::whereKey($factor->id)->lockForUpdate()->firstOrFail();
            $fromStatus = $factor->approval_status ?: 'draft';

            $this->releaseReservation($factor, $user, false);
            $factor->update([
                'status' => 3,
                'sales_status' => 'rejected',
                'approval_status' => 'rejected',
                'approval_reviewed_at' => now(),
                'approval_reviewed_by' => $user?->id,
                'approval_note' => $note,
                'updated_by' => $user?->id,
            ]);

            $this->recordEvent($factor->fresh() ?: $factor, 'rejected', $fromStatus, 'rejected', $note, $user);

            return $factor->fresh(['items.product', 'customer', 'reservations']) ?: $factor;
        });
    }

    public function reserveInventory(Pishfactor $factor, $user = null): Pishfactor
    {
        return DB::transaction(function () use ($factor, $user) {
            $factor = Pishfactor::whereKey($factor->id)->lockForUpdate()->firstOrFail();
            $factor->loadMissing('items.product');

            if (!in_array($factor->approval_status, ['approved', 'pending_approval'], true)) {
                throw ValidationException::withMessages(['sales_order' => 'Sales order must be approved before reservation.']);
            }

            $this->releaseReservation($factor, $user, false);

            foreach ($factor->items as $item) {
                if (!$item instanceof PishFactorItems || !$item->product) {
                    continue;
                }

                $quantity = $this->itemQuantity($item, $item->product);
                if ($quantity <= 0) {
                    continue;
                }

                $storeId = $this->itemStoreId($item, $item->product);
                $locationId = (int) ($item->warehouse_location_id ?: 0);
                $availableQuantity = $this->availableQuantity($factor, $item, $storeId, $locationId);

                if ($availableQuantity < $quantity) {
                    throw ValidationException::withMessages([
                        'inventory' => 'موجودی آزاد برای رزرو کالا «' . ($item->product->title ?? $item->pr_id) . '» کافی نیست. موجودی آزاد: ' . number_format($availableQuantity, 3) . '، مقدار درخواستی: ' . number_format($quantity, 3),
                    ]);
                }

                SalesInventoryReservation::create([
                    'tenant_id' => $factor->tenant_id ?: $factor->tenants_id,
                    'organization_id' => $factor->organization_id,
                    'pishfactor_id' => $factor->id,
                    'pish_factor_item_id' => $item->id,
                    'store_id' => $storeId,
                    'warehouse_location_id' => $locationId,
                    'product_id' => $item->pr_id,
                    ...$this->tracePayload($item),
                    'quantity' => $quantity,
                    'available_quantity_snapshot' => $availableQuantity,
                    'shortage_quantity' => 0,
                    'status' => 'reserved',
                    'reserved_at' => now(),
                    'created_by' => $user?->id,
                ]);

                $item->update(['reserved_quantity' => $quantity]);
                $this->incrementReservedBalance($factor, $item, $storeId, $quantity, $locationId);
                $this->incrementReservedTraceBalance($factor, $item, $storeId, $quantity, $locationId);
            }

            $factor->update([
                'reserve_status' => 'reserved',
                'reserved_at' => now(),
                'sales_status' => 'reserved',
                'delivery_status' => 'ready',
                'updated_by' => $user?->id,
            ]);

            $this->recordEvent($factor->fresh() ?: $factor, 'reserved', null, 'reserved', null, $user);

            return $factor->fresh(['items.product', 'customer', 'reservations']) ?: $factor;
        });
    }

    public function releaseReservation(Pishfactor $factor, $user = null, bool $recordEvent = true): Pishfactor
    {
        $factor = Pishfactor::whereKey($factor->id)->lockForUpdate()->firstOrFail();
        $factor->loadMissing('reservations', 'items');

        foreach ($factor->reservations()->where('status', 'reserved')->get() as $reservation) {
            $this->incrementReservedBalance($factor, null, (int) $reservation->store_id, (float) $reservation->quantity * -1, (int) $reservation->warehouse_location_id, (int) $reservation->product_id);
            $this->incrementReservedTraceBalance($factor, null, (int) $reservation->store_id, (float) $reservation->quantity * -1, (int) $reservation->warehouse_location_id, (int) $reservation->product_id, $reservation);
            $reservation->update(['status' => 'released', 'released_at' => now(), 'release_reason' => 'manual_release']);
        }

        foreach ($factor->items as $item) {
            $item->update(['reserved_quantity' => 0]);
        }

        $factor->update(['reserve_status' => 'released', 'reserved_at' => null, 'updated_by' => $user?->id]);

        if ($recordEvent) {
            $this->recordEvent($factor->fresh() ?: $factor, 'reservation_released', null, 'released', null, $user);
        }

        return $factor->fresh(['items.product', 'customer', 'reservations']) ?: $factor;
    }

    public function markWarehouseIssued(Pishfactor $factor, $user = null): Pishfactor
    {
        return DB::transaction(function () use ($factor, $user) {
            $factor = Pishfactor::whereKey($factor->id)->lockForUpdate()->firstOrFail();
            $fromStatus = $factor->sales_status;
            $this->consumeReservation($factor, $user);

            $factor->update([
                'step' => 2,
                'sales_status' => 'warehouse_issued',
                'warehouse_issue_status' => 'issued',
                'delivery_status' => 'ready',
                'reserve_status' => 'consumed',
                'updated_by' => $user?->id,
            ]);

            $this->recordEvent($factor->fresh() ?: $factor, 'warehouse_issued', $fromStatus, 'warehouse_issued', null, $user);

            return $factor->fresh(['items.product', 'customer', 'reservations']) ?: $factor;
        });
    }

    private function consumeReservation(Pishfactor $factor, $user = null): void
    {
        $factor->loadMissing('reservations', 'items');

        foreach ($factor->reservations()->where('status', 'reserved')->get() as $reservation) {
            $this->incrementReservedBalance($factor, null, (int) $reservation->store_id, (float) $reservation->quantity * -1, (int) $reservation->warehouse_location_id, (int) $reservation->product_id);
            $this->incrementReservedTraceBalance($factor, null, (int) $reservation->store_id, (float) $reservation->quantity * -1, (int) $reservation->warehouse_location_id, (int) $reservation->product_id, $reservation);
            $reservation->update(['status' => 'consumed', 'released_at' => now(), 'release_reason' => 'warehouse_issued']);
        }

        foreach ($factor->items as $item) {
            $item->update(['reserved_quantity' => 0]);
        }
    }

    public function markSettled(Pishfactor $factor, $user = null): Pishfactor
    {
        return $this->transition($factor, [
            'settlement_status' => 'settled',
            'sales_status' => 'settled',
        ], 'settled', $user);
    }

    public function markDelivered(Pishfactor $factor, $user = null): Pishfactor
    {
        return $this->transition($factor, [
            'status' => 4,
            'step' => 4,
            'sales_status' => 'delivered',
            'delivery_status' => 'delivered',
            'delivered_at' => now(),
        ], 'delivered', $user);
    }

    private function transition(Pishfactor $factor, array $attributes, string $eventType, $user = null): Pishfactor
    {
        return DB::transaction(function () use ($factor, $attributes, $eventType, $user) {
            $factor = Pishfactor::whereKey($factor->id)->lockForUpdate()->firstOrFail();
            $fromStatus = $factor->sales_status;
            $attributes['updated_by'] = $user?->id;
            $factor->update($attributes);
            $this->recordEvent($factor->fresh() ?: $factor, $eventType, $fromStatus, $attributes['sales_status'] ?? null, null, $user);

            return $factor->fresh(['items.product', 'customer', 'reservations']) ?: $factor;
        });
    }

    private function recordEvent(Pishfactor $factor, string $eventType, ?string $fromStatus, ?string $toStatus, ?string $note, $user): void
    {
        if (!Schema::hasTable('sales_workflow_events')) {
            return;
        }

        SalesWorkflowEvent::create([
            'tenant_id' => $factor->tenant_id ?: $factor->tenants_id,
            'organization_id' => $factor->organization_id,
            'pishfactor_id' => $factor->id,
            'event_type' => $eventType,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'order_amount' => $this->money($factor->fullPrice),
            'credit_status' => $factor->credit_status,
            'description' => $note,
            'created_by' => $user?->id,
        ]);
    }

    private function creditStatus(Pishfactor $factor): string
    {
        $amount = $this->money($factor->fullPrice);

        return $amount > 0 ? 'within_limit' : 'not_checked';
    }

    private function itemQuantity(PishFactorItems $item, ?Product $product): float
    {
        $packItems = $product ? (float) ($product->pack_items ?: 0) : 0;

        return round(((float) $item->pack * $packItems) + (float) $item->tedad, 3);
    }

    private function itemStoreId(PishFactorItems $item, ?Product $product): int
    {
        $raw = $product?->store_id;
        $ids = is_array($raw) ? $raw : json_decode((string) $raw, true);

        return (int) (is_array($ids) && count($ids) ? reset($ids) : 0);
    }

    private function incrementReservedBalance(Pishfactor $factor, ?PishFactorItems $item, int $storeId, float $quantity, int $locationId = 0, ?int $productId = null): void
    {
        if (!Schema::hasTable('inventory_balances') || $storeId <= 0) {
            return;
        }

        $productId = $productId ?: (int) $item?->pr_id;
        if ($productId <= 0) {
            return;
        }

        $tenantId = $factor->tenant_id ?: $factor->tenants_id;
        $balance = DB::table('inventory_balances')
            ->where('tenant_id', $tenantId)
            ->where('store_id', $storeId)
            ->where('warehouse_location_id', $locationId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        if ($balance) {
            DB::table('inventory_balances')->where('id', $balance->id)->update([
                'reserved_quantity' => DB::raw('GREATEST(reserved_quantity + ' . $quantity . ', 0)'),
                'updated_at' => now(),
            ]);
        }
    }

    private function availableQuantity(Pishfactor $factor, PishFactorItems $item, int $storeId, int $locationId): float
    {
        if ($storeId <= 0) {
            return 0.0;
        }

        $tenantId = $factor->tenant_id ?: $factor->tenants_id;
        $balance = DB::table('inventory_balances')
            ->where('tenant_id', $tenantId)
            ->where('store_id', $storeId)
            ->where('warehouse_location_id', $locationId)
            ->where('product_id', $item->pr_id)
            ->lockForUpdate()
            ->first();

        $available = $balance ? (float) $balance->quantity - (float) $balance->reserved_quantity : 0.0;

        if ($this->itemHasTrace($item)) {
            $traceBalance = $this->traceBalanceQuery($factor, $item, $storeId, $locationId)
                ->lockForUpdate()
                ->first();

            $traceAvailable = $traceBalance ? (float) $traceBalance->quantity - (float) $traceBalance->reserved_quantity : 0.0;

            return min($available, $traceAvailable);
        }

        return $available;
    }

    private function incrementReservedTraceBalance(Pishfactor $factor, ?PishFactorItems $item, int $storeId, float $quantity, int $locationId = 0, ?int $productId = null, ?SalesInventoryReservation $reservation = null): void
    {
        if (!Schema::hasTable('inventory_trace_balances') || $storeId <= 0) {
            return;
        }

        $query = $reservation
            ? $this->reservationTraceBalanceQuery($factor, $reservation, $storeId, $locationId, $productId)
            : $this->traceBalanceQuery($factor, $item, $storeId, $locationId);

        $traceBalance = $query->lockForUpdate()->first();

        if ($traceBalance) {
            DB::table('inventory_trace_balances')->where('id', $traceBalance->id)->update([
                'reserved_quantity' => DB::raw('GREATEST(reserved_quantity + ' . $quantity . ', 0)'),
                'updated_at' => now(),
            ]);
        }
    }

    private function traceBalanceQuery(Pishfactor $factor, ?PishFactorItems $item, int $storeId, int $locationId)
    {
        $tenantId = $factor->tenant_id ?: $factor->tenants_id;

        return DB::table('inventory_trace_balances')
            ->where('tenant_id', $tenantId)
            ->where('store_id', $storeId)
            ->where('warehouse_location_id', $locationId)
            ->where('product_id', (int) $item?->pr_id)
            ->where('batch_no', $item?->batch_no)
            ->where('lot_no', $item?->lot_no)
            ->where('serial_no', $item?->serial_no)
            ->where('manufactured_at', $item?->manufactured_at)
            ->where('expiry_date', $item?->expiry_date)
            ->where('color', $item?->color)
            ->where('size', $item?->size)
            ->where('quality_grade', $item?->quality_grade);
    }

    private function reservationTraceBalanceQuery(Pishfactor $factor, SalesInventoryReservation $reservation, int $storeId, int $locationId, ?int $productId)
    {
        $tenantId = $factor->tenant_id ?: $factor->tenants_id;

        return DB::table('inventory_trace_balances')
            ->where('tenant_id', $tenantId)
            ->where('store_id', $storeId)
            ->where('warehouse_location_id', $locationId)
            ->where('product_id', $productId ?: $reservation->product_id)
            ->where('batch_no', $reservation->batch_no)
            ->where('lot_no', $reservation->lot_no)
            ->where('serial_no', $reservation->serial_no)
            ->where('manufactured_at', $reservation->manufactured_at)
            ->where('expiry_date', $reservation->expiry_date)
            ->where('color', $reservation->color)
            ->where('size', $reservation->size)
            ->where('quality_grade', $reservation->quality_grade);
    }

    private function itemHasTrace(PishFactorItems $item): bool
    {
        return (bool) ($item->batch_no || $item->lot_no || $item->serial_no || $item->expiry_date || $item->color || $item->size || $item->quality_grade);
    }

    private function tracePayload(PishFactorItems $item): array
    {
        return [
            'batch_no' => $item->batch_no,
            'lot_no' => $item->lot_no,
            'serial_no' => $item->serial_no,
            'manufactured_at' => $item->manufactured_at,
            'expiry_date' => $item->expiry_date,
            'color' => $item->color,
            'size' => $item->size,
            'quality_grade' => $item->quality_grade,
        ];
    }

    private function money($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 2);
    }
}

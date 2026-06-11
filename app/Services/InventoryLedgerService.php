<?php

namespace App\Services;

use App\Models\Depot;
use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\InventoryTraceBalance;
use App\Models\Pishfactor;
use App\Models\PishFactorItems;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

class InventoryLedgerService
{
    public function record(array $attributes): InventoryMovement
    {
        return DB::transaction(function () use ($attributes) {
            $movement = InventoryMovement::create($this->costedMovement($this->normalizeMovement($attributes)));
            $this->applyMovementToBalance($movement, 1);
            $this->applyMovementToTraceBalance($movement, 1);

            return $movement;
        });
    }

    public function replaceReceiptMovements(Receipt $receipt, iterable $depots, ?int $userId = null): void
    {
        DB::transaction(function () use ($receipt, $depots, $userId) {
            InventoryMovement::where('receipt_id', $receipt->id)
                ->get()
                ->each(function (InventoryMovement $movement) {
                    $this->applyMovementToBalance($movement, -1);
                    $this->applyMovementToTraceBalance($movement, -1);
                    $movement->delete();
                });

            foreach ($depots as $depot) {
                if (!$depot instanceof Depot || !$depot->pr_id || (float) $depot->entity == 0.0) {
                    continue;
                }

                $direction = (int) $depot->status === 0 ? 'out' : 'in';

                $movement = InventoryMovement::create($this->costedMovement($this->normalizeMovement([
                    'tenant_id' => $receipt->tenant_id ?: $depot->tenant_id,
                    'organization_id' => $receipt->organization_id ?: $this->storeOrganizationId((int) $depot->store_id),
                    'store_id' => $depot->store_id,
                    'warehouse_location_id' => $depot->warehouse_location_id ?: 0,
                    'product_id' => $depot->pr_id,
                    'receipt_id' => $receipt->id,
                    'source_id' => $depot->id,
                    'source_type' => Depot::class,
                    'movement_type' => $this->receiptMovementType($receipt, $direction),
                    'direction' => $direction,
                    'quantity' => abs((float) $depot->entity),
                    'quantity_sub_unit' => abs((float) ($depot->entity_sub_unit ?: 0)),
                    'unit_cost' => $depot->price ?: $depot->product?->price ?: null,
                    'reference_no' => $receipt->number,
                    'occurred_at' => $receipt->date_en ?: $receipt->created_at ?: now(),
                    'description' => $receipt->tozihat,
                    'batch_no' => $depot->batch_no,
                    'lot_no' => $depot->lot_no,
                    'serial_no' => $depot->serial_no,
                    'manufactured_at' => $depot->manufactured_at,
                    'expiry_date' => $depot->expiry_date,
                    'color' => $depot->color,
                    'size' => $depot->size,
                    'quality_grade' => $depot->quality_grade,
                    'weight' => $depot->weight,
                    'tracking_notes' => $depot->tracking_notes,
                    'user_id' => $userId ?: $receipt->user_id,
                ])));

                $this->applyMovementToBalance($movement, 1);
                $this->applyMovementToTraceBalance($movement, 1);
            }
        });
    }

    public function removeReceiptMovements(Receipt $receipt): void
    {
        DB::transaction(function () use ($receipt) {
            InventoryMovement::where('receipt_id', $receipt->id)
                ->get()
                ->each(function (InventoryMovement $movement) {
                    $this->applyMovementToBalance($movement, -1);
                    $this->applyMovementToTraceBalance($movement, -1);
                    $movement->delete();
                });
        });
    }

    public function replacePishfactorMovements(Pishfactor $pishfactor, ?int $userId = null): void
    {
        DB::transaction(function () use ($pishfactor, $userId) {
            $this->deleteSourceMovements(Pishfactor::class, (int) $pishfactor->id);

            if (!$this->pishfactorShouldIssueStock($pishfactor)) {
                return;
            }

            $pishfactor->loadMissing('items.product');

            foreach ($pishfactor->items as $item) {
                if (!$item instanceof PishFactorItems || !$item->pr_id) {
                    continue;
                }

                $product = $item->product ?: Product::find($item->pr_id);
                $quantity = $this->pishfactorItemQuantity($item, $product);

                if ($quantity <= 0) {
                    continue;
                }

                $movement = InventoryMovement::create($this->costedMovement($this->normalizeMovement([
                    'tenant_id' => $pishfactor->tenant_id ?: $pishfactor->tenants_id,
                    'organization_id' => $pishfactor->organization_id,
                    'store_id' => $this->pishfactorItemStoreId($item, $product),
                    'warehouse_location_id' => 0,
                    'product_id' => $item->pr_id,
                    'source_id' => $pishfactor->id,
                    'source_type' => Pishfactor::class,
                    'movement_type' => 'sale',
                    'direction' => 'out',
                    'quantity' => $quantity,
                    'quantity_sub_unit' => (float) ($item->tedad ?: 0),
                    'unit' => $product ? $product->pr_unit : null,
                    'reference_no' => $pishfactor->invoiceID,
                    'occurred_at' => $pishfactor->recive_date_en ?: $pishfactor->updated_at ?: $pishfactor->created_at ?: now(),
                    'description' => 'خروج فروش فاکتور ' . $pishfactor->invoiceID,
                    'batch_no' => $item->batch_no,
                    'lot_no' => $item->lot_no,
                    'serial_no' => $item->serial_no,
                    'manufactured_at' => $item->manufactured_at,
                    'expiry_date' => $item->expiry_date,
                    'color' => $item->color,
                    'size' => $item->size,
                    'quality_grade' => $item->quality_grade,
                    'weight' => $item->weight,
                    'tracking_notes' => $item->tracking_notes,
                    'user_id' => $userId ?: $pishfactor->updated_by ?: $pishfactor->visitor_id,
                ])));

                $this->applyMovementToBalance($movement, 1);
                $this->applyMovementToTraceBalance($movement, 1);
            }
        });
    }

    public function removePishfactorMovements(Pishfactor $pishfactor): void
    {
        DB::transaction(function () use ($pishfactor) {
            $this->deleteSourceMovements(Pishfactor::class, (int) $pishfactor->id);
        });
    }

    public function removeSourceMovements(string $sourceType, int $sourceId): void
    {
        DB::transaction(function () use ($sourceType, $sourceId) {
            $this->deleteSourceMovements($sourceType, $sourceId);
        });
    }

    private function normalizeMovement(array $attributes): array
    {
        return array_merge([
            'tenant_id' => null,
            'organization_id' => null,
            'warehouse_location_id' => 0,
            'receipt_id' => null,
            'transfer_id' => null,
            'source_id' => null,
            'source_type' => null,
            'quantity_sub_unit' => 0,
            'unit_cost' => null,
            'total_cost' => null,
            'valuation_method' => 'weighted_average',
            'unit' => null,
            'reference_no' => null,
            'occurred_at' => now(),
            'description' => null,
            'batch_no' => null,
            'lot_no' => null,
            'serial_no' => null,
            'manufactured_at' => null,
            'expiry_date' => null,
            'color' => null,
            'size' => null,
            'quality_grade' => null,
            'weight' => null,
            'tracking_notes' => null,
            'trace_status' => 'available',
            'user_id' => null,
        ], $attributes);
    }

    private function costedMovement(array $attributes): array
    {
        $quantity = abs((float) ($attributes['quantity'] ?? 0));
        $unitCost = $this->money($attributes['unit_cost'] ?? null);
        $valuationMethod = $attributes['valuation_method'] ?: $this->productValuationMethod((int) ($attributes['product_id'] ?? 0));

        if ($valuationMethod === 'weighted_average' && ($attributes['direction'] ?? null) === 'out') {
            $unitCost = $this->currentUnitCost(
                (int) ($attributes['store_id'] ?? 0),
                (int) ($attributes['product_id'] ?? 0),
                (int) ($attributes['warehouse_location_id'] ?? 0)
            );
        }

        if ($unitCost <= 0) {
            $unitCost = $this->currentUnitCost(
                (int) ($attributes['store_id'] ?? 0),
                (int) ($attributes['product_id'] ?? 0),
                (int) ($attributes['warehouse_location_id'] ?? 0)
            );
        }

        if ($unitCost <= 0) {
            $unitCost = $this->productUnitCost((int) ($attributes['product_id'] ?? 0));
        }

        $attributes['unit_cost'] = round($unitCost, 2);
        $attributes['total_cost'] = round($this->money($attributes['total_cost'] ?? null) ?: $quantity * $unitCost, 2);
        $attributes['valuation_method'] = $valuationMethod ?: 'weighted_average';

        return $attributes;
    }

    private function applyMovementToBalance(InventoryMovement $movement, int $multiplier): void
    {
        $quantityDelta = (float) $movement->quantity * $this->directionSign($movement->direction) * $multiplier;
        $subUnitDelta = (float) $movement->quantity_sub_unit * $this->directionSign($movement->direction) * $multiplier;
        $costDelta = (float) $movement->total_cost * $this->directionSign($movement->direction) * $multiplier;

        $balance = InventoryBalance::firstOrNew([
            'tenant_id' => $movement->tenant_id,
            'store_id' => $movement->store_id,
            'warehouse_location_id' => $movement->warehouse_location_id ?: 0,
            'product_id' => $movement->product_id,
        ]);

        $balance->organization_id = $movement->organization_id ?: $balance->organization_id;
        $balance->quantity = (float) ($balance->quantity ?: 0) + $quantityDelta;
        $balance->quantity_sub_unit = (float) ($balance->quantity_sub_unit ?: 0) + $subUnitDelta;
        $balance->total_cost = round((float) ($balance->total_cost ?: 0) + $costDelta, 2);
        $balance->unit_cost = (float) $balance->quantity > 0 ? round((float) $balance->total_cost / (float) $balance->quantity, 2) : 0;
        $balance->reserved_quantity = $balance->reserved_quantity ?: 0;
        $balance->last_movement_at = $movement->occurred_at ?: now();
        $balance->last_costed_at = now();
        $balance->save();
    }

    private function applyMovementToTraceBalance(InventoryMovement $movement, int $multiplier): void
    {
        if (!$this->movementHasTrace($movement)) {
            return;
        }

        $quantityDelta = (float) $movement->quantity * $this->directionSign($movement->direction) * $multiplier;
        $subUnitDelta = (float) $movement->quantity_sub_unit * $this->directionSign($movement->direction) * $multiplier;
        $costDelta = (float) $movement->total_cost * $this->directionSign($movement->direction) * $multiplier;

        $traceBalance = InventoryTraceBalance::firstOrNew([
            'tenant_id' => $movement->tenant_id,
            'store_id' => $movement->store_id,
            'warehouse_location_id' => $movement->warehouse_location_id ?: 0,
            'product_id' => $movement->product_id,
            'batch_no' => $movement->batch_no,
            'lot_no' => $movement->lot_no,
            'serial_no' => $movement->serial_no,
            'manufactured_at' => $movement->manufactured_at,
            'expiry_date' => $movement->expiry_date,
            'color' => $movement->color,
            'size' => $movement->size,
            'quality_grade' => $movement->quality_grade,
        ]);

        $traceBalance->organization_id = $movement->organization_id ?: $traceBalance->organization_id;
        $traceBalance->weight = $movement->weight ?: $traceBalance->weight;
        $traceBalance->trace_status = $movement->trace_status ?: $traceBalance->trace_status ?: 'available';
        $traceBalance->quantity = (float) ($traceBalance->quantity ?: 0) + $quantityDelta;
        $traceBalance->quantity_sub_unit = (float) ($traceBalance->quantity_sub_unit ?: 0) + $subUnitDelta;
        $traceBalance->total_cost = round((float) ($traceBalance->total_cost ?: 0) + $costDelta, 2);
        $traceBalance->unit_cost = (float) $traceBalance->quantity > 0 ? round((float) $traceBalance->total_cost / (float) $traceBalance->quantity, 2) : 0;
        $traceBalance->reserved_quantity = $traceBalance->reserved_quantity ?: 0;
        $traceBalance->last_movement_at = $movement->occurred_at ?: now();
        $traceBalance->tracking_notes = $movement->tracking_notes ?: $traceBalance->tracking_notes;
        $traceBalance->save();
    }

    private function movementHasTrace(InventoryMovement $movement): bool
    {
        return (bool) ($movement->batch_no || $movement->lot_no || $movement->serial_no || $movement->expiry_date || $movement->color || $movement->size || $movement->quality_grade);
    }

    private function currentUnitCost(int $storeId, int $productId, int $locationId): float
    {
        if (!$storeId || !$productId) {
            return 0.0;
        }

        return (float) (InventoryBalance::where('store_id', $storeId)
            ->where('product_id', $productId)
            ->where('warehouse_location_id', $locationId)
            ->value('unit_cost') ?: 0);
    }

    private function productUnitCost(int $productId): float
    {
        if (!$productId) {
            return 0.0;
        }

        $product = Product::find($productId);

        return $product ? $this->money($product->price) : 0.0;
    }

    private function productValuationMethod(int $productId): string
    {
        if (!$productId) {
            return 'weighted_average';
        }

        return Product::where('id', $productId)->value('valuation_method') ?: 'weighted_average';
    }

    private function money($value): float
    {
        return (float) str_replace(',', '', (string) ($value ?? 0));
    }

    private function directionSign(string $direction): int
    {
        return $direction === 'out' ? -1 : 1;
    }

    private function receiptMovementType(Receipt $receipt, string $direction): string
    {
        if ($receipt->return_source_receipt_id) {
            return $direction === 'out' ? 'warehouse_return_out' : 'warehouse_return_in';
        }

        if ((int) $receipt->type === 6) {
            return $direction === 'out' ? 'transfer_out' : 'transfer_in';
        }

        if ((int) $receipt->type === 5) {
            return 'opening';
        }

        return $direction === 'out' ? 'issue' : 'receipt';
    }

    private function storeOrganizationId(int $storeId): ?int
    {
        $store = Store::find($storeId);

        if (!$store) {
            return null;
        }

        $decoded = json_decode((string) $store->organization_id, true);

        if (is_array($decoded)) {
            return (int) ($decoded[0] ?? 0) ?: null;
        }

        return $store->organization_id ? (int) $store->organization_id : null;
    }

    private function deleteSourceMovements(string $sourceType, int $sourceId): void
    {
        InventoryMovement::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->get()
            ->each(function (InventoryMovement $movement) {
                $this->applyMovementToBalance($movement, -1);
                $this->applyMovementToTraceBalance($movement, -1);
                $movement->delete();
            });
    }

    private function pishfactorShouldIssueStock(Pishfactor $pishfactor): bool
    {
        $policy = app(SettingService::class)->get('sales_inventory_policy', [
            'tenant_id' => $pishfactor->tenant_id ?: $pishfactor->tenants_id,
            'organization_id' => $pishfactor->organization_id,
        ], 'stock_based');

        if ($policy === 'preorder_supply') {
            return (int) $pishfactor->status === 4;
        }

        return in_array((int) $pishfactor->status, [1, 4], true) && (int) $pishfactor->step >= 2;
    }

    private function pishfactorItemQuantity(PishFactorItems $item, ?Product $product): float
    {
        $packItems = $product && (float) $product->pack_items > 0 ? (float) $product->pack_items : 1.0;

        return ((float) ($item->pack ?: 0) * $packItems) + (float) ($item->tedad ?: 0);
    }

    private function pishfactorItemStoreId(PishFactorItems $item, ?Product $product): int
    {
        if (!empty($item->store_id)) {
            return (int) $item->store_id;
        }

        if (!$product || empty($product->store_id)) {
            return 0;
        }

        $decoded = json_decode((string) $product->store_id, true);

        if (is_array($decoded)) {
            return (int) ($decoded[0] ?? 0);
        }

        return (int) $product->store_id;
    }
}

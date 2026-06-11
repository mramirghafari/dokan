<?php

namespace App\Services;

use App\Models\InventoryAdjustment;
use App\Models\InventoryAdjustmentItem;
use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\Store;
use App\Models\WarehouseLocation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryAdjustmentService
{
    public function __construct(private InventoryLedgerService $ledgerService) {}

    public function createDraft(array $payload, $user): InventoryAdjustment
    {
        return DB::transaction(function () use ($payload, $user) {
            $store = Store::findOrFail((int) $payload['store_id']);
            $items = collect($payload['items'] ?? [])
                ->filter(fn($item) => !empty($item['product_id']))
                ->values();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'حداقل یک کالا برای انبارگردانی وارد کنید.']);
            }

            $adjustment = InventoryAdjustment::create([
                'tenant_id' => $store->tenant_id ?: $store->tenants_id ?: ($user->tenant_id ?? null),
                'organization_id' => $this->storeOrganizationId($store),
                'store_id' => $store->id,
                'user_id' => $user->id,
                'number' => $this->nextNumber(),
                'date_fa' => Arr::get($payload, 'date_fa'),
                'date_en' => Arr::get($payload, 'date_en') ?: now()->toDateString(),
                'status' => 'draft',
                'notes' => Arr::get($payload, 'notes'),
            ]);

            foreach ($items as $item) {
                $product = Product::findOrFail((int) $item['product_id']);
                $locationId = (int) ($item['warehouse_location_id'] ?? 0);
                $this->assertLocationBelongsToStore($locationId, $store);

                $systemQuantity = $this->currentQuantity($store->id, $product->id, $locationId);
                $countedQuantity = $this->number($item['counted_quantity'] ?? 0);
                $unitCost = $this->number($item['unit_cost'] ?? $product->price ?? 0);
                $difference = round($countedQuantity - $systemQuantity, 3);

                InventoryAdjustmentItem::create([
                    'inventory_adjustment_id' => $adjustment->id,
                    'product_id' => $product->id,
                    'warehouse_location_id' => $locationId,
                    'system_quantity' => $systemQuantity,
                    'counted_quantity' => $countedQuantity,
                    'difference_quantity' => $difference,
                    'unit_cost' => $unitCost,
                    'amount' => round($difference * $unitCost, 2),
                    'description' => $item['description'] ?? null,
                ]);
            }

            return $adjustment->load(['items.product', 'store']);
        });
    }

    public function approve(InventoryAdjustment $adjustment, $user): InventoryAdjustment
    {
        return DB::transaction(function () use ($adjustment, $user) {
            $adjustment = $adjustment->fresh(['items.product', 'store']) ?: $adjustment;

            if ($adjustment->status === 'canceled') {
                throw ValidationException::withMessages(['status' => 'سند ابطال شده قابل تایید نیست.']);
            }

            if ($adjustment->status === 'approved') {
                return $adjustment;
            }

            foreach ($adjustment->items as $item) {
                $difference = round((float) $item->difference_quantity, 3);

                if ($difference == 0.0) {
                    continue;
                }

                $movement = $this->ledgerService->record([
                    'tenant_id' => $adjustment->tenant_id,
                    'organization_id' => $adjustment->organization_id,
                    'store_id' => $adjustment->store_id,
                    'warehouse_location_id' => $item->warehouse_location_id ?: 0,
                    'product_id' => $item->product_id,
                    'source_id' => $item->id,
                    'source_type' => InventoryAdjustmentItem::class,
                    'movement_type' => 'adjustment',
                    'direction' => $difference > 0 ? 'in' : 'out',
                    'quantity' => abs($difference),
                    'unit_cost' => $item->unit_cost,
                    'reference_no' => $adjustment->number,
                    'occurred_at' => $adjustment->date_en ?: now(),
                    'description' => $item->description ?: 'اصلاحیه انبارگردانی شماره ' . $adjustment->number,
                    'user_id' => $user->id,
                ]);

                $item->update(['movement_id' => $movement->id]);
            }

            $adjustment->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $user->id,
            ]);

            return $adjustment->fresh(['items.product', 'store']) ?: $adjustment;
        });
    }

    public function cancel(InventoryAdjustment $adjustment, $user): InventoryAdjustment
    {
        return DB::transaction(function () use ($adjustment, $user) {
            $adjustment = $adjustment->fresh(['items']) ?: $adjustment;

            if ($adjustment->status === 'canceled') {
                return $adjustment;
            }

            foreach ($adjustment->items as $item) {
                $this->ledgerService->removeSourceMovements(InventoryAdjustmentItem::class, (int) $item->id);
                $item->update(['movement_id' => null]);
            }

            $adjustment->update([
                'status' => 'canceled',
                'canceled_at' => now(),
                'canceled_by' => $user->id,
            ]);

            return $adjustment->fresh(['items.product', 'store']) ?: $adjustment;
        });
    }

    private function currentQuantity(int $storeId, int $productId, int $locationId): float
    {
        return (float) (InventoryBalance::where('store_id', $storeId)
            ->where('product_id', $productId)
            ->where('warehouse_location_id', $locationId)
            ->value('quantity') ?: 0);
    }

    private function assertLocationBelongsToStore(int $locationId, Store $store): void
    {
        if ($locationId === 0) {
            return;
        }

        $exists = WarehouseLocation::where('id', $locationId)
            ->where('store_id', $store->id)
            ->where('is_active', 1)
            ->exists();

        if (!$exists) {
            throw ValidationException::withMessages(['warehouse_location_id' => 'مکان انتخاب شده متعلق به این انبار نیست.']);
        }
    }

    private function storeOrganizationId(Store $store): ?int
    {
        $decoded = json_decode((string) $store->organization_id, true);

        if (is_array($decoded)) {
            return (int) ($decoded[0] ?? 0) ?: null;
        }

        return $store->organization_id ? (int) $store->organization_id : null;
    }

    private function nextNumber(): string
    {
        return 'ADJ-' . now()->format('Ymd-His') . '-' . random_int(100, 999);
    }

    private function number($value): float
    {
        return (float) str_replace(',', '', (string) $value);
    }
}

<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\ProductionFormula;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\Store;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionService
{
    public function __construct(
        private InventoryLedgerService $ledgerService,
        private AccountingPostingService $accountingService
    ) {}

    public function createExtractionOrder(array $payload, $user): ProductionOrder
    {
        return DB::transaction(function () use ($payload, $user) {
            $store = Store::findOrFail((int) $payload['store_id']);
            $material = Product::findOrFail((int) $payload['material_id']);
            $materialQuantity = $this->number($payload['entity'] ?? 0);

            if ($materialQuantity <= 0) {
                throw ValidationException::withMessages(['entity' => 'مقدار ماده اولیه باید بزرگتر از صفر باشد.']);
            }

            $outputs = $this->outputs($payload);

            if ($outputs->isEmpty()) {
                throw ValidationException::withMessages(['prs' => 'حداقل یک محصول تولیدی با مقدار معتبر وارد کنید.']);
            }

            $organizationId = $this->storeOrganizationId($store);
            $tenantId = $store->tenant_id ?: $store->tenants_id ?: ($user->tenant_id ?? null);

            $order = ProductionOrder::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'store_id' => $store->id,
                'user_id' => $user->id,
                'number' => $this->nextNumber(),
                'date_fa' => Arr::get($payload, 'date_fa'),
                'date_en' => Arr::get($payload, 'date_en') ?: now()->toDateString(),
                'status' => 'approved',
                'planned_quantity' => $materialQuantity,
                'actual_quantity' => $outputs->sum('quantity'),
                'approved_at' => now(),
                'approved_by' => $user->id,
                'notes' => Arr::get($payload, 'notes'),
            ]);

            $materialCost = $this->currentUnitCost($store->id, $material->id) ?: $this->number($material->price);
            $materialTotalCost = round($materialQuantity * $materialCost, 2);

            $materialLine = ProductionOrderItem::create([
                'production_order_id' => $order->id,
                'product_id' => $material->id,
                'warehouse_location_id' => 0,
                'line_type' => 'material',
                'quantity' => $materialQuantity,
                'unit_cost' => $materialCost,
                'total_cost' => $materialTotalCost,
                'description' => 'مصرف ماده اولیه تولید ' . $order->number,
            ]);

            $materialMovement = $this->ledgerService->record([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'store_id' => $store->id,
                'warehouse_location_id' => 0,
                'product_id' => $material->id,
                'source_id' => $materialLine->id,
                'source_type' => ProductionOrderItem::class,
                'movement_type' => 'production_material',
                'direction' => 'out',
                'quantity' => $materialQuantity,
                'unit_cost' => $materialCost,
                'reference_no' => $order->number,
                'occurred_at' => $order->date_en ?: now(),
                'description' => $materialLine->description,
                'user_id' => $user->id,
            ]);

            $materialLine->update([
                'movement_id' => $materialMovement->id,
                'unit_cost' => $materialMovement->unit_cost,
                'total_cost' => $materialMovement->total_cost,
            ]);

            $totalOutputQuantity = max($outputs->sum('quantity'), 0.001);
            $materialTotalCost = (float) $materialMovement->total_cost;

            foreach ($outputs as $output) {
                $product = Product::findOrFail((int) $output['product_id']);
                $quantity = (float) $output['quantity'];
                $lineCost = round($materialTotalCost * ($quantity / $totalOutputQuantity), 2);
                $unitCost = $quantity > 0 ? round($lineCost / $quantity, 2) : 0;

                $outputLine = ProductionOrderItem::create([
                    'production_order_id' => $order->id,
                    'product_id' => $product->id,
                    'warehouse_location_id' => 0,
                    'line_type' => 'finished_good',
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $lineCost,
                    'description' => 'رسید محصول تولید ' . $order->number,
                ]);

                $movement = $this->ledgerService->record([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'store_id' => $store->id,
                    'warehouse_location_id' => 0,
                    'product_id' => $product->id,
                    'source_id' => $outputLine->id,
                    'source_type' => ProductionOrderItem::class,
                    'movement_type' => 'production_receipt',
                    'direction' => 'in',
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'reference_no' => $order->number,
                    'occurred_at' => $order->date_en ?: now(),
                    'description' => $outputLine->description,
                    'user_id' => $user->id,
                ]);

                $outputLine->update([
                    'movement_id' => $movement->id,
                    'unit_cost' => $movement->unit_cost,
                    'total_cost' => $movement->total_cost,
                ]);
            }

            $order->update([
                'material_cost' => $materialTotalCost,
                'finished_unit_cost' => $order->actual_quantity > 0 ? round($materialTotalCost / (float) $order->actual_quantity, 2) : 0,
            ]);

            $this->accountingService->postProductionVoucher($order, $user);

            return $order->fresh(['items.product', 'store']) ?: $order;
        });
    }

    public function createFormulaOrder(array $payload, $user): ProductionOrder
    {
        return DB::transaction(function () use ($payload, $user) {
            $store = Store::findOrFail((int) $payload['store_id']);
            $formula = ProductionFormula::with(['product', 'items.materialProduct'])->findOrFail((int) $payload['production_formula_id']);
            $actualQuantity = $this->number($payload['actual_quantity'] ?? 0);

            if ($actualQuantity <= 0) {
                throw ValidationException::withMessages(['actual_quantity' => 'مقدار تولید باید بزرگتر از صفر باشد.']);
            }

            if (!$formula->is_active) {
                throw ValidationException::withMessages(['production_formula_id' => 'فرمول انتخاب شده غیرفعال است.']);
            }

            if ($formula->items->isEmpty()) {
                throw ValidationException::withMessages(['production_formula_id' => 'فرمول انتخاب شده مواد اولیه ندارد.']);
            }

            $organizationId = $formula->organization_id ?: $this->storeOrganizationId($store);
            $tenantId = $formula->tenant_id ?: $store->tenant_id ?: $store->tenants_id ?: ($user->tenant_id ?? null);
            $baseQuantity = max($this->number($formula->base_quantity), 0.001);

            $order = ProductionOrder::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'store_id' => $store->id,
                'production_formula_id' => $formula->id,
                'user_id' => $user->id,
                'number' => $this->nextNumber(),
                'date_fa' => Arr::get($payload, 'date_fa'),
                'date_en' => Arr::get($payload, 'date_en') ?: now()->toDateString(),
                'status' => 'approved',
                'planned_quantity' => $actualQuantity,
                'actual_quantity' => $actualQuantity,
                'approved_at' => now(),
                'approved_by' => $user->id,
                'notes' => Arr::get($payload, 'notes'),
            ]);

            $materialTotalCost = 0;

            foreach ($formula->items as $formulaItem) {
                $materialStoreId = (int) ($formulaItem->store_id ?: $store->id);
                $wastePercent = $this->number($formula->standard_waste_percent) + $this->number($formulaItem->waste_percent);
                $materialQuantity = round(($this->number($formulaItem->quantity) / $baseQuantity) * $actualQuantity * (1 + ($wastePercent / 100)), 3);

                if ($materialQuantity <= 0) {
                    continue;
                }

                $materialCost = $this->currentUnitCost($materialStoreId, (int) $formulaItem->material_product_id) ?: $this->number($formulaItem->materialProduct?->price);
                $lineTotalCost = round($materialQuantity * $materialCost, 2);

                $materialLine = ProductionOrderItem::create([
                    'production_order_id' => $order->id,
                    'product_id' => $formulaItem->material_product_id,
                    'warehouse_location_id' => 0,
                    'line_type' => 'material',
                    'quantity' => $materialQuantity,
                    'unit_cost' => $materialCost,
                    'total_cost' => $lineTotalCost,
                    'description' => 'مصرف ماده اولیه از فرمول ' . $formula->title . ' - تولید ' . $order->number,
                ]);

                $movement = $this->ledgerService->record([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'store_id' => $materialStoreId,
                    'warehouse_location_id' => 0,
                    'product_id' => $formulaItem->material_product_id,
                    'source_id' => $materialLine->id,
                    'source_type' => ProductionOrderItem::class,
                    'movement_type' => 'production_material',
                    'direction' => 'out',
                    'quantity' => $materialQuantity,
                    'unit_cost' => $materialCost,
                    'reference_no' => $order->number,
                    'occurred_at' => $order->date_en ?: now(),
                    'description' => $materialLine->description,
                    'user_id' => $user->id,
                ]);

                $materialLine->update([
                    'movement_id' => $movement->id,
                    'unit_cost' => $movement->unit_cost,
                    'total_cost' => $movement->total_cost,
                ]);

                $materialTotalCost += (float) $movement->total_cost;
            }

            if ($materialTotalCost <= 0) {
                throw ValidationException::withMessages(['production_formula_id' => 'هیچ مصرف معتبری برای فرمول انتخاب شده محاسبه نشد.']);
            }

            $finishedUnitCost = round($materialTotalCost / $actualQuantity, 2);
            $outputLine = ProductionOrderItem::create([
                'production_order_id' => $order->id,
                'product_id' => $formula->product_id,
                'warehouse_location_id' => 0,
                'line_type' => 'finished_good',
                'quantity' => $actualQuantity,
                'unit_cost' => $finishedUnitCost,
                'total_cost' => round($materialTotalCost, 2),
                'description' => 'رسید محصول از فرمول ' . $formula->title . ' - تولید ' . $order->number,
            ]);

            $movement = $this->ledgerService->record([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'store_id' => $store->id,
                'warehouse_location_id' => 0,
                'product_id' => $formula->product_id,
                'source_id' => $outputLine->id,
                'source_type' => ProductionOrderItem::class,
                'movement_type' => 'production_receipt',
                'direction' => 'in',
                'quantity' => $actualQuantity,
                'unit_cost' => $finishedUnitCost,
                'reference_no' => $order->number,
                'occurred_at' => $order->date_en ?: now(),
                'description' => $outputLine->description,
                'user_id' => $user->id,
            ]);

            $outputLine->update([
                'movement_id' => $movement->id,
                'unit_cost' => $movement->unit_cost,
                'total_cost' => $movement->total_cost,
            ]);

            $order->update([
                'material_cost' => round($materialTotalCost, 2),
                'finished_unit_cost' => $finishedUnitCost,
            ]);

            $this->accountingService->postProductionVoucher($order, $user);

            return $order->fresh(['items.product', 'store', 'formula.product']) ?: $order;
        });
    }

    public function cancel(ProductionOrder $order, $user): ProductionOrder
    {
        return DB::transaction(function () use ($order, $user) {
            $order = $order->fresh(['items']) ?: $order;

            if ($order->status === 'canceled') {
                return $order;
            }

            foreach ($order->items as $item) {
                $this->ledgerService->removeSourceMovements(ProductionOrderItem::class, (int) $item->id);
                $item->update(['movement_id' => null]);
            }

            $this->accountingService->removeProductionVoucher($order);

            $order->update([
                'status' => 'canceled',
                'canceled_at' => now(),
                'canceled_by' => $user->id,
            ]);

            return $order->fresh(['items.product', 'store']) ?: $order;
        });
    }

    private function outputs(array $payload)
    {
        $products = $payload['prs'] ?? [];
        $extracted = $payload['estehsal'] ?? [];
        $flavored = $payload['taamdar'] ?? [];

        return collect($products)->map(function ($productId, $index) use ($extracted, $flavored) {
            $quantity = $this->number($extracted[$index] ?? 0) + $this->number($flavored[$index] ?? 0);

            return [
                'product_id' => $productId,
                'quantity' => $quantity,
            ];
        })->filter(fn($row) => !empty($row['product_id']) && $row['quantity'] > 0)->values();
    }

    private function currentUnitCost(int $storeId, int $productId): float
    {
        return (float) (InventoryBalance::where('store_id', $storeId)
            ->where('product_id', $productId)
            ->where('warehouse_location_id', 0)
            ->value('unit_cost') ?: 0);
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
        return 'PRD-' . now()->format('Ymd-His') . '-' . random_int(100, 999);
    }

    private function number($value): float
    {
        return (float) str_replace(',', '', (string) ($value ?? 0));
    }
}

<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductionFormulaItem;
use App\Models\ProductionOrderItem;
use App\Models\PurchaseOrderItem;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\WarehouseLocation;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InventoryValuationReportService
{
    public function build($user, array $filters = []): array
    {
        $fromDate = Carbon::parse($filters['from_date'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $toDate = Carbon::parse($filters['to_date'] ?? now()->toDateString())->endOfDay();

        $openingRows = $this->movementAggregate($user, $filters)
            ->where('occurred_at', '<', $fromDate)
            ->get()
            ->keyBy(fn($row) => $this->rowKey($row));

        $periodRows = $this->movementAggregate($user, $filters)
            ->whereBetween('occurred_at', [$fromDate, $toDate])
            ->selectRaw("SUM(CASE WHEN direction = 'in' THEN quantity ELSE 0 END) as in_quantity")
            ->selectRaw("SUM(CASE WHEN direction = 'in' THEN total_cost ELSE 0 END) as in_cost")
            ->selectRaw("SUM(CASE WHEN direction = 'out' THEN quantity ELSE 0 END) as out_quantity")
            ->selectRaw("SUM(CASE WHEN direction = 'out' THEN total_cost ELSE 0 END) as out_cost")
            ->get()
            ->keyBy(fn($row) => $this->rowKey($row));

        $currentRows = $this->currentBalances($user, $filters)->keyBy(fn($row) => $this->rowKey($row));
        $keys = collect($openingRows->keys())->merge($periodRows->keys())->merge($currentRows->keys())->unique()->values();
        $maps = $this->lookupMaps($keys, $openingRows, $periodRows, $currentRows);

        $rows = $keys->map(function ($key) use ($openingRows, $periodRows, $currentRows, $maps) {
            $opening = $openingRows->get($key);
            $period = $periodRows->get($key);
            $current = $currentRows->get($key);

            $openingQuantity = (float) ($opening->net_quantity ?? 0);
            $openingCost = (float) ($opening->net_cost ?? 0);
            $inQuantity = (float) ($period->in_quantity ?? 0);
            $inCost = (float) ($period->in_cost ?? 0);
            $outQuantity = (float) ($period->out_quantity ?? 0);
            $outCost = (float) ($period->out_cost ?? 0);
            $endingQuantity = $openingQuantity + $inQuantity - $outQuantity;
            $endingCost = round($openingCost + $inCost - $outCost, 2);
            $currentQuantity = (float) ($current->quantity ?? 0);
            $currentCost = (float) ($current->total_cost ?? 0);

            [$storeId, $locationId, $productId] = array_map('intval', explode(':', $key));

            return [
                'store_id' => $storeId,
                'warehouse_location_id' => $locationId,
                'product_id' => $productId,
                'store_title' => $maps['stores'][$storeId]->title ?? '-',
                'location_title' => $locationId > 0 ? ($maps['locations'][$locationId]->path ?? $maps['locations'][$locationId]->title ?? '-') : 'بدون مکان',
                'product_title' => trim(($maps['products'][$productId]->title ?? '-') . ' ' . ($maps['products'][$productId]->display_name ?? '')),
                'opening_quantity' => $openingQuantity,
                'opening_cost' => $openingCost,
                'in_quantity' => $inQuantity,
                'in_cost' => $inCost,
                'out_quantity' => $outQuantity,
                'out_cost' => $outCost,
                'ending_quantity' => $endingQuantity,
                'ending_cost' => $endingCost,
                'ending_unit_cost' => $endingQuantity > 0 ? round($endingCost / $endingQuantity, 2) : 0,
                'current_quantity' => $currentQuantity,
                'current_cost' => $currentCost,
                'quantity_variance' => round($currentQuantity - $endingQuantity, 3),
                'cost_variance' => round($currentCost - $endingCost, 2),
            ];
        })->sortBy([['store_title', 'asc'], ['product_title', 'asc']])->values();

        return [
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'rows' => $rows,
            'totals' => $this->totals($rows),
            'cardex' => $this->runningCardex($user, $filters, $fromDate, $toDate),
            'variance' => $this->varianceReport($user, $filters, $fromDate, $toDate),
        ];
    }

    private function varianceReport($user, array $filters, Carbon $fromDate, Carbon $toDate): array
    {
        $purchaseRows = $this->purchasePriceVarianceRows($user, $filters, $fromDate, $toDate);
        $materialRows = $this->materialConsumptionVarianceRows($user, $filters, $fromDate, $toDate);

        return [
            'purchase_price_rows' => $purchaseRows,
            'material_consumption_rows' => $materialRows,
            'totals' => [
                'purchase_variance_amount' => round((float) $purchaseRows->sum('variance_amount'), 2),
                'material_quantity_variance' => round((float) $materialRows->sum('quantity_variance'), 3),
                'material_variance_amount' => round((float) $materialRows->sum('variance_amount'), 2),
            ],
        ];
    }

    private function purchasePriceVarianceRows($user, array $filters, Carbon $fromDate, Carbon $toDate): Collection
    {
        $quantityExpression = 'CASE WHEN purchase_order_items.received_quantity > 0 THEN purchase_order_items.received_quantity ELSE purchase_order_items.quantity END';

        $query = PurchaseOrderItem::query()
            ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->whereNull('purchase_order_items.deleted_at')
            ->whereNull('purchase_orders.deleted_at')
            ->whereDate('purchase_orders.order_date_en', '>=', $fromDate->toDateString())
            ->whereDate('purchase_orders.order_date_en', '<=', $toDate->toDateString())
            ->when(!empty($filters['store_id']), fn($query) => $query->where('purchase_orders.store_id', $filters['store_id']))
            ->when(!empty($filters['product_id']), fn($query) => $query->where('purchase_order_items.product_id', $filters['product_id']))
            ->select('purchase_order_items.product_id', 'purchase_orders.supplier_id', 'purchase_orders.store_id')
            ->selectRaw("SUM($quantityExpression) as quantity")
            ->selectRaw("SUM(CASE WHEN purchase_order_items.total_amount > 0 THEN purchase_order_items.total_amount ELSE ($quantityExpression) * purchase_order_items.unit_price END) as actual_amount")
            ->groupBy('purchase_order_items.product_id', 'purchase_orders.supplier_id', 'purchase_orders.store_id');

        if ((int) $user?->isGod !== 1) {
            $query->where('purchase_orders.tenant_id', $this->tenantId($user));
        }

        $rows = $query->get();
        $products = Product::whereIn('id', $rows->pluck('product_id')->filter()->unique())->get()->keyBy('id');
        $suppliers = Supplier::whereIn('id', $rows->pluck('supplier_id')->filter()->unique())->get()->keyBy('id');
        $stores = Store::whereIn('id', $rows->pluck('store_id')->filter()->unique())->get()->keyBy('id');

        return $rows->map(function ($row) use ($products, $suppliers, $stores) {
            $quantity = (float) $row->quantity;
            $actualAmount = round((float) $row->actual_amount, 2);
            $actualUnitPrice = $quantity > 0 ? round($actualAmount / $quantity, 2) : 0;
            $product = $products->get($row->product_id);
            $referenceUnitPrice = $this->referenceProductCost($product);
            $referenceAmount = round($quantity * $referenceUnitPrice, 2);
            $varianceAmount = round($actualAmount - $referenceAmount, 2);

            return [
                'product_id' => (int) $row->product_id,
                'product_title' => $this->productTitle($product),
                'supplier_id' => $row->supplier_id ? (int) $row->supplier_id : null,
                'supplier_title' => $row->supplier_id ? ($suppliers->get($row->supplier_id)?->title ?: $suppliers->get($row->supplier_id)?->name ?: 'تامین کننده حذف شده') : 'بدون تامین کننده',
                'store_title' => $stores->get($row->store_id)?->title ?: '-',
                'quantity' => round($quantity, 3),
                'actual_unit_price' => $actualUnitPrice,
                'reference_unit_price' => $referenceUnitPrice,
                'actual_amount' => $actualAmount,
                'reference_amount' => $referenceAmount,
                'variance_amount' => $varianceAmount,
                'variance_percent' => $referenceAmount > 0 ? round(($varianceAmount / $referenceAmount) * 100, 2) : null,
            ];
        })
            ->filter(fn($row) => round(abs((float) $row['actual_amount']) + abs((float) $row['reference_amount']), 2) > 0)
            ->sortByDesc(fn($row) => abs((float) $row['variance_amount']))
            ->values();
    }

    private function materialConsumptionVarianceRows($user, array $filters, Carbon $fromDate, Carbon $toDate): Collection
    {
        $query = ProductionOrderItem::query()
            ->with(['order', 'product'])
            ->where('line_type', 'material')
            ->whereHas('order', function ($query) use ($user, $filters, $fromDate, $toDate) {
                $query->whereNull('production_orders.deleted_at')
                    ->where('production_orders.status', '<>', 'canceled')
                    ->whereDate('production_orders.date_en', '>=', $fromDate->toDateString())
                    ->whereDate('production_orders.date_en', '<=', $toDate->toDateString())
                    ->when(!empty($filters['store_id']), fn($query) => $query->where('production_orders.store_id', $filters['store_id']));

                if ((int) $user?->isGod !== 1) {
                    $query->where('production_orders.tenant_id', $this->tenantId($user));
                }
            })
            ->when(!empty($filters['product_id']), fn($query) => $query->where('product_id', $filters['product_id']));

        $items = $query->get();
        $formulaItems = $this->formulaItemsFor($items);

        return $items->map(function (ProductionOrderItem $item) use ($formulaItems) {
            $order = $item->order;
            $formulaItem = $order?->production_formula_id ? $formulaItems->get($order->production_formula_id . ':' . $item->product_id) : null;
            $actualQuantity = (float) $item->quantity;
            $actualUnitCost = (float) $item->unit_cost;
            $expectedQuantity = $this->expectedMaterialQuantity($item, $formulaItem);
            $quantityVariance = round($actualQuantity - $expectedQuantity, 3);
            $varianceAmount = round($quantityVariance * $actualUnitCost, 2);

            return [
                'production_order_id' => (int) $item->production_order_id,
                'production_number' => $order?->number ?: '-',
                'product_id' => (int) $item->product_id,
                'product_title' => $this->productTitle($item->product),
                'actual_quantity' => round($actualQuantity, 3),
                'expected_quantity' => round($expectedQuantity, 3),
                'quantity_variance' => $quantityVariance,
                'actual_unit_cost' => round($actualUnitCost, 2),
                'variance_amount' => $varianceAmount,
                'reference_source' => $formulaItem ? 'BOM' : 'برنامه تولید',
            ];
        })
            ->filter(fn($row) => round(abs((float) $row['actual_quantity']) + abs((float) $row['expected_quantity']), 3) > 0)
            ->sortByDesc(fn($row) => abs((float) $row['variance_amount']))
            ->values();
    }

    private function formulaItemsFor(Collection $items): Collection
    {
        $formulaIds = $items->pluck('order.production_formula_id')->filter()->unique()->values();

        if ($formulaIds->isEmpty()) {
            return collect();
        }

        return ProductionFormulaItem::with('formula')
            ->whereIn('production_formula_id', $formulaIds)
            ->get()
            ->keyBy(fn($item) => $item->production_formula_id . ':' . $item->material_product_id);
    }

    private function expectedMaterialQuantity(ProductionOrderItem $item, ?ProductionFormulaItem $formulaItem): float
    {
        if ($formulaItem && $formulaItem->formula) {
            $baseQuantity = max((float) $formulaItem->formula->base_quantity, 0.001);
            $productionQuantity = max((float) ($item->order?->actual_quantity ?: $item->order?->planned_quantity ?: 0), 0);
            $wasteMultiplier = 1 + ((float) $formulaItem->waste_percent / 100);

            return round((((float) $formulaItem->quantity / $baseQuantity) * $productionQuantity) * $wasteMultiplier, 3);
        }

        return round((float) ($item->order?->planned_quantity ?: $item->quantity), 3);
    }

    private function movementAggregate($user, array $filters)
    {
        return $this->movementBase($user, $filters)
            ->select('store_id', 'warehouse_location_id', 'product_id')
            ->selectRaw("SUM(CASE WHEN direction = 'in' THEN quantity ELSE -quantity END) as net_quantity")
            ->selectRaw("SUM(CASE WHEN direction = 'in' THEN total_cost ELSE -total_cost END) as net_cost")
            ->groupBy('store_id', 'warehouse_location_id', 'product_id');
    }

    private function movementBase($user, array $filters)
    {
        return InventoryMovement::query()
            ->forOrganizations($user)
            ->when(!empty($filters['store_id']), fn($query) => $query->where('store_id', $filters['store_id']))
            ->when(!empty($filters['product_id']), fn($query) => $query->where('product_id', $filters['product_id']))
            ->when(isset($filters['warehouse_location_id']) && $filters['warehouse_location_id'] !== '', fn($query) => $query->where('warehouse_location_id', $filters['warehouse_location_id']));
    }

    private function currentBalances($user, array $filters): Collection
    {
        return InventoryBalance::query()
            ->forOrganizations($user)
            ->when(!empty($filters['store_id']), fn($query) => $query->where('store_id', $filters['store_id']))
            ->when(!empty($filters['product_id']), fn($query) => $query->where('product_id', $filters['product_id']))
            ->when(isset($filters['warehouse_location_id']) && $filters['warehouse_location_id'] !== '', fn($query) => $query->where('warehouse_location_id', $filters['warehouse_location_id']))
            ->get(['store_id', 'warehouse_location_id', 'product_id', 'quantity', 'total_cost']);
    }

    private function runningCardex($user, array $filters, Carbon $fromDate, Carbon $toDate): Collection
    {
        if (empty($filters['product_id'])) {
            return collect();
        }

        $opening = $this->movementBase($user, $filters)
            ->where('occurred_at', '<', $fromDate)
            ->selectRaw("SUM(CASE WHEN direction = 'in' THEN quantity ELSE -quantity END) as quantity")
            ->selectRaw("SUM(CASE WHEN direction = 'in' THEN total_cost ELSE -total_cost END) as cost")
            ->first();

        $runningQuantity = (float) ($opening->quantity ?? 0);
        $runningCost = (float) ($opening->cost ?? 0);

        return $this->movementBase($user, $filters)
            ->with(['store', 'warehouseLocation'])
            ->whereBetween('occurred_at', [$fromDate, $toDate])
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get()
            ->map(function (InventoryMovement $movement) use (&$runningQuantity, &$runningCost) {
                $sign = $movement->direction === 'out' ? -1 : 1;
                $runningQuantity += (float) $movement->quantity * $sign;
                $runningCost += (float) $movement->total_cost * $sign;

                return [
                    'occurred_at' => $movement->occurred_at,
                    'store_title' => optional($movement->store)->title ?: '-',
                    'location_title' => optional($movement->warehouseLocation)->path ?: 'بدون مکان',
                    'movement_type' => $movement->movement_type,
                    'reference_no' => $movement->reference_no,
                    'direction' => $movement->direction,
                    'quantity' => (float) $movement->quantity,
                    'unit_cost' => (float) $movement->unit_cost,
                    'total_cost' => (float) $movement->total_cost,
                    'running_quantity' => round($runningQuantity, 3),
                    'running_cost' => round($runningCost, 2),
                    'running_unit_cost' => $runningQuantity > 0 ? round($runningCost / $runningQuantity, 2) : 0,
                ];
            });
    }

    private function lookupMaps(Collection $keys, Collection $openingRows, Collection $periodRows, Collection $currentRows): array
    {
        $rows = collect([$openingRows, $periodRows, $currentRows])->flatMap(fn($collection) => $collection->values());

        return [
            'stores' => Store::whereIn('id', $rows->pluck('store_id')->filter()->unique())->get()->keyBy('id'),
            'locations' => WarehouseLocation::whereIn('id', $rows->pluck('warehouse_location_id')->filter()->unique())->get()->keyBy('id'),
            'products' => Product::whereIn('id', $rows->pluck('product_id')->filter()->unique())->get()->keyBy('id'),
        ];
    }

    private function totals(Collection $rows): array
    {
        return [
            'opening_cost' => $rows->sum('opening_cost'),
            'in_cost' => $rows->sum('in_cost'),
            'out_cost' => $rows->sum('out_cost'),
            'ending_cost' => $rows->sum('ending_cost'),
            'current_cost' => $rows->sum('current_cost'),
            'cost_variance' => $rows->sum('cost_variance'),
        ];
    }

    private function referenceProductCost(?Product $product): float
    {
        if (!$product) {
            return 0.0;
        }

        foreach (['purchase_price', 'cost_price', 'price'] as $field) {
            $value = (float) ($product->{$field} ?: 0);

            if ($value > 0) {
                return round($value, 2);
            }
        }

        return 0.0;
    }

    private function productTitle(?Product $product): string
    {
        if (!$product) {
            return 'کالای حذف شده';
        }

        return trim((string) (($product->title ?: $product->display_name ?: $product->sku ?: $product->id) . ' ' . ($product->display_name && $product->display_name !== $product->title ? $product->display_name : '')));
    }

    private function tenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }

    private function rowKey($row): string
    {
        return (int) $row->store_id . ':' . (int) $row->warehouse_location_id . ':' . (int) $row->product_id;
    }
}

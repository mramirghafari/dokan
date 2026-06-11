<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InventorySlowMovingReportService
{
    public function build($user, array $filters = []): array
    {
        $fromDate = Carbon::parse($filters['from_date'] ?? now()->subDays(90)->toDateString())->startOfDay();
        $toDate = Carbon::parse($filters['to_date'] ?? now()->toDateString())->endOfDay();
        $status = $filters['status'] ?? 'at_risk';
        $slowThreshold = max(0, (float) ($filters['slow_threshold'] ?? 3));

        $movementRows = $this->movementAggregate($user, $filters, $fromDate, $toDate);
        $balances = $this->balances($user, $filters);

        $rows = $balances->map(function ($balance) use ($movementRows, $slowThreshold) {
            $key = $this->rowKey($balance);
            $movement = $movementRows->get($key);
            $effectiveOutQuantity = (float) ($movement->effective_out_quantity ?? 0);
            $salesQuantity = (float) ($movement->sales_quantity ?? 0);
            $salesCost = (float) ($movement->sales_cost ?? 0);
            $quantity = (float) $balance->quantity;
            $stockValue = (float) ($balance->total_cost ?: ($quantity * (float) $balance->unit_cost));

            return [
                'balance_id' => $balance->id,
                'store_id' => $balance->store_id,
                'warehouse_location_id' => $balance->warehouse_location_id,
                'product_id' => $balance->product_id,
                'store_title' => $balance->store?->title ?: '-',
                'location_title' => $balance->warehouseLocation?->path ?: 'بدون مکان',
                'product_title' => trim(($balance->product?->title ?: '-') . ' ' . ($balance->product?->display_name ?: '')),
                'current_quantity' => round($quantity, 3),
                'reserved_quantity' => round((float) $balance->reserved_quantity, 3),
                'available_quantity' => round($quantity - (float) $balance->reserved_quantity, 3),
                'unit_cost' => round((float) ($balance->unit_cost ?: $balance->product?->cost_price ?: $balance->product?->price ?: 0), 2),
                'stock_value' => round($stockValue, 2),
                'sales_quantity' => round($salesQuantity, 3),
                'effective_out_quantity' => round($effectiveOutQuantity, 3),
                'sales_cost' => round($salesCost, 2),
                'turnover_ratio' => $quantity > 0 ? round($effectiveOutQuantity / $quantity, 4) : 0,
                'last_movement_at' => $balance->last_movement_at,
                'status' => $this->status($quantity, $effectiveOutQuantity, $slowThreshold),
            ];
        })
            ->filter(fn($row) => $this->matchesStatus($row, $status))
            ->sortBy([['status', 'asc'], ['stock_value', 'desc']])
            ->values();

        return [
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'slow_threshold' => $slowThreshold,
            'status' => $status,
            'rows' => $rows,
            'summary' => $this->summary($rows),
        ];
    }

    private function movementAggregate($user, array $filters, Carbon $fromDate, Carbon $toDate): Collection
    {
        return InventoryMovement::query()
            ->forOrganizations($user)
            ->when(!empty($filters['store_id']), fn($query) => $query->where('store_id', $filters['store_id']))
            ->when(!empty($filters['product_id']), fn($query) => $query->where('product_id', $filters['product_id']))
            ->whereBetween('occurred_at', [$fromDate, $toDate])
            ->where('direction', 'out')
            ->select('store_id', 'warehouse_location_id', 'product_id')
            ->selectRaw("SUM(CASE WHEN movement_type = 'sale' THEN quantity ELSE 0 END) as sales_quantity")
            ->selectRaw("SUM(CASE WHEN movement_type = 'sale' THEN total_cost ELSE 0 END) as sales_cost")
            ->selectRaw("SUM(CASE WHEN movement_type IN ('sale', 'issue') THEN quantity ELSE 0 END) as effective_out_quantity")
            ->groupBy('store_id', 'warehouse_location_id', 'product_id')
            ->get()
            ->keyBy(fn($row) => $this->rowKey($row));
    }

    private function balances($user, array $filters): Collection
    {
        return InventoryBalance::query()
            ->with(['product', 'store', 'warehouseLocation'])
            ->forOrganizations($user)
            ->when(!empty($filters['store_id']), fn($query) => $query->where('store_id', $filters['store_id']))
            ->when(!empty($filters['product_id']), fn($query) => $query->where('product_id', $filters['product_id']))
            ->where('quantity', '>', 0)
            ->orderBy('store_id')
            ->orderBy('product_id')
            ->get();
    }

    private function status(float $quantity, float $effectiveOutQuantity, float $slowThreshold): string
    {
        if ($quantity <= 0) {
            return 'empty';
        }

        if ($effectiveOutQuantity <= 0) {
            return 'stagnant';
        }

        if ($effectiveOutQuantity <= $slowThreshold) {
            return 'slow';
        }

        return 'moving';
    }

    private function matchesStatus(array $row, string $status): bool
    {
        return match ($status) {
            'all' => true,
            'stagnant' => $row['status'] === 'stagnant',
            'slow' => $row['status'] === 'slow',
            'moving' => $row['status'] === 'moving',
            'at_risk' => in_array($row['status'], ['stagnant', 'slow'], true),
            default => true,
        };
    }

    private function summary(Collection $rows): array
    {
        return [
            'rows' => $rows->count(),
            'stagnant' => $rows->where('status', 'stagnant')->count(),
            'slow' => $rows->where('status', 'slow')->count(),
            'moving' => $rows->where('status', 'moving')->count(),
            'current_quantity' => round((float) $rows->sum('current_quantity'), 3),
            'stock_value' => round((float) $rows->sum('stock_value'), 2),
            'effective_out_quantity' => round((float) $rows->sum('effective_out_quantity'), 3),
        ];
    }

    private function rowKey($row): string
    {
        return (int) $row->store_id . ':' . (int) $row->warehouse_location_id . ':' . (int) $row->product_id;
    }
}

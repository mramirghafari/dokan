<?php

namespace App\Services;

use App\Models\InventoryBalance;
use Illuminate\Support\Collection;

class InventoryReorderReportService
{
    public function build($user, array $filters = []): array
    {
        $status = $filters['status'] ?? 'needs_order';

        $balances = InventoryBalance::query()
            ->with(['product', 'store', 'warehouseLocation'])
            ->forOrganizations($user)
            ->when(!empty($filters['store_id']), fn($query) => $query->where('store_id', $filters['store_id']))
            ->when(!empty($filters['product_id']), fn($query) => $query->where('product_id', $filters['product_id']))
            ->orderBy('store_id')
            ->orderBy('product_id')
            ->get();

        $rows = $balances->map(fn($balance) => $this->row($balance))
            ->filter(fn($row) => $this->matchesStatus($row, $status))
            ->values();

        return [
            'rows' => $rows,
            'summary' => $this->summary($rows),
            'status' => $status,
        ];
    }

    private function row($balance): array
    {
        $quantity = round((float) $balance->quantity, 3);
        $reserved = round((float) $balance->reserved_quantity, 3);
        $available = round($quantity - $reserved, 3);
        $minimum = $this->threshold($balance->minimum_quantity, $balance->product?->orderLimit);
        $maximum = $this->threshold($balance->maximum_quantity, null);
        $target = $maximum > $minimum ? $maximum : ($minimum > 0 ? $minimum * 2 : 0);
        $shortage = $minimum > 0 ? max(0, round($minimum - $available, 3)) : ($available < 0 ? abs($available) : 0);
        $suggestedQuantity = $shortage > 0 ? max($shortage, round($target - $available, 3)) : 0;
        $unitCost = (float) ($balance->unit_cost ?: $balance->product?->price ?: 0);

        return [
            'balance_id' => $balance->id,
            'store_id' => $balance->store_id,
            'warehouse_location_id' => $balance->warehouse_location_id,
            'reorder_key' => $balance->id . ':' . $balance->store_id . ':' . $balance->product_id,
            'store_title' => $balance->store?->title ?: '-',
            'location_title' => $balance->warehouseLocation?->path ?: 'بدون مکان',
            'product_id' => $balance->product_id,
            'product_title' => trim(($balance->product?->title ?: '-') . ' ' . ($balance->product?->display_name ?: '')),
            'quantity' => $quantity,
            'reserved_quantity' => $reserved,
            'available_quantity' => $available,
            'minimum_quantity' => $minimum,
            'maximum_quantity' => $maximum,
            'shortage_quantity' => $shortage,
            'suggested_quantity' => $suggestedQuantity,
            'unit_cost' => $unitCost,
            'suggested_cost' => round($suggestedQuantity * $unitCost, 2),
            'last_movement_at' => $balance->last_movement_at,
            'status' => $this->status($available, $minimum, $shortage),
        ];
    }

    private function threshold($primary, $fallback): float
    {
        $value = (float) ($primary ?: $fallback ?: 0);

        return round(max(0, $value), 3);
    }

    private function status(float $available, float $minimum, float $shortage): string
    {
        if ($available < 0) {
            return 'negative';
        }

        if ($available == 0.0) {
            return 'out_of_stock';
        }

        if ($shortage > 0 || ($minimum > 0 && $available <= $minimum)) {
            return 'needs_order';
        }

        return 'ok';
    }

    private function matchesStatus(array $row, string $status): bool
    {
        return match ($status) {
            'all' => true,
            'negative' => $row['status'] === 'negative',
            'out_of_stock' => in_array($row['status'], ['negative', 'out_of_stock'], true),
            'needs_order' => in_array($row['status'], ['negative', 'out_of_stock', 'needs_order'], true),
            default => true,
        };
    }

    private function summary(Collection $rows): array
    {
        return [
            'rows' => $rows->count(),
            'negative' => $rows->where('status', 'negative')->count(),
            'out_of_stock' => $rows->where('status', 'out_of_stock')->count(),
            'needs_order' => $rows->where('status', 'needs_order')->count(),
            'suggested_quantity' => round((float) $rows->sum('suggested_quantity'), 3),
            'suggested_cost' => round((float) $rows->sum('suggested_cost'), 2),
        ];
    }
}

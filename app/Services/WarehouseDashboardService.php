<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\InventoryMovement;

class WarehouseDashboardService
{
    public function forUser($user): array
    {
        if (!TenantSettings::enabled('feature_warehouse_management')) {
            return [
                'enabled' => false,
                'low_stock_items' => collect(),
                'recent_movements' => collect(),
                'alerts' => collect(),
                'negative_count' => 0,
                'low_count' => 0,
                'missing_location_count' => 0,
            ];
        }

        $balances = InventoryBalance::query()
            ->with(['product', 'store', 'warehouseLocation'])
            ->forOrganizations($user)
            ->orderBy('quantity')
            ->limit(500)
            ->get();

        $lowStockItems = $balances
            ->filter(function ($balance) {
                $product = $balance->product;

                if (!$product) {
                    return false;
                }

                $quantity = (float) $balance->quantity;
                $orderLimit = (float) ($product->orderLimit ?: 0);

                return $quantity <= 0 || ($orderLimit > 0 && $quantity <= $orderLimit);
            })
            ->take(12)
            ->values();

        $negativeCount = $balances->filter(fn($balance) => (float) $balance->quantity < 0)->count();
        $missingLocationCount = $balances->filter(fn($balance) => (int) $balance->warehouse_location_id === 0)->count();

        $recentMovements = InventoryMovement::query()
            ->with(['product', 'store', 'warehouseLocation'])
            ->forOrganizations($user)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $alerts = collect();

        if ($negativeCount > 0) {
            $alerts->push([
                'type' => 'danger',
                'title' => 'موجودی منفی',
                'body' => $negativeCount . ' ردیف موجودی زیر صفر ثبت شده است.',
            ]);
        }

        if ($lowStockItems->count() > 0) {
            $alerts->push([
                'type' => 'warning',
                'title' => 'نقطه سفارش',
                'body' => $lowStockItems->count() . ' قلم کالا نزدیک حد سفارش یا کمتر از آن است.',
            ]);
        }

        if ($missingLocationCount > 0) {
            $alerts->push([
                'type' => 'info',
                'title' => 'مکان انبار',
                'body' => $missingLocationCount . ' ردیف موجودی هنوز به قفسه یا مکان مشخص وصل نشده است.',
            ]);
        }

        if ($alerts->isEmpty()) {
            $alerts->push([
                'type' => 'success',
                'title' => 'وضعیت انبار',
                'body' => 'هشدار بحرانی برای موجودی های ثبت شده دیده نشد.',
            ]);
        }

        return [
            'enabled' => true,
            'low_stock_items' => $lowStockItems,
            'recent_movements' => $recentMovements,
            'alerts' => $alerts,
            'negative_count' => $negativeCount,
            'low_count' => $lowStockItems->count(),
            'missing_location_count' => $missingLocationCount,
        ];
    }
}

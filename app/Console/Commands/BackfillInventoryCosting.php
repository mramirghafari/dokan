<?php

namespace App\Console\Commands;

use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillInventoryCosting extends Command
{
    protected $signature = 'inventory:backfill-costing {--dry-run : Count movements that need costing without writing changes}';

    protected $description = 'Backfill unit and total costs for existing inventory movements and balances.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $updatedMovements = 0;
        $updatedBalances = 0;

        $groups = InventoryMovement::query()
            ->select('tenant_id', 'store_id', 'warehouse_location_id', 'product_id')
            ->groupBy('tenant_id', 'store_id', 'warehouse_location_id', 'product_id')
            ->get();

        foreach ($groups as $group) {
            $product = Product::find($group->product_id);
            $fallbackCost = $this->money($product?->price);
            $runningQuantity = 0.0;
            $runningCost = 0.0;

            $movements = InventoryMovement::query()
                ->where('store_id', $group->store_id)
                ->where('warehouse_location_id', $group->warehouse_location_id ?: 0)
                ->where('product_id', $group->product_id)
                ->when($group->tenant_id, fn($query) => $query->where('tenant_id', $group->tenant_id), fn($query) => $query->whereNull('tenant_id'))
                ->orderBy('occurred_at')
                ->orderBy('id')
                ->get();

            DB::transaction(function () use ($movements, $group, $fallbackCost, $dryRun, &$runningQuantity, &$runningCost, &$updatedMovements, &$updatedBalances) {
                foreach ($movements as $movement) {
                    $quantity = abs((float) $movement->quantity);
                    $unitCost = $this->money($movement->unit_cost);

                    if ($unitCost <= 0) {
                        $unitCost = $movement->direction === 'out' && $runningQuantity > 0
                            ? round($runningCost / $runningQuantity, 2)
                            : $fallbackCost;
                    }

                    $totalCost = round($quantity * $unitCost, 2);

                    if (round((float) $movement->unit_cost, 2) != $unitCost || round((float) $movement->total_cost, 2) != $totalCost) {
                        $updatedMovements++;

                        if (!$dryRun) {
                            $movement->update([
                                'unit_cost' => $unitCost,
                                'total_cost' => $totalCost,
                                'valuation_method' => $movement->valuation_method ?: 'weighted_average',
                            ]);
                        }
                    }

                    $sign = $movement->direction === 'out' ? -1 : 1;
                    $runningQuantity += $quantity * $sign;
                    $runningCost += $totalCost * $sign;
                }

                $balance = InventoryBalance::where('store_id', $group->store_id)
                    ->where('warehouse_location_id', $group->warehouse_location_id ?: 0)
                    ->where('product_id', $group->product_id)
                    ->when($group->tenant_id, fn($query) => $query->where('tenant_id', $group->tenant_id), fn($query) => $query->whereNull('tenant_id'))
                    ->first();

                if (!$balance) {
                    return;
                }

                $unitCost = (float) $balance->quantity > 0 ? round($runningCost / (float) $balance->quantity, 2) : 0;

                if (round((float) $balance->unit_cost, 2) != $unitCost || round((float) $balance->total_cost, 2) != round($runningCost, 2)) {
                    $updatedBalances++;

                    if (!$dryRun) {
                        $balance->update([
                            'unit_cost' => $unitCost,
                            'total_cost' => round($runningCost, 2),
                            'last_costed_at' => now(),
                        ]);
                    }
                }
            });
        }

        $mode = $dryRun ? 'Dry run' : 'Costing backfill complete';
        $this->info("{$mode}: {$updatedMovements} movements, {$updatedBalances} balances.");

        return self::SUCCESS;
    }

    private function money($value): float
    {
        return (float) str_replace(',', '', (string) ($value ?? 0));
    }
}

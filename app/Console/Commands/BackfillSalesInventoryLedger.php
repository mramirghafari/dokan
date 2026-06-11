<?php

namespace App\Console\Commands;

use App\Models\Pishfactor;
use App\Services\InventoryLedgerService;
use Illuminate\Console\Command;

class BackfillSalesInventoryLedger extends Command
{
    protected $signature = 'inventory:backfill-sales-ledger {--factor_id= : Backfill only one factor} {--dry-run : Count eligible factors without writing movements}';

    protected $description = 'Backfill sale issue inventory movements from outgoing customer orders.';

    public function handle(InventoryLedgerService $ledger): int
    {
        $factorId = $this->option('factor_id');
        $dryRun = (bool) $this->option('dry-run');

        $query = Pishfactor::query()
            ->with('items.product')
            ->whereIn('status', [1, 4])
            ->where('step', '>=', 2)
            ->whereHas('items', function ($query) {
                $query->whereNotNull('pr_id')
                    ->where(function ($query) {
                        $query->where('pack', '!=', 0)->orWhere('tedad', '!=', 0);
                    });
            })
            ->when($factorId, function ($query) use ($factorId) {
                $query->where('id', $factorId);
            })
            ->orderBy('id');

        $factors = 0;
        $items = 0;

        $query->chunkById(100, function ($chunk) use ($ledger, $dryRun, &$factors, &$items) {
            foreach ($chunk as $factor) {
                $eligibleItems = $factor->items->filter(function ($item) {
                    return $item->pr_id && ((float) ($item->pack ?: 0) != 0.0 || (float) ($item->tedad ?: 0) != 0.0);
                });

                if ($eligibleItems->isEmpty()) {
                    continue;
                }

                $factors++;
                $items += $eligibleItems->count();

                if (!$dryRun) {
                    $ledger->replacePishfactorMovements($factor, $factor->updated_by ?: $factor->visitor_id);
                }
            }
        });

        $mode = $dryRun ? 'Dry run' : 'Sales backfill complete';
        $this->info("{$mode}: {$factors} factors, {$items} item rows.");

        return self::SUCCESS;
    }
}

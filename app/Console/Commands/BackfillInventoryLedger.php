<?php

namespace App\Console\Commands;

use App\Models\Receipt;
use App\Services\InventoryLedgerService;
use Illuminate\Console\Command;

class BackfillInventoryLedger extends Command
{
    protected $signature = 'inventory:backfill-ledger {--receipt_id= : Backfill only one receipt} {--dry-run : Count eligible rows without writing movements}';

    protected $description = 'Backfill inventory movements and balances from legacy receipt depot rows.';

    public function handle(InventoryLedgerService $ledger): int
    {
        $receiptId = $this->option('receipt_id');
        $dryRun = (bool) $this->option('dry-run');

        $query = Receipt::query()
            ->with(['depots' => function ($query) {
                $query->whereNotNull('pr_id')->where('entity', '!=', 0);
            }])
            ->whereHas('depots', function ($query) {
                $query->whereNotNull('pr_id')->where('entity', '!=', 0);
            })
            ->when($receiptId, function ($query) use ($receiptId) {
                $query->where('id', $receiptId);
            })
            ->orderBy('id');

        $receipts = 0;
        $rows = 0;

        $query->chunkById(100, function ($chunk) use ($ledger, $dryRun, &$receipts, &$rows) {
            foreach ($chunk as $receipt) {
                $eligibleDepots = $receipt->depots->filter(function ($depot) {
                    return $depot->pr_id && (float) $depot->entity != 0.0;
                });

                if ($eligibleDepots->isEmpty()) {
                    continue;
                }

                $receipts++;
                $rows += $eligibleDepots->count();

                if (!$dryRun) {
                    $ledger->replaceReceiptMovements($receipt, $eligibleDepots, $receipt->user_id);
                }
            }
        });

        $mode = $dryRun ? 'Dry run' : 'Backfill complete';
        $this->info("{$mode}: {$receipts} receipts, {$rows} depot rows.");

        return self::SUCCESS;
    }
}

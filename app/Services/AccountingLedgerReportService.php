<?php

namespace App\Services;

use App\Models\Accounts;
use App\Models\VoucherItems;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AccountingLedgerReportService
{
    public function build($user, array $filters = []): array
    {
        $fromDate = $filters['from_date'] ?? now()->startOfMonth()->toDateString();
        $toDate = $filters['to_date'] ?? now()->toDateString();
        $accountId = !empty($filters['account_id']) ? (int) $filters['account_id'] : null;
        $permanentOnly = (bool) ($filters['permanent_only'] ?? false);

        $journalRows = $this->baseQuery($user, $fromDate, $toDate, $permanentOnly)
            ->with(['voucher', 'account', 'costCenter'])
            ->when($accountId, fn($query) => $query->where('voucher_items.account_id', $accountId))
            ->limit(500)
            ->get();

        $ledgerRows = $accountId ? $this->accountLedgerRows($user, $fromDate, $toDate, $accountId, $permanentOnly) : collect();
        $trialBalance = $this->trialBalance($user, $fromDate, $toDate, $permanentOnly);

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'account_id' => $accountId,
            'permanent_only' => $permanentOnly,
            'journal_rows' => $journalRows,
            'ledger_rows' => $ledgerRows,
            'trial_balance' => $trialBalance,
            'summary' => [
                'journal_debit' => round((float) $journalRows->sum('debit_amount'), 2),
                'journal_credit' => round((float) $journalRows->sum('credit_amount'), 2),
                'trial_debit' => round((float) $trialBalance->sum('debit_amount'), 2),
                'trial_credit' => round((float) $trialBalance->sum('credit_amount'), 2),
                'trial_balance_debit' => round((float) $trialBalance->sum('debit_balance'), 2),
                'trial_balance_credit' => round((float) $trialBalance->sum('credit_balance'), 2),
                'accounts_count' => $trialBalance->count(),
            ],
        ];
    }

    private function accountLedgerRows($user, string $fromDate, string $toDate, int $accountId, bool $permanentOnly): Collection
    {
        $opening = $this->aggregateBaseQuery($user, null, Carbon::parse($fromDate)->subDay()->toDateString(), $permanentOnly)
            ->where('voucher_items.account_id', $accountId)
            ->selectRaw('COALESCE(SUM(voucher_items.debit_amount), 0) as debit, COALESCE(SUM(voucher_items.credit_amount), 0) as credit')
            ->first();
        $runningBalance = round((float) $opening->debit - (float) $opening->credit, 2);

        return $this->baseQuery($user, $fromDate, $toDate, $permanentOnly)
            ->with(['voucher', 'account', 'costCenter'])
            ->where('voucher_items.account_id', $accountId)
            ->get()
            ->map(function ($item) use (&$runningBalance) {
                $runningBalance = round($runningBalance + (float) $item->debit_amount - (float) $item->credit_amount, 2);
                $item->running_balance = $runningBalance;

                return $item;
            });
    }

    private function trialBalance($user, string $fromDate, string $toDate, bool $permanentOnly): Collection
    {
        $rows = $this->aggregateBaseQuery($user, $fromDate, $toDate, $permanentOnly)
            ->select('voucher_items.account_id')
            ->selectRaw('COALESCE(SUM(voucher_items.debit_amount), 0) as debit_amount, COALESCE(SUM(voucher_items.credit_amount), 0) as credit_amount')
            ->groupBy('voucher_items.account_id')
            ->get();
        $accounts = Accounts::whereIn('id', $rows->pluck('account_id')->filter()->values())->get()->keyBy('id');

        return $rows->map(function ($row) use ($accounts) {
            $debit = round((float) $row->debit_amount, 2);
            $credit = round((float) $row->credit_amount, 2);
            $balance = round($debit - $credit, 2);

            return [
                'account_id' => $row->account_id,
                'account' => $accounts->get($row->account_id),
                'debit_amount' => $debit,
                'credit_amount' => $credit,
                'debit_balance' => $balance > 0 ? $balance : 0,
                'credit_balance' => $balance < 0 ? abs($balance) : 0,
            ];
        })->sortBy(fn($row) => optional($row['account'])->code)->values();
    }

    private function baseQuery($user, ?string $fromDate, ?string $toDate, bool $permanentOnly)
    {
        return $this->aggregateBaseQuery($user, $fromDate, $toDate, $permanentOnly)
            ->select('voucher_items.*')
            ->orderByRaw('vouchers.voucher_date_en IS NULL')
            ->orderBy('vouchers.voucher_date_en')
            ->orderBy('vouchers.voucher_number')
            ->orderBy('voucher_items.id');
    }

    private function aggregateBaseQuery($user, ?string $fromDate, ?string $toDate, bool $permanentOnly)
    {
        $query = VoucherItems::query()
            ->join('vouchers', 'voucher_items.voucher_id', '=', 'vouchers.id')
            ->whereNull('voucher_items.deleted_at')
            ->whereNull('vouchers.deleted_at')
            ->where(function ($query) {
                $query->whereNull('vouchers.status')
                    ->orWhere('vouchers.status', '<>', 'cancelled');
            });

        if ($fromDate) {
            $query->whereDate('vouchers.voucher_date_en', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('vouchers.voucher_date_en', '<=', $toDate);
        }

        if ($permanentOnly) {
            $query->where('vouchers.is_permanent', 1);
        }

        if ((int) $user?->isGod !== 1) {
            $query->where('vouchers.tenant_id', $this->tenantId($user));
        }

        return $query;
    }

    private function tenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }
}

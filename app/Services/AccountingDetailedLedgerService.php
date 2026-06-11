<?php

namespace App\Services;

use App\Models\Accounts;
use App\Models\VoucherItems;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AccountingDetailedLedgerService
{
    public function build($user, array $filters = []): array
    {
        $toDate = $filters['to_date'] ?? now()->toDateString();
        $fromDate = $filters['from_date'] ?? Carbon::parse($toDate)->startOfMonth()->toDateString();
        $permanentOnly = (bool) ($filters['permanent_only'] ?? false);
        $level = !empty($filters['level']) ? (int) $filters['level'] : null;
        $accounts = $this->accounts($user);
        $opening = $this->aggregateRows($user, null, Carbon::parse($fromDate)->subDay()->toDateString(), $permanentOnly);
        $period = $this->aggregateRows($user, $fromDate, $toDate, $permanentOnly);
        $directRows = $this->directRows($accounts, $opening, $period);
        $ledgerRows = $this->rollupRows($directRows, $accounts, $level);
        $trialRows = $directRows->filter(fn($row) => round(abs((float) $row['closing_balance']), 2) > 0 || round((float) $row['period_debit'] + (float) $row['period_credit'], 2) > 0)
            ->sortBy(fn($row) => $row['account']?->code)
            ->values();
        $summarySource = $directRows->values();

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'permanent_only' => $permanentOnly,
            'level' => $level,
            'trial_rows' => $trialRows,
            'ledger_rows' => $ledgerRows,
            'summary' => [
                'opening_debit' => round((float) $summarySource->sum('opening_debit'), 2),
                'opening_credit' => round((float) $summarySource->sum('opening_credit'), 2),
                'period_debit' => round((float) $summarySource->sum('period_debit'), 2),
                'period_credit' => round((float) $summarySource->sum('period_credit'), 2),
                'total_debit' => round((float) $summarySource->sum('total_debit'), 2),
                'total_credit' => round((float) $summarySource->sum('total_credit'), 2),
                'closing_debit' => round((float) $summarySource->sum('closing_debit'), 2),
                'closing_credit' => round((float) $summarySource->sum('closing_credit'), 2),
                'accounts_count' => $trialRows->count(),
                'ledger_accounts_count' => $ledgerRows->count(),
            ],
        ];
    }

    private function accounts($user): Collection
    {
        $query = Accounts::query()->orderBy('code')->orderBy('name');

        if ((int) $user?->isGod !== 1) {
            $tenantId = $this->tenantId($user);
            $query->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)->orWhere('tenants_id', $tenantId);
            });
        }

        return $query->get()->keyBy('id');
    }

    private function aggregateRows($user, ?string $fromDate, ?string $toDate, bool $permanentOnly): Collection
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

        return $query->select('voucher_items.account_id')
            ->selectRaw('COALESCE(SUM(voucher_items.debit_amount), 0) as debit_amount, COALESCE(SUM(voucher_items.credit_amount), 0) as credit_amount')
            ->groupBy('voucher_items.account_id')
            ->get()
            ->keyBy('account_id');
    }

    private function directRows(Collection $accounts, Collection $opening, Collection $period): Collection
    {
        return $opening->keys()->merge($period->keys())->unique()->mapWithKeys(function ($accountId) use ($accounts, $opening, $period) {
            $openingDebit = round((float) ($opening->get($accountId)?->debit_amount ?: 0), 2);
            $openingCredit = round((float) ($opening->get($accountId)?->credit_amount ?: 0), 2);
            $periodDebit = round((float) ($period->get($accountId)?->debit_amount ?: 0), 2);
            $periodCredit = round((float) ($period->get($accountId)?->credit_amount ?: 0), 2);
            $openingBalance = round($openingDebit - $openingCredit, 2);
            $totalDebit = round($openingDebit + $periodDebit, 2);
            $totalCredit = round($openingCredit + $periodCredit, 2);
            $closingBalance = round($totalDebit - $totalCredit, 2);
            $row = $this->makeRow((int) $accountId, $accounts->get($accountId), $openingDebit, $openingCredit, $periodDebit, $periodCredit, $totalDebit, $totalCredit, $openingBalance, $closingBalance, true);

            return [(int) $accountId => $row];
        });
    }

    private function rollupRows(Collection $directRows, Collection $accounts, ?int $level): Collection
    {
        $rows = [];

        foreach ($directRows as $row) {
            foreach ($this->accountChain((int) $row['account_id'], $accounts) as $account) {
                $accountLevel = (int) ($account->level ?: 0);

                if ($level && $accountLevel > $level) {
                    continue;
                }

                if (!isset($rows[$account->id])) {
                    $rows[$account->id] = $this->makeRow((int) $account->id, $account, 0, 0, 0, 0, 0, 0, 0, 0, false);
                }

                $rows[$account->id]['opening_debit'] = round($rows[$account->id]['opening_debit'] + $row['opening_debit'], 2);
                $rows[$account->id]['opening_credit'] = round($rows[$account->id]['opening_credit'] + $row['opening_credit'], 2);
                $rows[$account->id]['period_debit'] = round($rows[$account->id]['period_debit'] + $row['period_debit'], 2);
                $rows[$account->id]['period_credit'] = round($rows[$account->id]['period_credit'] + $row['period_credit'], 2);
                $rows[$account->id]['total_debit'] = round($rows[$account->id]['total_debit'] + $row['total_debit'], 2);
                $rows[$account->id]['total_credit'] = round($rows[$account->id]['total_credit'] + $row['total_credit'], 2);
            }
        }

        return collect($rows)->map(function ($row) {
            $openingBalance = round($row['opening_debit'] - $row['opening_credit'], 2);
            $closingBalance = round($row['total_debit'] - $row['total_credit'], 2);
            $row['opening_balance'] = $openingBalance;
            $row['opening_balance_debit'] = $openingBalance > 0 ? $openingBalance : 0;
            $row['opening_balance_credit'] = $openingBalance < 0 ? abs($openingBalance) : 0;
            $row['closing_balance'] = $closingBalance;
            $row['closing_debit'] = $closingBalance > 0 ? $closingBalance : 0;
            $row['closing_credit'] = $closingBalance < 0 ? abs($closingBalance) : 0;

            return $row;
        })->filter(fn($row) => round(abs((float) $row['closing_balance']), 2) > 0 || round((float) $row['period_debit'] + (float) $row['period_credit'], 2) > 0)
            ->sortBy(fn($row) => $row['account']?->code)
            ->values();
    }

    private function accountChain(int $accountId, Collection $accounts): array
    {
        $chain = [];
        $account = $accounts->get($accountId);
        $guard = 0;

        while ($account && $guard < 12) {
            $chain[] = $account;
            $parentId = (int) $account->parent_id;
            $account = $parentId > 0 ? $accounts->get($parentId) : null;
            $guard++;
        }

        return $chain;
    }

    private function makeRow(int $accountId, ?Accounts $account, float $openingDebit, float $openingCredit, float $periodDebit, float $periodCredit, float $totalDebit, float $totalCredit, float $openingBalance, float $closingBalance, bool $direct): array
    {
        return [
            'account_id' => $accountId,
            'account' => $account,
            'level' => (int) ($account?->level ?: 0),
            'direct' => $direct,
            'opening_debit' => $openingBalance > 0 ? $openingBalance : 0,
            'opening_credit' => $openingBalance < 0 ? abs($openingBalance) : 0,
            'opening_raw_debit' => $openingDebit,
            'opening_raw_credit' => $openingCredit,
            'period_debit' => $periodDebit,
            'period_credit' => $periodCredit,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'closing_debit' => $closingBalance > 0 ? $closingBalance : 0,
            'closing_credit' => $closingBalance < 0 ? abs($closingBalance) : 0,
        ];
    }

    private function tenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }
}

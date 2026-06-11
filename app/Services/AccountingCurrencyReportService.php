<?php

namespace App\Services;

use App\Models\Accounts;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\VoucherItems;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AccountingCurrencyReportService
{
    public function build($user, array $filters = []): array
    {
        $toDate = $filters['to_date'] ?? now()->toDateString();
        $fromDate = $filters['from_date'] ?? Carbon::parse($toDate)->startOfMonth()->toDateString();
        $permanentOnly = (bool) ($filters['permanent_only'] ?? false);
        $currencyId = !empty($filters['currency_id']) ? (int) $filters['currency_id'] : null;
        $currencies = $this->currencies($user);
        $rows = $this->rows($user, $fromDate, $toDate, $permanentOnly, $currencyId, $currencies);

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'permanent_only' => $permanentOnly,
            'currency_id' => $currencyId,
            'currencies' => $currencies->values(),
            'rows' => $rows,
            'recent_rates' => $this->recentRates($user),
            'summary' => [
                'currencies_count' => $rows->pluck('currency_id')->unique()->count(),
                'accounts_count' => $rows->pluck('account_id')->unique()->count(),
                'foreign_debit' => round((float) $rows->sum('foreign_debit'), 4),
                'foreign_credit' => round((float) $rows->sum('foreign_credit'), 4),
                'local_debit' => round((float) $rows->sum('local_debit'), 2),
                'local_credit' => round((float) $rows->sum('local_credit'), 2),
                'local_balance' => round((float) $rows->sum('local_balance'), 2),
                'revalued_balance' => round((float) $rows->sum('revalued_balance'), 2),
                'revaluation_difference' => round((float) $rows->sum('revaluation_difference'), 2),
            ],
        ];
    }

    private function rows($user, string $fromDate, string $toDate, bool $permanentOnly, ?int $currencyId, Collection $currencies): Collection
    {
        $query = VoucherItems::query()
            ->join('vouchers', 'voucher_items.voucher_id', '=', 'vouchers.id')
            ->whereNull('voucher_items.deleted_at')
            ->whereNull('vouchers.deleted_at')
            ->whereNotNull('voucher_items.currency_id')
            ->where(function ($query) {
                $query->whereNull('vouchers.status')
                    ->orWhere('vouchers.status', '<>', 'cancelled');
            })
            ->whereDate('vouchers.voucher_date_en', '>=', $fromDate)
            ->whereDate('vouchers.voucher_date_en', '<=', $toDate);

        if ($permanentOnly) {
            $query->where('vouchers.is_permanent', 1);
        }

        if ($currencyId) {
            $query->where('voucher_items.currency_id', $currencyId);
        }

        if ((int) $user?->isGod !== 1) {
            $query->where('vouchers.tenant_id', $this->tenantId($user));
        }

        $aggregates = $query->select('voucher_items.currency_id', 'voucher_items.account_id')
            ->selectRaw('COALESCE(SUM(voucher_items.foreign_debit_amount), 0) as foreign_debit')
            ->selectRaw('COALESCE(SUM(voucher_items.foreign_credit_amount), 0) as foreign_credit')
            ->selectRaw('COALESCE(SUM(voucher_items.debit_amount), 0) as local_debit')
            ->selectRaw('COALESCE(SUM(voucher_items.credit_amount), 0) as local_credit')
            ->selectRaw('COALESCE(MAX(voucher_items.exchange_rate), 0) as recorded_rate')
            ->groupBy('voucher_items.currency_id', 'voucher_items.account_id')
            ->get();

        $accounts = Accounts::whereIn('id', $aggregates->pluck('account_id')->filter()->unique())->get()->keyBy('id');
        $latestRates = $this->latestRates($user, $aggregates->pluck('currency_id')->filter()->unique(), $toDate);

        return $aggregates->map(function ($row) use ($accounts, $currencies, $latestRates) {
            $currencyId = (int) $row->currency_id;
            $foreignDebit = round((float) $row->foreign_debit, 4);
            $foreignCredit = round((float) $row->foreign_credit, 4);
            $localDebit = round((float) $row->local_debit, 2);
            $localCredit = round((float) $row->local_credit, 2);
            $foreignBalance = round($foreignDebit - $foreignCredit, 4);
            $localBalance = round($localDebit - $localCredit, 2);
            $latestRate = $latestRates->get($currencyId)?->rate ?: (float) $row->recorded_rate ?: null;
            $revaluedBalance = $latestRate ? round($foreignBalance * (float) $latestRate, 2) : $localBalance;

            return [
                'currency_id' => $currencyId,
                'currency' => $currencies->get($currencyId),
                'account_id' => (int) $row->account_id,
                'account' => $accounts->get((int) $row->account_id),
                'foreign_debit' => $foreignDebit,
                'foreign_credit' => $foreignCredit,
                'foreign_balance' => $foreignBalance,
                'local_debit' => $localDebit,
                'local_credit' => $localCredit,
                'local_balance' => $localBalance,
                'latest_rate' => $latestRate,
                'latest_rate_date' => optional($latestRates->get($currencyId)?->rate_date)->format('Y-m-d'),
                'revalued_balance' => $revaluedBalance,
                'revaluation_difference' => round($revaluedBalance - $localBalance, 2),
            ];
        })->filter(fn($row) => abs((float) $row['foreign_balance']) > 0 || abs((float) $row['local_balance']) > 0)
            ->sortBy([['currency_id', 'asc'], ['account_id', 'asc']])
            ->values();
    }

    private function currencies($user): Collection
    {
        $query = Currency::query()->where('isActive', 1)->orderByDesc('is_default')->orderBy('code');

        if ((int) $user?->isGod !== 1) {
            $tenantId = $this->tenantId($user);
            $query->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            });
        }

        return $query->get()->keyBy('id');
    }

    private function latestRates($user, Collection $currencyIds, string $toDate): Collection
    {
        if ($currencyIds->isEmpty()) {
            return collect();
        }

        $query = ExchangeRate::query()
            ->whereIn('currency_id', $currencyIds->all())
            ->whereDate('rate_date', '<=', $toDate)
            ->orderBy('currency_id')
            ->orderByDesc('rate_date')
            ->orderByDesc('id');

        if ((int) $user?->isGod !== 1) {
            $tenantId = $this->tenantId($user);
            $query->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            });
        }

        return $query->get()->unique('currency_id')->keyBy('currency_id');
    }

    private function recentRates($user): Collection
    {
        $query = ExchangeRate::with('currency')->orderByDesc('rate_date')->orderByDesc('id');

        if ((int) $user?->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
        }

        return $query->limit(20)->get();
    }

    private function tenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }
}

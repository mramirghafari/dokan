<?php

namespace App\Services;

use App\Models\Accounts;
use App\Models\FinancialPeriodClosing;
use App\Models\FiscalYear;
use App\Models\Voucher;
use App\Models\VoucherItems;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AccountingPeriodClosingService
{
    public function __construct(private NumberingService $numberingService) {}

    public function preview(FiscalYear $fiscalYear, $user): array
    {
        $this->ensureTenant($fiscalYear, $user);
        $balances = $this->balances($fiscalYear, $user);
        $openingBalances = $balances->filter(fn($row) => !$this->isTemporaryAccount($row['account']))->values();
        $closingTotals = $this->closingTotals($balances);
        $openingTotals = $this->openingTotals($openingBalances, $fiscalYear, $user);

        return [
            'fiscal_year' => $fiscalYear,
            'balances' => $balances,
            'opening_balances' => $openingBalances,
            'closing_totals' => $closingTotals,
            'opening_totals' => $openingTotals,
            'can_close' => $balances->isNotEmpty() && round($closingTotals['debit'], 2) === round($closingTotals['credit'], 2),
        ];
    }

    public function close(FiscalYear $fiscalYear, $user): FinancialPeriodClosing
    {
        $this->ensureTenant($fiscalYear, $user);

        if (FinancialPeriodClosing::where('tenant_id', $this->tenantId($user))->where('fiscal_year_id', $fiscalYear->id)->whereNull('deleted_at')->exists()) {
            throw ValidationException::withMessages(['fiscal_year_id' => 'این سال مالی قبلا بسته شده است.']);
        }

        $preview = $this->preview($fiscalYear, $user);

        if (!$preview['can_close']) {
            throw ValidationException::withMessages(['fiscal_year_id' => 'برای بستن دوره باید مانده حساب ها تراز باشد.']);
        }

        return DB::transaction(function () use ($fiscalYear, $user, $preview) {
            $nextFiscalYear = $this->ensureNextFiscalYear($fiscalYear, $user);
            $closingVoucher = $this->createClosingVoucher($fiscalYear, $preview['balances'], $user);
            $openingVoucher = $this->createOpeningVoucher($nextFiscalYear, $preview['opening_balances'], $preview['opening_totals']['retained_earnings_line'], $user);

            $fiscalYear->update(['status' => 'closed', 'is_default' => false]);
            $nextFiscalYear->update(['status' => 'open', 'is_default' => true]);

            return FinancialPeriodClosing::create([
                'tenant_id' => $this->tenantId($user),
                'fiscal_year_id' => $fiscalYear->id,
                'next_fiscal_year_id' => $nextFiscalYear->id,
                'period_start' => $fiscalYear->starts_at,
                'period_end' => $fiscalYear->ends_at,
                'closing_voucher_id' => $closingVoucher->id,
                'opening_voucher_id' => $openingVoucher?->id,
                'total_debit' => $closingVoucher->total_debit,
                'total_credit' => $closingVoucher->total_credit,
                'opening_total_debit' => $openingVoucher?->total_debit ?: 0,
                'opening_total_credit' => $openingVoucher?->total_credit ?: 0,
                'accounts_count' => $preview['balances']->count(),
                'opening_accounts_count' => $preview['opening_balances']->count(),
                'status' => 'closed',
                'balances_snapshot' => $preview['balances']->map(fn($row) => [
                    'account_id' => $row['account_id'],
                    'account_code' => $row['account']?->code,
                    'account_name' => $row['account']?->name,
                    'balance' => $row['balance'],
                    'account_category' => $row['account']?->account_category,
                ])->values()->all(),
                'description' => 'بستن دوره مالی ' . $fiscalYear->title,
                'closed_by' => $user?->id,
                'closed_at' => now(),
            ]);
        });
    }

    private function balances(FiscalYear $fiscalYear, $user)
    {
        $rows = VoucherItems::query()
            ->join('vouchers', 'voucher_items.voucher_id', '=', 'vouchers.id')
            ->whereNull('voucher_items.deleted_at')
            ->whereNull('vouchers.deleted_at')
            ->where(function ($query) {
                $query->whereNull('vouchers.status')
                    ->orWhere('vouchers.status', '<>', 'cancelled');
            })
            ->whereDate('vouchers.voucher_date_en', '>=', $fiscalYear->starts_at->toDateString())
            ->whereDate('vouchers.voucher_date_en', '<=', $fiscalYear->ends_at->toDateString())
            ->where(function ($query) {
                $query->whereNull('vouchers.document_type')
                    ->orWhereNotIn('vouchers.document_type', ['period_closing', 'period_opening']);
            })
            ->when((int) $user?->isGod !== 1, fn($query) => $query->where('vouchers.tenant_id', $this->tenantId($user)))
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
                'account_id' => (int) $row->account_id,
                'account' => $accounts->get($row->account_id),
                'debit_amount' => $debit,
                'credit_amount' => $credit,
                'balance' => $balance,
            ];
        })->filter(fn($row) => round(abs((float) $row['balance']), 2) > 0)->sortBy(fn($row) => $row['account']?->code)->values();
    }

    private function closingTotals($balances): array
    {
        return [
            'debit' => round((float) $balances->where('balance', '<', 0)->sum(fn($row) => abs((float) $row['balance'])), 2),
            'credit' => round((float) $balances->where('balance', '>', 0)->sum('balance'), 2),
        ];
    }

    private function openingTotals($openingBalances, FiscalYear $fiscalYear, $user): array
    {
        $debit = round((float) $openingBalances->where('balance', '>', 0)->sum('balance'), 2);
        $credit = round((float) $openingBalances->where('balance', '<', 0)->sum(fn($row) => abs((float) $row['balance'])), 2);
        $difference = round($debit - $credit, 2);
        $retainedEarningsLine = null;

        if ($difference !== 0.0) {
            $retainedEarningsLine = [
                'account_id' => null,
                'debit_amount' => $difference < 0 ? abs($difference) : 0,
                'credit_amount' => $difference > 0 ? $difference : 0,
                'description' => 'انتقال نتیجه عملکرد به سود و زیان انباشته ' . $fiscalYear->title,
            ];

            if ($difference > 0) {
                $credit = round($credit + $difference, 2);
            } else {
                $debit = round($debit + abs($difference), 2);
            }
        }

        return ['debit' => $debit, 'credit' => $credit, 'retained_earnings_line' => $retainedEarningsLine];
    }

    private function createClosingVoucher(FiscalYear $fiscalYear, $balances, $user): Voucher
    {
        $tenantId = $this->tenantId($user);
        $organizationId = $this->organizationId($user);
        $date = $fiscalYear->ends_at->toDateString();
        $totals = $this->closingTotals($balances);

        $voucher = Voucher::create([
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'factor_id' => 0,
            'account_id' => $balances->first()['account_id'],
            'voucher_type' => 0,
            'voucher_number' => $this->numberingService->nextVoucherNumber($tenantId, $date, 'CLS'),
            'voucher_date_fa' => $this->jalaliDate($date),
            'voucher_date_en' => $date,
            'amount' => $totals['debit'],
            'total_debit' => $totals['debit'],
            'total_credit' => $totals['credit'],
            'method' => 0,
            'document_type' => 'period_closing',
            'status' => 'permanent',
            'is_permanent' => true,
            'source_type' => FiscalYear::class,
            'source_id' => $fiscalYear->id,
            'fiscal_year' => $this->jalaliYear($date),
            'description' => 'سند اختتامیه ' . $fiscalYear->title,
            'posted_at' => now(),
            'approved_by' => $user?->id,
            'created_by' => $user?->id,
        ]);

        foreach ($balances as $row) {
            $balance = (float) $row['balance'];
            $voucher->items()->create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'account_id' => $row['account_id'],
                'amount' => abs($balance),
                'debit_amount' => $balance < 0 ? abs($balance) : 0,
                'credit_amount' => $balance > 0 ? $balance : 0,
                'method' => $balance < 0 ? 1 : 0,
                'description' => 'بستن مانده حساب ' . ($row['account']?->name ?: $row['account_id']),
            ]);
        }

        return $voucher->refresh();
    }

    private function createOpeningVoucher(FiscalYear $nextFiscalYear, $openingBalances, ?array $retainedEarningsLine, $user): ?Voucher
    {
        $tenantId = $this->tenantId($user);
        $organizationId = $this->organizationId($user);
        $date = $nextFiscalYear->starts_at->toDateString();
        $lines = $openingBalances->map(function ($row) {
            $balance = (float) $row['balance'];

            return [
                'account_id' => $row['account_id'],
                'amount' => abs($balance),
                'debit_amount' => $balance > 0 ? $balance : 0,
                'credit_amount' => $balance < 0 ? abs($balance) : 0,
                'description' => 'افتتاح مانده حساب ' . ($row['account']?->name ?: $row['account_id']),
            ];
        })->values();

        if ($retainedEarningsLine) {
            $retainedEarningsLine['account_id'] = $retainedEarningsLine['account_id'] ?: $this->systemAccountId('SYS-3301', 'سود و زیان انباشته', $tenantId, $organizationId, $user);
            $retainedEarningsLine['amount'] = max((float) $retainedEarningsLine['debit_amount'], (float) $retainedEarningsLine['credit_amount']);
            $lines->push($retainedEarningsLine);
        }

        if ($lines->isEmpty()) {
            return null;
        }

        $totalDebit = round((float) $lines->sum('debit_amount'), 2);
        $totalCredit = round((float) $lines->sum('credit_amount'), 2);

        $voucher = Voucher::create([
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'factor_id' => 0,
            'account_id' => $lines->first()['account_id'],
            'voucher_type' => 0,
            'voucher_number' => $this->numberingService->nextVoucherNumber($tenantId, $date, 'OPN'),
            'voucher_date_fa' => $this->jalaliDate($date),
            'voucher_date_en' => $date,
            'amount' => $totalDebit,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'method' => 0,
            'document_type' => 'period_opening',
            'status' => 'permanent',
            'is_permanent' => true,
            'source_type' => FiscalYear::class,
            'source_id' => $nextFiscalYear->id,
            'fiscal_year' => $this->jalaliYear($date),
            'description' => 'سند افتتاحیه ' . $nextFiscalYear->title,
            'posted_at' => now(),
            'approved_by' => $user?->id,
            'created_by' => $user?->id,
        ]);

        foreach ($lines as $line) {
            $voucher->items()->create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'account_id' => $line['account_id'],
                'amount' => $line['amount'],
                'debit_amount' => $line['debit_amount'],
                'credit_amount' => $line['credit_amount'],
                'method' => $line['debit_amount'] > 0 ? 1 : 0,
                'description' => $line['description'],
            ]);
        }

        return $voucher->refresh();
    }

    private function ensureNextFiscalYear(FiscalYear $fiscalYear, $user): FiscalYear
    {
        $tenantId = $this->tenantId($user);
        $startsAt = $fiscalYear->ends_at->copy()->addDay()->toDateString();
        $endsAt = $fiscalYear->ends_at->copy()->addYear()->toDateString();
        $title = 'سال مالی ' . verta($startsAt)->format('Y');

        return FiscalYear::firstOrCreate(
            ['tenant_id' => $tenantId, 'starts_at' => $startsAt],
            ['title' => $title, 'ends_at' => $endsAt, 'status' => 'open', 'is_default' => false]
        );
    }

    private function isTemporaryAccount(?Accounts $account): bool
    {
        $category = $account?->account_category;

        if (in_array($category, ['income', 'expense'], true)) {
            return true;
        }

        return str_starts_with((string) $account?->code, 'SYS-4') || str_starts_with((string) $account?->code, 'SYS-5');
    }

    private function systemAccountId(string $code, string $name, ?int $tenantId, ?int $organizationId, $user): int
    {
        $query = Accounts::query()->where('code', $code);

        if ($tenantId && Schema::hasColumn('accounts', 'tenant_id')) {
            $query->where('tenant_id', $tenantId);
        } elseif ($tenantId && Schema::hasColumn('accounts', 'tenants_id')) {
            $query->where('tenants_id', $tenantId);
        }

        $account = $query->first();

        if ($account) {
            return (int) $account->id;
        }

        $attributes = ['code' => $code, 'name' => $name, 'level' => 3, 'type' => 'system', 'nature' => 0, 'isActive' => 1, 'parent_id' => 0];
        if (Schema::hasColumn('accounts', 'is_system')) {
            $attributes['is_system'] = true;
        }
        if (Schema::hasColumn('accounts', 'account_category')) {
            $attributes['account_category'] = 'equity';
        }
        if (Schema::hasColumn('accounts', 'tenant_id')) {
            $attributes['tenant_id'] = $tenantId;
        }
        if (Schema::hasColumn('accounts', 'tenants_id')) {
            $attributes['tenants_id'] = $tenantId ?: 0;
        }
        if (Schema::hasColumn('accounts', 'organization_id')) {
            $attributes['organization_id'] = $organizationId;
        }
        if (Schema::hasColumn('accounts', 'created_by')) {
            $attributes['created_by'] = $user?->id ?: 0;
        }

        return (int) Accounts::create($attributes)->id;
    }

    private function ensureTenant(FiscalYear $fiscalYear, $user): void
    {
        if ((int) $user?->isGod === 1) {
            return;
        }

        if ((int) $fiscalYear->tenant_id !== (int) $this->tenantId($user)) {
            throw ValidationException::withMessages(['fiscal_year_id' => 'سال مالی با پنل فعلی همخوانی ندارد.']);
        }
    }

    private function tenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }

    private function organizationId($user): ?int
    {
        $organizationId = $user?->organization_id;
        $decoded = is_string($organizationId) ? json_decode($organizationId, true) : null;

        return is_array($decoded) ? (int) ($decoded[0] ?? 0) : ($organizationId ?: null);
    }

    private function jalaliDate(string $date): string
    {
        try {
            return verta($date)->format('Y/m/d');
        } catch (\Throwable $exception) {
            return $date;
        }
    }

    private function jalaliYear(string $date): string
    {
        try {
            return verta($date)->format('Y');
        } catch (\Throwable $exception) {
            return substr($date, 0, 4);
        }
    }
}

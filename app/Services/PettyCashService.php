<?php

namespace App\Services;

use App\Models\OperationalExpense;
use App\Models\Accounts;
use App\Models\CostCenter;
use App\Models\ExpenseType;
use App\Models\PettyCashFund;
use App\Models\PettyCashTransaction;
use App\Models\Voucher;
use App\Models\VoucherItems;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PettyCashService
{
    public function __construct(
        private NumberingService $numberingService,
        private AccountingPostingService $postingService
    ) {}

    public function createFund(array $payload, $user): PettyCashFund
    {
        $tenantId = $this->tenantId($user);
        $organizationId = $this->organizationId($user);
        $this->ensureModelTenant(Accounts::findOrFail((int) Arr::get($payload, 'account_id')), $user, 'حساب تنخواه');

        return PettyCashFund::create([
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'account_id' => Arr::get($payload, 'account_id'),
            'custodian_user_id' => Arr::get($payload, 'custodian_user_id'),
            'fund_code' => Arr::get($payload, 'fund_code') ?: $this->numberingService->nextDocumentNumber('petty_cash_fund', 'PCF', $tenantId, $organizationId),
            'title' => Arr::get($payload, 'title'),
            'custodian_name' => Arr::get($payload, 'custodian_name'),
            'ceiling_amount' => $this->money(Arr::get($payload, 'ceiling_amount')),
            'status' => Arr::get($payload, 'status', 'active'),
            'description' => Arr::get($payload, 'description'),
            'created_by' => $user?->id,
        ]);
    }

    public function charge(PettyCashFund $fund, array $payload, $user): PettyCashTransaction
    {
        $amount = $this->money(Arr::get($payload, 'amount'));
        $counterAccountId = (int) Arr::get($payload, 'counter_account_id');

        if ($amount <= 0 || $counterAccountId <= 0) {
            throw ValidationException::withMessages(['amount' => 'برای شارژ تنخواه، مبلغ و حساب پرداخت کننده الزامی است.']);
        }

        $this->ensureActiveFund($fund);
        $this->ensureModelTenant(Accounts::findOrFail($counterAccountId), $user, 'حساب پرداخت کننده');
        $currentBalance = $this->fundBalance($fund, $user);
        if ((float) $fund->ceiling_amount > 0 && round($currentBalance + $amount, 2) > (float) $fund->ceiling_amount) {
            throw ValidationException::withMessages(['amount' => 'مبلغ شارژ از سقف مصوب تنخواه عبور می کند.']);
        }

        $date = Arr::get($payload, 'transaction_date_en') ?: now()->toDateString();

        return DB::transaction(function () use ($fund, $payload, $user, $amount, $counterAccountId, $date) {
            $transaction = $this->createTransaction($fund, $payload, $user, 'charge', $amount, 0, $amount, $counterAccountId, null);
            $voucher = $this->postVoucher($transaction, $user, $date, 'petty_cash_charge', 'PCH', [
                ['account_id' => $fund->account_id, 'debit_amount' => $amount, 'credit_amount' => 0, 'description' => 'شارژ تنخواه ' . $fund->title],
                ['account_id' => $counterAccountId, 'debit_amount' => 0, 'credit_amount' => $amount, 'description' => 'پرداخت شارژ تنخواه ' . $fund->title],
            ]);

            $transaction->update(['voucher_id' => $voucher->id]);

            return $transaction->fresh(['voucher.items.account', 'fund.account']) ?: $transaction;
        });
    }

    public function spend(PettyCashFund $fund, array $payload, $user): PettyCashTransaction
    {
        $amount = $this->money(Arr::get($payload, 'amount'));
        $taxAmount = $this->money(Arr::get($payload, 'tax_amount'));
        $totalAmount = round($amount + $taxAmount, 2);

        if ($totalAmount <= 0) {
            throw ValidationException::withMessages(['amount' => 'مبلغ هزینه تنخواه باید بزرگتر از صفر باشد.']);
        }

        $this->ensureActiveFund($fund);
        $this->ensureModelTenant(CostCenter::findOrFail((int) Arr::get($payload, 'cost_center_id')), $user, 'مرکز هزینه');
        $this->ensureModelTenant(ExpenseType::findOrFail((int) Arr::get($payload, 'expense_type_id')), $user, 'نوع هزینه');
        if (Arr::get($payload, 'expense_account_id')) {
            $this->ensureModelTenant(Accounts::findOrFail((int) Arr::get($payload, 'expense_account_id')), $user, 'حساب هزینه');
        }
        if ($totalAmount > $this->fundBalance($fund, $user)) {
            throw ValidationException::withMessages(['amount' => 'مبلغ هزینه از مانده تنخواه بیشتر است.']);
        }

        $date = Arr::get($payload, 'transaction_date_en') ?: now()->toDateString();

        return DB::transaction(function () use ($fund, $payload, $user, $amount, $taxAmount, $totalAmount, $date) {
            $expense = OperationalExpense::create([
                'tenant_id' => $fund->tenant_id,
                'organization_id' => $fund->organization_id,
                'store_id' => Arr::get($payload, 'store_id'),
                'cost_center_id' => Arr::get($payload, 'cost_center_id'),
                'expense_type_id' => Arr::get($payload, 'expense_type_id'),
                'expense_account_id' => Arr::get($payload, 'expense_account_id'),
                'settlement_account_id' => $fund->account_id,
                'expense_number' => $this->nextPettyCashExpenseNumber($fund->tenant_id),
                'expense_date_en' => $date,
                'expense_date_fa' => $this->jalaliDate($date),
                'status' => 'approved',
                'payment_status' => 'petty_cash',
                'amount' => $amount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'reference_number' => Arr::get($payload, 'reference_number'),
                'description' => Arr::get($payload, 'description'),
                'created_by' => $user?->id,
            ]);

            $voucher = $this->postingService->postOperationalExpenseVoucher($expense, $user);
            $transaction = $this->createTransaction($fund, $payload, $user, 'expense', $amount, $taxAmount, $totalAmount, $fund->account_id, $expense->id);
            $transaction->update(['voucher_id' => $voucher->id]);

            return $transaction->fresh(['voucher.items.account', 'expense', 'fund.account']) ?: $transaction;
        });
    }

    public function settle(PettyCashFund $fund, array $payload, $user): PettyCashTransaction
    {
        $amount = $this->money(Arr::get($payload, 'amount'));
        $counterAccountId = (int) Arr::get($payload, 'counter_account_id');

        if ($amount <= 0 || $counterAccountId <= 0) {
            throw ValidationException::withMessages(['amount' => 'برای تسویه تنخواه، مبلغ و حساب دریافت کننده الزامی است.']);
        }

        $this->ensureActiveFund($fund);
        $this->ensureModelTenant(Accounts::findOrFail($counterAccountId), $user, 'حساب دریافت کننده');
        if ($amount > $this->fundBalance($fund, $user)) {
            throw ValidationException::withMessages(['amount' => 'مبلغ تسویه از مانده تنخواه بیشتر است.']);
        }

        $date = Arr::get($payload, 'transaction_date_en') ?: now()->toDateString();

        return DB::transaction(function () use ($fund, $payload, $user, $amount, $counterAccountId, $date) {
            $transaction = $this->createTransaction($fund, $payload, $user, 'settlement', $amount, 0, $amount, $counterAccountId, null);
            $voucher = $this->postVoucher($transaction, $user, $date, 'petty_cash_settlement', 'PCS', [
                ['account_id' => $counterAccountId, 'debit_amount' => $amount, 'credit_amount' => 0, 'description' => 'برگشت/تسویه تنخواه ' . $fund->title],
                ['account_id' => $fund->account_id, 'debit_amount' => 0, 'credit_amount' => $amount, 'description' => 'کاهش مانده تنخواه ' . $fund->title],
            ]);

            $transaction->update(['voucher_id' => $voucher->id]);

            return $transaction->fresh(['voucher.items.account', 'fund.account']) ?: $transaction;
        });
    }

    public function report($user, Collection $funds, array $filters = []): array
    {
        $fundIds = $funds->pluck('id')->values();
        $accountIds = $funds->pluck('account_id')->filter()->values();
        $balances = $this->ledgerBalances($user, $accountIds);
        $transactions = PettyCashTransaction::with(['fund.account', 'voucher', 'expense.expenseType', 'counterAccount'])
            ->when($fundIds->isNotEmpty(), fn($query) => $query->whereIn('petty_cash_fund_id', $fundIds->all()))
            ->when((int) $user?->isGod !== 1, fn($query) => $query->where('tenant_id', $this->tenantId($user)))
            ->when(!empty($filters['fund_id']), fn($query) => $query->where('petty_cash_fund_id', $filters['fund_id']))
            ->when(!empty($filters['type']), fn($query) => $query->where('transaction_type', $filters['type']))
            ->latest('transaction_date_en')
            ->latest('id')
            ->paginate(30);

        $transactions->appends(array_filter($filters));

        return [
            'balances' => $balances,
            'transactions' => $transactions,
            'totals' => [
                'fund_count' => $funds->count(),
                'active_count' => $funds->where('status', 'active')->count(),
                'balance' => $funds->sum(fn($fund) => (float) ($balances[$fund->account_id] ?? 0)),
                'ceiling' => $funds->sum('ceiling_amount'),
            ],
        ];
    }

    private function createTransaction(PettyCashFund $fund, array $payload, $user, string $type, float $amount, float $taxAmount, float $totalAmount, ?int $counterAccountId, ?int $expenseId): PettyCashTransaction
    {
        $date = Arr::get($payload, 'transaction_date_en') ?: now()->toDateString();

        return PettyCashTransaction::create([
            'tenant_id' => $fund->tenant_id,
            'organization_id' => $fund->organization_id,
            'petty_cash_fund_id' => $fund->id,
            'expense_id' => $expenseId,
            'counter_account_id' => $counterAccountId,
            'cost_center_id' => Arr::get($payload, 'cost_center_id'),
            'expense_type_id' => Arr::get($payload, 'expense_type_id'),
            'transaction_type' => $type,
            'transaction_date_en' => $date,
            'transaction_date_fa' => $this->jalaliDate($date),
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'status' => 'approved',
            'reference_number' => Arr::get($payload, 'reference_number'),
            'description' => Arr::get($payload, 'description'),
            'created_by' => $user?->id,
        ]);
    }

    private function postVoucher(PettyCashTransaction $transaction, $user, string $date, string $documentType, string $prefix, array $lines): Voucher
    {
        $tenantId = $transaction->tenant_id ?: $this->tenantId($user);
        $organizationId = $transaction->organization_id ?: $this->organizationId($user);
        $amount = round(collect($lines)->sum('debit_amount'), 2);

        $voucher = Voucher::create([
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'factor_id' => 0,
            'account_id' => $lines[0]['account_id'],
            'voucher_type' => 3,
            'voucher_number' => $this->numberingService->nextVoucherNumber($tenantId, $date, $prefix),
            'voucher_date_fa' => $this->jalaliDate($date),
            'voucher_date_en' => $date,
            'amount' => $amount,
            'total_debit' => $amount,
            'total_credit' => $amount,
            'method' => 1,
            'document_type' => $documentType,
            'status' => 'draft',
            'is_permanent' => false,
            'source_type' => PettyCashTransaction::class,
            'source_id' => $transaction->id,
            'fiscal_year' => $this->jalaliYear($date),
            'description' => $transaction->description ?: 'عملیات تنخواه',
            'created_by' => $user?->id,
        ]);

        foreach ($lines as $line) {
            $voucher->items()->create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'account_id' => $line['account_id'],
                'amount' => max($line['debit_amount'], $line['credit_amount']),
                'debit_amount' => $line['debit_amount'],
                'credit_amount' => $line['credit_amount'],
                'method' => 1,
                'description' => $line['description'],
            ]);
        }

        return $voucher->load('items.account');
    }

    private function ledgerBalances($user, Collection $accountIds): Collection
    {
        if ($accountIds->isEmpty()) {
            return collect();
        }

        $query = VoucherItems::query()
            ->join('vouchers', 'voucher_items.voucher_id', '=', 'vouchers.id')
            ->whereIn('voucher_items.account_id', $accountIds->all())
            ->whereNull('voucher_items.deleted_at')
            ->whereNull('vouchers.deleted_at');

        if ((int) $user?->isGod !== 1) {
            $query->where('vouchers.tenant_id', $this->tenantId($user));
        }

        return $query
            ->selectRaw('voucher_items.account_id, COALESCE(SUM(voucher_items.debit_amount), 0) - COALESCE(SUM(voucher_items.credit_amount), 0) as balance')
            ->groupBy('voucher_items.account_id')
            ->pluck('balance', 'account_id')
            ->map(fn($balance) => round((float) $balance, 2));
    }

    private function fundBalance(PettyCashFund $fund, $user): float
    {
        return (float) ($this->ledgerBalances($user, collect([$fund->account_id]))[$fund->account_id] ?? 0);
    }

    private function ensureActiveFund(PettyCashFund $fund): void
    {
        if ($fund->status !== 'active') {
            throw ValidationException::withMessages(['fund' => 'تنخواه انتخاب شده فعال نیست.']);
        }
    }

    private function ensureModelTenant($model, $user, string $label): void
    {
        if ((int) $user?->isGod === 1) {
            return;
        }

        $tenantId = (int) $this->tenantId($user);
        $modelTenantId = (int) ($model->tenant_id ?: $model->tenants_id);

        if ($tenantId !== $modelTenantId) {
            throw ValidationException::withMessages(['tenant' => $label . ' با پنل فعلی همخوانی ندارد.']);
        }
    }

    private function nextPettyCashExpenseNumber(?int $tenantId): string
    {
        $year = verta()->format('Y');
        $base = 'PCE-' . $year . '-';
        $query = OperationalExpense::where('expense_number', 'like', $base . '%');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $lastNumber = $query->orderByDesc('id')->value('expense_number');
        $next = 1;

        if ($lastNumber && preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $base . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
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
            return now()->format('Y');
        }
    }

    private function money($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 2);
    }

    private function tenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }

    private function organizationId($user): ?int
    {
        $organizationId = $user?->organization_id;
        $decoded = is_string($organizationId) ? json_decode($organizationId, true) : null;

        if (is_array($decoded)) {
            return isset($decoded[0]) ? (int) $decoded[0] : null;
        }

        return $organizationId ? (int) $organizationId : null;
    }
}

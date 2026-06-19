<?php

namespace App\Services;

use App\Models\Accounts;
use App\Models\FiscalYear;
use App\Models\Voucher;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * سرویس سند افتتاحیه (Opening Voucher) — مدل سپیدار.
 *
 * مانده‌های افتتاحیهٔ حساب‌های دائمی (ترازنامه‌ای) را در ابتدای سال مالی
 * به‌صورت یک سند افتتاحیهٔ متوازن (Σ بدهکار = Σ بستانکار) ثبت می‌کند.
 */
class OpeningVoucherService
{
    public function __construct(private NumberingService $numberingService) {}

    /**
     * حساب‌های دائمی قابل ثبت (برگ/leaf) برای سند افتتاحیه.
     * حساب‌های موقت (درآمد/هزینه) و حساب‌های کل دارای زیرمجموعه حذف می‌شوند.
     */
    public function permanentAccounts($user)
    {
        $query = Accounts::query()->where('isActive', 1)->orderBy('code')->orderBy('name');

        if ((int) $user?->isGod !== 1) {
            $tenantId = $this->tenantId($user);
            $query->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)->orWhere('tenants_id', $tenantId);
            });
        }

        $accounts = $query->get();
        $parentIds = $accounts->pluck('parent_id')->filter()->map(fn ($id) => (int) $id)->unique()->flip();

        return $accounts
            ->reject(fn ($account) => $this->isTemporaryAccount($account))
            ->reject(fn ($account) => $parentIds->has((int) $account->id))
            ->values();
    }

    /**
     * ردیف‌های پیش‌فرض سند افتتاحیه.
     * اگر سند افتتاحیهٔ موقت قبلی برای این سال مالی وجود داشته باشد، همان بارگذاری می‌شود؛
     * در غیر این‌صورت از مانده افتتاحیهٔ هر حساب (opening_balance + ماهیت حساب) ساخته می‌شود.
     */
    public function buildRows($user, ?FiscalYear $fiscalYear): array
    {
        $existing = $this->existingDraft($user, $fiscalYear);

        if ($existing) {
            return $existing->items->map(fn ($item) => [
                'account_id' => (int) $item->account_id,
                'description' => $item->description,
                'debit_amount' => (float) $item->debit_amount,
                'credit_amount' => (float) $item->credit_amount,
                'cost_center_id' => $item->cost_center_id,
                'revenue_center_id' => $item->revenue_center_id,
                'branch_id' => $item->branch_id,
                'project_code' => $item->project_code,
                'product_id' => $item->product_id,
                'customer_id' => $item->customer_id,
                'employee_id' => $item->employee_id,
                'currency_id' => $item->currency_id,
                'foreign_debit_amount' => $item->foreign_debit_amount,
                'foreign_credit_amount' => $item->foreign_credit_amount,
                'exchange_rate' => $item->exchange_rate,
            ])->values()->all();
        }

        return $this->permanentAccounts($user)
            ->map(function ($account) {
                $balance = (float) $account->opening_balance;
                $isCreditNature = (int) $account->nature === 2;
                $debit = 0.0;
                $credit = 0.0;

                if ($isCreditNature) {
                    $credit = $balance >= 0 ? $balance : 0.0;
                    $debit = $balance < 0 ? abs($balance) : 0.0;
                } else {
                    $debit = $balance >= 0 ? $balance : 0.0;
                    $credit = $balance < 0 ? abs($balance) : 0.0;
                }

                return [
                    'account_id' => (int) $account->id,
                    'description' => 'مانده افتتاحیه ' . $account->name,
                    'debit_amount' => round($debit, 2),
                    'credit_amount' => round($credit, 2),
                ];
            })
            ->filter(fn ($row) => $row['debit_amount'] > 0 || $row['credit_amount'] > 0)
            ->values()
            ->all();
    }

    public function existingDraft($user, ?FiscalYear $fiscalYear): ?Voucher
    {
        if (!$fiscalYear) {
            return null;
        }

        $query = Voucher::with('items')
            ->where('document_type', 'period_opening')
            ->where('is_permanent', false)
            ->where('status', 'draft');

        if (Schema::hasColumn('vouchers', 'fiscal_year_id')) {
            $query->where('fiscal_year_id', $fiscalYear->id);
        }

        if ((int) $user?->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
        }

        return $query->orderByDesc('id')->first();
    }

    public function save(array $payload, $user, FiscalYear $fiscalYear): Voucher
    {
        $lines = $this->normalizeLines($payload);

        if (count($lines) < 2) {
            throw ValidationException::withMessages([
                'items' => 'سند افتتاحیه باید حداقل دو ردیف داشته باشد.',
            ]);
        }

        $totalDebit = round(array_sum(array_column($lines, 'debit_amount')), 2);
        $totalCredit = round(array_sum(array_column($lines, 'credit_amount')), 2);

        if ($totalDebit <= 0 || $totalDebit !== $totalCredit) {
            throw ValidationException::withMessages([
                'items' => 'سند افتتاحیه باید متوازن باشد؛ جمع بدهکار و بستانکار برابر و بزرگ‌تر از صفر باشد. (اختلاف فعلی: ' . number_format(abs($totalDebit - $totalCredit)) . ')',
            ]);
        }

        if (!$fiscalYear->isOpen()) {
            throw ValidationException::withMessages([
                'fiscal_year_id' => 'سال مالی انتخاب‌شده باز نیست و امکان ثبت سند افتتاحیه در آن وجود ندارد.',
            ]);
        }

        $tenantId = $this->tenantId($user);
        $organizationId = $this->organizationId($user);
        $date = Arr::get($payload, 'voucher_date_en') ?: optional($fiscalYear->starts_at)->toDateString() ?: now()->toDateString();
        $reference = $this->trimNullable(Arr::get($payload, 'reference_number'));
        $description = $this->trimNullable(Arr::get($payload, 'description')) ?: 'سند افتتاحیه ' . $fiscalYear->title;

        return DB::transaction(function () use ($lines, $user, $fiscalYear, $tenantId, $organizationId, $date, $reference, $description, $totalDebit, $totalCredit) {
            $existing = $this->existingDraft($user, $fiscalYear);

            $attributes = [
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'account_id' => $lines[0]['account_id'],
                'voucher_type' => 1,
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $totalDebit,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'method' => 0,
                'document_type' => 'period_opening',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => FiscalYear::class,
                'source_id' => $fiscalYear->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => $description,
            ];

            if (Schema::hasColumn('vouchers', 'reference_number')) {
                $attributes['reference_number'] = $reference;
            }

            if (Schema::hasColumn('vouchers', 'fiscal_year_id')) {
                $attributes['fiscal_year_id'] = $fiscalYear->id;
            }

            if ($existing) {
                $existing->update(array_merge($attributes, ['updated_by' => $user?->id]));
                $existing->items()->delete();
                $voucher = $existing;
            } else {
                $attributes['factor_id'] = 0;
                $attributes['voucher_number'] = $this->numberingService->nextVoucherNumber($tenantId, $date, 'OPN');
                $attributes['created_by'] = $user?->id;
                $voucher = Voucher::create($attributes);
            }

            foreach ($lines as $line) {
                $voucher->items()->create(array_merge([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $line['account_id'],
                    'amount' => max($line['debit_amount'], $line['credit_amount']),
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'method' => $line['debit_amount'] > 0 ? 1 : 0,
                    'description' => $line['description'],
                ], $line['analytic']));

                $this->persistOpeningBalance($line);
            }

            return $voucher->refresh()->load('items.account');
        });
    }

    private function persistOpeningBalance(array $line): void
    {
        if (!Schema::hasColumn('accounts', 'opening_balance')) {
            return;
        }

        $signed = round($line['debit_amount'] - $line['credit_amount'], 2);
        Accounts::whereKey($line['account_id'])->update(['opening_balance' => $signed]);
    }

    private function normalizeLines(array $payload): array
    {
        $accountIds = Arr::get($payload, 'account_id', []);
        $debits = Arr::get($payload, 'debit_amount', []);
        $credits = Arr::get($payload, 'credit_amount', []);
        $descriptions = Arr::get($payload, 'item_description', []);
        $costCenterIds = Arr::get($payload, 'cost_center_id', []);
        $revenueCenterIds = Arr::get($payload, 'revenue_center_id', []);
        $branchIds = Arr::get($payload, 'branch_id', []);
        $projectCodes = Arr::get($payload, 'project_code', []);
        $productIds = Arr::get($payload, 'product_id', []);
        $customerIds = Arr::get($payload, 'customer_id', []);
        $employeeIds = Arr::get($payload, 'employee_id', []);
        $currencyIds = Arr::get($payload, 'currency_id', []);
        $foreignDebits = Arr::get($payload, 'foreign_debit_amount', []);
        $foreignCredits = Arr::get($payload, 'foreign_credit_amount', []);
        $exchangeRates = Arr::get($payload, 'exchange_rate', []);
        $lines = [];

        foreach ($accountIds as $index => $accountId) {
            $debit = $this->money($debits[$index] ?? 0);
            $credit = $this->money($credits[$index] ?? 0);

            if (!$accountId || ($debit <= 0 && $credit <= 0)) {
                continue;
            }

            if ($debit > 0 && $credit > 0) {
                throw ValidationException::withMessages([
                    'items' => 'در هر ردیف فقط یکی از مبلغ بدهکار یا بستانکار باید پر شود.',
                ]);
            }

            $currencyId = !empty($currencyIds[$index]) ? (int) $currencyIds[$index] : null;
            $foreignDebit = (float) ($foreignDebits[$index] ?? 0);
            $foreignCredit = (float) ($foreignCredits[$index] ?? 0);
            $exchangeRate = (float) ($exchangeRates[$index] ?? 0);

            $lines[] = [
                'account_id' => (int) $accountId,
                'debit_amount' => $debit,
                'credit_amount' => $credit,
                'description' => $this->trimNullable($descriptions[$index] ?? null),
                'analytic' => [
                    'cost_center_id' => !empty($costCenterIds[$index]) ? (int) $costCenterIds[$index] : null,
                    'revenue_center_id' => !empty($revenueCenterIds[$index]) ? (int) $revenueCenterIds[$index] : null,
                    'branch_id' => !empty($branchIds[$index]) ? (int) $branchIds[$index] : null,
                    'project_code' => $this->trimNullable($projectCodes[$index] ?? null),
                    'product_id' => !empty($productIds[$index]) ? (int) $productIds[$index] : null,
                    'customer_id' => !empty($customerIds[$index]) ? (int) $customerIds[$index] : null,
                    'employee_id' => !empty($employeeIds[$index]) ? (int) $employeeIds[$index] : null,
                    'currency_id' => $currencyId,
                    'foreign_debit_amount' => $currencyId && $foreignDebit > 0 ? $foreignDebit : null,
                    'foreign_credit_amount' => $currencyId && $foreignCredit > 0 ? $foreignCredit : null,
                    'exchange_rate' => $currencyId && $exchangeRate > 0 ? $exchangeRate : null,
                ],
            ];
        }

        return $lines;
    }

    private function isTemporaryAccount(?Accounts $account): bool
    {
        $category = $account?->account_category;

        if (in_array($category, ['income', 'expense'], true)) {
            return true;
        }

        return str_starts_with((string) $account?->code, 'SYS-4') || str_starts_with((string) $account?->code, 'SYS-5');
    }

    private function money($value): float
    {
        return round((float) str_replace([',', ' '], '', (string) $value), 2);
    }

    private function trimNullable($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
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

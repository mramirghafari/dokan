<?php

namespace App\Services;

use App\Models\Accounts;
use App\Models\CompanyAsset;
use App\Models\CompanyAssetDisposal;
use App\Models\CompanyAssetEvent;
use App\Models\FiscalYear;
use App\Models\Voucher;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class FixedAssetDisposalService
{
    public function __construct(private NumberingService $numberingService) {}

    public function post(CompanyAsset $asset, array $payload, $user): CompanyAssetDisposal
    {
        $asset->refresh();

        if (in_array($asset->status, ['sold', 'scrapped'], true)) {
            throw ValidationException::withMessages(['company_asset_id' => 'این دارایی قبلا از چرخه بهره برداری خارج شده است.']);
        }

        if (!$asset->asset_account_id) {
            throw ValidationException::withMessages(['asset_account_id' => 'برای فروش یا اسقاط دارایی، حساب دارایی باید روی دفتر اموال ثبت شده باشد.']);
        }

        $acquisitionCost = round((float) $asset->acquisition_cost, 2);
        $accumulatedDepreciation = round(min((float) $asset->accumulated_depreciation, $acquisitionCost), 2);
        $bookValue = round(max(0, $acquisitionCost - $accumulatedDepreciation), 2);
        $proceedsAmount = round(max(0, (float) Arr::get($payload, 'proceeds_amount', 0)), 2);

        if ($accumulatedDepreciation > 0 && !$asset->accumulated_depreciation_account_id) {
            throw ValidationException::withMessages(['accumulated_depreciation_account_id' => 'برای تسویه استهلاک انباشته، حساب استهلاک انباشته دارایی باید ثبت شده باشد.']);
        }

        if ($proceedsAmount > 0 && empty($payload['proceeds_account_id'])) {
            throw ValidationException::withMessages(['proceeds_account_id' => 'برای فروش با مبلغ دریافتی، حساب نقد/دریافتنی فروش دارایی را انتخاب کنید.']);
        }

        $tenantId = $asset->tenant_id ?: $this->tenantId($user);
        $organizationId = $asset->organization_id ?: $this->organizationId($user);
        $date = Arr::get($payload, 'disposal_date_en') ?: now()->toDateString();
        $this->ensureVoucherDateIsOpen($date, $tenantId);

        return DB::transaction(function () use ($asset, $payload, $user, $tenantId, $organizationId, $date, $acquisitionCost, $accumulatedDepreciation, $bookValue, $proceedsAmount) {
            $gainAmount = round(max(0, $proceedsAmount - $bookValue), 2);
            $lossAmount = round(max(0, $bookValue - $proceedsAmount), 2);
            $statusAfter = $this->statusAfter(Arr::get($payload, 'disposal_type'));
            $gainAccountId = $gainAmount > 0 ? $this->systemAccountId('SYS-4205', 'سود فروش دارایی ثابت', $tenantId, $organizationId, $user) : null;
            $lossAccountId = $lossAmount > 0 ? $this->systemAccountId('SYS-5205', 'زیان فروش دارایی ثابت', $tenantId, $organizationId, $user) : null;

            $disposal = CompanyAssetDisposal::create([
                'company_asset_id' => $asset->id,
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'disposal_type' => Arr::get($payload, 'disposal_type'),
                'disposal_date_en' => $date,
                'disposal_date_fa' => $this->jalaliDate($date),
                'acquisition_cost' => $acquisitionCost,
                'accumulated_depreciation' => $accumulatedDepreciation,
                'book_value' => $bookValue,
                'proceeds_amount' => $proceedsAmount,
                'gain_amount' => $gainAmount,
                'loss_amount' => $lossAmount,
                'proceeds_account_id' => Arr::get($payload, 'proceeds_account_id'),
                'gain_account_id' => $gainAccountId,
                'loss_account_id' => $lossAccountId,
                'status_before' => $asset->status,
                'status_after' => $statusAfter,
                'buyer_name' => Arr::get($payload, 'buyer_name'),
                'description' => Arr::get($payload, 'description'),
                'created_by' => $user?->id,
            ]);

            $voucher = $this->createVoucher($asset, $disposal, $user, $tenantId, $organizationId, $date);
            $event = CompanyAssetEvent::create([
                'company_asset_id' => $asset->id,
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'event_type' => $disposal->disposal_type === 'sale' ? 'sale' : ($disposal->disposal_type === 'scrap' ? 'scrap' : 'status_change'),
                'event_date_en' => $date,
                'event_date_fa' => $this->jalaliDate($date),
                'from_store_id' => $asset->store_id,
                'from_employee_id' => $asset->custodian_employee_id,
                'status_before' => $asset->status,
                'status_after' => $statusAfter,
                'amount' => $proceedsAmount,
                'title' => $this->disposalTitle($disposal),
                'description' => Arr::get($payload, 'description'),
                'created_by' => $user?->id,
            ]);

            $asset->update([
                'status' => $statusAfter,
                'updated_by' => $user?->id,
            ]);

            $disposal->update([
                'voucher_id' => $voucher->id,
                'event_id' => $event->id,
            ]);

            return $disposal->refresh()->load('voucher.items', 'event');
        });
    }

    private function createVoucher(CompanyAsset $asset, CompanyAssetDisposal $disposal, $user, ?int $tenantId, ?int $organizationId, string $date): Voucher
    {
        $lines = [];

        if ((float) $disposal->proceeds_amount > 0) {
            $lines[] = [
                'account_id' => $disposal->proceeds_account_id,
                'debit_amount' => (float) $disposal->proceeds_amount,
                'credit_amount' => 0,
                'description' => 'دریافتنی/نقد فروش دارایی ' . $asset->asset_code,
            ];
        }

        if ((float) $disposal->accumulated_depreciation > 0) {
            $lines[] = [
                'account_id' => $asset->accumulated_depreciation_account_id,
                'debit_amount' => (float) $disposal->accumulated_depreciation,
                'credit_amount' => 0,
                'description' => 'تسویه استهلاک انباشته ' . $asset->asset_code,
            ];
        }

        if ((float) $disposal->loss_amount > 0) {
            $lines[] = [
                'account_id' => $disposal->loss_account_id,
                'debit_amount' => (float) $disposal->loss_amount,
                'credit_amount' => 0,
                'description' => 'زیان خروج دارایی ' . $asset->asset_code,
            ];
        }

        $lines[] = [
            'account_id' => $asset->asset_account_id,
            'debit_amount' => 0,
            'credit_amount' => (float) $disposal->acquisition_cost,
            'description' => 'خروج بهای تمام شده دارایی ' . $asset->asset_code,
        ];

        if ((float) $disposal->gain_amount > 0) {
            $lines[] = [
                'account_id' => $disposal->gain_account_id,
                'debit_amount' => 0,
                'credit_amount' => (float) $disposal->gain_amount,
                'description' => 'سود فروش دارایی ' . $asset->asset_code,
            ];
        }

        $totalDebit = round(array_sum(array_column($lines, 'debit_amount')), 2);
        $totalCredit = round(array_sum(array_column($lines, 'credit_amount')), 2);

        if ($totalDebit <= 0 || $totalDebit !== $totalCredit) {
            throw ValidationException::withMessages(['voucher' => 'سند خروج دارایی تراز نیست.']);
        }

        $voucher = Voucher::create([
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'factor_id' => 0,
            'account_id' => $lines[0]['account_id'],
            'voucher_type' => 0,
            'voucher_number' => $this->numberingService->nextVoucherNumber($tenantId, $date, 'FAD'),
            'voucher_date_fa' => $this->jalaliDate($date),
            'voucher_date_en' => $date,
            'amount' => $totalDebit,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'method' => 0,
            'document_type' => 'asset_disposal',
            'status' => 'draft',
            'is_permanent' => false,
            'source_type' => CompanyAssetDisposal::class,
            'source_id' => $disposal->id,
            'fiscal_year' => $this->jalaliYear($date),
            'description' => $this->disposalTitle($disposal) . ' - ' . $asset->asset_code . ' - ' . $asset->name,
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
                'method' => $line['debit_amount'] > 0 ? 1 : 0,
                'cost_center_id' => $asset->cost_center_id,
                'branch_id' => $asset->store_id,
                'employee_id' => $asset->custodian_employee_id,
                'analytic_note' => 'company_asset_disposal',
                'description' => $line['description'],
            ]);
        }

        return $voucher->load('items');
    }

    private function statusAfter(string $disposalType): string
    {
        return match ($disposalType) {
            'sale' => 'sold',
            'scrap' => 'scrapped',
            default => 'idle',
        };
    }

    private function disposalTitle(CompanyAssetDisposal $disposal): string
    {
        return match ($disposal->disposal_type) {
            'sale' => 'فروش دارایی ثابت',
            'scrap' => 'اسقاط دارایی ثابت',
            default => 'خروج از سرویس دارایی ثابت',
        };
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

        $attributes = [
            'code' => $code,
            'name' => $name,
            'level' => 3,
            'type' => 'system',
            'nature' => str_starts_with($code, 'SYS-4') ? 0 : 1,
            'isActive' => 1,
            'parent_id' => 0,
        ];

        if (Schema::hasColumn('accounts', 'is_system')) {
            $attributes['is_system'] = true;
        }
        if (Schema::hasColumn('accounts', 'account_category')) {
            $attributes['account_category'] = str_starts_with($code, 'SYS-4') ? 'income' : 'expense';
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

    private function ensureVoucherDateIsOpen(string $date, ?int $tenantId): void
    {
        if (!Schema::hasTable('fiscal_years')) {
            return;
        }

        $query = FiscalYear::query()->whereDate('starts_at', '<=', $date)->whereDate('ends_at', '>=', $date);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        } else {
            $query->whereNull('tenant_id');
        }

        $fiscalYear = $query->where('status', '<>', 'open')->orderByDesc('id')->first();

        if ($fiscalYear) {
            throw ValidationException::withMessages(['disposal_date_en' => 'سال مالی «' . $fiscalYear->title . '» بسته است و ثبت خروج دارایی در این بازه مجاز نیست.']);
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

        if (is_array($decoded)) {
            return isset($decoded[0]) ? (int) $decoded[0] : null;
        }

        return $organizationId ? (int) $organizationId : null;
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
}

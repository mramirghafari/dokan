<?php

namespace App\Services;

use App\Models\CompanyAsset;
use App\Models\CompanyAssetDepreciation;
use App\Models\CompanyAssetDepreciationPolicy;
use App\Models\FiscalYear;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class FixedAssetDepreciationService
{
    public function __construct(private NumberingService $numberingService) {}

    public function preview($assets, string $periodStart, string $periodEnd): Collection
    {
        $this->validatePeriod($periodStart, $periodEnd);
        $assetCollection = $assets instanceof EloquentCollection || $assets instanceof Collection ? $assets : collect($assets);

        return $assetCollection
            ->map(fn(CompanyAsset $asset) => $this->previewAsset($asset, $periodStart, $periodEnd))
            ->filter(fn(?array $row) => $row && $row['period_amount'] > 0)
            ->values();
    }

    public function post($assets, string $periodStart, string $periodEnd, $user): array
    {
        $this->validatePeriod($periodStart, $periodEnd);
        $tenantId = $this->tenantId($user);
        $this->ensureVoucherDateIsOpen($periodEnd, $tenantId);

        return DB::transaction(function () use ($assets, $periodStart, $periodEnd, $user) {
            $posted = 0;
            $skipped = 0;
            $total = 0;
            $vouchers = [];

            foreach ($assets as $asset) {
                $asset->refresh();
                $row = $this->previewAsset($asset, $periodStart, $periodEnd);

                if (!$row || $row['period_amount'] <= 0) {
                    $skipped++;
                    continue;
                }

                $voucher = $this->postAsset($asset, $row, $periodStart, $periodEnd, $user);
                $posted++;
                $total += $row['period_amount'];
                $vouchers[] = $voucher->id;
            }

            return [
                'posted' => $posted,
                'skipped' => $skipped,
                'total' => round($total, 2),
                'voucher_ids' => $vouchers,
            ];
        });
    }

    private function postAsset(CompanyAsset $asset, array $row, string $periodStart, string $periodEnd, $user): Voucher
    {
        $tenantId = $asset->tenant_id ?: $this->tenantId($user);
        $organizationId = $asset->organization_id ?: $this->organizationId($user);
        $amount = $row['period_amount'];
        $this->ensureVoucherDateIsOpen($periodEnd, $tenantId);

        $policy = $row['policy'];

        $depreciation = CompanyAssetDepreciation::create([
            'company_asset_id' => $asset->id,
            'policy_id' => $policy?->id,
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'period_start_en' => $periodStart,
            'period_end_en' => $periodEnd,
            'period_start_fa' => $this->jalaliDate($periodStart),
            'period_end_fa' => $this->jalaliDate($periodEnd),
            'depreciable_amount' => $row['depreciable_amount'],
            'period_amount' => $amount,
            'accumulated_before' => $row['accumulated_before'],
            'accumulated_after' => $row['accumulated_after'],
            'book_value_before' => $row['book_value_before'],
            'book_value_after' => $row['book_value_after'],
            'status' => 'posted',
            'created_by' => $user?->id,
        ]);

        $voucher = Voucher::create([
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'factor_id' => 0,
            'account_id' => $row['depreciation_expense_account_id'],
            'voucher_type' => 0,
            'voucher_number' => $this->numberingService->nextVoucherNumber($tenantId, $periodEnd, 'DEP'),
            'voucher_date_fa' => $this->jalaliDate($periodEnd),
            'voucher_date_en' => $periodEnd,
            'amount' => $amount,
            'total_debit' => $amount,
            'total_credit' => $amount,
            'method' => 0,
            'document_type' => 'asset_depreciation',
            'status' => 'draft',
            'is_permanent' => false,
            'source_type' => CompanyAssetDepreciation::class,
            'source_id' => $depreciation->id,
            'fiscal_year' => $this->jalaliYear($periodEnd),
            'description' => 'سند استهلاک دارایی ' . $asset->asset_code . ' - ' . $asset->name,
            'created_by' => $user?->id,
        ]);

        $voucher->items()->create([
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'account_id' => $row['depreciation_expense_account_id'],
            'amount' => $amount,
            'debit_amount' => $amount,
            'credit_amount' => 0,
            'method' => 1,
            'cost_center_id' => $asset->cost_center_id,
            'branch_id' => $asset->store_id,
            'employee_id' => $asset->custodian_employee_id,
            'analytic_note' => 'company_asset_depreciation',
            'description' => 'هزینه استهلاک ' . $asset->asset_code,
        ]);

        $voucher->items()->create([
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'account_id' => $row['accumulated_depreciation_account_id'],
            'amount' => $amount,
            'debit_amount' => 0,
            'credit_amount' => $amount,
            'method' => 0,
            'cost_center_id' => $asset->cost_center_id,
            'branch_id' => $asset->store_id,
            'employee_id' => $asset->custodian_employee_id,
            'analytic_note' => 'company_asset_depreciation',
            'description' => 'استهلاک انباشته ' . $asset->asset_code,
        ]);

        $asset->update([
            'accumulated_depreciation' => $row['accumulated_after'],
            'updated_by' => $user?->id,
        ]);

        $depreciation->update(['voucher_id' => $voucher->id]);

        return $voucher->load('items');
    }

    private function previewAsset(CompanyAsset $asset, string $periodStart, string $periodEnd): ?array
    {
        $policy = $this->activePolicy($asset, $periodEnd);
        $method = $policy?->depreciation_method ?: $asset->depreciation_method;
        $usefulLifeMonths = $policy?->useful_life_months ?: $asset->useful_life_months;
        $salvageValue = $policy ? (float) $policy->salvage_value : (float) $asset->salvage_value;
        $annualRatePercent = $policy?->annual_rate_percent ? (float) $policy->annual_rate_percent : null;
        $expenseAccountId = $policy?->depreciation_expense_account_id ?: $asset->depreciation_expense_account_id;
        $accumulatedAccountId = $policy?->accumulated_depreciation_account_id ?: $asset->accumulated_depreciation_account_id;

        if ($asset->status !== 'active' || $method === 'none') {
            return null;
        }

        if ((!$usefulLifeMonths && $method === 'straight_line') || !$expenseAccountId || !$accumulatedAccountId) {
            return null;
        }

        if ($method === 'rate_percent' && (!$annualRatePercent || $annualRatePercent <= 0)) {
            return null;
        }

        if (CompanyAssetDepreciation::where('company_asset_id', $asset->id)
            ->whereDate('period_start_en', $periodStart)
            ->whereDate('period_end_en', $periodEnd)
            ->exists()
        ) {
            return null;
        }

        $depreciableAmount = max(0, (float) $asset->acquisition_cost - $salvageValue);
        $accumulatedBefore = min((float) $asset->accumulated_depreciation, $depreciableAmount);
        $remaining = max(0, $depreciableAmount - $accumulatedBefore);

        if ($remaining <= 0) {
            return null;
        }

        $monthlyAmount = $method === 'rate_percent'
            ? round(((float) $asset->acquisition_cost * ($annualRatePercent / 100)) / 12, 2)
            : round($depreciableAmount / (int) $usefulLifeMonths, 2);
        $periodAmount = min($remaining, round($monthlyAmount * $this->periodMonths($periodStart, $periodEnd), 2));

        if ($periodAmount <= 0) {
            return null;
        }

        return [
            'asset' => $asset,
            'policy' => $policy,
            'depreciation_method' => $method,
            'useful_life_months' => $usefulLifeMonths,
            'salvage_value' => round($salvageValue, 2),
            'annual_rate_percent' => $annualRatePercent,
            'depreciation_expense_account_id' => $expenseAccountId,
            'accumulated_depreciation_account_id' => $accumulatedAccountId,
            'depreciable_amount' => round($depreciableAmount, 2),
            'period_amount' => round($periodAmount, 2),
            'accumulated_before' => round($accumulatedBefore, 2),
            'accumulated_after' => round($accumulatedBefore + $periodAmount, 2),
            'book_value_before' => $asset->bookValue(),
            'book_value_after' => round(max($salvageValue, (float) $asset->acquisition_cost - $accumulatedBefore - $periodAmount), 2),
        ];
    }

    private function activePolicy(CompanyAsset $asset, string $periodEnd): ?CompanyAssetDepreciationPolicy
    {
        return CompanyAssetDepreciationPolicy::where('company_asset_id', $asset->id)
            ->whereDate('effective_date_en', '<=', $periodEnd)
            ->orderByDesc('effective_date_en')
            ->orderByDesc('id')
            ->first();
    }

    private function validatePeriod(string $periodStart, string $periodEnd): void
    {
        if (Carbon::parse($periodEnd)->lt(Carbon::parse($periodStart))) {
            throw ValidationException::withMessages(['period_end_en' => 'تاریخ پایان دوره استهلاک باید بعد از شروع باشد.']);
        }
    }

    private function periodMonths(string $periodStart, string $periodEnd): int
    {
        $start = Carbon::parse($periodStart)->startOfMonth();
        $end = Carbon::parse($periodEnd)->startOfMonth();

        return max(1, $start->diffInMonths($end) + 1);
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
            throw ValidationException::withMessages(['period_end_en' => 'سال مالی «' . $fiscalYear->title . '» بسته است و ثبت سند استهلاک در این بازه مجاز نیست.']);
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

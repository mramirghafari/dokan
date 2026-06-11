<?php

namespace App\Services;

use App\Models\CompanyAsset;
use App\Models\CompanyAssetCapitalAddition;
use App\Models\CompanyAssetEvent;
use App\Models\FiscalYear;
use App\Models\Voucher;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class FixedAssetCapitalAdditionService
{
    public function __construct(private NumberingService $numberingService) {}

    public function post(CompanyAsset $asset, array $payload, $user): CompanyAssetCapitalAddition
    {
        $asset->refresh();

        if (in_array($asset->status, ['sold', 'scrapped'], true)) {
            throw ValidationException::withMessages(['company_asset_id' => 'برای دارایی فروخته شده یا اسقاط شده نمی توان الحاق سرمایه ای ثبت کرد.']);
        }

        if (!$asset->asset_account_id && empty($payload['asset_account_id'])) {
            throw ValidationException::withMessages(['asset_account_id' => 'برای سرمایه ای کردن هزینه، حساب دارایی باید انتخاب شود.']);
        }

        if (empty($payload['credit_account_id'])) {
            throw ValidationException::withMessages(['credit_account_id' => 'حساب بستانکار پرداختنی/نقد را انتخاب کنید.']);
        }

        $amount = round(max(0, (float) Arr::get($payload, 'amount', 0)), 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'مبلغ الحاق سرمایه ای باید بزرگتر از صفر باشد.']);
        }

        $tenantId = $asset->tenant_id ?: $this->tenantId($user);
        $organizationId = $asset->organization_id ?: $this->organizationId($user);
        $date = Arr::get($payload, 'addition_date_en') ?: now()->toDateString();
        $this->ensureVoucherDateIsOpen($date, $tenantId);

        return DB::transaction(function () use ($asset, $payload, $user, $tenantId, $organizationId, $date, $amount) {
            $assetCostBefore = round((float) $asset->acquisition_cost, 2);
            $assetCostAfter = round($assetCostBefore + $amount, 2);
            $assetAccountId = Arr::get($payload, 'asset_account_id') ?: $asset->asset_account_id;

            $addition = CompanyAssetCapitalAddition::create([
                'company_asset_id' => $asset->id,
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'addition_type' => Arr::get($payload, 'addition_type', 'major_repair'),
                'addition_date_en' => $date,
                'addition_date_fa' => $this->jalaliDate($date),
                'amount' => $amount,
                'asset_cost_before' => $assetCostBefore,
                'asset_cost_after' => $assetCostAfter,
                'asset_account_id' => $assetAccountId,
                'credit_account_id' => Arr::get($payload, 'credit_account_id'),
                'supplier_name' => Arr::get($payload, 'supplier_name'),
                'reference_number' => Arr::get($payload, 'reference_number'),
                'description' => Arr::get($payload, 'description'),
                'created_by' => $user?->id,
            ]);

            $voucher = $this->createVoucher($asset, $addition, $user, $tenantId, $organizationId, $date);
            $event = CompanyAssetEvent::create([
                'company_asset_id' => $asset->id,
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'event_type' => 'valuation',
                'event_date_en' => $date,
                'event_date_fa' => $this->jalaliDate($date),
                'from_store_id' => $asset->store_id,
                'from_employee_id' => $asset->custodian_employee_id,
                'status_before' => $asset->status,
                'status_after' => $asset->status,
                'amount' => $amount,
                'title' => $this->additionTitle($addition->addition_type),
                'description' => Arr::get($payload, 'description'),
                'created_by' => $user?->id,
            ]);

            $asset->update([
                'asset_account_id' => $assetAccountId,
                'acquisition_cost' => $assetCostAfter,
                'updated_by' => $user?->id,
            ]);

            $addition->update([
                'voucher_id' => $voucher->id,
                'event_id' => $event->id,
            ]);

            return $addition->refresh()->load('voucher.items', 'event');
        });
    }

    private function createVoucher(CompanyAsset $asset, CompanyAssetCapitalAddition $addition, $user, ?int $tenantId, ?int $organizationId, string $date): Voucher
    {
        $amount = (float) $addition->amount;
        $voucher = Voucher::create([
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'factor_id' => 0,
            'account_id' => $addition->asset_account_id,
            'voucher_type' => 0,
            'voucher_number' => $this->numberingService->nextVoucherNumber($tenantId, $date, 'FAC'),
            'voucher_date_fa' => $this->jalaliDate($date),
            'voucher_date_en' => $date,
            'amount' => $amount,
            'total_debit' => $amount,
            'total_credit' => $amount,
            'method' => 0,
            'document_type' => 'asset_capital_addition',
            'status' => 'draft',
            'is_permanent' => false,
            'source_type' => CompanyAssetCapitalAddition::class,
            'source_id' => $addition->id,
            'fiscal_year' => $this->jalaliYear($date),
            'description' => $this->additionTitle($addition->addition_type) . ' - ' . $asset->asset_code . ' - ' . $asset->name,
            'created_by' => $user?->id,
        ]);

        $voucher->items()->create([
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'account_id' => $addition->asset_account_id,
            'amount' => $amount,
            'debit_amount' => $amount,
            'credit_amount' => 0,
            'method' => 1,
            'cost_center_id' => $asset->cost_center_id,
            'branch_id' => $asset->store_id,
            'employee_id' => $asset->custodian_employee_id,
            'analytic_note' => 'company_asset_capital_addition',
            'description' => 'افزایش بهای تمام شده دارایی ' . $asset->asset_code,
        ]);

        $voucher->items()->create([
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'account_id' => $addition->credit_account_id,
            'amount' => $amount,
            'debit_amount' => 0,
            'credit_amount' => $amount,
            'method' => 0,
            'cost_center_id' => $asset->cost_center_id,
            'branch_id' => $asset->store_id,
            'employee_id' => $asset->custodian_employee_id,
            'analytic_note' => 'company_asset_capital_addition',
            'description' => 'بستانکار الحاق سرمایه ای دارایی ' . $asset->asset_code,
        ]);

        return $voucher->load('items');
    }

    private function additionTitle(string $additionType): string
    {
        return match ($additionType) {
            'expansion' => 'الحاق و گسترش سرمایه ای دارایی',
            'component' => 'افزودن قطعه سرمایه ای دارایی',
            'upgrade' => 'ارتقای سرمایه ای دارایی',
            default => 'تعمیرات اساسی سرمایه ای دارایی',
        };
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
            throw ValidationException::withMessages(['addition_date_en' => 'سال مالی «' . $fiscalYear->title . '» بسته است و ثبت الحاق سرمایه ای در این بازه مجاز نیست.']);
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

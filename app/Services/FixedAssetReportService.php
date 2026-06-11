<?php

namespace App\Services;

use App\Models\CompanyAsset;
use Illuminate\Support\Collection;

class FixedAssetReportService
{
    public function build($user, array $filters = []): array
    {
        $requestedDateBasis = $filters['date_basis'] ?? 'in_service';
        $dateBasis = in_array($requestedDateBasis, ['acquisition', 'in_service'], true)
            ? $requestedDateBasis
            : 'in_service';
        $dateColumn = $dateBasis === 'acquisition' ? 'acquisition_date_en' : 'in_service_date_en';

        $query = CompanyAsset::with([
            'store',
            'costCenter',
            'custodian',
            'depreciations',
            'disposals.voucher',
            'capitalAdditions.voucher',
            'depreciationPolicies',
        ])->orderBy('asset_category')->orderBy('asset_code')->orderBy('id');

        if ((int) $user?->isGod !== 1) {
            $query->where('tenant_id', $this->tenantId($user));
        }

        foreach (['status', 'asset_category', 'cost_center_id', 'store_id', 'custodian_employee_id'] as $filter) {
            if (!empty($filters[$filter])) {
                $query->where($filter, in_array($filter, ['cost_center_id', 'store_id', 'custodian_employee_id'], true) ? (int) $filters[$filter] : $filters[$filter]);
            }
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate($dateColumn, '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate($dateColumn, '<=', $filters['to_date']);
        }

        if (!empty($filters['q'])) {
            $search = trim((string) $filters['q']);
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('asset_code', 'like', '%' . $search . '%')
                    ->orWhere('plaque_number', 'like', '%' . $search . '%')
                    ->orWhere('serial_number', 'like', '%' . $search . '%');
            });
        }

        $assets = $query->get();
        $rows = $this->rows($assets);

        return [
            'filters' => [
                'date_basis' => $dateBasis,
                'from_date' => $filters['from_date'] ?? null,
                'to_date' => $filters['to_date'] ?? null,
                'status' => $filters['status'] ?? null,
                'asset_category' => $filters['asset_category'] ?? null,
                'cost_center_id' => $filters['cost_center_id'] ?? null,
                'store_id' => $filters['store_id'] ?? null,
                'custodian_employee_id' => $filters['custodian_employee_id'] ?? null,
                'q' => $filters['q'] ?? null,
            ],
            'rows' => $rows,
            'summary' => $this->summary($rows),
            'groups' => [
                'category' => $this->groupSummary($rows, 'asset_category_label'),
                'status' => $this->groupSummary($rows, 'status_label'),
                'store' => $this->groupSummary($rows, 'store_title'),
                'cost_center' => $this->groupSummary($rows, 'cost_center_title'),
                'custodian' => $this->groupSummary($rows, 'custodian_name'),
            ],
        ];
    }

    private function rows(Collection $assets): Collection
    {
        return $assets->map(function (CompanyAsset $asset) {
            $depreciationAmount = round((float) $asset->depreciations->sum('period_amount'), 2);
            $capitalAdditionAmount = round((float) $asset->capitalAdditions->sum('amount'), 2);
            $lastDisposal = $asset->disposals->sortByDesc('disposal_date_en')->first();
            $lastPolicy = $asset->depreciationPolicies->sortByDesc('effective_date_en')->first();
            $lastAddition = $asset->capitalAdditions->sortByDesc('addition_date_en')->first();

            return [
                'asset_id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'plaque_number' => $asset->plaque_number,
                'name' => $asset->name,
                'asset_category' => $asset->asset_category,
                'asset_category_label' => $this->assetCategoryLabel($asset->asset_category),
                'status' => $asset->status,
                'status_label' => $this->statusLabel($asset->status),
                'store_title' => $asset->store?->title ?: ($asset->location ?: 'بدون محل'),
                'cost_center_title' => $asset->costCenter?->name ?: 'بدون مرکز هزینه',
                'custodian_name' => $asset->custodian?->name ?: 'بدون تحویل گیرنده',
                'acquisition_date' => optional($asset->acquisition_date_en)->format('Y-m-d'),
                'in_service_date' => optional($asset->in_service_date_en)->format('Y-m-d'),
                'acquisition_cost' => round((float) $asset->acquisition_cost, 2),
                'salvage_value' => round((float) $asset->salvage_value, 2),
                'accumulated_depreciation' => round((float) $asset->accumulated_depreciation, 2),
                'posted_depreciation' => $depreciationAmount,
                'book_value' => $asset->bookValue(),
                'capital_addition_amount' => $capitalAdditionAmount,
                'capital_addition_count' => $asset->capitalAdditions->count(),
                'depreciation_count' => $asset->depreciations->count(),
                'disposal_count' => $asset->disposals->count(),
                'last_policy' => $lastPolicy ? [
                    'date' => $lastPolicy->effective_date_fa ?: optional($lastPolicy->effective_date_en)->format('Y-m-d'),
                    'method' => $lastPolicy->depreciation_method,
                    'rate' => $lastPolicy->annual_rate_percent,
                ] : null,
                'last_addition' => $lastAddition ? [
                    'date' => $lastAddition->addition_date_fa ?: optional($lastAddition->addition_date_en)->format('Y-m-d'),
                    'amount' => round((float) $lastAddition->amount, 2),
                    'type' => $this->additionTypeLabel($lastAddition->addition_type),
                ] : null,
                'last_disposal' => $lastDisposal ? [
                    'date' => $lastDisposal->disposal_date_fa ?: optional($lastDisposal->disposal_date_en)->format('Y-m-d'),
                    'type' => $lastDisposal->disposal_type,
                    'proceeds' => round((float) $lastDisposal->proceeds_amount, 2),
                    'gain' => round((float) $lastDisposal->gain_amount, 2),
                    'loss' => round((float) $lastDisposal->loss_amount, 2),
                ] : null,
            ];
        })->values();
    }

    private function summary(Collection $rows): array
    {
        return [
            'count' => $rows->count(),
            'active' => $rows->where('status', 'active')->count(),
            'idle' => $rows->where('status', 'idle')->count(),
            'disposed' => $rows->whereIn('status', ['sold', 'scrapped'])->count(),
            'acquisition_cost' => round((float) $rows->sum('acquisition_cost'), 2),
            'accumulated_depreciation' => round((float) $rows->sum('accumulated_depreciation'), 2),
            'book_value' => round((float) $rows->sum('book_value'), 2),
            'capital_addition_amount' => round((float) $rows->sum('capital_addition_amount'), 2),
            'posted_depreciation' => round((float) $rows->sum('posted_depreciation'), 2),
        ];
    }

    private function groupSummary(Collection $rows, string $key): Collection
    {
        return $rows->groupBy($key)->map(function (Collection $group, string $label) {
            return [
                'label' => $label ?: 'نامشخص',
                'count' => $group->count(),
                'acquisition_cost' => round((float) $group->sum('acquisition_cost'), 2),
                'accumulated_depreciation' => round((float) $group->sum('accumulated_depreciation'), 2),
                'book_value' => round((float) $group->sum('book_value'), 2),
                'capital_addition_amount' => round((float) $group->sum('capital_addition_amount'), 2),
            ];
        })->sortByDesc('book_value')->values();
    }

    private function assetCategoryLabel(?string $category): string
    {
        return [
            'building' => 'ساختمان و تاسیسات',
            'vehicle' => 'خودرو و ناوگان',
            'machinery' => 'ماشین آلات',
            'office_equipment' => 'تجهیزات اداری',
            'computer' => 'رایانه و تجهیزات IT',
            'furniture' => 'اثاثیه',
            'tool' => 'ابزار و تجهیزات',
            'other' => 'سایر اموال',
        ][$category] ?? ($category ?: 'نامشخص');
    }

    private function statusLabel(?string $status): string
    {
        return [
            'active' => 'فعال در بهره برداری',
            'idle' => 'بلااستفاده موقت',
            'under_repair' => 'در تعمیر',
            'sold' => 'فروخته شده',
            'scrapped' => 'اسقاط شده',
        ][$status] ?? ($status ?: 'نامشخص');
    }

    private function additionTypeLabel(?string $type): string
    {
        return [
            'major_repair' => 'تعمیرات اساسی سرمایه ای',
            'expansion' => 'الحاق/گسترش دارایی',
            'component' => 'افزودن قطعه سرمایه ای',
            'upgrade' => 'ارتقای سرمایه ای',
        ][$type] ?? ($type ?: 'نامشخص');
    }

    private function tenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }
}

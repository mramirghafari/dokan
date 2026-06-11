<?php

namespace App\Services;

use App\Models\VoucherItems;
use Carbon\Carbon;

class AccountingAnalyticDimensionReportService
{
    public const DIMENSIONS = [
        'cost_center' => 'مرکز هزینه',
        'branch' => 'شعبه/انبار',
        'project' => 'پروژه',
        'product' => 'کالا',
        'customer' => 'مشتری',
        'employee' => 'کارمند',
        'contract' => 'قرارداد',
        'route' => 'مسیر',
        'cost_center_project' => 'مرکز هزینه + پروژه',
        'project_contract_product' => 'پروژه + قرارداد + کالا',
        'branch_route_product' => 'شعبه/انبار + مسیر + کالا',
    ];

    private const COMPOSITE_DIMENSIONS = [
        'cost_center_project' => ['cost_center', 'project'],
        'project_contract_product' => ['project', 'contract', 'product'],
        'branch_route_product' => ['branch', 'route', 'product'],
    ];

    public function build($user, array $filters = []): array
    {
        $toDate = $filters['to_date'] ?? now()->toDateString();
        $fromDate = $filters['from_date'] ?? Carbon::parse($toDate)->startOfMonth()->toDateString();
        $dimension = array_key_exists($filters['dimension'] ?? '', self::DIMENSIONS) ? $filters['dimension'] : 'cost_center';
        $permanentOnly = (bool) ($filters['permanent_only'] ?? false);

        $items = VoucherItems::query()
            ->with(['voucher', 'account', 'costCenter', 'branch', 'product', 'customer', 'employee'])
            ->whereHas('voucher', function ($query) use ($user, $fromDate, $toDate, $permanentOnly) {
                $query->whereNull('deleted_at')
                    ->where(function ($query) {
                        $query->whereNull('status')->orWhere('status', '<>', 'cancelled');
                    })
                    ->whereDate('voucher_date_en', '>=', $fromDate)
                    ->whereDate('voucher_date_en', '<=', $toDate);

                if ($permanentOnly) {
                    $query->where('is_permanent', 1);
                }

                if ((int) $user?->isGod !== 1) {
                    $query->where('tenant_id', $this->tenantId($user));
                }
            })
            ->get();

        $missingRows = $this->missingRequiredRows($items);

        $groups = $items->groupBy(fn($item) => $this->dimensionKey($item, $dimension))
            ->map(function ($group) use ($dimension) {
                $first = $group->first();
                $debit = round((float) $group->sum('debit_amount'), 2);
                $credit = round((float) $group->sum('credit_amount'), 2);

                return [
                    'key' => $this->dimensionKey($first, $dimension),
                    'label' => $this->dimensionLabel($first, $dimension),
                    'items_count' => $group->count(),
                    'debit' => $debit,
                    'credit' => $credit,
                    'net' => round($debit - $credit, 2),
                    'latest_voucher' => optional($group->sortByDesc(fn($item) => $item->voucher?->voucher_date_en)->first()?->voucher)->voucher_number,
                ];
            })
            ->sortByDesc(fn($row) => abs((float) $row['net']) + (float) $row['debit'] + (float) $row['credit'])
            ->values();

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'dimension' => $dimension,
            'dimensions' => self::DIMENSIONS,
            'permanent_only' => $permanentOnly,
            'rows' => $groups,
            'summary' => [
                'items_count' => $items->count(),
                'assigned_items_count' => $items->filter(fn($item) => $this->dimensionKey($item, $dimension) !== 'unassigned')->count(),
                'missing_required_count' => $missingRows->count(),
                'debit' => round((float) $items->sum('debit_amount'), 2),
                'credit' => round((float) $items->sum('credit_amount'), 2),
                'groups_count' => $groups->count(),
                'coverage_percent' => $items->count() > 0 ? round((($items->count() - $missingRows->count()) / $items->count()) * 100, 2) : 100,
            ],
            'quality' => [
                'missing_required_rows' => $missingRows->take(30)->values(),
                'dimension_coverage' => $this->dimensionCoverage($items),
            ],
        ];
    }

    private function dimensionKey($item, string $dimension): string
    {
        if (isset(self::COMPOSITE_DIMENSIONS[$dimension])) {
            $parts = collect(self::COMPOSITE_DIMENSIONS[$dimension])
                ->map(fn($singleDimension) => $this->singleDimensionKey($item, $singleDimension));

            return $parts->every(fn($part) => $part === 'unassigned') ? 'unassigned' : $parts->implode('|');
        }

        return $this->singleDimensionKey($item, $dimension);
    }

    private function singleDimensionKey($item, string $dimension): string
    {
        $value = match ($dimension) {
            'cost_center' => $item->cost_center_id,
            'branch' => $item->branch_id,
            'project' => $item->project_code,
            'product' => $item->product_id,
            'customer' => $item->customer_id,
            'employee' => $item->employee_id,
            'contract' => $item->contract_code,
            'route' => $item->route_code,
            default => null,
        };

        $value = trim((string) $value);

        return $value === '' ? 'unassigned' : $value;
    }

    private function dimensionLabel($item, string $dimension): string
    {
        if (isset(self::COMPOSITE_DIMENSIONS[$dimension])) {
            if ($this->dimensionKey($item, $dimension) === 'unassigned') {
                return 'بدون تخصیص';
            }

            return collect(self::COMPOSITE_DIMENSIONS[$dimension])
                ->map(fn($singleDimension) => $this->singleDimensionLabel($item, $singleDimension))
                ->implode(' / ');
        }

        return $this->singleDimensionLabel($item, $dimension);
    }

    private function singleDimensionLabel($item, string $dimension): string
    {
        if ($this->dimensionKey($item, $dimension) === 'unassigned') {
            return 'بدون تخصیص';
        }

        return match ($dimension) {
            'cost_center' => trim((string) optional($item->costCenter)->code . ' - ' . optional($item->costCenter)->name, ' -') ?: (string) $item->cost_center_id,
            'branch' => optional($item->branch)->title ?: (string) $item->branch_id,
            'project' => (string) $item->project_code,
            'product' => trim((string) optional($item->product)->title . ' ' . optional($item->product)->display_name) ?: (string) $item->product_id,
            'customer' => optional($item->customer)->name ?: optional($item->customer)->tablo ?: (string) $item->customer_id,
            'employee' => optional($item->employee)->name ?: (string) $item->employee_id,
            'contract' => (string) $item->contract_code,
            'route' => (string) $item->route_code,
            default => 'نامشخص',
        };
    }

    private function missingRequiredRows($items)
    {
        return $items->map(function ($item) {
            $missing = [];

            if ((bool) $item->account?->cost_center_required && $this->singleDimensionKey($item, 'cost_center') === 'unassigned') {
                $missing[] = 'مرکز هزینه';
            }

            if ((bool) $item->account?->floating_detail_required && !$this->hasAnyAnalyticDimension($item)) {
                $missing[] = 'تفصیل شناور';
            }

            if (empty($missing)) {
                return null;
            }

            return [
                'voucher_number' => $item->voucher?->voucher_number ?: '-',
                'voucher_date' => $item->voucher?->voucher_date_en ?: '-',
                'account' => trim((string) ($item->account?->code . ' - ' . $item->account?->name), ' -') ?: '-',
                'description' => $item->description ?: '-',
                'debit' => round((float) $item->debit_amount, 2),
                'credit' => round((float) $item->credit_amount, 2),
                'missing' => implode('، ', $missing),
            ];
        })->filter()->values();
    }

    private function dimensionCoverage($items): array
    {
        return collect(self::DIMENSIONS)
            ->reject(fn($label, $dimension) => isset(self::COMPOSITE_DIMENSIONS[$dimension]))
            ->map(function ($label, $dimension) use ($items) {
                $assigned = $items->filter(fn($item) => $this->singleDimensionKey($item, $dimension) !== 'unassigned')->count();

                return [
                    'dimension' => $dimension,
                    'label' => $label,
                    'assigned' => $assigned,
                    'percent' => $items->count() > 0 ? round(($assigned / $items->count()) * 100, 2) : 0,
                ];
            })
            ->values()
            ->all();
    }

    private function hasAnyAnalyticDimension($item): bool
    {
        foreach (array_keys(self::DIMENSIONS) as $dimension) {
            if (isset(self::COMPOSITE_DIMENSIONS[$dimension])) {
                continue;
            }

            if ($this->singleDimensionKey($item, $dimension) !== 'unassigned') {
                return true;
            }
        }

        return false;
    }

    private function tenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }
}

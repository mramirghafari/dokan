<?php

namespace App\Services;

use App\Models\Accounts;
use App\Models\ExpenseAllocation;
use App\Models\Product;
use App\Models\RevenueCenter;
use App\Models\User;
use App\Models\VoucherItems;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AccountingFinancialStatementService
{
    public function build($user, array $filters = []): array
    {
        $toDate = $filters['to_date'] ?? now()->toDateString();
        $fromDate = $filters['from_date'] ?? Carbon::parse($toDate)->startOfYear()->toDateString();
        $permanentOnly = (bool) ($filters['permanent_only'] ?? false);
        $revenueCenterId = !empty($filters['revenue_center_id']) ? (int) $filters['revenue_center_id'] : null;
        $profitDimension = $this->profitDimension($filters['profit_dimension'] ?? 'revenue_center');
        $periodRows = $this->statementRows($user, $fromDate, $toDate, $permanentOnly, ['period_closing', 'period_opening'], $revenueCenterId);
        $positionRows = $this->statementRows($user, null, $toDate, $permanentOnly, ['period_closing']);
        $revenueCenterRows = $this->revenueCenterRows($user, $fromDate, $toDate, $permanentOnly, $revenueCenterId);
        $multiDimensionalProfitRows = $this->multiDimensionalProfitRows($user, $fromDate, $toDate, $permanentOnly, $revenueCenterId, $profitDimension, $revenueCenterRows);
        $marginDashboard = $this->marginDashboard($user, $fromDate, $toDate, $permanentOnly, $revenueCenterId, $revenueCenterRows);
        $incomeRows = $this->categoryRows($periodRows, 'income', fn($row) => round((float) $row['credit_amount'] - (float) $row['debit_amount'], 2));
        $expenseRows = $this->categoryRows($periodRows, 'expense', fn($row) => round((float) $row['debit_amount'] - (float) $row['credit_amount'], 2));
        $assetRows = $this->categoryRows($positionRows, 'asset', fn($row) => round((float) $row['debit_amount'] - (float) $row['credit_amount'], 2));
        $liabilityRows = $this->categoryRows($positionRows, 'liability', fn($row) => round((float) $row['credit_amount'] - (float) $row['debit_amount'], 2));
        $equityRows = $this->categoryRows($positionRows, 'equity', fn($row) => round((float) $row['credit_amount'] - (float) $row['debit_amount'], 2));
        $uncategorizedRows = $positionRows->filter(fn($row) => $this->accountCategory($row['account']) === 'uncategorized')->values();
        $totalIncome = round((float) $incomeRows->sum('amount'), 2);
        $totalExpense = round((float) $expenseRows->sum('amount'), 2);
        $netProfit = round($totalIncome - $totalExpense, 2);
        $totalAssets = round((float) $assetRows->sum('amount'), 2);
        $totalLiabilities = round((float) $liabilityRows->sum('amount'), 2);
        $totalEquity = round((float) $equityRows->sum('amount'), 2);
        $positionCheck = round($totalAssets - ($totalLiabilities + $totalEquity + $netProfit), 2);

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'permanent_only' => $permanentOnly,
            'revenue_center_id' => $revenueCenterId,
            'income_statement' => [
                'income_rows' => $incomeRows,
                'expense_rows' => $expenseRows,
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_profit' => $netProfit,
            ],
            'revenue_center_statement' => [
                'rows' => $revenueCenterRows,
                'total_income' => round((float) $revenueCenterRows->sum('income_amount'), 2),
                'total_expense' => round((float) $revenueCenterRows->sum('expense_amount'), 2),
                'net_profit' => round((float) $revenueCenterRows->sum('net_profit'), 2),
            ],
            'multi_dimensional_profit' => [
                'dimension' => $profitDimension,
                'rows' => $multiDimensionalProfitRows,
                'total_income' => round((float) $multiDimensionalProfitRows->sum('income_amount'), 2),
                'total_allocated_expense' => round((float) $multiDimensionalProfitRows->sum('allocated_expense_amount'), 2),
                'net_profit' => round((float) $multiDimensionalProfitRows->sum('net_profit'), 2),
            ],
            'margin_dashboard' => $marginDashboard,
            'financial_position' => [
                'asset_rows' => $assetRows,
                'liability_rows' => $liabilityRows,
                'equity_rows' => $equityRows,
                'uncategorized_rows' => $uncategorizedRows,
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquity,
                'position_check' => $positionCheck,
            ],
            'summary' => [
                'accounts_count' => $periodRows->merge($positionRows)->pluck('account_id')->unique()->count(),
                'income_accounts_count' => $incomeRows->count(),
                'expense_accounts_count' => $expenseRows->count(),
                'position_accounts_count' => $assetRows->count() + $liabilityRows->count() + $equityRows->count(),
                'has_uncategorized' => $uncategorizedRows->isNotEmpty(),
            ],
        ];
    }

    private function statementRows($user, ?string $fromDate, ?string $toDate, bool $permanentOnly, array $excludedDocumentTypes, ?int $revenueCenterId = null): Collection
    {
        $query = VoucherItems::query()
            ->join('vouchers', 'voucher_items.voucher_id', '=', 'vouchers.id')
            ->whereNull('voucher_items.deleted_at')
            ->whereNull('vouchers.deleted_at')
            ->where(function ($query) {
                $query->whereNull('vouchers.status')
                    ->orWhere('vouchers.status', '<>', 'cancelled');
            })
            ->where(function ($query) use ($excludedDocumentTypes) {
                $query->whereNull('vouchers.document_type')
                    ->orWhereNotIn('vouchers.document_type', $excludedDocumentTypes);
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

        if ($revenueCenterId) {
            $query->where('voucher_items.revenue_center_id', $revenueCenterId);
        }

        if ((int) $user?->isGod !== 1) {
            $query->where('vouchers.tenant_id', $this->tenantId($user));
        }

        $rows = $query->select('voucher_items.account_id')
            ->selectRaw('COALESCE(SUM(voucher_items.debit_amount), 0) as debit_amount, COALESCE(SUM(voucher_items.credit_amount), 0) as credit_amount')
            ->groupBy('voucher_items.account_id')
            ->get();
        $accounts = Accounts::whereIn('id', $rows->pluck('account_id')->filter()->values())->get()->keyBy('id');

        return $rows->map(function ($row) use ($accounts) {
            return [
                'account_id' => (int) $row->account_id,
                'account' => $accounts->get($row->account_id),
                'debit_amount' => round((float) $row->debit_amount, 2),
                'credit_amount' => round((float) $row->credit_amount, 2),
            ];
        })->values();
    }

    private function revenueCenterRows($user, ?string $fromDate, ?string $toDate, bool $permanentOnly, ?int $revenueCenterId): Collection
    {
        $query = VoucherItems::query()
            ->join('vouchers', 'voucher_items.voucher_id', '=', 'vouchers.id')
            ->whereNull('voucher_items.deleted_at')
            ->whereNull('vouchers.deleted_at')
            ->where(function ($query) {
                $query->whereNull('vouchers.status')
                    ->orWhere('vouchers.status', '<>', 'cancelled');
            })
            ->where(function ($query) {
                $query->whereNull('vouchers.document_type')
                    ->orWhereNotIn('vouchers.document_type', ['period_closing', 'period_opening']);
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

        if ($revenueCenterId) {
            $query->where('voucher_items.revenue_center_id', $revenueCenterId);
        }

        if ((int) $user?->isGod !== 1) {
            $query->where('vouchers.tenant_id', $this->tenantId($user));
        }

        $rows = $query->select('voucher_items.account_id', 'voucher_items.revenue_center_id')
            ->selectRaw('COALESCE(SUM(voucher_items.debit_amount), 0) as debit_amount, COALESCE(SUM(voucher_items.credit_amount), 0) as credit_amount')
            ->groupBy('voucher_items.account_id', 'voucher_items.revenue_center_id')
            ->get();
        $accounts = Accounts::whereIn('id', $rows->pluck('account_id')->filter()->values())->get()->keyBy('id');
        $centerIds = $rows->pluck('revenue_center_id')->filter()->unique()->values();
        $centers = RevenueCenter::whereIn('id', $centerIds)->get()->keyBy('id');

        return $rows->groupBy(fn($row) => $row->revenue_center_id ?: 0)
            ->map(function ($items, $centerId) use ($accounts, $centers) {
                $income = 0;
                $expense = 0;

                foreach ($items as $item) {
                    $category = $this->accountCategory($accounts->get($item->account_id));

                    if ($category === 'income') {
                        $income += (float) $item->credit_amount - (float) $item->debit_amount;
                    }

                    if ($category === 'expense') {
                        $expense += (float) $item->debit_amount - (float) $item->credit_amount;
                    }
                }

                return [
                    'revenue_center_id' => (int) $centerId ?: null,
                    'revenue_center' => $centers->get($centerId),
                    'label' => $centerId ? ($centers->get($centerId)?->name ?: 'مرکز درآمد حذف شده') : 'بدون مرکز درآمد',
                    'income_amount' => round($income, 2),
                    'expense_amount' => round($expense, 2),
                    'net_profit' => round($income - $expense, 2),
                    'items_count' => $items->count(),
                ];
            })
            ->filter(fn($row) => round(abs((float) $row['income_amount']) + abs((float) $row['expense_amount']), 2) > 0)
            ->sortByDesc('net_profit')
            ->values();
    }

    private function multiDimensionalProfitRows($user, ?string $fromDate, ?string $toDate, bool $permanentOnly, ?int $revenueCenterId, string $dimension, Collection $revenueCenterRows): Collection
    {
        if ($dimension === 'revenue_center') {
            return $revenueCenterRows->map(fn($row) => [
                'dimension_type' => 'revenue_center',
                'dimension_label' => $row['label'],
                'dimension_code' => optional($row['revenue_center'])->code,
                'income_amount' => $row['income_amount'],
                'allocated_expense_amount' => $row['expense_amount'],
                'net_profit' => $row['net_profit'],
                'income_items_count' => $row['items_count'],
                'expense_allocations_count' => $row['items_count'],
            ])->values();
        }

        $rows = collect();
        $incomeRows = $this->profitIncomeRows($user, $fromDate, $toDate, $permanentOnly, $revenueCenterId);
        $expenseRows = $this->profitExpenseAllocationRows($user, $fromDate, $toDate, $permanentOnly);
        $salesCostRows = $this->profitSalesCostRows($user, $fromDate, $toDate, $permanentOnly, $revenueCenterId);

        foreach ($incomeRows as $incomeRow) {
            $key = $this->profitDimensionKey($incomeRow, $dimension);
            $row = $rows->get($key['key'], $this->emptyProfitRow($dimension, $key['label'], $key['code']));
            $row['income_amount'] = round((float) $row['income_amount'] + (float) $incomeRow['amount'], 2);
            $row['income_items_count']++;
            $rows->put($key['key'], $row);
        }

        foreach ($expenseRows as $expenseRow) {
            $key = $this->profitDimensionKey($expenseRow, $dimension);
            $row = $rows->get($key['key'], $this->emptyProfitRow($dimension, $key['label'], $key['code']));
            $row['allocated_expense_amount'] = round((float) $row['allocated_expense_amount'] + (float) $expenseRow['amount'], 2);
            $row['expense_allocations_count']++;
            $rows->put($key['key'], $row);
        }

        foreach ($salesCostRows as $expenseRow) {
            $key = $this->profitDimensionKey($expenseRow, $dimension);
            $row = $rows->get($key['key'], $this->emptyProfitRow($dimension, $key['label'], $key['code']));
            $row['allocated_expense_amount'] = round((float) $row['allocated_expense_amount'] + (float) $expenseRow['amount'], 2);
            $row['expense_allocations_count']++;
            $rows->put($key['key'], $row);
        }

        return $rows->map(function ($row) {
            $row['net_profit'] = round((float) $row['income_amount'] - (float) $row['allocated_expense_amount'], 2);

            return $row;
        })
            ->filter(fn($row) => round(abs((float) $row['income_amount']) + abs((float) $row['allocated_expense_amount']), 2) > 0)
            ->sortByDesc('net_profit')
            ->values();
    }

    private function marginDashboard($user, ?string $fromDate, ?string $toDate, bool $permanentOnly, ?int $revenueCenterId, Collection $revenueCenterRows): array
    {
        $dimensions = [
            'product' => 'کالا / محصول',
            'route' => 'مسیر فروش',
            'visitor' => 'ویزیتور',
            'revenue_center' => 'مرکز درآمد',
            'project' => 'پروژه',
        ];

        $rowsByDimension = collect();
        $allRows = collect();

        foreach ($dimensions as $dimension => $label) {
            $dimensionRows = $this->multiDimensionalProfitRows($user, $fromDate, $toDate, $permanentOnly, $revenueCenterId, $dimension, $revenueCenterRows)
                ->map(fn($row) => $this->marginRow($row, $dimension, $label))
                ->values();

            $rowsByDimension->put($dimension, [
                'label' => $label,
                'rows' => $dimensionRows,
            ]);

            $allRows = $allRows->merge($dimensionRows);
        }

        $productRows = $rowsByDimension->get('product')['rows'];
        $totalIncome = round((float) $productRows->sum('income_amount'), 2);
        $totalCost = round((float) $productRows->sum('allocated_expense_amount'), 2);
        $netProfit = round($totalIncome - $totalCost, 2);

        return [
            'summary' => [
                'total_income' => $totalIncome,
                'total_cost' => $totalCost,
                'net_profit' => $netProfit,
                'margin_percent' => $this->marginPercent($totalIncome, $netProfit),
                'break_even_gap' => round(max($totalCost - $totalIncome, 0), 2),
                'loss_makers_count' => $allRows->where('status', 'loss')->count(),
                'near_break_even_count' => $allRows->where('status', 'near_break_even')->count(),
                'profitable_count' => $allRows->where('status', 'profitable')->count(),
            ],
            'loss_makers' => $allRows->where('status', 'loss')->sortBy('net_profit')->take(8)->values(),
            'near_break_even' => $allRows->where('status', 'near_break_even')->sortBy('margin_percent')->take(8)->values(),
            'top_profit' => $allRows->sortByDesc('net_profit')->take(8)->values(),
            'rows_by_dimension' => $rowsByDimension,
        ];
    }

    private function marginRow(array $row, string $dimension, string $dimensionLabel): array
    {
        $income = round((float) $row['income_amount'], 2);
        $cost = round((float) $row['allocated_expense_amount'], 2);
        $netProfit = round((float) $row['net_profit'], 2);
        $marginPercent = $this->marginPercent($income, $netProfit);

        return $row + [
            'dashboard_dimension' => $dimension,
            'dashboard_dimension_label' => $dimensionLabel,
            'margin_percent' => $marginPercent,
            'break_even_gap' => round(max($cost - $income, 0), 2),
            'status' => $this->marginStatus($income, $netProfit, $marginPercent),
        ];
    }

    private function marginPercent(float $income, float $netProfit): ?float
    {
        if (round($income, 2) <= 0) {
            return null;
        }

        return round(($netProfit / $income) * 100, 2);
    }

    private function marginStatus(float $income, float $netProfit, ?float $marginPercent): string
    {
        if ($netProfit < 0) {
            return 'loss';
        }

        if ($income > 0 && $marginPercent !== null && $marginPercent <= 5) {
            return 'near_break_even';
        }

        return 'profitable';
    }

    private function profitIncomeRows($user, ?string $fromDate, ?string $toDate, bool $permanentOnly, ?int $revenueCenterId): Collection
    {
        $query = VoucherItems::query()
            ->join('vouchers', 'voucher_items.voucher_id', '=', 'vouchers.id')
            ->join('accounts', 'voucher_items.account_id', '=', 'accounts.id')
            ->whereNull('voucher_items.deleted_at')
            ->whereNull('vouchers.deleted_at')
            ->where(function ($query) {
                $query->whereNull('vouchers.status')
                    ->orWhere('vouchers.status', '<>', 'cancelled');
            })
            ->where(function ($query) {
                $query->whereNull('vouchers.document_type')
                    ->orWhereNotIn('vouchers.document_type', ['period_closing', 'period_opening']);
            })
            ->where(function ($query) {
                $query->where('accounts.account_category', 'income')
                    ->orWhere('accounts.code', 'like', 'SYS-4%');
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

        if ($revenueCenterId) {
            $query->where('voucher_items.revenue_center_id', $revenueCenterId);
        }

        if ((int) $user?->isGod !== 1) {
            $query->where('vouchers.tenant_id', $this->tenantId($user));
        }

        $rows = $query->select('voucher_items.revenue_center_id', 'voucher_items.product_id', 'voucher_items.project_code', 'voucher_items.contract_code', 'voucher_items.route_code', 'voucher_items.employee_id')
            ->selectRaw('COALESCE(SUM(voucher_items.credit_amount - voucher_items.debit_amount), 0) as amount')
            ->groupBy('voucher_items.revenue_center_id', 'voucher_items.product_id', 'voucher_items.project_code', 'voucher_items.contract_code', 'voucher_items.route_code', 'voucher_items.employee_id')
            ->get();

        return $rows->map(fn($row) => [
            'revenue_center_id' => $row->revenue_center_id ? (int) $row->revenue_center_id : null,
            'product_id' => $row->product_id ? (int) $row->product_id : null,
            'project_code' => $row->project_code,
            'contract_code' => $row->contract_code,
            'route_code' => $row->route_code,
            'employee_id' => $row->employee_id ? (int) $row->employee_id : null,
            'amount' => round((float) $row->amount, 2),
        ])->values();
    }

    private function profitExpenseAllocationRows($user, ?string $fromDate, ?string $toDate, bool $permanentOnly): Collection
    {
        $query = ExpenseAllocation::query()
            ->leftJoin('vouchers', 'expense_allocations.voucher_id', '=', 'vouchers.id')
            ->leftJoin('operational_expenses', 'expense_allocations.operational_expense_id', '=', 'operational_expenses.id')
            ->whereNull('expense_allocations.deleted_at')
            ->where(function ($query) {
                $query->whereNull('vouchers.id')
                    ->orWhereNull('vouchers.deleted_at');
            })
            ->where(function ($query) {
                $query->whereNull('vouchers.id')
                    ->orWhereNull('vouchers.status')
                    ->orWhere('vouchers.status', '<>', 'cancelled');
            });

        if ($fromDate) {
            $query->where(function ($query) use ($fromDate) {
                $query->whereDate('vouchers.voucher_date_en', '>=', $fromDate)
                    ->orWhere(function ($query) use ($fromDate) {
                        $query->whereNull('vouchers.id')->whereDate('operational_expenses.expense_date_en', '>=', $fromDate);
                    });
            });
        }

        if ($toDate) {
            $query->where(function ($query) use ($toDate) {
                $query->whereDate('vouchers.voucher_date_en', '<=', $toDate)
                    ->orWhere(function ($query) use ($toDate) {
                        $query->whereNull('vouchers.id')->whereDate('operational_expenses.expense_date_en', '<=', $toDate);
                    });
            });
        }

        if ($permanentOnly) {
            $query->where('vouchers.is_permanent', 1);
        }

        if ((int) $user?->isGod !== 1) {
            $query->where('expense_allocations.tenant_id', $this->tenantId($user));
        }

        $rows = $query->select('expense_allocations.allocation_target_type', 'expense_allocations.allocation_target_id', 'expense_allocations.target_type', 'expense_allocations.target_id', 'expense_allocations.product_id', 'expense_allocations.project_code', 'expense_allocations.contract_code')
            ->selectRaw('COALESCE(SUM(NULLIF(expense_allocations.allocated_amount, 0)), SUM(expense_allocations.amount), 0) as amount')
            ->groupBy('expense_allocations.allocation_target_type', 'expense_allocations.allocation_target_id', 'expense_allocations.target_type', 'expense_allocations.target_id', 'expense_allocations.product_id', 'expense_allocations.project_code', 'expense_allocations.contract_code')
            ->get();

        return $rows->map(fn($row) => [
            'allocation_target_type' => $row->allocation_target_type ?: $row->target_type,
            'allocation_target_id' => $row->allocation_target_id ?: $row->target_id,
            'product_id' => $row->product_id ? (int) $row->product_id : null,
            'project_code' => $row->project_code,
            'contract_code' => $row->contract_code,
            'route_code' => null,
            'employee_id' => null,
            'amount' => round((float) $row->amount, 2),
        ])->values();
    }

    private function profitSalesCostRows($user, ?string $fromDate, ?string $toDate, bool $permanentOnly, ?int $revenueCenterId): Collection
    {
        $query = VoucherItems::query()
            ->join('vouchers', 'voucher_items.voucher_id', '=', 'vouchers.id')
            ->join('accounts', 'voucher_items.account_id', '=', 'accounts.id')
            ->whereNull('voucher_items.deleted_at')
            ->whereNull('vouchers.deleted_at')
            ->whereIn('vouchers.document_type', ['sales_cogs', 'sales_return_cogs'])
            ->where(function ($query) {
                $query->whereNull('vouchers.status')
                    ->orWhere('vouchers.status', '<>', 'cancelled');
            })
            ->where(function ($query) {
                $query->where('accounts.account_category', 'expense')
                    ->orWhere('accounts.code', 'like', 'SYS-5%');
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

        if ($revenueCenterId) {
            $query->where('voucher_items.revenue_center_id', $revenueCenterId);
        }

        if ((int) $user?->isGod !== 1) {
            $query->where('vouchers.tenant_id', $this->tenantId($user));
        }

        $rows = $query->select('voucher_items.revenue_center_id', 'voucher_items.product_id', 'voucher_items.project_code', 'voucher_items.contract_code', 'voucher_items.route_code', 'voucher_items.employee_id')
            ->selectRaw('COALESCE(SUM(voucher_items.debit_amount - voucher_items.credit_amount), 0) as amount')
            ->groupBy('voucher_items.revenue_center_id', 'voucher_items.product_id', 'voucher_items.project_code', 'voucher_items.contract_code', 'voucher_items.route_code', 'voucher_items.employee_id')
            ->get();

        return $rows->map(fn($row) => [
            'revenue_center_id' => $row->revenue_center_id ? (int) $row->revenue_center_id : null,
            'allocation_target_type' => $row->product_id ? 'product' : null,
            'allocation_target_id' => $row->product_id ? (int) $row->product_id : null,
            'product_id' => $row->product_id ? (int) $row->product_id : null,
            'project_code' => $row->project_code,
            'contract_code' => $row->contract_code,
            'route_code' => $row->route_code,
            'employee_id' => $row->employee_id ? (int) $row->employee_id : null,
            'amount' => round((float) $row->amount, 2),
        ])->values();
    }

    private function profitDimensionKey(array $row, string $dimension): array
    {
        if ($dimension === 'product') {
            $productId = (int) ($row['product_id'] ?: (($row['allocation_target_type'] ?? null) === 'product' ? ($row['allocation_target_id'] ?? 0) : 0));

            return [
                'key' => 'product:' . $productId,
                'label' => $productId ? $this->productLabel($productId) : 'بدون کالا',
                'code' => $productId ?: null,
            ];
        }

        if ($dimension === 'project') {
            $projectCode = trim((string) ($row['project_code'] ?: (($row['allocation_target_type'] ?? null) === 'project' ? ($row['allocation_target_id'] ?? '') : '')));

            return [
                'key' => 'project:' . ($projectCode ?: 'none'),
                'label' => $projectCode ? 'پروژه ' . $projectCode : 'بدون پروژه',
                'code' => $projectCode ?: null,
            ];
        }

        if ($dimension === 'route') {
            $routeCode = trim((string) ($row['route_code'] ?? ''));

            return [
                'key' => 'route:' . ($routeCode ?: 'none'),
                'label' => $routeCode ? 'مسیر ' . $routeCode : 'بدون مسیر',
                'code' => $routeCode ?: null,
            ];
        }

        if ($dimension === 'visitor') {
            $employeeId = (int) ($row['employee_id'] ?? 0);

            return [
                'key' => 'visitor:' . $employeeId,
                'label' => $employeeId ? $this->userLabel($employeeId) : 'بدون ویزیتور',
                'code' => $employeeId ?: null,
            ];
        }

        $contractCode = trim((string) ($row['contract_code'] ?: (($row['allocation_target_type'] ?? null) === 'contract' ? ($row['allocation_target_id'] ?? '') : '')));

        return [
            'key' => 'contract:' . ($contractCode ?: 'none'),
            'label' => $contractCode ? 'قرارداد ' . $contractCode : 'بدون قرارداد',
            'code' => $contractCode ?: null,
        ];
    }

    private function emptyProfitRow(string $dimension, string $label, $code): array
    {
        return [
            'dimension_type' => $dimension,
            'dimension_label' => $label,
            'dimension_code' => $code,
            'income_amount' => 0,
            'allocated_expense_amount' => 0,
            'net_profit' => 0,
            'income_items_count' => 0,
            'expense_allocations_count' => 0,
        ];
    }

    private function productLabel(int $productId): string
    {
        $product = Product::find($productId);

        return $product ? trim((string) ($product->title ?: $product->display_name ?: $product->name ?: $product->id)) : 'کالای حذف شده #' . $productId;
    }

    private function userLabel(int $userId): string
    {
        $user = User::find($userId);

        return $user ? trim((string) ($user->name ?: $user->username ?: $user->mobile ?: $user->id)) : 'ویزیتور حذف شده #' . $userId;
    }

    private function profitDimension(string $dimension): string
    {
        return in_array($dimension, ['revenue_center', 'product', 'project', 'contract', 'route', 'visitor'], true) ? $dimension : 'revenue_center';
    }

    private function categoryRows(Collection $rows, string $category, callable $amountResolver): Collection
    {
        return $rows->filter(fn($row) => $this->accountCategory($row['account']) === $category)
            ->map(function ($row) use ($amountResolver) {
                $row['amount'] = $amountResolver($row);

                return $row;
            })
            ->filter(fn($row) => round(abs((float) $row['amount']), 2) > 0)
            ->sortBy(fn($row) => $row['account']?->code)
            ->values();
    }

    private function accountCategory(?Accounts $account): string
    {
        if (in_array($account?->account_category, ['asset', 'liability', 'equity', 'income', 'expense'], true)) {
            return $account->account_category;
        }

        return match (true) {
            str_starts_with((string) $account?->code, 'SYS-1') => 'asset',
            str_starts_with((string) $account?->code, 'SYS-2') => 'liability',
            str_starts_with((string) $account?->code, 'SYS-3') => 'equity',
            str_starts_with((string) $account?->code, 'SYS-4') => 'income',
            str_starts_with((string) $account?->code, 'SYS-5') => 'expense',
            default => 'uncategorized',
        };
    }

    private function tenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }
}

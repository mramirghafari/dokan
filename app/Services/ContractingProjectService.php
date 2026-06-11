<?php

namespace App\Services;

use App\Models\Accounts;
use App\Models\ContractingCostEntry;
use App\Models\ContractingGuarantee;
use App\Models\ContractingProgressStatement;
use App\Models\ContractingProject;
use App\Models\FiscalYear;
use App\Models\Voucher;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ContractingProjectService
{
    public function __construct(private NumberingService $numberingService) {}

    public function createProject(array $payload, $user): ContractingProject
    {
        $tenantId = $this->tenantId($user);
        $organizationId = $this->organizationId($user);
        $startDate = Arr::get($payload, 'start_date_en') ?: now()->toDateString();
        $items = $this->normalizeProjectItems(Arr::get($payload, 'items', []));
        $itemsTotal = round(array_sum(array_column($items, 'total_amount')), 2);
        $contractAmount = round((float) Arr::get($payload, 'contract_amount', 0), 2) ?: $itemsTotal;

        return DB::transaction(function () use ($payload, $user, $tenantId, $organizationId, $startDate, $items, $contractAmount) {
            $project = ContractingProject::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'project_code' => Arr::get($payload, 'project_code') ?: $this->numberingService->nextDocumentNumber('contracting_project', 'PRJ', $tenantId, $organizationId, $startDate),
                'title' => Arr::get($payload, 'title'),
                'customer_id' => Arr::get($payload, 'customer_id'),
                'contract_number' => Arr::get($payload, 'contract_number'),
                'contract_type' => Arr::get($payload, 'contract_type', 'construction'),
                'status' => Arr::get($payload, 'status', 'active'),
                'start_date_en' => $startDate,
                'start_date_fa' => $this->jalaliDate($startDate),
                'end_date_en' => Arr::get($payload, 'end_date_en'),
                'end_date_fa' => Arr::get($payload, 'end_date_en') ? $this->jalaliDate(Arr::get($payload, 'end_date_en')) : null,
                'contract_amount' => $contractAmount,
                'approved_budget' => round((float) Arr::get($payload, 'approved_budget', 0), 2),
                'retention_percent' => round((float) Arr::get($payload, 'retention_percent', 0), 4),
                'advance_payment_percent' => round((float) Arr::get($payload, 'advance_payment_percent', 0), 4),
                'performance_bond_percent' => round((float) Arr::get($payload, 'performance_bond_percent', 0), 4),
                'vat_percent' => round((float) Arr::get($payload, 'vat_percent', 0), 4),
                'receivable_account_id' => Arr::get($payload, 'receivable_account_id') ?: $this->systemAccountId('SYS-1205', 'دریافتنی قراردادهای پیمانکاری', $tenantId, $organizationId, $user),
                'revenue_account_id' => Arr::get($payload, 'revenue_account_id') ?: $this->systemAccountId('SYS-4300', 'درآمد صورت وضعیت پیمانکاری', $tenantId, $organizationId, $user),
                'advance_account_id' => Arr::get($payload, 'advance_account_id') ?: $this->systemAccountId('SYS-2108', 'پیش دریافت پیمانکاری', $tenantId, $organizationId, $user),
                'retention_account_id' => Arr::get($payload, 'retention_account_id') ?: $this->systemAccountId('SYS-1210', 'سپرده حسن انجام کار دریافتنی', $tenantId, $organizationId, $user),
                'tax_account_id' => Arr::get($payload, 'tax_account_id') ?: $this->systemAccountId('SYS-2402', 'مالیات و عوارض فروش پرداختنی', $tenantId, $organizationId, $user),
                'cost_account_id' => Arr::get($payload, 'cost_account_id') ?: $this->systemAccountId('SYS-5300', 'بهای تمام شده پروژه پیمانکاری', $tenantId, $organizationId, $user),
                'payable_account_id' => Arr::get($payload, 'payable_account_id') ?: $this->systemAccountId('SYS-2101', 'حساب های پرداختنی پروژه', $tenantId, $organizationId, $user),
                'guarantee_control_account_id' => Arr::get($payload, 'guarantee_control_account_id') ?: $this->systemAccountId('SYS-9101', 'حساب انتظامی ضمانت نامه ها', $tenantId, $organizationId, $user),
                'guarantee_commitment_account_id' => Arr::get($payload, 'guarantee_commitment_account_id') ?: $this->systemAccountId('SYS-9102', 'طرف حساب انتظامی ضمانت نامه ها', $tenantId, $organizationId, $user),
                'cost_center_id' => Arr::get($payload, 'cost_center_id'),
                'project_manager_id' => Arr::get($payload, 'project_manager_id'),
                'description' => Arr::get($payload, 'description'),
                'created_by' => $user?->id,
            ]);

            foreach ($items as $sortOrder => $item) {
                $project->items()->create(array_merge($item, [
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'sort_order' => $sortOrder + 1,
                ]));
            }

            return $project->refresh()->load(['customer', 'items']);
        });
    }

    public function createProgressStatement(ContractingProject $project, array $payload, $user): ContractingProgressStatement
    {
        $project->loadMissing('items');
        $tenantId = $project->tenant_id ?: $this->tenantId($user);
        $organizationId = $project->organization_id ?: $this->organizationId($user);
        $date = Arr::get($payload, 'statement_date_en') ?: now()->toDateString();
        $this->ensureVoucherDateIsOpen($date, $tenantId, 'statement_date_en');

        $statementRows = $this->normalizeStatementItems($project, Arr::get($payload, 'items', []));
        $currentAmount = round(array_sum(array_column($statementRows, 'gross_amount')), 2);

        if ($currentAmount <= 0) {
            throw ValidationException::withMessages(['items' => 'برای ثبت صورت وضعیت حداقل یک ردیف دارای مبلغ لازم است.']);
        }

        $previousAmount = round((float) $project->progressStatements()->sum('current_amount'), 2);
        $grossAmount = round($previousAmount + $currentAmount, 2);
        $retentionAmount = $this->money(Arr::get($payload, 'retention_amount', $currentAmount * ((float) $project->retention_percent / 100)));
        $advanceDeductionAmount = $this->money(Arr::get($payload, 'advance_deduction_amount', $currentAmount * ((float) $project->advance_payment_percent / 100)));
        $taxAmount = $this->money(Arr::get($payload, 'tax_amount', $currentAmount * ((float) $project->vat_percent / 100)));
        $payableAmount = round($currentAmount + $taxAmount - $retentionAmount - $advanceDeductionAmount, 2);

        if ($payableAmount < 0) {
            throw ValidationException::withMessages(['payable_amount' => 'کسورات صورت وضعیت از مبلغ جاری و مالیات بیشتر شده است.']);
        }

        return DB::transaction(function () use ($project, $payload, $user, $tenantId, $organizationId, $date, $statementRows, $grossAmount, $previousAmount, $currentAmount, $retentionAmount, $advanceDeductionAmount, $taxAmount, $payableAmount) {
            $statement = ContractingProgressStatement::create([
                'contracting_project_id' => $project->id,
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'statement_number' => Arr::get($payload, 'statement_number') ?: $this->numberingService->nextDocumentNumber('contracting_progress_statement', 'PST', $tenantId, $organizationId, $date),
                'statement_date_en' => $date,
                'statement_date_fa' => $this->jalaliDate($date),
                'period_from_en' => Arr::get($payload, 'period_from_en'),
                'period_to_en' => Arr::get($payload, 'period_to_en'),
                'gross_amount' => $grossAmount,
                'previous_amount' => $previousAmount,
                'current_amount' => $currentAmount,
                'retention_amount' => $retentionAmount,
                'advance_deduction_amount' => $advanceDeductionAmount,
                'tax_amount' => $taxAmount,
                'payable_amount' => $payableAmount,
                'status' => Arr::get($payload, 'status', 'posted'),
                'description' => Arr::get($payload, 'description'),
                'created_by' => $user?->id,
            ]);

            foreach ($statementRows as $row) {
                $statement->items()->create($row + [
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                ]);

                if ($row['contracting_project_item_id']) {
                    $projectItem = $project->items->firstWhere('id', $row['contracting_project_item_id']);
                    $projectItem?->update([
                        'executed_quantity' => $row['cumulative_quantity'],
                        'executed_amount' => round((float) $projectItem->executed_amount + (float) $row['gross_amount'], 2),
                    ]);
                }
            }

            $voucher = $this->createProgressVoucher($project, $statement, $user, $tenantId, $organizationId, $date);
            $statement->update(['voucher_id' => $voucher->id]);

            return $statement->refresh()->load(['items', 'voucher.items.account']);
        });
    }

    public function createGuarantee(ContractingProject $project, array $payload, $user): ContractingGuarantee
    {
        $tenantId = $project->tenant_id ?: $this->tenantId($user);
        $organizationId = $project->organization_id ?: $this->organizationId($user);
        $date = Arr::get($payload, 'issue_date_en') ?: now()->toDateString();
        $amount = $this->money(Arr::get($payload, 'amount'));

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'مبلغ ضمانت نامه باید بزرگتر از صفر باشد.']);
        }

        return DB::transaction(function () use ($project, $payload, $user, $tenantId, $organizationId, $date, $amount) {
            $guarantee = ContractingGuarantee::create([
                'contracting_project_id' => $project->id,
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'guarantee_number' => Arr::get($payload, 'guarantee_number') ?: $this->numberingService->nextDocumentNumber('contracting_guarantee', 'PGR', $tenantId, $organizationId, $date),
                'guarantee_type' => Arr::get($payload, 'guarantee_type', 'performance'),
                'issuer' => Arr::get($payload, 'issuer'),
                'beneficiary' => Arr::get($payload, 'beneficiary'),
                'amount' => $amount,
                'issue_date_en' => $date,
                'issue_date_fa' => $this->jalaliDate($date),
                'expiry_date_en' => Arr::get($payload, 'expiry_date_en'),
                'expiry_date_fa' => Arr::get($payload, 'expiry_date_en') ? $this->jalaliDate(Arr::get($payload, 'expiry_date_en')) : null,
                'status' => Arr::get($payload, 'status', 'active'),
                'description' => Arr::get($payload, 'description'),
                'created_by' => $user?->id,
            ]);

            $voucher = $this->createGuaranteeVoucher($project, $guarantee, $user, $tenantId, $organizationId, $date);
            $guarantee->update(['voucher_id' => $voucher->id]);

            return $guarantee->refresh()->load('voucher.items.account');
        });
    }

    public function createCostEntry(ContractingProject $project, array $payload, $user): ContractingCostEntry
    {
        $tenantId = $project->tenant_id ?: $this->tenantId($user);
        $organizationId = $project->organization_id ?: $this->organizationId($user);
        $date = Arr::get($payload, 'cost_date_en') ?: now()->toDateString();
        $this->ensureVoucherDateIsOpen($date, $tenantId, 'cost_date_en');
        $amount = $this->money(Arr::get($payload, 'amount'));
        $taxAmount = $this->money(Arr::get($payload, 'tax_amount'));
        $totalAmount = round($amount + $taxAmount, 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'مبلغ هزینه پروژه باید بزرگتر از صفر باشد.']);
        }

        return DB::transaction(function () use ($project, $payload, $user, $tenantId, $organizationId, $date, $amount, $taxAmount, $totalAmount) {
            $costEntry = ContractingCostEntry::create([
                'contracting_project_id' => $project->id,
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'cost_number' => Arr::get($payload, 'cost_number') ?: $this->numberingService->nextDocumentNumber('contracting_cost', 'CST', $tenantId, $organizationId, $date),
                'cost_date_en' => $date,
                'cost_date_fa' => $this->jalaliDate($date),
                'cost_type' => Arr::get($payload, 'cost_type', 'direct'),
                'supplier_id' => Arr::get($payload, 'supplier_id'),
                'amount' => $amount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'cost_account_id' => Arr::get($payload, 'cost_account_id') ?: $project->cost_account_id,
                'tax_account_id' => Arr::get($payload, 'tax_account_id') ?: $this->systemAccountId('SYS-1207', 'مالیات خرید و هزینه قابل بازیافت', $tenantId, $organizationId, $user),
                'payable_account_id' => Arr::get($payload, 'payable_account_id') ?: $project->payable_account_id,
                'status' => Arr::get($payload, 'status', 'posted'),
                'description' => Arr::get($payload, 'description'),
                'created_by' => $user?->id,
            ]);

            $voucher = $this->createCostVoucher($project, $costEntry, $user, $tenantId, $organizationId, $date);
            $costEntry->update(['voucher_id' => $voucher->id]);

            return $costEntry->refresh()->load(['voucher.items.account', 'supplier']);
        });
    }

    private function createProgressVoucher(ContractingProject $project, ContractingProgressStatement $statement, $user, ?int $tenantId, ?int $organizationId, string $date): Voucher
    {
        $lines = [
            ['account_id' => $project->receivable_account_id, 'debit_amount' => (float) $statement->payable_amount, 'credit_amount' => 0, 'description' => 'دریافتنی صورت وضعیت ' . $statement->statement_number],
            ['account_id' => $project->retention_account_id, 'debit_amount' => (float) $statement->retention_amount, 'credit_amount' => 0, 'description' => 'سپرده حسن انجام کار ' . $statement->statement_number],
            ['account_id' => $project->advance_account_id, 'debit_amount' => (float) $statement->advance_deduction_amount, 'credit_amount' => 0, 'description' => 'کسر پیش دریافت صورت وضعیت ' . $statement->statement_number],
            ['account_id' => $project->revenue_account_id, 'debit_amount' => 0, 'credit_amount' => (float) $statement->current_amount, 'description' => 'درآمد پیمانکاری ' . $statement->statement_number],
            ['account_id' => $project->tax_account_id, 'debit_amount' => 0, 'credit_amount' => (float) $statement->tax_amount, 'description' => 'مالیات و عوارض صورت وضعیت ' . $statement->statement_number],
        ];

        return $this->createVoucher($lines, [
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'date' => $date,
            'prefix' => 'PST',
            'document_type' => 'contracting_progress_statement',
            'source_type' => ContractingProgressStatement::class,
            'source_id' => $statement->id,
            'description' => 'ثبت صورت وضعیت ' . $statement->statement_number . ' پروژه ' . $project->project_code,
            'cost_center_id' => $project->cost_center_id,
            'project_code' => $project->project_code,
            'contract_code' => $project->contract_number,
            'customer_id' => $project->customer_id,
            'user' => $user,
        ]);
    }

    private function createGuaranteeVoucher(ContractingProject $project, ContractingGuarantee $guarantee, $user, ?int $tenantId, ?int $organizationId, string $date): Voucher
    {
        return $this->createVoucher([
            ['account_id' => $project->guarantee_control_account_id, 'debit_amount' => (float) $guarantee->amount, 'credit_amount' => 0, 'description' => 'ثبت ضمانت نامه ' . $guarantee->guarantee_number],
            ['account_id' => $project->guarantee_commitment_account_id, 'debit_amount' => 0, 'credit_amount' => (float) $guarantee->amount, 'description' => 'طرف حساب ضمانت نامه ' . $guarantee->guarantee_number],
        ], [
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'date' => $date,
            'prefix' => 'PGR',
            'document_type' => 'contracting_guarantee',
            'source_type' => ContractingGuarantee::class,
            'source_id' => $guarantee->id,
            'description' => 'ثبت ضمانت نامه پروژه ' . $project->project_code,
            'cost_center_id' => $project->cost_center_id,
            'project_code' => $project->project_code,
            'contract_code' => $project->contract_number,
            'customer_id' => $project->customer_id,
            'user' => $user,
        ]);
    }

    private function createCostVoucher(ContractingProject $project, ContractingCostEntry $costEntry, $user, ?int $tenantId, ?int $organizationId, string $date): Voucher
    {
        $lines = [
            ['account_id' => $costEntry->cost_account_id, 'debit_amount' => (float) $costEntry->amount, 'credit_amount' => 0, 'description' => 'هزینه پروژه ' . $costEntry->cost_number],
            ['account_id' => $costEntry->tax_account_id, 'debit_amount' => (float) $costEntry->tax_amount, 'credit_amount' => 0, 'description' => 'مالیات هزینه پروژه ' . $costEntry->cost_number],
            ['account_id' => $costEntry->payable_account_id, 'debit_amount' => 0, 'credit_amount' => (float) $costEntry->total_amount, 'description' => 'پرداختنی هزینه پروژه ' . $costEntry->cost_number],
        ];

        return $this->createVoucher($lines, [
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'date' => $date,
            'prefix' => 'CST',
            'document_type' => 'contracting_cost',
            'source_type' => ContractingCostEntry::class,
            'source_id' => $costEntry->id,
            'description' => 'ثبت هزینه پروژه ' . $project->project_code,
            'cost_center_id' => $project->cost_center_id,
            'project_code' => $project->project_code,
            'contract_code' => $project->contract_number,
            'customer_id' => $project->customer_id,
            'user' => $user,
        ]);
    }

    private function createVoucher(array $rawLines, array $meta): Voucher
    {
        $lines = array_values(array_filter($rawLines, fn($line) => round((float) $line['debit_amount'] + (float) $line['credit_amount'], 2) > 0));
        $totalDebit = round(array_sum(array_column($lines, 'debit_amount')), 2);
        $totalCredit = round(array_sum(array_column($lines, 'credit_amount')), 2);

        if (count($lines) < 2 || $totalDebit <= 0 || $totalDebit !== $totalCredit) {
            throw ValidationException::withMessages(['voucher' => 'سند حسابداری پیمانکاری تراز نیست.']);
        }

        $voucher = Voucher::create([
            'tenant_id' => Arr::get($meta, 'tenant_id'),
            'organization_id' => Arr::get($meta, 'organization_id'),
            'factor_id' => 0,
            'account_id' => $lines[0]['account_id'],
            'voucher_type' => 0,
            'voucher_number' => $this->numberingService->nextVoucherNumber(Arr::get($meta, 'tenant_id'), Arr::get($meta, 'date'), Arr::get($meta, 'prefix', 'CTR')),
            'voucher_date_fa' => $this->jalaliDate(Arr::get($meta, 'date')),
            'voucher_date_en' => Arr::get($meta, 'date'),
            'amount' => $totalDebit,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'method' => 0,
            'document_type' => Arr::get($meta, 'document_type'),
            'status' => 'draft',
            'is_permanent' => false,
            'source_type' => Arr::get($meta, 'source_type'),
            'source_id' => Arr::get($meta, 'source_id'),
            'fiscal_year' => $this->jalaliYear(Arr::get($meta, 'date')),
            'description' => Arr::get($meta, 'description'),
            'created_by' => Arr::get($meta, 'user')?->id,
        ]);

        foreach ($lines as $line) {
            $voucher->items()->create([
                'tenant_id' => Arr::get($meta, 'tenant_id'),
                'organization_id' => Arr::get($meta, 'organization_id'),
                'account_id' => $line['account_id'],
                'amount' => max((float) $line['debit_amount'], (float) $line['credit_amount']),
                'debit_amount' => $line['debit_amount'],
                'credit_amount' => $line['credit_amount'],
                'method' => $line['debit_amount'] > 0 ? 1 : 0,
                'cost_center_id' => Arr::get($meta, 'cost_center_id'),
                'project_code' => Arr::get($meta, 'project_code'),
                'contract_code' => Arr::get($meta, 'contract_code'),
                'customer_id' => Arr::get($meta, 'customer_id'),
                'analytic_note' => Arr::get($meta, 'document_type'),
                'description' => $line['description'],
            ]);
        }

        return $voucher->load('items.account');
    }

    private function normalizeProjectItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            $title = trim((string) Arr::get($item, 'title'));
            $quantity = round((float) Arr::get($item, 'quantity', 0), 4);
            $unitPrice = $this->money(Arr::get($item, 'unit_price'));

            if ($title === '' || $quantity <= 0 || $unitPrice < 0) {
                continue;
            }

            $normalized[] = [
                'item_code' => Arr::get($item, 'item_code'),
                'title' => $title,
                'unit' => Arr::get($item, 'unit'),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => round($quantity * $unitPrice, 2),
                'description' => Arr::get($item, 'description'),
            ];
        }

        if (empty($normalized)) {
            throw ValidationException::withMessages(['items' => 'برای قرارداد حداقل یک ردیف فهرست بها لازم است.']);
        }

        return $normalized;
    }

    private function normalizeStatementItems(ContractingProject $project, array $items): array
    {
        $rows = [];

        foreach ($items as $item) {
            $projectItem = $project->items->firstWhere('id', (int) Arr::get($item, 'contracting_project_item_id'));
            $quantity = round((float) Arr::get($item, 'quantity', 0), 4);

            if (!$projectItem || $quantity <= 0) {
                continue;
            }

            $previousQuantity = round((float) $projectItem->executed_quantity, 4);
            $cumulativeQuantity = round($previousQuantity + $quantity, 4);
            $grossAmount = round($quantity * (float) $projectItem->unit_price, 2);

            $rows[] = [
                'contracting_project_item_id' => $projectItem->id,
                'item_code' => $projectItem->item_code,
                'title' => $projectItem->title,
                'unit' => $projectItem->unit,
                'quantity' => $quantity,
                'previous_quantity' => $previousQuantity,
                'cumulative_quantity' => $cumulativeQuantity,
                'unit_price' => $projectItem->unit_price,
                'gross_amount' => $grossAmount,
                'description' => Arr::get($item, 'description'),
            ];
        }

        return $rows;
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
            'nature' => $this->accountNature($code),
            'isActive' => 1,
            'parent_id' => 0,
        ];

        if (Schema::hasColumn('accounts', 'is_system')) {
            $attributes['is_system'] = true;
        }
        if (Schema::hasColumn('accounts', 'account_category')) {
            $attributes['account_category'] = $this->accountCategory($code);
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

    private function accountNature(string $code): int
    {
        return str_starts_with($code, 'SYS-2') || str_starts_with($code, 'SYS-4') || $code === 'SYS-9102' ? 0 : 1;
    }

    private function accountCategory(string $code): string
    {
        if (str_starts_with($code, 'SYS-4')) {
            return 'income';
        }
        if (str_starts_with($code, 'SYS-5')) {
            return 'expense';
        }
        if (str_starts_with($code, 'SYS-2')) {
            return 'liability';
        }

        return 'asset';
    }

    private function ensureVoucherDateIsOpen(string $date, ?int $tenantId, string $field): void
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
            throw ValidationException::withMessages([$field => 'سال مالی «' . $fiscalYear->title . '» بسته است و ثبت سند پیمانکاری در این بازه مجاز نیست.']);
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

<?php

namespace App\Services;

use App\Models\Accounts;
use App\Models\CustomerPortalPayment;
use App\Models\ExpenseAllocation;
use App\Models\FiscalYear;
use App\Models\InventoryMovement;
use App\Models\OperationalExpense;
use App\Models\OperationalIncome;
use App\Models\PayrollRun;
use App\Models\PayrollRunPayment;
use App\Models\PaymentTerminal;
use App\Models\Pishfactor;
use App\Models\ProductionOrder;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\PurchaseServiceInvoice;
use App\Models\Receipt;
use App\Models\TreasuryInstrument;
use App\Models\TreasuryInstrumentHistory;
use App\Models\Voucher;
use App\Models\VoucherTemplate;
use App\Models\TreasuryChequeLeaf;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AccountingPostingService
{
    public function __construct(private NumberingService $numberingService) {}

    public function createManualVoucher(array $payload, $user): Voucher
    {
        $lines = $this->normalizeLines($payload);
        $totalDebit = array_sum(array_column($lines, 'debit_amount'));
        $totalCredit = array_sum(array_column($lines, 'credit_amount'));

        if (count($lines) < 2) {
            throw ValidationException::withMessages([
                'items' => 'برای ثبت سند حسابداری حداقل دو ردیف لازم است.',
            ]);
        }

        if (round($totalDebit, 2) <= 0 || round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw ValidationException::withMessages([
                'items' => 'جمع بدهکار و بستانکار سند باید برابر و بزرگتر از صفر باشد.',
            ]);
        }

        $tenantId = $this->tenantId($user);
        $organizationId = $this->organizationId($user);
        $date = Arr::get($payload, 'voucher_date_en') ?: now()->toDateString();
        $this->ensureVoucherDateIsOpen($date, $tenantId);

        return DB::transaction(function () use ($payload, $user, $lines, $totalDebit, $totalCredit, $tenantId, $organizationId, $date) {
            $voucher = Voucher::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => 0,
                'account_id' => $lines[0]['account_id'],
                'voucher_type' => 0,
                'voucher_number' => Arr::get($payload, 'voucher_number') ?: $this->numberingService->nextVoucherNumber($tenantId, $date),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $totalDebit,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'method' => 0,
                'document_type' => Arr::get($payload, 'document_type', 'manual'),
                'status' => 'draft',
                'is_permanent' => false,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => Arr::get($payload, 'description') ?: 'سند حسابداری دستی',
                'created_by' => $user?->id,
            ]);

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
                ], $this->analyticLineAttributes($line)));
            }

            return $voucher->load('items.account');
        });
    }

    public function updateDraftVoucher(Voucher $voucher, array $payload, $user): Voucher
    {
        $this->ensureEditableDraftVoucher($voucher);

        $lines = $this->normalizeLines($payload);
        $totalDebit = array_sum(array_column($lines, 'debit_amount'));
        $totalCredit = array_sum(array_column($lines, 'credit_amount'));

        if (count($lines) < 2) {
            throw ValidationException::withMessages([
                'items' => 'برای ویرایش سند حسابداری حداقل دو ردیف لازم است.',
            ]);
        }

        if (round($totalDebit, 2) <= 0 || round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw ValidationException::withMessages([
                'items' => 'جمع بدهکار و بستانکار سند باید برابر و بزرگتر از صفر باشد.',
            ]);
        }

        $date = Arr::get($payload, 'voucher_date_en') ?: now()->toDateString();
        $tenantId = $voucher->tenant_id ?: $this->tenantId($user);
        $organizationId = $voucher->organization_id ?: $this->organizationId($user);
        $this->ensureVoucherDateIsOpen($date, $tenantId);

        return DB::transaction(function () use ($voucher, $payload, $user, $lines, $totalDebit, $totalCredit, $tenantId, $organizationId, $date) {
            $voucher->update([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'account_id' => $lines[0]['account_id'],
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $totalDebit,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => Arr::get($payload, 'description') ?: $voucher->description,
                'updated_by' => $user?->id,
            ]);

            $voucher->items()->delete();

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
                ], $this->analyticLineAttributes($line)));
            }

            return $voucher->refresh()->load('items.account');
        });
    }

    public function makePermanent(Voucher $voucher, $user): Voucher
    {
        $this->ensureVoucherDateIsOpen($voucher->voucher_date_en ?: now()->toDateString(), $voucher->tenant_id ?: $this->tenantId($user));

        if ($voucher->status === 'cancelled') {
            throw ValidationException::withMessages([
                'voucher' => 'سند ابطال شده نمی تواند دائمی شود.',
            ]);
        }

        $totalDebit = (float) $voucher->items()->sum('debit_amount');
        $totalCredit = (float) $voucher->items()->sum('credit_amount');

        if (round($totalDebit, 2) <= 0 || round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw ValidationException::withMessages([
                'voucher' => 'سند نامتوازن است و نمی تواند دائمی شود.',
            ]);
        }

        $voucher->update([
            'status' => 'permanent',
            'is_permanent' => true,
            'posted_at' => now(),
            'approved_by' => $user?->id,
            'updated_by' => $user?->id,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'amount' => $totalDebit,
        ]);

        return $voucher->refresh();
    }

    public function reverseVoucher(Voucher $voucher, array $payload, $user): Voucher
    {
        $voucher->loadMissing('items');

        if (!$voucher->is_permanent) {
            throw ValidationException::withMessages([
                'voucher' => 'فقط سند دائمی با سند برگشتی اصلاح می شود. سند موقت را ابطال کنید.',
            ]);
        }

        if ($voucher->status === 'reversed' || $voucher->reversal_voucher_id) {
            throw ValidationException::withMessages([
                'voucher' => 'برای این سند قبلا سند برگشتی ثبت شده است.',
            ]);
        }

        if ($voucher->original_voucher_id || $voucher->document_type === 'voucher_reversal') {
            throw ValidationException::withMessages([
                'voucher' => 'سند برگشتی قابل برگشت مجدد نیست.',
            ]);
        }

        if (in_array($voucher->document_type, ['period_closing', 'period_opening'], true)) {
            throw ValidationException::withMessages([
                'voucher' => 'سند اختتامیه یا افتتاحیه فقط از مسیر بستن دوره مالی کنترل می شود.',
            ]);
        }

        if ($voucher->items->isEmpty()) {
            throw ValidationException::withMessages([
                'voucher' => 'سند بدون ردیف قابل برگشت نیست.',
            ]);
        }

        $date = Arr::get($payload, 'voucher_date_en') ?: now()->toDateString();
        $tenantId = $voucher->tenant_id ?: $this->tenantId($user);
        $organizationId = $voucher->organization_id ?: $this->organizationId($user);
        $reason = Arr::get($payload, 'reason') ?: 'برگشت سند شماره ' . $voucher->voucher_number;
        $this->ensureVoucherDateIsOpen($date, $tenantId);

        $totalDebit = (float) $voucher->items->sum('credit_amount');
        $totalCredit = (float) $voucher->items->sum('debit_amount');

        if (round($totalDebit, 2) <= 0 || round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw ValidationException::withMessages([
                'voucher' => 'سند اصلی برای برگشت تراز نیست.',
            ]);
        }

        return DB::transaction(function () use ($voucher, $user, $date, $tenantId, $organizationId, $reason, $totalDebit, $totalCredit) {
            $reversal = Voucher::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => $voucher->factor_id ?: 0,
                'account_id' => $voucher->items->first()->account_id,
                'voucher_type' => $voucher->voucher_type,
                'voucher_number' => $this->numberingService->nextVoucherNumber($tenantId, $date, 'REV'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $totalDebit,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'method' => $voucher->method,
                'document_type' => 'voucher_reversal',
                'status' => 'permanent',
                'is_permanent' => true,
                'source_type' => Voucher::class,
                'source_id' => $voucher->id,
                'original_voucher_id' => $voucher->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => 'سند برگشتی شماره ' . $voucher->voucher_number,
                'reversal_reason' => $reason,
                'posted_at' => now(),
                'approved_by' => $user?->id,
                'created_by' => $user?->id,
            ]);

            foreach ($voucher->items as $item) {
                $reversal->items()->create(array_merge([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $item->account_id,
                    'amount' => max((float) $item->credit_amount, (float) $item->debit_amount),
                    'debit_amount' => (float) $item->credit_amount,
                    'credit_amount' => (float) $item->debit_amount,
                    'method' => $item->method,
                    'payment_terminal_id' => $item->payment_terminal_id,
                    'issuing_bank' => $item->issuing_bank,
                    'due_date' => $item->due_date,
                    'cheque_photo' => $item->cheque_photo,
                    'description' => 'برگشت: ' . ($item->description ?: $voucher->voucher_number),
                ], $this->analyticItemAttributes($item)));
            }

            $voucher->update([
                'status' => 'reversed',
                'reversal_voucher_id' => $reversal->id,
                'reversal_reason' => $reason,
                'reversed_at' => now(),
                'reversed_by' => $user?->id,
                'updated_by' => $user?->id,
            ]);

            return $reversal->load('items.account');
        });
    }

    public function cancelDraftVoucher(Voucher $voucher, array $payload, $user): Voucher
    {
        if ($voucher->is_permanent) {
            throw ValidationException::withMessages([
                'voucher' => 'سند دائمی حذف یا ابطال مستقیم نمی شود؛ برای آن سند برگشتی ثبت کنید.',
            ]);
        }

        if ($voucher->status === 'cancelled') {
            return $voucher;
        }

        $voucher->update([
            'status' => 'cancelled',
            'reversal_reason' => Arr::get($payload, 'reason') ?: 'ابطال سند موقت',
            'cancelled_at' => now(),
            'cancelled_by' => $user?->id,
            'updated_by' => $user?->id,
        ]);

        return $voucher->refresh();
    }

    public function ensureEditableDraftVoucher(Voucher $voucher): void
    {
        if ($voucher->is_permanent || $voucher->status !== 'draft') {
            throw ValidationException::withMessages([
                'voucher' => 'فقط سند موقت قابل ویرایش است.',
            ]);
        }

        if (!in_array($voucher->document_type, ['manual', 'manual_copy', 'manual_template', 'manual_merge'], true)) {
            throw ValidationException::withMessages([
                'voucher' => 'سندهای عملیاتی فقط از مسیر سند مبدا اصلاح می شوند.',
            ]);
        }
    }

    public function mergeDraftVouchers(array $voucherIds, array $payload, $user): Voucher
    {
        $voucherIds = collect($voucherIds)->filter()->map(fn($id) => (int) $id)->unique()->values();

        if ($voucherIds->count() < 2) {
            throw ValidationException::withMessages([
                'voucher_ids' => 'برای ادغام حداقل دو سند موقت لازم است.',
            ]);
        }

        $tenantId = $this->tenantId($user);
        $query = Voucher::with('items')->whereIn('id', $voucherIds);

        if ((int) $user?->isGod !== 1) {
            $query->where('tenant_id', $tenantId);
        }

        $vouchers = $query->get();

        if ($vouchers->count() !== $voucherIds->count()) {
            throw ValidationException::withMessages([
                'voucher_ids' => 'برخی سندهای انتخاب شده قابل دسترسی نیستند.',
            ]);
        }

        foreach ($vouchers as $voucher) {
            if ($voucher->is_permanent || $voucher->status !== 'draft' || !in_array($voucher->document_type, ['manual', 'manual_copy', 'manual_template', 'manual_merge'], true)) {
                throw ValidationException::withMessages([
                    'voucher_ids' => 'فقط سندهای موقت دستی، کپی، الگو یا ادغام شده قابل ادغام هستند.',
                ]);
            }

            if ($voucher->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'voucher_ids' => 'سند بدون ردیف قابل ادغام نیست.',
                ]);
            }
        }

        $totalDebit = round((float) $vouchers->sum(fn($voucher) => $voucher->items->sum('debit_amount')), 2);
        $totalCredit = round((float) $vouchers->sum(fn($voucher) => $voucher->items->sum('credit_amount')), 2);

        if ($totalDebit <= 0 || $totalDebit !== $totalCredit) {
            throw ValidationException::withMessages([
                'voucher_ids' => 'جمع سندهای انتخاب شده باید تراز باشد.',
            ]);
        }

        $date = Arr::get($payload, 'voucher_date_en') ?: now()->toDateString();
        $this->ensureVoucherDateIsOpen($date, $tenantId);
        $organizationId = $vouchers->first()->organization_id ?: $this->organizationId($user);

        return DB::transaction(function () use ($vouchers, $payload, $user, $tenantId, $organizationId, $date, $totalDebit, $totalCredit) {
            $merged = Voucher::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => 0,
                'account_id' => $vouchers->first()->items->first()->account_id,
                'voucher_type' => 0,
                'voucher_number' => $this->numberingService->nextVoucherNumber($tenantId, $date, 'MRG'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $totalDebit,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'method' => 0,
                'document_type' => 'manual_merge',
                'status' => 'draft',
                'is_permanent' => false,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => Arr::get($payload, 'description') ?: 'ادغام اسناد موقت',
                'created_by' => $user?->id,
            ]);

            foreach ($vouchers as $voucher) {
                foreach ($voucher->items as $item) {
                    $merged->items()->create(array_merge([
                        'tenant_id' => $tenantId,
                        'organization_id' => $organizationId,
                        'account_id' => $item->account_id,
                        'amount' => max((float) $item->debit_amount, (float) $item->credit_amount),
                        'debit_amount' => (float) $item->debit_amount,
                        'credit_amount' => (float) $item->credit_amount,
                        'method' => $item->method,
                        'payment_terminal_id' => $item->payment_terminal_id,
                        'issuing_bank' => $item->issuing_bank,
                        'due_date' => $item->due_date,
                        'cheque_photo' => $item->cheque_photo,
                        'description' => $voucher->voucher_number . ' - ' . ($item->description ?: $voucher->description),
                    ], $this->analyticItemAttributes($item)));
                }

                $voucher->update([
                    'status' => 'cancelled',
                    'merged_into_voucher_id' => $merged->id,
                    'merged_at' => now(),
                    'merged_by' => $user?->id,
                    'cancelled_at' => now(),
                    'cancelled_by' => $user?->id,
                    'reversal_reason' => 'ادغام در سند ' . $merged->voucher_number,
                    'updated_by' => $user?->id,
                ]);
            }

            return $merged->load('items.account');
        });
    }

    public function copyVoucherToDraft(Voucher $sourceVoucher, array $payload, $user): Voucher
    {
        $sourceVoucher->loadMissing('items');

        if ($sourceVoucher->status === 'cancelled') {
            throw ValidationException::withMessages([
                'voucher' => 'سند ابطال شده قابل کپی نیست.',
            ]);
        }

        if ($sourceVoucher->items->isEmpty()) {
            throw ValidationException::withMessages([
                'voucher' => 'سند بدون ردیف قابل کپی نیست.',
            ]);
        }

        $date = Arr::get($payload, 'voucher_date_en') ?: now()->toDateString();
        $tenantId = $sourceVoucher->tenant_id ?: $this->tenantId($user);
        $organizationId = $this->organizationIdFromSubject($sourceVoucher, $user);
        $this->ensureVoucherDateIsOpen($date, $tenantId);

        $totalDebit = (float) $sourceVoucher->items->sum('debit_amount');
        $totalCredit = (float) $sourceVoucher->items->sum('credit_amount');

        if (round($totalDebit, 2) <= 0 || round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw ValidationException::withMessages([
                'voucher' => 'سند مبدا تراز نیست و قابل کپی به سند جدید نیست.',
            ]);
        }

        return DB::transaction(function () use ($sourceVoucher, $user, $date, $tenantId, $organizationId, $totalDebit, $totalCredit) {
            $copy = Voucher::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => 0,
                'account_id' => $sourceVoucher->items->first()->account_id,
                'voucher_type' => 0,
                'voucher_number' => $this->numberingService->nextVoucherNumber($tenantId, $date, 'CPY'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $totalDebit,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'method' => 0,
                'document_type' => 'manual_copy',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => Voucher::class,
                'source_id' => $sourceVoucher->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => 'کپی سند ' . $sourceVoucher->voucher_number,
                'created_by' => $user?->id,
            ]);

            foreach ($sourceVoucher->items as $item) {
                $copy->items()->create(array_merge([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $item->account_id,
                    'amount' => max((float) $item->debit_amount, (float) $item->credit_amount),
                    'debit_amount' => (float) $item->debit_amount,
                    'credit_amount' => (float) $item->credit_amount,
                    'method' => $item->method,
                    'payment_terminal_id' => $item->payment_terminal_id,
                    'issuing_bank' => $item->issuing_bank,
                    'due_date' => $item->due_date,
                    'cheque_photo' => $item->cheque_photo,
                    'description' => $item->description,
                ], $this->analyticItemAttributes($item)));
            }

            return $copy->load('items.account');
        });
    }

    public function createTemplateFromVoucher(Voucher $sourceVoucher, array $payload, $user): VoucherTemplate
    {
        $sourceVoucher->loadMissing('items');

        if ($sourceVoucher->status === 'cancelled') {
            throw ValidationException::withMessages([
                'voucher' => 'سند ابطال شده قابل تبدیل به الگو نیست.',
            ]);
        }

        if ($sourceVoucher->items->isEmpty()) {
            throw ValidationException::withMessages([
                'voucher' => 'سند بدون ردیف قابل تبدیل به الگو نیست.',
            ]);
        }

        $totalDebit = (float) $sourceVoucher->items->sum('debit_amount');
        $totalCredit = (float) $sourceVoucher->items->sum('credit_amount');

        if (round($totalDebit, 2) <= 0 || round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw ValidationException::withMessages([
                'voucher' => 'سند مبدا تراز نیست و قابل تبدیل به الگو نیست.',
            ]);
        }

        $tenantId = $sourceVoucher->tenant_id ?: $this->tenantId($user);
        $organizationId = $sourceVoucher->organization_id ?: $this->organizationId($user);
        $name = trim((string) Arr::get($payload, 'name')) ?: 'الگوی سند ' . $sourceVoucher->voucher_number;

        return DB::transaction(function () use ($sourceVoucher, $payload, $user, $tenantId, $organizationId, $name) {
            $template = VoucherTemplate::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'source_voucher_id' => $sourceVoucher->id,
                'name' => $name,
                'frequency' => Arr::get($payload, 'frequency', 'on_demand') ?: 'on_demand',
                'description' => Arr::get($payload, 'description') ?: $sourceVoucher->description,
                'is_active' => true,
                'created_by' => $user?->id,
            ]);

            foreach ($sourceVoucher->items as $item) {
                $template->items()->create(array_merge([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $item->account_id,
                    'amount' => max((float) $item->debit_amount, (float) $item->credit_amount),
                    'debit_amount' => (float) $item->debit_amount,
                    'credit_amount' => (float) $item->credit_amount,
                    'method' => $item->method,
                    'payment_terminal_id' => $item->payment_terminal_id,
                    'issuing_bank' => $item->issuing_bank,
                    'due_date' => $item->due_date,
                    'cheque_photo' => $item->cheque_photo,
                    'description' => $item->description,
                ], $this->analyticItemAttributes($item)));
            }

            return $template->load('items.account');
        });
    }

    public function createDraftFromTemplate(VoucherTemplate $template, array $payload, $user): Voucher
    {
        $template->loadMissing('items');

        if (!$template->is_active) {
            throw ValidationException::withMessages([
                'template' => 'الگوی انتخاب شده غیرفعال است.',
            ]);
        }

        if ($template->items->isEmpty()) {
            throw ValidationException::withMessages([
                'template' => 'الگوی بدون ردیف قابل تبدیل به سند نیست.',
            ]);
        }

        $date = Arr::get($payload, 'voucher_date_en') ?: now()->toDateString();
        $tenantId = $template->tenant_id ?: $this->tenantId($user);
        $organizationId = $template->organization_id ?: $this->organizationId($user);
        $this->ensureVoucherDateIsOpen($date, $tenantId);

        $totalDebit = (float) $template->items->sum('debit_amount');
        $totalCredit = (float) $template->items->sum('credit_amount');

        if (round($totalDebit, 2) <= 0 || round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw ValidationException::withMessages([
                'template' => 'الگوی انتخاب شده تراز نیست.',
            ]);
        }

        return DB::transaction(function () use ($template, $payload, $user, $date, $tenantId, $organizationId, $totalDebit, $totalCredit) {
            $voucher = Voucher::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => 0,
                'account_id' => $template->items->first()->account_id,
                'voucher_type' => 0,
                'voucher_number' => $this->numberingService->nextVoucherNumber($tenantId, $date, 'TPL'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $totalDebit,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'method' => 0,
                'document_type' => 'manual_template',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => VoucherTemplate::class,
                'source_id' => $template->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => Arr::get($payload, 'description') ?: 'ثبت از الگوی ' . $template->name,
                'created_by' => $user?->id,
            ]);

            foreach ($template->items as $item) {
                $voucher->items()->create(array_merge([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $item->account_id,
                    'amount' => max((float) $item->debit_amount, (float) $item->credit_amount),
                    'debit_amount' => (float) $item->debit_amount,
                    'credit_amount' => (float) $item->credit_amount,
                    'method' => $item->method,
                    'payment_terminal_id' => $item->payment_terminal_id,
                    'issuing_bank' => $item->issuing_bank,
                    'due_date' => $item->due_date,
                    'cheque_photo' => $item->cheque_photo,
                    'description' => $item->description,
                ], $this->analyticItemAttributes($item)));
            }

            return $voucher->load('items.account');
        });
    }

    public function postOperationalExpenseVoucher(OperationalExpense $expense, $user = null): Voucher
    {
        $expense->loadMissing(['expenseType', 'costCenter']);
        $tenantId = $expense->tenant_id ?: $this->tenantId($user);
        $organizationId = $expense->organization_id ?: $this->organizationId($user);
        $date = $expense->expense_date_en ? $expense->expense_date_en->format('Y-m-d') : now()->toDateString();
        $totalAmount = $this->money($expense->total_amount ?: ((float) $expense->amount + (float) $expense->tax_amount));

        if ($totalAmount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'مبلغ هزینه باید بزرگتر از صفر باشد.',
            ]);
        }

        $this->ensureVoucherDateIsOpen($date, $tenantId);

        $expenseAccountId = (int) ($expense->expense_account_id ?: $expense->expenseType?->account_id)
            ?: $this->systemAccountId('SYS-5201', 'هزینه های عمومی و اداری', $tenantId, $organizationId, $user);
        $settlementAccountId = (int) $expense->settlement_account_id
            ?: $this->systemAccountId('SYS-2203', 'سایر حساب های پرداختنی', $tenantId, $organizationId, $user);
        $lines = [
            [
                'account_id' => $expenseAccountId,
                'debit_amount' => $totalAmount,
                'credit_amount' => 0,
                'description' => 'ثبت هزینه ' . ($expense->expenseType?->name ?: $expense->expense_number),
            ],
            [
                'account_id' => $settlementAccountId,
                'debit_amount' => 0,
                'credit_amount' => $totalAmount,
                'description' => 'تسویه/پرداخت هزینه ' . ($expense->expense_number ?: ''),
            ],
        ];

        return DB::transaction(function () use ($expense, $user, $tenantId, $organizationId, $date, $totalAmount, $lines) {
            $voucher = Voucher::where('source_type', OperationalExpense::class)
                ->where('source_id', $expense->id)
                ->where('document_type', 'operational_expense')
                ->first() ?: new Voucher();

            if ($voucher->exists && $voucher->is_permanent) {
                return $voucher->load('items.account');
            }

            $voucher->fill([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => 0,
                'account_id' => $lines[0]['account_id'],
                'voucher_type' => 0,
                'voucher_number' => $voucher->voucher_number ?: $this->numberingService->nextVoucherNumber($tenantId, $date, 'EXP'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $totalAmount,
                'total_debit' => $totalAmount,
                'total_credit' => $totalAmount,
                'method' => 0,
                'document_type' => 'operational_expense',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => OperationalExpense::class,
                'source_id' => $expense->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => $expense->description ?: 'ثبت هزینه عملیاتی ' . $expense->expense_number,
                'created_by' => $voucher->created_by ?: $user?->id,
                'updated_by' => $user?->id,
            ]);
            $voucher->save();
            $voucher->items()->delete();
            $expenseVoucherItem = null;

            foreach ($lines as $line) {
                $voucherItem = $voucher->items()->create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $line['account_id'],
                    'cost_center_id' => $expense->cost_center_id,
                    'expense_id' => $expense->id,
                    'branch_id' => $expense->store_id,
                    'product_id' => $expense->product_id,
                    'project_code' => $expense->project_code,
                    'contract_code' => $expense->contract_code,
                    'analytic_note' => $expense->specialized_kind ?: $expense->workflow_status,
                    'amount' => max($line['debit_amount'], $line['credit_amount']),
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'method' => 0,
                    'description' => $line['description'],
                ]);

                if ((float) $line['debit_amount'] > 0) {
                    $expenseVoucherItem = $voucherItem;
                }
            }

            $this->syncOperationalExpenseAllocation($expense, $voucher, $expenseVoucherItem, $tenantId, $organizationId, $totalAmount, $user);

            $expense->update([
                'voucher_id' => $voucher->id,
                'updated_by' => $user?->id,
            ]);

            return $voucher->load(['items.account', 'items.costCenter']);
        });
    }

    private function syncOperationalExpenseAllocation(OperationalExpense $expense, Voucher $voucher, $voucherItem, ?int $tenantId, ?int $organizationId, float $totalAmount, $user): void
    {
        if (!Schema::hasTable('expense_allocations')) {
            return;
        }

        $basis = $expense->allocation_basis ?: $expense->costCenter?->allocation_basis ?: 'direct';
        $targetType = $expense->allocation_target_type ?: 'manual';
        $targetId = $expense->allocation_target_id;

        if ($expense->product_id && in_array($targetType, ['', 'manual'], true)) {
            $targetType = 'product';
            $targetId = $expense->product_id;
        } elseif ($expense->project_code && in_array($targetType, ['', 'manual'], true)) {
            $targetType = 'project';
        } elseif ($expense->contract_code && in_array($targetType, ['', 'manual'], true)) {
            $targetType = 'contract';
        } elseif ($expense->cost_center_id && in_array($targetType, ['', 'manual'], true)) {
            $targetType = 'cost_center';
            $targetId = $expense->cost_center_id;
        }

        $expense->allocations()->delete();

        $payload = [
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'operational_expense_id' => $expense->id,
            'voucher_id' => $voucher->id,
            'voucher_item_id' => $voucherItem?->id,
            'cost_center_id' => $expense->cost_center_id,
            'expense_type_id' => $expense->expense_type_id,
            'allocation_basis' => $basis ?: 'direct',
            'allocation_target_type' => $targetType ?: 'manual',
            'allocation_target_id' => $targetId,
            'product_id' => $expense->product_id,
            'project_code' => $expense->project_code,
            'contract_code' => $expense->contract_code,
            'basis_quantity' => 1,
            'allocation_percent' => 100,
            'allocated_amount' => $totalAmount,
            'note' => $expense->allocation_note ?: $expense->workflow_note,
            'created_by' => $user?->id,
            'updated_by' => $user?->id,
        ];

        if (Schema::hasColumn('expense_allocations', 'target_type')) {
            $payload['target_type'] = $payload['allocation_target_type'];
        }

        if (Schema::hasColumn('expense_allocations', 'target_id')) {
            $payload['target_id'] = $payload['allocation_target_id'];
        }

        if (Schema::hasColumn('expense_allocations', 'basis_value')) {
            $payload['basis_value'] = $payload['basis_quantity'];
        }

        if (Schema::hasColumn('expense_allocations', 'amount')) {
            $payload['amount'] = $payload['allocated_amount'];
        }

        $allocation = ExpenseAllocation::create($payload);

        if ($voucherItem && Schema::hasColumn('voucher_items', 'expense_allocation_id')) {
            $voucherItem->update(['expense_allocation_id' => $allocation->id]);
        }
    }

    public function postOperationalIncomeVoucher(OperationalIncome $income, $user = null): Voucher
    {
        $income->loadMissing(['incomeType', 'revenueCenter']);
        $tenantId = $income->tenant_id ?: $this->tenantId($user);
        $organizationId = $income->organization_id ?: $this->organizationId($user);
        $date = $income->income_date_en ? $income->income_date_en->format('Y-m-d') : now()->toDateString();
        $amount = $this->money($income->amount);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'مبلغ درآمد باید بزرگتر از صفر باشد.',
            ]);
        }

        $this->ensureVoucherDateIsOpen($date, $tenantId);

        $receiptAccountId = (int) $income->receipt_account_id
            ?: $this->systemAccountId('SYS-1101', 'صندوق و بانک درآمدهای عملیاتی', $tenantId, $organizationId, $user);
        $incomeAccountId = (int) ($income->income_account_id ?: $income->incomeType?->account_id)
            ?: $this->systemAccountId('SYS-4101', 'درآمدهای عملیاتی و خدماتی', $tenantId, $organizationId, $user);
        $lines = [
            [
                'account_id' => $receiptAccountId,
                'debit_amount' => $amount,
                'credit_amount' => 0,
                'description' => 'دریافت/ثبت درآمد ' . ($income->income_number ?: ''),
                'revenue_center_id' => null,
            ],
            [
                'account_id' => $incomeAccountId,
                'debit_amount' => 0,
                'credit_amount' => $amount,
                'description' => 'ثبت درآمد ' . ($income->incomeType?->name ?: $income->income_number),
                'revenue_center_id' => $income->revenue_center_id,
            ],
        ];

        return DB::transaction(function () use ($income, $user, $tenantId, $organizationId, $date, $amount, $lines) {
            $voucher = Voucher::where('source_type', OperationalIncome::class)
                ->where('source_id', $income->id)
                ->where('document_type', 'operational_income')
                ->first() ?: new Voucher();

            if ($voucher->exists && $voucher->is_permanent) {
                return $voucher->load('items.account');
            }

            $voucher->fill([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => 0,
                'account_id' => $lines[1]['account_id'],
                'voucher_type' => 0,
                'voucher_number' => $voucher->voucher_number ?: $this->numberingService->nextVoucherNumber($tenantId, $date, 'INC'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $amount,
                'total_debit' => $amount,
                'total_credit' => $amount,
                'method' => 0,
                'document_type' => 'operational_income',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => OperationalIncome::class,
                'source_id' => $income->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => $income->description ?: 'ثبت درآمد عملیاتی ' . $income->income_number,
                'created_by' => $voucher->created_by ?: $user?->id,
                'updated_by' => $user?->id,
            ]);
            $voucher->save();
            $voucher->items()->delete();

            foreach ($lines as $line) {
                $voucher->items()->create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $line['account_id'],
                    'revenue_center_id' => $line['revenue_center_id'],
                    'income_id' => $income->id,
                    'branch_id' => $income->store_id,
                    'amount' => max($line['debit_amount'], $line['credit_amount']),
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'method' => 0,
                    'description' => $line['description'],
                ]);
            }

            $income->update([
                'voucher_id' => $voucher->id,
                'updated_by' => $user?->id,
            ]);

            return $voucher->load(['items.account', 'items.revenueCenter']);
        });
    }

    public function postPishfactorSaleVoucher(Pishfactor $pishfactor, array $payment = [], $user = null): ?Voucher
    {
        $pishfactor = $pishfactor->fresh() ?: $pishfactor;

        if ($this->isSalesReturnPishfactor($pishfactor)) {
            $this->deleteDraftVoucher(Pishfactor::class, (int) $pishfactor->id, 'sales_invoice');
            return $this->postPishfactorSalesReturnVoucher($pishfactor, $payment, $user);
        }

        $total = $this->money($pishfactor->fullPrice);

        if ($total <= 0 || !in_array((int) $pishfactor->status, [1, 4], true)) {
            return null;
        }

        $tenantId = $this->tenantIdFromSubject($pishfactor, $user);
        $organizationId = $this->organizationIdFromSubject($pishfactor, $user);
        $date = $this->postingDate($pishfactor);
        $this->ensureVoucherDateIsOpen($date, $tenantId);
        $paidAmount = min($total, $this->paymentAmount($payment));
        $receivableAmount = round($total - $paidAmount, 2);
        $netSales = min($total, $this->money($pishfactor->pat_price));
        $taxAmount = round($total - $netSales, 2);

        if ($netSales <= 0) {
            $netSales = $total;
            $taxAmount = 0;
        }

        return DB::transaction(function () use ($pishfactor, $payment, $user, $total, $tenantId, $organizationId, $date, $paidAmount, $receivableAmount, $netSales, $taxAmount) {
            $paymentType = (int) Arr::get($payment, 'payment_type', $pishfactor->payment_type ?: 5);
            $lines = [];
            $dimensions = $this->pishfactorAnalyticDimensions($pishfactor);

            if ($paidAmount > 0) {
                $lines[] = [
                    'account_id' => $this->paymentAccountId($paymentType, $payment, $tenantId, $organizationId, $user),
                    'debit_amount' => $paidAmount,
                    'credit_amount' => 0,
                    'method' => $paymentType,
                    'payment_terminal_id' => $paymentType === 4 ? Arr::get($payment, 'terminal_id') : null,
                    'cheque_photo' => $paymentType === 2 ? Arr::get($payment, 'cheque_photo') : null,
                    'description' => 'دریافت بابت فاکتور فروش شماره ' . $pishfactor->invoiceID,
                ];
            }

            if ($receivableAmount > 0) {
                $lines[] = [
                    'account_id' => $this->systemAccountId('SYS-1201', 'حساب های دریافتنی تجاری', $tenantId, $organizationId, $user),
                    'debit_amount' => $receivableAmount,
                    'credit_amount' => 0,
                    'method' => 1,
                    'payment_terminal_id' => null,
                    'cheque_photo' => null,
                    'description' => 'مانده دریافتنی فاکتور فروش شماره ' . $pishfactor->invoiceID,
                ];
            }

            $salesAccountId = $this->systemAccountId('SYS-4101', 'فروش کالا و خدمات', $tenantId, $organizationId, $user);
            foreach ($this->pishfactorSalesProductLines($pishfactor, $netSales) as $productLine) {
                $lines[] = [
                    'account_id' => $salesAccountId,
                    'debit_amount' => 0,
                    'credit_amount' => $productLine['amount'],
                    'method' => 0,
                    'payment_terminal_id' => null,
                    'cheque_photo' => null,
                    'product_id' => $productLine['product_id'],
                    'customer_id' => $pishfactor->customer_id,
                    'revenue_center_id' => $dimensions['revenue_center_id'],
                    'project_code' => $dimensions['project_code'],
                    'employee_id' => $dimensions['employee_id'],
                    'contract_code' => $dimensions['contract_code'],
                    'route_code' => $dimensions['route_code'],
                    'analytic_note' => $productLine['analytic_note'],
                    'description' => $productLine['description'],
                ];
            }

            if ($taxAmount > 0) {
                $lines[] = [
                    'account_id' => $this->systemAccountId('SYS-2401', 'مالیات و عوارض فروش', $tenantId, $organizationId, $user),
                    'debit_amount' => 0,
                    'credit_amount' => $taxAmount,
                    'method' => 0,
                    'payment_terminal_id' => null,
                    'cheque_photo' => null,
                    'description' => 'مالیات فروش فاکتور شماره ' . $pishfactor->invoiceID,
                ];
            }

            $voucher = Voucher::where('source_type', Pishfactor::class)
                ->where('source_id', $pishfactor->id)
                ->where('document_type', 'sales_invoice')
                ->first()
                ?: Voucher::where('factor_id', $pishfactor->id)
                ->where('voucher_type', 1)
                ->where(function ($query) use ($pishfactor) {
                    $query->where('voucher_number', (string) $pishfactor->invoiceID)
                        ->orWhere('description', 'like', 'رسید پرداخت فاکتور شماره %');
                })
                ->first()
                ?: new Voucher();

            if ($voucher->exists && $voucher->is_permanent) {
                return $voucher->load('items.account');
            }

            $voucher->fill([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => $pishfactor->id,
                'account_id' => $lines[0]['account_id'],
                'voucher_type' => 1,
                'voucher_number' => $voucher->voucher_number ?: $this->numberingService->nextVoucherNumber($tenantId, $date, 'SAL'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $total,
                'total_debit' => $total,
                'total_credit' => $total,
                'method' => $paymentType,
                'document_type' => 'sales_invoice',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => Pishfactor::class,
                'source_id' => $pishfactor->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => 'سند فروش فاکتور شماره ' . $pishfactor->invoiceID,
                'created_by' => $voucher->created_by ?: $user?->id,
                'updated_by' => $user?->id,
            ]);
            $voucher->save();

            $voucher->items()->delete();

            foreach ($lines as $line) {
                $voucher->items()->create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $line['account_id'],
                    'amount' => max($line['debit_amount'], $line['credit_amount']),
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'method' => $line['method'],
                    'payment_terminal_id' => $line['payment_terminal_id'],
                    'cheque_photo' => $line['cheque_photo'],
                    'product_id' => $line['product_id'] ?? null,
                    'customer_id' => $line['customer_id'] ?? null,
                    'revenue_center_id' => $line['revenue_center_id'] ?? null,
                    'project_code' => $line['project_code'] ?? null,
                    'employee_id' => $line['employee_id'] ?? null,
                    'contract_code' => $line['contract_code'] ?? null,
                    'route_code' => $line['route_code'] ?? null,
                    'analytic_note' => $line['analytic_note'] ?? null,
                    'description' => $line['description'],
                ]);
            }

            return $voucher->load('items.account');
        });
    }

    public function postCustomerPortalPaymentVoucher(CustomerPortalPayment $payment, $user = null): ?Voucher
    {
        $payment = $payment->fresh(['customer', 'order']) ?: $payment;
        $amount = $this->money($payment->payable_amount ?: $payment->amount);

        if ($amount <= 0 || $payment->status !== 'verified') {
            $this->deleteDraftVoucher(CustomerPortalPayment::class, (int) $payment->id, 'customer_portal_payment');
            return null;
        }

        $tenantId = $this->tenantIdFromSubject($payment, $user);
        $organizationId = $this->organizationIdFromSubject($payment, $user);
        $date = optional($payment->verified_at ?: $payment->submitted_at)->toDateString() ?: now()->toDateString();
        $this->ensureVoucherDateIsOpen($date, $tenantId);

        $paymentType = match ($payment->payment_method) {
            'online_gateway', 'pos' => 4,
            'bank_transfer', 'card_to_card' => 3,
            default => 3,
        };
        $terminalId = (int) config('services.customer_portal_gateway.terminal_id') ?: null;
        $treasuryAccountId = $this->paymentAccountId($paymentType, ['terminal_id' => $terminalId], $tenantId, $organizationId, $user);
        $receivableAccountId = $this->systemAccountId('SYS-1201', 'حساب های دریافتنی تجاری', $tenantId, $organizationId, $user);
        $customerName = $payment->customer?->name ?: ('مشتری #' . $payment->customer_id);
        $reference = $payment->reference_number ?: $payment->authority ?: $payment->id;
        $orderTitle = $payment->pishfactor_id ? ' پیش فاکتور #' . $payment->pishfactor_id : '';
        $description = 'تسویه پرداخت پورتال ' . $reference . ' برای ' . $customerName . $orderTitle;

        return DB::transaction(function () use ($payment, $user, $tenantId, $organizationId, $date, $amount, $paymentType, $terminalId, $treasuryAccountId, $receivableAccountId, $description) {
            $voucher = Voucher::where('source_type', CustomerPortalPayment::class)
                ->where('source_id', $payment->id)
                ->where('document_type', 'customer_portal_payment')
                ->first() ?: new Voucher();

            if ($voucher->exists && $voucher->is_permanent) {
                return $voucher->load('items.account');
            }

            $voucher->fill([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => $payment->pishfactor_id ?: 0,
                'account_id' => $treasuryAccountId,
                'voucher_type' => 8,
                'voucher_number' => $voucher->voucher_number ?: $this->numberingService->nextVoucherNumber($tenantId, $date, 'CPP'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $amount,
                'total_debit' => $amount,
                'total_credit' => $amount,
                'method' => $paymentType,
                'document_type' => 'customer_portal_payment',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => CustomerPortalPayment::class,
                'source_id' => $payment->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => $description,
                'created_by' => $voucher->created_by ?: $user?->id,
                'updated_by' => $user?->id,
            ]);
            $voucher->save();

            $voucher->items()->delete();

            $voucher->items()->create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'account_id' => $treasuryAccountId,
                'amount' => $amount,
                'debit_amount' => $amount,
                'credit_amount' => 0,
                'method' => $paymentType,
                'payment_terminal_id' => $paymentType === 4 ? $terminalId : null,
                'customer_id' => $payment->customer_id,
                'description' => $description,
            ]);

            $voucher->items()->create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'account_id' => $receivableAccountId,
                'amount' => $amount,
                'debit_amount' => 0,
                'credit_amount' => $amount,
                'method' => 0,
                'customer_id' => $payment->customer_id,
                'description' => 'کاهش دریافتنی بابت ' . $description,
            ]);

            $paymentUpdate = ['accounting_voucher_id' => $voucher->id];
            if (Schema::hasColumn('customer_portal_payments', 'gateway_settlement_status')) {
                $paymentUpdate['gateway_settlement_status'] = 'posted';
            }
            if (Schema::hasColumn('customer_portal_payments', 'gateway_settled_at')) {
                $paymentUpdate['gateway_settled_at'] = now();
            }
            $payment->update($paymentUpdate);

            return $voucher->load('items.account');
        });
    }

    public function postPishfactorCostOfGoodsVoucher(Pishfactor $pishfactor, $user = null): ?Voucher
    {
        $pishfactor = $pishfactor->fresh() ?: $pishfactor;

        if ($this->isSalesReturnPishfactor($pishfactor)) {
            $this->deleteDraftVoucher(Pishfactor::class, (int) $pishfactor->id, 'sales_cogs');
            return $this->postPishfactorSalesReturnCostVoucher($pishfactor, $user);
        }

        if (!in_array((int) $pishfactor->status, [1, 4], true)) {
            $this->deleteDraftVoucher(Pishfactor::class, (int) $pishfactor->id, 'sales_cogs');
            return null;
        }

        $totalCost = $this->pishfactorCostOfGoods($pishfactor);

        if ($totalCost <= 0) {
            $this->deleteDraftVoucher(Pishfactor::class, (int) $pishfactor->id, 'sales_cogs');
            return null;
        }

        $tenantId = $this->tenantIdFromSubject($pishfactor, $user);
        $organizationId = $this->organizationIdFromSubject($pishfactor, $user);
        $date = $this->postingDate($pishfactor);
        $this->ensureVoucherDateIsOpen($date, $tenantId);

        return DB::transaction(function () use ($pishfactor, $user, $totalCost, $tenantId, $organizationId, $date) {
            $lines = [];
            $dimensions = $this->pishfactorAnalyticDimensions($pishfactor);
            $cogsAccountId = $this->systemAccountId('SYS-5102', 'بهای تمام شده کالای فروش رفته', $tenantId, $organizationId, $user);
            $inventoryAccountId = $this->systemAccountId('SYS-1301', 'موجودی کالا', $tenantId, $organizationId, $user);

            foreach ($this->pishfactorCostProductLines($pishfactor, $totalCost) as $productLine) {
                $lines[] = [
                    'account_id' => $cogsAccountId,
                    'debit_amount' => $productLine['amount'],
                    'credit_amount' => 0,
                    'product_id' => $productLine['product_id'],
                    'customer_id' => $pishfactor->customer_id,
                    'revenue_center_id' => $dimensions['revenue_center_id'],
                    'project_code' => $dimensions['project_code'],
                    'employee_id' => $dimensions['employee_id'],
                    'contract_code' => $dimensions['contract_code'],
                    'route_code' => $dimensions['route_code'],
                    'analytic_note' => 'sales_cogs_product',
                    'description' => $productLine['description'],
                ];
                $lines[] = [
                    'account_id' => $inventoryAccountId,
                    'debit_amount' => 0,
                    'credit_amount' => $productLine['amount'],
                    'product_id' => $productLine['product_id'],
                    'customer_id' => $pishfactor->customer_id,
                    'revenue_center_id' => $dimensions['revenue_center_id'],
                    'project_code' => $dimensions['project_code'],
                    'employee_id' => $dimensions['employee_id'],
                    'contract_code' => $dimensions['contract_code'],
                    'route_code' => $dimensions['route_code'],
                    'analytic_note' => 'sales_cogs_inventory',
                    'description' => 'کاهش موجودی بابت ' . $productLine['description'],
                ];
            }

            $voucher = Voucher::where('source_type', Pishfactor::class)
                ->where('source_id', $pishfactor->id)
                ->where('document_type', 'sales_cogs')
                ->first() ?: new Voucher();

            if ($voucher->exists && $voucher->is_permanent) {
                return $voucher->load('items.account');
            }

            $voucher->fill([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => $pishfactor->id,
                'account_id' => $lines[0]['account_id'],
                'voucher_type' => 4,
                'voucher_number' => $voucher->voucher_number ?: $this->numberingService->nextVoucherNumber($tenantId, $date, 'COG'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $totalCost,
                'total_debit' => $totalCost,
                'total_credit' => $totalCost,
                'method' => 0,
                'document_type' => 'sales_cogs',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => Pishfactor::class,
                'source_id' => $pishfactor->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => 'سند بهای تمام شده فروش فاکتور شماره ' . $pishfactor->invoiceID,
                'created_by' => $voucher->created_by ?: $user?->id,
                'updated_by' => $user?->id,
            ]);
            $voucher->save();

            $voucher->items()->delete();

            foreach ($lines as $line) {
                $voucher->items()->create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $line['account_id'],
                    'amount' => max($line['debit_amount'], $line['credit_amount']),
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'method' => $line['debit_amount'] > 0 ? 1 : 0,
                    'product_id' => $line['product_id'] ?? null,
                    'customer_id' => $line['customer_id'] ?? null,
                    'revenue_center_id' => $line['revenue_center_id'] ?? null,
                    'project_code' => $line['project_code'] ?? null,
                    'employee_id' => $line['employee_id'] ?? null,
                    'contract_code' => $line['contract_code'] ?? null,
                    'route_code' => $line['route_code'] ?? null,
                    'analytic_note' => $line['analytic_note'] ?? null,
                    'description' => $line['description'],
                ]);
            }

            return $voucher->load('items.account');
        });
    }

    public function removePishfactorCostOfGoodsVoucher(Pishfactor $pishfactor): void
    {
        $this->deleteDraftVoucher(Pishfactor::class, (int) $pishfactor->id, 'sales_cogs');
        $this->deleteDraftVoucher(Pishfactor::class, (int) $pishfactor->id, 'sales_return_cogs');
    }

    public function postPishfactorSalesReturnVoucher(Pishfactor $pishfactor, array $payment = [], $user = null): ?Voucher
    {
        $pishfactor = $pishfactor->fresh() ?: $pishfactor;
        $total = $this->money($pishfactor->fullPrice);

        if ($total <= 0 || !in_array((int) $pishfactor->status, [1, 4], true)) {
            $this->deleteDraftVoucher(Pishfactor::class, (int) $pishfactor->id, 'sales_return');
            return null;
        }

        $tenantId = $this->tenantIdFromSubject($pishfactor, $user);
        $organizationId = $this->organizationIdFromSubject($pishfactor, $user);
        $date = $this->postingDate($pishfactor);
        $this->ensureVoucherDateIsOpen($date, $tenantId);
        $refundAmount = min($total, $this->paymentAmount($payment));
        $receivableReduction = round($total - $refundAmount, 2);
        $netSales = min($total, $this->money($pishfactor->pat_price));
        $taxAmount = round($total - $netSales, 2);

        if ($netSales <= 0) {
            $netSales = $total;
            $taxAmount = 0;
        }

        return DB::transaction(function () use ($pishfactor, $payment, $user, $total, $tenantId, $organizationId, $date, $refundAmount, $receivableReduction, $netSales, $taxAmount) {
            $paymentType = (int) Arr::get($payment, 'payment_type', $pishfactor->payment_type ?: 5);
            $lines = [];
            $dimensions = $this->pishfactorAnalyticDimensions($pishfactor);
            $returnAccountId = $this->systemAccountId('SYS-4102', 'برگشت از فروش و تخفیفات', $tenantId, $organizationId, $user);

            foreach ($this->pishfactorSalesProductLines($pishfactor, $netSales) as $productLine) {
                $lines[] = [
                    'account_id' => $returnAccountId,
                    'debit_amount' => $productLine['amount'],
                    'credit_amount' => 0,
                    'method' => 1,
                    'payment_terminal_id' => null,
                    'cheque_photo' => null,
                    'product_id' => $productLine['product_id'],
                    'customer_id' => $pishfactor->customer_id,
                    'revenue_center_id' => $dimensions['revenue_center_id'],
                    'project_code' => $dimensions['project_code'],
                    'employee_id' => $dimensions['employee_id'],
                    'contract_code' => $dimensions['contract_code'],
                    'route_code' => $dimensions['route_code'],
                    'analytic_note' => 'sales_return_product',
                    'description' => 'برگشت ' . $productLine['description'],
                ];
            }

            if ($taxAmount > 0) {
                $lines[] = [
                    'account_id' => $this->systemAccountId('SYS-2401', 'مالیات و عوارض فروش', $tenantId, $organizationId, $user),
                    'debit_amount' => $taxAmount,
                    'credit_amount' => 0,
                    'method' => 1,
                    'payment_terminal_id' => null,
                    'cheque_photo' => null,
                    'description' => 'برگشت مالیات فروش فاکتور شماره ' . $pishfactor->invoiceID,
                ];
            }

            if ($refundAmount > 0) {
                $lines[] = [
                    'account_id' => $this->paymentAccountId($paymentType, $payment, $tenantId, $organizationId, $user),
                    'debit_amount' => 0,
                    'credit_amount' => $refundAmount,
                    'method' => $paymentType,
                    'payment_terminal_id' => $paymentType === 4 ? Arr::get($payment, 'terminal_id') : null,
                    'cheque_photo' => $paymentType === 2 ? Arr::get($payment, 'cheque_photo') : null,
                    'description' => 'استرداد وجه بابت برگشت فروش فاکتور شماره ' . $pishfactor->invoiceID,
                ];
            }

            if ($receivableReduction > 0) {
                $lines[] = [
                    'account_id' => $this->systemAccountId('SYS-1201', 'حساب های دریافتنی تجاری', $tenantId, $organizationId, $user),
                    'debit_amount' => 0,
                    'credit_amount' => $receivableReduction,
                    'method' => 0,
                    'payment_terminal_id' => null,
                    'cheque_photo' => null,
                    'description' => 'کاهش دریافتنی بابت برگشت فروش فاکتور شماره ' . $pishfactor->invoiceID,
                ];
            }

            $voucher = Voucher::where('source_type', Pishfactor::class)
                ->where('source_id', $pishfactor->id)
                ->where('document_type', 'sales_return')
                ->first() ?: new Voucher();

            if ($voucher->exists && $voucher->is_permanent) {
                return $voucher->load('items.account');
            }

            $voucher->fill([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => $pishfactor->id,
                'account_id' => $lines[0]['account_id'],
                'voucher_type' => 1,
                'voucher_number' => $voucher->voucher_number ?: $this->numberingService->nextVoucherNumber($tenantId, $date, 'SRT'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $total,
                'total_debit' => $total,
                'total_credit' => $total,
                'method' => $paymentType,
                'document_type' => 'sales_return',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => Pishfactor::class,
                'source_id' => $pishfactor->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => 'سند برگشت فروش فاکتور شماره ' . $pishfactor->invoiceID,
                'created_by' => $voucher->created_by ?: $user?->id,
                'updated_by' => $user?->id,
            ]);
            $voucher->save();
            $voucher->items()->delete();

            foreach ($lines as $line) {
                $voucher->items()->create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $line['account_id'],
                    'amount' => max($line['debit_amount'], $line['credit_amount']),
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'method' => $line['method'],
                    'payment_terminal_id' => $line['payment_terminal_id'],
                    'cheque_photo' => $line['cheque_photo'],
                    'product_id' => $line['product_id'] ?? null,
                    'customer_id' => $line['customer_id'] ?? null,
                    'revenue_center_id' => $line['revenue_center_id'] ?? null,
                    'project_code' => $line['project_code'] ?? null,
                    'employee_id' => $line['employee_id'] ?? null,
                    'contract_code' => $line['contract_code'] ?? null,
                    'route_code' => $line['route_code'] ?? null,
                    'analytic_note' => $line['analytic_note'] ?? null,
                    'description' => $line['description'],
                ]);
            }

            return $voucher->load('items.account');
        });
    }

    public function postPishfactorSalesReturnCostVoucher(Pishfactor $pishfactor, $user = null): ?Voucher
    {
        $pishfactor = $pishfactor->fresh() ?: $pishfactor;

        if (!in_array((int) $pishfactor->status, [1, 4], true)) {
            $this->deleteDraftVoucher(Pishfactor::class, (int) $pishfactor->id, 'sales_return_cogs');
            return null;
        }

        $totalCost = $this->pishfactorReturnCostOfGoods($pishfactor);

        if ($totalCost <= 0) {
            $this->deleteDraftVoucher(Pishfactor::class, (int) $pishfactor->id, 'sales_return_cogs');
            return null;
        }

        $tenantId = $this->tenantIdFromSubject($pishfactor, $user);
        $organizationId = $this->organizationIdFromSubject($pishfactor, $user);
        $date = $this->postingDate($pishfactor);
        $this->ensureVoucherDateIsOpen($date, $tenantId);

        return DB::transaction(function () use ($pishfactor, $user, $totalCost, $tenantId, $organizationId, $date) {
            $lines = [];
            $dimensions = $this->pishfactorAnalyticDimensions($pishfactor);
            $inventoryAccountId = $this->systemAccountId('SYS-1301', 'موجودی کالا', $tenantId, $organizationId, $user);
            $cogsAccountId = $this->systemAccountId('SYS-5102', 'بهای تمام شده کالای فروش رفته', $tenantId, $organizationId, $user);

            foreach ($this->pishfactorReturnCostProductLines($pishfactor, $totalCost) as $productLine) {
                $lines[] = [
                    'account_id' => $inventoryAccountId,
                    'debit_amount' => $productLine['amount'],
                    'credit_amount' => 0,
                    'product_id' => $productLine['product_id'],
                    'customer_id' => $pishfactor->customer_id,
                    'revenue_center_id' => $dimensions['revenue_center_id'],
                    'project_code' => $dimensions['project_code'],
                    'employee_id' => $dimensions['employee_id'],
                    'contract_code' => $dimensions['contract_code'],
                    'route_code' => $dimensions['route_code'],
                    'analytic_note' => 'sales_return_inventory',
                    'description' => 'بازگشت موجودی بابت ' . $productLine['description'],
                ];
                $lines[] = [
                    'account_id' => $cogsAccountId,
                    'debit_amount' => 0,
                    'credit_amount' => $productLine['amount'],
                    'product_id' => $productLine['product_id'],
                    'customer_id' => $pishfactor->customer_id,
                    'revenue_center_id' => $dimensions['revenue_center_id'],
                    'project_code' => $dimensions['project_code'],
                    'employee_id' => $dimensions['employee_id'],
                    'contract_code' => $dimensions['contract_code'],
                    'route_code' => $dimensions['route_code'],
                    'analytic_note' => 'sales_return_cogs_product',
                    'description' => $productLine['description'],
                ];
            }

            $voucher = Voucher::where('source_type', Pishfactor::class)
                ->where('source_id', $pishfactor->id)
                ->where('document_type', 'sales_return_cogs')
                ->first() ?: new Voucher();

            if ($voucher->exists && $voucher->is_permanent) {
                return $voucher->load('items.account');
            }

            $voucher->fill([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => $pishfactor->id,
                'account_id' => $lines[0]['account_id'],
                'voucher_type' => 4,
                'voucher_number' => $voucher->voucher_number ?: $this->numberingService->nextVoucherNumber($tenantId, $date, 'SRC'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $totalCost,
                'total_debit' => $totalCost,
                'total_credit' => $totalCost,
                'method' => 0,
                'document_type' => 'sales_return_cogs',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => Pishfactor::class,
                'source_id' => $pishfactor->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => 'سند برگشت بهای تمام شده فروش فاکتور شماره ' . $pishfactor->invoiceID,
                'created_by' => $voucher->created_by ?: $user?->id,
                'updated_by' => $user?->id,
            ]);
            $voucher->save();
            $voucher->items()->delete();

            foreach ($lines as $line) {
                $voucher->items()->create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $line['account_id'],
                    'amount' => max($line['debit_amount'], $line['credit_amount']),
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'method' => $line['debit_amount'] > 0 ? 1 : 0,
                    'product_id' => $line['product_id'] ?? null,
                    'customer_id' => $line['customer_id'] ?? null,
                    'revenue_center_id' => $line['revenue_center_id'] ?? null,
                    'project_code' => $line['project_code'] ?? null,
                    'employee_id' => $line['employee_id'] ?? null,
                    'contract_code' => $line['contract_code'] ?? null,
                    'route_code' => $line['route_code'] ?? null,
                    'analytic_note' => $line['analytic_note'] ?? null,
                    'description' => $line['description'],
                ]);
            }

            return $voucher->load('items.account');
        });
    }

    public function postReceiptInventoryVoucher(Receipt $receipt, $user = null): ?Voucher
    {
        $receipt = $receipt->fresh(['depots.product']) ?: $receipt;

        if ((int) $receipt->type === 6 || ($receipt->document_status && $receipt->document_status !== 'approved')) {
            $this->deleteDraftVoucher(Receipt::class, (int) $receipt->id, 'inventory_receipt');
            return null;
        }

        $tenantId = $this->tenantIdFromSubject($receipt, $user);
        $organizationId = $this->organizationIdFromSubject($receipt, $user);
        $date = $this->receiptPostingDate($receipt);
        $this->ensureVoucherDateIsOpen($date, $tenantId);
        $inventoryDebit = 0.0;
        $inventoryCredit = 0.0;

        foreach ($receipt->depots as $depot) {
            $amount = $this->inventoryDepotAmount($depot);

            if ($amount <= 0) {
                continue;
            }

            if ((int) $depot->status === 0) {
                $inventoryCredit += $amount;
            } else {
                $inventoryDebit += $amount;
            }
        }

        $inventoryDebit = round($inventoryDebit, 2);
        $inventoryCredit = round($inventoryCredit, 2);
        $total = max($inventoryDebit, $inventoryCredit);

        if ($total <= 0) {
            $this->deleteDraftVoucher(Receipt::class, (int) $receipt->id, 'inventory_receipt');
            return null;
        }

        return DB::transaction(function () use ($receipt, $user, $tenantId, $organizationId, $date, $inventoryDebit, $inventoryCredit, $total) {
            $lines = [];
            $inventoryAccountId = $this->systemAccountId('SYS-1301', 'موجودی کالا', $tenantId, $organizationId, $user);

            if ($inventoryDebit > 0) {
                $lines[] = [
                    'account_id' => $inventoryAccountId,
                    'debit_amount' => $inventoryDebit,
                    'credit_amount' => 0,
                    'description' => 'افزایش موجودی رسید انبار شماره ' . ($receipt->number ?: $receipt->id),
                ];
                $lines[] = [
                    'account_id' => $this->inventoryOffsetAccountId($receipt, $tenantId, $organizationId, $user),
                    'debit_amount' => 0,
                    'credit_amount' => $inventoryDebit,
                    'description' => 'طرف حساب رسید انبار شماره ' . ($receipt->number ?: $receipt->id),
                ];
            }

            if ($inventoryCredit > 0) {
                $lines[] = [
                    'account_id' => $this->inventoryOffsetAccountId($receipt, $tenantId, $organizationId, $user),
                    'debit_amount' => $inventoryCredit,
                    'credit_amount' => 0,
                    'description' => 'طرف حساب خروج انبار شماره ' . ($receipt->number ?: $receipt->id),
                ];
                $lines[] = [
                    'account_id' => $inventoryAccountId,
                    'debit_amount' => 0,
                    'credit_amount' => $inventoryCredit,
                    'description' => 'کاهش موجودی سند انبار شماره ' . ($receipt->number ?: $receipt->id),
                ];
            }

            $voucher = Voucher::where('source_type', Receipt::class)
                ->where('source_id', $receipt->id)
                ->where('document_type', 'inventory_receipt')
                ->first() ?: new Voucher();

            if ($voucher->exists && $voucher->is_permanent) {
                return $voucher->load('items.account');
            }

            $voucher->fill([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => 0,
                'account_id' => $lines[0]['account_id'],
                'voucher_type' => 2,
                'voucher_number' => $voucher->voucher_number ?: $this->numberingService->nextVoucherNumber($tenantId, $date, 'INV'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $total,
                'total_debit' => $total,
                'total_credit' => $total,
                'method' => 0,
                'document_type' => 'inventory_receipt',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => Receipt::class,
                'source_id' => $receipt->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => 'سند حسابداری رسید انبار شماره ' . ($receipt->number ?: $receipt->id),
                'created_by' => $voucher->created_by ?: $user?->id,
                'updated_by' => $user?->id,
            ]);
            $voucher->save();

            $voucher->items()->delete();

            foreach ($lines as $line) {
                $voucher->items()->create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $line['account_id'],
                    'amount' => max($line['debit_amount'], $line['credit_amount']),
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'method' => $line['debit_amount'] > 0 ? 1 : 0,
                    'description' => $line['description'],
                ]);
            }

            return $voucher->load('items.account');
        });
    }

    public function removeReceiptInventoryVoucher(Receipt $receipt): void
    {
        $this->deleteDraftVoucher(Receipt::class, (int) $receipt->id, 'inventory_receipt');
    }

    public function hasPermanentReceiptInventoryVoucher(Receipt $receipt): bool
    {
        return Voucher::where('source_type', Receipt::class)
            ->where('source_id', $receipt->id)
            ->where('document_type', 'inventory_receipt')
            ->where('is_permanent', 1)
            ->exists();
    }

    public function postProductionVoucher(ProductionOrder $productionOrder, $user = null): ?Voucher
    {
        $productionOrder = $productionOrder->fresh(['items']) ?: $productionOrder;

        if ($productionOrder->status === 'canceled') {
            $this->deleteDraftVoucher(ProductionOrder::class, (int) $productionOrder->id, 'production_cost');
            return null;
        }

        $materialCost = round((float) $productionOrder->items->where('line_type', 'material')->sum('total_cost'), 2);
        $finishedCost = round((float) $productionOrder->items->where('line_type', 'finished_good')->sum('total_cost'), 2);
        $totalCost = $materialCost > 0 ? $materialCost : $finishedCost;

        if ($totalCost <= 0) {
            $this->deleteDraftVoucher(ProductionOrder::class, (int) $productionOrder->id, 'production_cost');
            return null;
        }

        $tenantId = $this->tenantIdFromSubject($productionOrder, $user);
        $organizationId = $this->organizationIdFromSubject($productionOrder, $user);
        $date = $productionOrder->date_en ? date('Y-m-d', strtotime((string) $productionOrder->date_en)) : now()->toDateString();
        $this->ensureVoucherDateIsOpen($date, $tenantId);

        return DB::transaction(function () use ($productionOrder, $user, $tenantId, $organizationId, $date, $totalCost) {
            $lines = [
                [
                    'account_id' => $this->systemAccountId('SYS-1302', 'موجودی کالای ساخته شده', $tenantId, $organizationId, $user),
                    'debit_amount' => $totalCost,
                    'credit_amount' => 0,
                    'description' => 'رسید محصول تولید شماره ' . $productionOrder->number,
                ],
                [
                    'account_id' => $this->systemAccountId('SYS-1303', 'موجودی مواد اولیه', $tenantId, $organizationId, $user),
                    'debit_amount' => 0,
                    'credit_amount' => $totalCost,
                    'description' => 'مصرف مواد اولیه تولید شماره ' . $productionOrder->number,
                ],
            ];

            $voucher = Voucher::where('source_type', ProductionOrder::class)
                ->where('source_id', $productionOrder->id)
                ->where('document_type', 'production_cost')
                ->first() ?: new Voucher();

            if ($voucher->exists && $voucher->is_permanent) {
                return $voucher->load('items.account');
            }

            $voucher->fill([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => 0,
                'account_id' => $lines[0]['account_id'],
                'voucher_type' => 5,
                'voucher_number' => $voucher->voucher_number ?: $this->numberingService->nextVoucherNumber($tenantId, $date, 'PRD'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $totalCost,
                'total_debit' => $totalCost,
                'total_credit' => $totalCost,
                'method' => 0,
                'document_type' => 'production_cost',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => ProductionOrder::class,
                'source_id' => $productionOrder->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => 'سند حسابداری تولید شماره ' . $productionOrder->number,
                'created_by' => $voucher->created_by ?: $user?->id,
                'updated_by' => $user?->id,
            ]);
            $voucher->save();

            $voucher->items()->delete();

            foreach ($lines as $line) {
                $voucher->items()->create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $line['account_id'],
                    'amount' => max($line['debit_amount'], $line['credit_amount']),
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'method' => $line['debit_amount'] > 0 ? 1 : 0,
                    'description' => $line['description'],
                ]);
            }

            return $voucher->load('items.account');
        });
    }

    public function removeProductionVoucher(ProductionOrder $productionOrder): void
    {
        $this->deleteDraftVoucher(ProductionOrder::class, (int) $productionOrder->id, 'production_cost');
    }

    public function postPayrollRunVoucher(PayrollRun $payrollRun, $user = null): ?Voucher
    {
        $payrollRun = $payrollRun->fresh(['items']) ?: $payrollRun;

        if ($payrollRun->status === 'canceled') {
            $this->deleteDraftVoucher(PayrollRun::class, (int) $payrollRun->id, 'payroll_accrual');
            return null;
        }

        $grossSalary = round((float) $payrollRun->gross_salary, 2);
        $employerInsurance = round((float) $payrollRun->employer_insurance_amount, 2);
        $employeeInsurance = round((float) $payrollRun->employee_insurance_amount, 2);
        $taxAmount = round((float) $payrollRun->tax_amount, 2);
        $otherDeductions = round((float) $payrollRun->other_deductions_amount, 2);
        $netPay = round((float) $payrollRun->net_pay_amount, 2);
        $totalDebit = round($grossSalary + $employerInsurance, 2);
        $insurancePayable = round($employeeInsurance + $employerInsurance, 2);
        $miscDeductionsPayable = max(0, round($totalDebit - $netPay - $taxAmount - $insurancePayable, 2));

        if ($totalDebit <= 0 || $netPay < 0) {
            $this->deleteDraftVoucher(PayrollRun::class, (int) $payrollRun->id, 'payroll_accrual');
            return null;
        }

        $tenantId = $this->tenantIdFromSubject($payrollRun, $user);
        $organizationId = $this->organizationIdFromSubject($payrollRun, $user);
        $date = $payrollRun->payroll_date_en ? date('Y-m-d', strtotime((string) $payrollRun->payroll_date_en)) : now()->toDateString();
        $this->ensureVoucherDateIsOpen($date, $tenantId);

        return DB::transaction(function () use ($payrollRun, $user, $tenantId, $organizationId, $date, $grossSalary, $employerInsurance, $taxAmount, $miscDeductionsPayable, $netPay, $totalDebit, $insurancePayable) {
            $lines = [
                [
                    'account_id' => $this->systemAccountId('SYS-5202', 'هزینه حقوق و دستمزد', $tenantId, $organizationId, $user),
                    'debit_amount' => $grossSalary,
                    'credit_amount' => 0,
                    'description' => 'هزینه حقوق دوره ' . $payrollRun->period_year . '/' . $payrollRun->period_month,
                ],
            ];

            if ($employerInsurance > 0) {
                $lines[] = [
                    'account_id' => $this->systemAccountId('SYS-5203', 'هزینه بیمه سهم کارفرما', $tenantId, $organizationId, $user),
                    'debit_amount' => $employerInsurance,
                    'credit_amount' => 0,
                    'description' => 'بیمه سهم کارفرما دوره ' . $payrollRun->period_year . '/' . $payrollRun->period_month,
                ];
            }

            if ($netPay > 0) {
                $lines[] = [
                    'account_id' => $this->systemAccountId('SYS-2204', 'حقوق پرداختنی کارکنان', $tenantId, $organizationId, $user),
                    'debit_amount' => 0,
                    'credit_amount' => $netPay,
                    'description' => 'خالص حقوق پرداختنی دوره ' . $payrollRun->period_year . '/' . $payrollRun->period_month,
                ];
            }

            if ($taxAmount > 0) {
                $lines[] = [
                    'account_id' => $this->systemAccountId('SYS-2402', 'مالیات حقوق پرداختنی', $tenantId, $organizationId, $user),
                    'debit_amount' => 0,
                    'credit_amount' => $taxAmount,
                    'description' => 'مالیات حقوق دوره ' . $payrollRun->period_year . '/' . $payrollRun->period_month,
                ];
            }

            if ($insurancePayable > 0) {
                $lines[] = [
                    'account_id' => $this->systemAccountId('SYS-2403', 'بیمه حقوق پرداختنی', $tenantId, $organizationId, $user),
                    'debit_amount' => 0,
                    'credit_amount' => $insurancePayable,
                    'description' => 'بیمه حقوق دوره ' . $payrollRun->period_year . '/' . $payrollRun->period_month,
                ];
            }

            if ($miscDeductionsPayable > 0) {
                $lines[] = [
                    'account_id' => $this->systemAccountId('SYS-2205', 'سایر کسورات حقوق پرداختنی', $tenantId, $organizationId, $user),
                    'debit_amount' => 0,
                    'credit_amount' => $miscDeductionsPayable,
                    'description' => 'سایر کسورات حقوق دوره ' . $payrollRun->period_year . '/' . $payrollRun->period_month,
                ];
            }

            $totalCredit = round(array_sum(array_column($lines, 'credit_amount')), 2);

            if (round($totalDebit, 2) !== $totalCredit) {
                throw ValidationException::withMessages([
                    'payroll' => 'سند حقوق تراز نیست و قابل ثبت نیست.',
                ]);
            }

            $voucher = Voucher::where('source_type', PayrollRun::class)
                ->where('source_id', $payrollRun->id)
                ->where('document_type', 'payroll_accrual')
                ->first() ?: new Voucher();

            if ($voucher->exists && $voucher->is_permanent) {
                return $voucher->load('items.account');
            }

            $voucher->fill([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => 0,
                'account_id' => $lines[0]['account_id'],
                'voucher_type' => 6,
                'voucher_number' => $voucher->voucher_number ?: $this->numberingService->nextVoucherNumber($tenantId, $date, 'PAY'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $totalDebit,
                'total_debit' => $totalDebit,
                'total_credit' => $totalDebit,
                'method' => 0,
                'document_type' => 'payroll_accrual',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => PayrollRun::class,
                'source_id' => $payrollRun->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => 'سند حسابداری حقوق دوره ' . $payrollRun->period_year . '/' . $payrollRun->period_month,
                'created_by' => $voucher->created_by ?: $user?->id,
                'updated_by' => $user?->id,
            ]);
            $voucher->save();

            $voucher->items()->delete();

            foreach ($lines as $line) {
                if ((float) $line['debit_amount'] <= 0 && (float) $line['credit_amount'] <= 0) {
                    continue;
                }

                $voucher->items()->create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $line['account_id'],
                    'amount' => max($line['debit_amount'], $line['credit_amount']),
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'method' => $line['debit_amount'] > 0 ? 1 : 0,
                    'description' => $line['description'],
                ]);
            }

            return $voucher->load('items.account');
        });
    }

    public function removePayrollRunVoucher(PayrollRun $payrollRun): void
    {
        $this->deleteDraftVoucher(PayrollRun::class, (int) $payrollRun->id, 'payroll_accrual');
    }

    public function postPayrollPaymentVoucher(PayrollRunPayment $payment, array $payload = [], $user = null): Voucher
    {
        $payment = $payment->fresh(['payrollRun']) ?: $payment;
        $amount = $this->money($payment->amount);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'مبلغ پرداخت حقوق معتبر نیست.',
            ]);
        }

        $tenantId = $this->tenantIdFromSubject($payment, $user);
        $organizationId = $this->organizationIdFromSubject($payment, $user);
        $date = $payment->payment_date_en ? date('Y-m-d', strtotime((string) $payment->payment_date_en)) : now()->toDateString();
        $this->ensureVoucherDateIsOpen($date, $tenantId);
        $paymentMethod = (int) ($payment->payment_method ?: Arr::get($payload, 'payment_method', 3));
        $payableAccountId = $this->systemAccountId('SYS-2204', 'حقوق پرداختنی کارکنان', $tenantId, $organizationId, $user);
        $treasuryAccountId = (int) ($payment->treasury_account_id ?: Arr::get($payload, 'treasury_account_id'))
            ?: $this->outgoingPaymentAccountId($paymentMethod, ['terminal_id' => $payment->payment_terminal_id], $tenantId, $organizationId, $user);
        $description = $payment->description ?: 'پرداخت حقوق دوره ' . $payment->payrollRun?->period_year . '/' . $payment->payrollRun?->period_month;

        return DB::transaction(function () use ($payment, $user, $tenantId, $organizationId, $date, $amount, $paymentMethod, $payableAccountId, $treasuryAccountId, $description) {
            $voucher = Voucher::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => 0,
                'account_id' => $payableAccountId,
                'voucher_type' => 6,
                'voucher_number' => $this->numberingService->nextVoucherNumber($tenantId, $date, 'PYP'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $amount,
                'total_debit' => $amount,
                'total_credit' => $amount,
                'method' => $paymentMethod,
                'document_type' => 'payroll_payment',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => PayrollRunPayment::class,
                'source_id' => $payment->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => $description,
                'created_by' => $user?->id,
            ]);

            foreach (
                [
                    [$payableAccountId, $amount, 0, null, null, null],
                    [$treasuryAccountId, 0, $amount, $payment->payment_terminal_id, $payment->issuing_bank, $payment->due_date],
                ] as $line
            ) {
                $voucher->items()->create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $line[0],
                    'amount' => max($line[1], $line[2]),
                    'debit_amount' => $line[1],
                    'credit_amount' => $line[2],
                    'method' => $paymentMethod,
                    'payment_terminal_id' => $line[3],
                    'issuing_bank' => $line[4],
                    'due_date' => $line[5],
                    'description' => $description,
                ]);
            }

            return $voucher->load('items.account');
        });
    }

    public function postPurchaseOrderPayableVoucher(PurchaseOrder $purchaseOrder, $user = null): ?Voucher
    {
        $purchaseOrder = $purchaseOrder->fresh(['items', 'supplier']) ?: $purchaseOrder;
        $total = $this->money($purchaseOrder->items->sum(function ($item) {
            return min((float) $item->quantity, (float) $item->received_quantity) * (float) $item->unit_price;
        }));

        if ($total <= 0 || !in_array($purchaseOrder->status, ['partial_received', 'received'], true)) {
            $this->deleteDraftVoucher(PurchaseOrder::class, (int) $purchaseOrder->id, 'purchase_payable');
            return null;
        }

        $tenantId = $this->tenantIdFromSubject($purchaseOrder, $user);
        $organizationId = $this->organizationIdFromSubject($purchaseOrder, $user);
        $date = optional($purchaseOrder->order_date_en)->toDateString() ?: now()->toDateString();
        $this->ensureVoucherDateIsOpen($date, $tenantId);
        $supplierName = $purchaseOrder->supplier?->title ?: $purchaseOrder->supplier?->name ?: 'تامین کننده';
        $lines = [
            [
                'account_id' => $this->systemAccountId('SYS-2201', 'حساب واسط رسید انبار', $tenantId, $organizationId, $user),
                'debit_amount' => $total,
                'credit_amount' => 0,
                'description' => 'تسویه حساب واسط رسید خرید شماره ' . $purchaseOrder->order_number,
            ],
            [
                'account_id' => $this->systemAccountId('SYS-2202', 'حساب های پرداختنی تامین کنندگان', $tenantId, $organizationId, $user),
                'debit_amount' => 0,
                'credit_amount' => $total,
                'description' => 'بدهی خرید به ' . $supplierName . ' بابت سفارش ' . $purchaseOrder->order_number,
            ],
        ];

        return DB::transaction(function () use ($purchaseOrder, $user, $tenantId, $organizationId, $date, $total, $lines) {
            $voucher = Voucher::where('source_type', PurchaseOrder::class)
                ->where('source_id', $purchaseOrder->id)
                ->where('document_type', 'purchase_payable')
                ->first() ?: new Voucher();

            if ($voucher->exists && $voucher->is_permanent) {
                return $voucher->load('items.account');
            }

            $voucher->fill([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => 0,
                'account_id' => $lines[0]['account_id'],
                'voucher_type' => 5,
                'voucher_number' => $voucher->voucher_number ?: $this->numberingService->nextVoucherNumber($tenantId, $date, 'PUR'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $total,
                'total_debit' => $total,
                'total_credit' => $total,
                'method' => 0,
                'document_type' => 'purchase_payable',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => PurchaseOrder::class,
                'source_id' => $purchaseOrder->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => 'سند بدهی خرید شماره ' . $purchaseOrder->order_number,
                'created_by' => $voucher->created_by ?: $user?->id,
                'updated_by' => $user?->id,
            ]);
            $voucher->save();

            $voucher->items()->delete();

            foreach ($lines as $line) {
                $voucher->items()->create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $line['account_id'],
                    'amount' => max($line['debit_amount'], $line['credit_amount']),
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'method' => $line['debit_amount'] > 0 ? 1 : 0,
                    'description' => $line['description'],
                ]);
            }

            return $voucher->load('items.account');
        });
    }

    public function removePurchaseOrderPayableVoucher(PurchaseOrder $purchaseOrder): void
    {
        $voucher = Voucher::where('source_type', PurchaseOrder::class)
            ->where('source_id', $purchaseOrder->id)
            ->where('document_type', 'purchase_payable')
            ->first();

        if ($voucher && $voucher->is_permanent) {
            throw ValidationException::withMessages([
                'invoice' => 'برای این سفارش سند بدهی خرید دائمی شده و تبدیل آن به فاکتور خرید از این مسیر مجاز نیست.',
            ]);
        }

        $this->deleteDraftVoucher(PurchaseOrder::class, (int) $purchaseOrder->id, 'purchase_payable');
    }

    public function postPurchaseInvoiceVoucher(PurchaseInvoice $invoice, $user = null): ?Voucher
    {
        $invoice = $invoice->fresh(['items', 'supplier', 'purchaseOrder']) ?: $invoice;

        if ($invoice->status === 'canceled') {
            $this->deleteDraftVoucher(PurchaseInvoice::class, (int) $invoice->id, 'purchase_invoice');
            return null;
        }

        $total = $this->money($invoice->total_amount);

        if ($total <= 0 || $invoice->items->isEmpty()) {
            $this->deleteDraftVoucher(PurchaseInvoice::class, (int) $invoice->id, 'purchase_invoice');
            return null;
        }

        $tenantId = $this->tenantIdFromSubject($invoice, $user);
        $organizationId = $this->organizationIdFromSubject($invoice, $user);
        $date = optional($invoice->invoice_date_en)->toDateString() ?: now()->toDateString();
        $this->ensureVoucherDateIsOpen($date, $tenantId);
        $supplierName = $invoice->supplier?->title ?: $invoice->supplier?->name ?: 'تامین کننده';
        $orderGoodsAmount = $this->money($invoice->items->sum(function ($item) {
            return (float) $item->quantity * (float) $item->order_unit_price;
        }));
        $taxAmount = $this->money($invoice->tax_amount);
        $priceVarianceAmount = $this->money($invoice->price_variance_amount);
        $lines = [];

        if ($orderGoodsAmount > 0) {
            $lines[] = [
                'account_id' => $this->systemAccountId('SYS-2201', 'حساب واسط رسید انبار', $tenantId, $organizationId, $user),
                'debit_amount' => $orderGoodsAmount,
                'credit_amount' => 0,
                'description' => 'تسویه واسط رسید خرید بر اساس فاکتور ' . $invoice->invoice_number,
            ];
        }

        if ($priceVarianceAmount > 0) {
            $lines[] = [
                'account_id' => $this->systemAccountId('SYS-5205', 'انحراف قیمت خرید', $tenantId, $organizationId, $user),
                'debit_amount' => $priceVarianceAmount,
                'credit_amount' => 0,
                'description' => 'انحراف افزایشی قیمت خرید فاکتور ' . $invoice->invoice_number,
            ];
        } elseif ($priceVarianceAmount < 0) {
            $lines[] = [
                'account_id' => $this->systemAccountId('SYS-4205', 'سود/کاهش انحراف قیمت خرید', $tenantId, $organizationId, $user),
                'debit_amount' => 0,
                'credit_amount' => abs($priceVarianceAmount),
                'description' => 'انحراف کاهشی قیمت خرید فاکتور ' . $invoice->invoice_number,
            ];
        }

        if ($taxAmount > 0) {
            $lines[] = [
                'account_id' => $this->systemAccountId('SYS-1601', 'مالیات ارزش افزوده خرید', $tenantId, $organizationId, $user),
                'debit_amount' => $taxAmount,
                'credit_amount' => 0,
                'description' => 'مالیات فاکتور خرید ' . $invoice->invoice_number,
            ];
        }

        $lines[] = [
            'account_id' => $this->systemAccountId('SYS-2202', 'حساب های پرداختنی تامین کنندگان', $tenantId, $organizationId, $user),
            'debit_amount' => 0,
            'credit_amount' => $total,
            'description' => 'بدهی خرید به ' . $supplierName . ' بابت فاکتور ' . $invoice->invoice_number,
        ];

        $debitTotal = round(array_sum(array_column($lines, 'debit_amount')), 2);
        $creditTotal = round(array_sum(array_column($lines, 'credit_amount')), 2);

        if ($debitTotal !== $creditTotal) {
            throw ValidationException::withMessages([
                'invoice' => 'سند فاکتور خرید تراز نیست و قابل ثبت نیست.',
            ]);
        }

        return DB::transaction(function () use ($invoice, $user, $tenantId, $organizationId, $date, $total, $lines) {
            $voucher = Voucher::where('source_type', PurchaseInvoice::class)
                ->where('source_id', $invoice->id)
                ->where('document_type', 'purchase_invoice')
                ->first() ?: new Voucher();

            if ($voucher->exists && $voucher->is_permanent) {
                return $voucher->load('items.account');
            }

            $voucher->fill([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => 0,
                'account_id' => $lines[0]['account_id'],
                'voucher_type' => 5,
                'voucher_number' => $voucher->voucher_number ?: $this->numberingService->nextVoucherNumber($tenantId, $date, 'PIN'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $total,
                'total_debit' => $total,
                'total_credit' => $total,
                'method' => 0,
                'document_type' => 'purchase_invoice',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => PurchaseInvoice::class,
                'source_id' => $invoice->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => 'سند فاکتور خرید ' . $invoice->invoice_number,
                'created_by' => $voucher->created_by ?: $user?->id,
                'updated_by' => $user?->id,
            ]);
            $voucher->save();

            $voucher->items()->delete();

            foreach ($lines as $line) {
                $voucher->items()->create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $line['account_id'],
                    'amount' => max($line['debit_amount'], $line['credit_amount']),
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'method' => $line['debit_amount'] > 0 ? 1 : 0,
                    'description' => $line['description'],
                ]);
            }

            return $voucher->load('items.account');
        });
    }

    public function postPurchaseServiceInvoiceVoucher(PurchaseServiceInvoice $invoice, $user = null): ?Voucher
    {
        $invoice = $invoice->fresh(['items', 'supplier', 'purchaseOrder']) ?: $invoice;

        if ($invoice->status === 'canceled') {
            $this->deleteDraftVoucher(PurchaseServiceInvoice::class, (int) $invoice->id, 'purchase_service_invoice');
            return null;
        }

        $total = $this->money($invoice->total_amount);

        if ($total <= 0 || $invoice->items->isEmpty()) {
            $this->deleteDraftVoucher(PurchaseServiceInvoice::class, (int) $invoice->id, 'purchase_service_invoice');
            return null;
        }

        $tenantId = $this->tenantIdFromSubject($invoice, $user);
        $organizationId = $this->organizationIdFromSubject($invoice, $user);
        $date = optional($invoice->invoice_date_en)->toDateString() ?: now()->toDateString();
        $this->ensureVoucherDateIsOpen($date, $tenantId);
        $supplierName = $invoice->supplier?->title ?: $invoice->supplier?->name ?: 'تامین کننده';
        $debitLines = [];
        $taxTotal = 0;

        foreach ($invoice->items as $item) {
            $amount = $this->money($item->amount);
            $taxAmount = $this->money($item->tax_amount);

            if ($amount <= 0 && $taxAmount <= 0) {
                continue;
            }

            $accountId = (int) $item->expense_account_id;

            if (!$accountId) {
                $accountId = $item->allocation_type === 'landed_cost'
                    ? $this->systemAccountId('SYS-1304', 'هزینه های جانبی خرید در جریان', $tenantId, $organizationId, $user)
                    : $this->systemAccountId('SYS-5204', 'هزینه خدمات و خرید غیرکالایی', $tenantId, $organizationId, $user);
            }

            if (!isset($debitLines[$accountId])) {
                $debitLines[$accountId] = [
                    'account_id' => $accountId,
                    'debit_amount' => 0,
                    'credit_amount' => 0,
                    'description' => 'اقلام فاکتور خدمات خرید ' . $invoice->invoice_number,
                ];
            }

            $debitLines[$accountId]['debit_amount'] = round($debitLines[$accountId]['debit_amount'] + $amount, 2);
            $taxTotal = round($taxTotal + $taxAmount, 2);
        }

        $lines = array_values($debitLines);

        if ($taxTotal > 0) {
            $lines[] = [
                'account_id' => $this->systemAccountId('SYS-1601', 'مالیات ارزش افزوده خرید', $tenantId, $organizationId, $user),
                'debit_amount' => $taxTotal,
                'credit_amount' => 0,
                'description' => 'مالیات فاکتور خدمات خرید ' . $invoice->invoice_number,
            ];
        }

        $debitTotal = round(array_sum(array_column($lines, 'debit_amount')), 2);

        if ($debitTotal !== $total) {
            throw ValidationException::withMessages([
                'items' => 'جمع اقلام فاکتور خدمات خرید با جمع کل فاکتور برابر نیست.',
            ]);
        }

        $lines[] = [
            'account_id' => $this->systemAccountId('SYS-2202', 'حساب های پرداختنی تامین کنندگان', $tenantId, $organizationId, $user),
            'debit_amount' => 0,
            'credit_amount' => $total,
            'description' => 'بدهی خدمات/هزینه جانبی خرید به ' . $supplierName . ' بابت فاکتور ' . $invoice->invoice_number,
        ];

        return DB::transaction(function () use ($invoice, $user, $tenantId, $organizationId, $date, $total, $lines) {
            $voucher = Voucher::where('source_type', PurchaseServiceInvoice::class)
                ->where('source_id', $invoice->id)
                ->where('document_type', 'purchase_service_invoice')
                ->first() ?: new Voucher();

            if ($voucher->exists && $voucher->is_permanent) {
                return $voucher->load('items.account');
            }

            $voucher->fill([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => 0,
                'account_id' => $lines[0]['account_id'],
                'voucher_type' => 5,
                'voucher_number' => $voucher->voucher_number ?: $this->numberingService->nextVoucherNumber($tenantId, $date, 'PSI'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $total,
                'total_debit' => $total,
                'total_credit' => $total,
                'method' => 0,
                'document_type' => 'purchase_service_invoice',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => PurchaseServiceInvoice::class,
                'source_id' => $invoice->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => 'سند فاکتور خدمات و هزینه جانبی خرید ' . $invoice->invoice_number,
                'created_by' => $voucher->created_by ?: $user?->id,
                'updated_by' => $user?->id,
            ]);
            $voucher->save();

            $voucher->items()->delete();

            foreach ($lines as $line) {
                $voucher->items()->create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $line['account_id'],
                    'amount' => max($line['debit_amount'], $line['credit_amount']),
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'method' => $line['debit_amount'] > 0 ? 1 : 0,
                    'description' => $line['description'],
                ]);
            }

            return $voucher->load('items.account');
        });
    }

    public function removePurchaseServiceInvoiceVoucher(PurchaseServiceInvoice $invoice): void
    {
        $this->deleteDraftVoucher(PurchaseServiceInvoice::class, (int) $invoice->id, 'purchase_service_invoice');
    }

    public function createTreasuryVoucher(array $payload, $user): Voucher
    {
        $transactionType = Arr::get($payload, 'transaction_type', 'receipt');
        $amount = $this->money(Arr::get($payload, 'amount'));
        $paymentMethod = (int) Arr::get($payload, 'payment_method', 1);
        $counterAccountId = (int) Arr::get($payload, 'counter_account_id');

        if (!in_array($transactionType, ['receipt', 'payment'], true)) {
            throw ValidationException::withMessages([
                'transaction_type' => 'نوع عملیات خزانه نامعتبر است.',
            ]);
        }

        if ($amount <= 0 || $counterAccountId <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'برای ثبت دریافت یا پرداخت، مبلغ و طرف حساب الزامی است.',
            ]);
        }

        $tenantId = $this->tenantId($user);
        $organizationId = $this->organizationId($user);
        $date = Arr::get($payload, 'voucher_date_en') ?: now()->toDateString();
        $this->ensureVoucherDateIsOpen($date, $tenantId);
        $payment = [
            'payment_type' => $paymentMethod,
            'terminal_id' => Arr::get($payload, 'payment_terminal_id'),
        ];
        $treasuryAccountId = (int) Arr::get($payload, 'treasury_account_id')
            ?: $this->paymentAccountId($paymentMethod, $payment, $tenantId, $organizationId, $user);
        $selectedChequeLeaf = null;
        $chequeNumber = Arr::get($payload, 'cheque_number');
        $issuingBank = Arr::get($payload, 'issuing_bank');

        if ($paymentMethod === 2 && $transactionType === 'payment' && Arr::get($payload, 'cheque_leaf_id')) {
            $selectedChequeLeaf = TreasuryChequeLeaf::with(['book.account'])->findOrFail((int) Arr::get($payload, 'cheque_leaf_id'));

            if ($selectedChequeLeaf->status !== 'available') {
                throw ValidationException::withMessages(['cheque_leaf_id' => 'برگ چک انتخاب شده قبلا مصرف شده یا مسدود است.']);
            }

            if ((int) $selectedChequeLeaf->account_id !== (int) $treasuryAccountId) {
                throw ValidationException::withMessages(['cheque_leaf_id' => 'برگ چک با حساب خزانه انتخاب شده همخوانی ندارد.']);
            }

            $this->ensureModelTenant($selectedChequeLeaf, $user, 'برگ چک');
            $chequeNumber = $selectedChequeLeaf->leaf_number;
            $issuingBank = $issuingBank ?: $selectedChequeLeaf->book?->bank_name;
        }

        $documentType = $transactionType === 'receipt' ? 'treasury_receipt' : 'treasury_payment';
        $prefix = $transactionType === 'receipt' ? 'TRR' : 'TRP';
        $description = Arr::get($payload, 'description') ?: ($transactionType === 'receipt' ? 'دریافت خزانه' : 'پرداخت خزانه');

        $lines = $transactionType === 'receipt'
            ? [
                [
                    'account_id' => $treasuryAccountId,
                    'debit_amount' => $amount,
                    'credit_amount' => 0,
                    'description' => $description,
                    'payment_terminal_id' => $paymentMethod === 4 ? Arr::get($payload, 'payment_terminal_id') : null,
                    'issuing_bank' => $issuingBank,
                    'due_date' => Arr::get($payload, 'due_date'),
                ],
                [
                    'account_id' => $counterAccountId,
                    'debit_amount' => 0,
                    'credit_amount' => $amount,
                    'description' => $description,
                    'payment_terminal_id' => null,
                    'issuing_bank' => null,
                    'due_date' => null,
                ],
            ]
            : [
                [
                    'account_id' => $counterAccountId,
                    'debit_amount' => $amount,
                    'credit_amount' => 0,
                    'description' => $description,
                    'payment_terminal_id' => null,
                    'issuing_bank' => null,
                    'due_date' => null,
                ],
                [
                    'account_id' => $treasuryAccountId,
                    'debit_amount' => 0,
                    'credit_amount' => $amount,
                    'description' => $description,
                    'payment_terminal_id' => $paymentMethod === 4 ? Arr::get($payload, 'payment_terminal_id') : null,
                    'issuing_bank' => $issuingBank,
                    'due_date' => Arr::get($payload, 'due_date'),
                ],
            ];

        return DB::transaction(function () use ($payload, $user, $tenantId, $organizationId, $date, $amount, $paymentMethod, $counterAccountId, $transactionType, $documentType, $prefix, $description, $lines, $selectedChequeLeaf, $chequeNumber, $issuingBank, $treasuryAccountId) {
            $voucher = Voucher::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => 0,
                'account_id' => $lines[0]['account_id'],
                'voucher_type' => 3,
                'voucher_number' => Arr::get($payload, 'voucher_number') ?: $this->numberingService->nextVoucherNumber($tenantId, $date, $prefix),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $amount,
                'total_debit' => $amount,
                'total_credit' => $amount,
                'method' => $paymentMethod,
                'document_type' => $documentType,
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => 'treasury',
                'source_id' => null,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => $description,
                'created_by' => $user?->id,
            ]);

            $treasuryVoucherItem = null;

            foreach ($lines as $line) {
                $voucherItem = $voucher->items()->create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $line['account_id'],
                    'amount' => max($line['debit_amount'], $line['credit_amount']),
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'method' => $paymentMethod,
                    'payment_terminal_id' => $line['payment_terminal_id'],
                    'issuing_bank' => $line['issuing_bank'],
                    'due_date' => $line['due_date'],
                    'description' => $line['description'],
                ]);

                if ($paymentMethod === 2 && (int) $line['account_id'] === (int) $treasuryAccountId) {
                    $treasuryVoucherItem = $voucherItem;
                }
            }

            if ($paymentMethod === 2) {
                $instrument = TreasuryInstrument::create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'voucher_id' => $voucher->id,
                    'voucher_item_id' => $treasuryVoucherItem?->id,
                    'treasury_cheque_book_id' => $selectedChequeLeaf?->treasury_cheque_book_id,
                    'treasury_cheque_leaf_id' => $selectedChequeLeaf?->id,
                    'counter_account_id' => $counterAccountId,
                    'current_holder_account_id' => $transactionType === 'receipt' ? $treasuryVoucherItem?->account_id : $counterAccountId,
                    'instrument_type' => 'cheque',
                    'direction' => $transactionType === 'receipt' ? 'incoming' : 'outgoing',
                    'status' => $transactionType === 'receipt' ? 'received' : 'issued',
                    'current_holder_name' => $transactionType === 'receipt' ? 'واحد خزانه' : null,
                    'amount' => $amount,
                    'issuing_bank' => $issuingBank,
                    'cheque_number' => $chequeNumber ?: $voucher->voucher_number,
                    'due_date' => Arr::get($payload, 'due_date'),
                    'status_date' => $date,
                    'last_status_note' => $description,
                    'last_status_changed_at' => now(),
                    'description' => $description,
                    'created_by' => $user?->id,
                ]);

                if ($selectedChequeLeaf) {
                    app(TreasuryChequeBookService::class)->issueLeaf($selectedChequeLeaf->fresh(['book']) ?: $selectedChequeLeaf, $instrument->load(['voucherItem', 'counterAccount']), $user);
                }

                TreasuryInstrumentHistory::create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'treasury_instrument_id' => $instrument->id,
                    'previous_status' => null,
                    'new_status' => $instrument->status,
                    'action_date' => $date,
                    'amount' => $amount,
                    'holder_account_id' => $instrument->current_holder_account_id,
                    'holder_name' => $instrument->current_holder_name,
                    'voucher_id' => $voucher->id,
                    'note' => $description,
                    'created_by' => $user?->id,
                ]);
            }

            return $voucher->load(['items.account', 'treasuryInstruments.counterAccount']);
        });
    }

    public function postPurchaseReturnVoucher(PurchaseReturn $purchaseReturn, $user = null): ?Voucher
    {
        $purchaseReturn = $purchaseReturn->fresh(['items', 'purchaseOrder']) ?: $purchaseReturn;
        $total = $this->money($purchaseReturn->items->sum('total_amount')) ?: $this->money($purchaseReturn->total_amount);

        if ($total <= 0 || $purchaseReturn->status !== 'approved') {
            $this->deleteDraftVoucher(PurchaseReturn::class, (int) $purchaseReturn->id, 'purchase_return');
            return null;
        }

        $tenantId = $this->tenantIdFromSubject($purchaseReturn, $user);
        $organizationId = $this->organizationIdFromSubject($purchaseReturn, $user);
        $date = optional($purchaseReturn->return_date_en)->toDateString() ?: now()->toDateString();
        $this->ensureVoucherDateIsOpen($date, $tenantId);
        $settlementAccountId = $this->purchaseReturnSettlementAccountId($purchaseReturn, $tenantId, $organizationId, $user);
        $lines = [
            [
                'account_id' => $settlementAccountId,
                'debit_amount' => $total,
                'credit_amount' => 0,
                'description' => 'کاهش بدهی/طلب تامین کننده بابت مرجوعی خرید ' . $purchaseReturn->return_number,
            ],
            [
                'account_id' => $this->systemAccountId('SYS-1301', 'موجودی کالا', $tenantId, $organizationId, $user),
                'debit_amount' => 0,
                'credit_amount' => $total,
                'description' => 'خروج موجودی بابت مرجوعی خرید ' . $purchaseReturn->return_number,
            ],
        ];

        return DB::transaction(function () use ($purchaseReturn, $user, $tenantId, $organizationId, $date, $total, $lines) {
            $voucher = Voucher::where('source_type', PurchaseReturn::class)
                ->where('source_id', $purchaseReturn->id)
                ->where('document_type', 'purchase_return')
                ->first() ?: new Voucher();

            if ($voucher->exists && $voucher->is_permanent) {
                return $voucher->load('items.account');
            }

            $voucher->fill([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => 0,
                'account_id' => $lines[0]['account_id'],
                'voucher_type' => 7,
                'voucher_number' => $voucher->voucher_number ?: $this->numberingService->nextVoucherNumber($tenantId, $date, 'PRT'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $total,
                'total_debit' => $total,
                'total_credit' => $total,
                'method' => 0,
                'document_type' => 'purchase_return',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => PurchaseReturn::class,
                'source_id' => $purchaseReturn->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => 'سند مرجوعی خرید شماره ' . $purchaseReturn->return_number,
                'created_by' => $voucher->created_by ?: $user?->id,
                'updated_by' => $user?->id,
            ]);
            $voucher->save();

            $voucher->items()->delete();

            foreach ($lines as $line) {
                $voucher->items()->create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $line['account_id'],
                    'amount' => max($line['debit_amount'], $line['credit_amount']),
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'method' => $line['debit_amount'] > 0 ? 1 : 0,
                    'description' => $line['description'],
                ]);
            }

            return $voucher->load('items.account');
        });
    }

    public function createPurchaseSupplierPaymentVoucher(PurchaseOrder $purchaseOrder, array $payload, $user): Voucher
    {
        $purchaseOrder = $purchaseOrder->fresh(['supplier']) ?: $purchaseOrder;
        $amount = $this->money(Arr::get($payload, 'amount'));

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'مبلغ پرداخت تامین کننده معتبر نیست.',
            ]);
        }

        $tenantId = $this->tenantIdFromSubject($purchaseOrder, $user);
        $organizationId = $this->organizationIdFromSubject($purchaseOrder, $user);
        $date = Arr::get($payload, 'payment_date_en') ?: now()->toDateString();
        $this->ensureVoucherDateIsOpen($date, $tenantId);
        $paymentMethod = (int) Arr::get($payload, 'payment_method', 3);
        $payment = ['terminal_id' => Arr::get($payload, 'payment_terminal_id')];
        $payableAccountId = $this->systemAccountId('SYS-2202', 'حساب های پرداختنی تامین کنندگان', $tenantId, $organizationId, $user);
        $treasuryAccountId = (int) Arr::get($payload, 'treasury_account_id')
            ?: $this->outgoingPaymentAccountId($paymentMethod, $payment, $tenantId, $organizationId, $user);
        $supplierName = $purchaseOrder->supplier?->title ?: $purchaseOrder->supplier?->name ?: 'تامین کننده';
        $description = Arr::get($payload, 'description') ?: 'پرداخت به ' . $supplierName . ' بابت سفارش خرید ' . $purchaseOrder->order_number;
        $lines = [
            [
                'account_id' => $payableAccountId,
                'debit_amount' => $amount,
                'credit_amount' => 0,
                'description' => $description,
                'payment_terminal_id' => null,
                'issuing_bank' => null,
                'due_date' => null,
            ],
            [
                'account_id' => $treasuryAccountId,
                'debit_amount' => 0,
                'credit_amount' => $amount,
                'description' => $description,
                'payment_terminal_id' => $paymentMethod === 4 ? Arr::get($payload, 'payment_terminal_id') : null,
                'issuing_bank' => Arr::get($payload, 'issuing_bank'),
                'due_date' => Arr::get($payload, 'due_date'),
            ],
        ];

        return DB::transaction(function () use ($payload, $purchaseOrder, $user, $tenantId, $organizationId, $date, $amount, $paymentMethod, $payableAccountId, $description, $lines) {
            $voucher = Voucher::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => 0,
                'account_id' => $payableAccountId,
                'voucher_type' => 6,
                'voucher_number' => Arr::get($payload, 'voucher_number') ?: $this->numberingService->nextVoucherNumber($tenantId, $date, 'PUP'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $amount,
                'total_debit' => $amount,
                'total_credit' => $amount,
                'method' => $paymentMethod,
                'document_type' => 'purchase_supplier_payment',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => PurchaseOrder::class,
                'source_id' => $purchaseOrder->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => $description,
                'created_by' => $user?->id,
            ]);

            $treasuryVoucherItem = null;

            foreach ($lines as $line) {
                $voucherItem = $voucher->items()->create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'account_id' => $line['account_id'],
                    'amount' => max($line['debit_amount'], $line['credit_amount']),
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'method' => $paymentMethod,
                    'payment_terminal_id' => $line['payment_terminal_id'],
                    'issuing_bank' => $line['issuing_bank'],
                    'due_date' => $line['due_date'],
                    'description' => $line['description'],
                ]);

                if ($line['credit_amount'] > 0) {
                    $treasuryVoucherItem = $voucherItem;
                }
            }

            if ($paymentMethod === 2) {
                TreasuryInstrument::create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'voucher_id' => $voucher->id,
                    'voucher_item_id' => $treasuryVoucherItem?->id,
                    'counter_account_id' => $payableAccountId,
                    'instrument_type' => 'cheque',
                    'direction' => 'outgoing',
                    'status' => 'issued',
                    'amount' => $amount,
                    'issuing_bank' => Arr::get($payload, 'issuing_bank'),
                    'cheque_number' => Arr::get($payload, 'cheque_number') ?: $voucher->voucher_number,
                    'due_date' => Arr::get($payload, 'due_date'),
                    'status_date' => $date,
                    'description' => $description,
                    'created_by' => $user?->id,
                ]);
            }

            return $voucher->load(['items.account', 'treasuryInstruments.counterAccount']);
        });
    }

    public function updateTreasuryInstrumentStatus(TreasuryInstrument $instrument, string $status, $user = null, ?int $settlementAccountId = null, array $context = []): TreasuryInstrument
    {
        $allowedStatuses = [
            'received',
            'issued',
            'deposited',
            'collected',
            'spent',
            'returned',
            'endorsed',
            'refunded',
            'replaced',
            'canceled',
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => 'وضعیت انتخاب شده برای چک نامعتبر است.',
            ]);
        }

        DB::transaction(function () use ($instrument, $status, $user, $settlementAccountId, $context) {
            $previousStatus = $instrument->status;
            $actionDate = Arr::get($context, 'status_date') ?: now()->toDateString();
            $holderAccountId = (int) (Arr::get($context, 'current_holder_account_id') ?: Arr::get($context, 'holder_account_id')) ?: null;
            $holderName = Arr::get($context, 'current_holder_name') ?: Arr::get($context, 'holder_name');
            $note = Arr::get($context, 'status_note') ?: Arr::get($context, 'note');
            $settlementAccountId = $settlementAccountId ?: ((int) Arr::get($context, 'settlement_account_id') ?: null);

            $instrument->update([
                'status' => $status,
                'status_date' => $actionDate,
                'current_holder_account_id' => $holderAccountId ?: $instrument->current_holder_account_id,
                'current_holder_name' => $holderName ?: $instrument->current_holder_name,
                'last_status_note' => $note,
                'last_status_changed_at' => now(),
                'updated_by' => $user?->id,
            ]);

            $voucher = $this->postTreasuryInstrumentStatusVoucher($instrument->fresh(['voucherItem', 'counterAccount']) ?: $instrument, $status, $user, $settlementAccountId);

            TreasuryInstrumentHistory::create([
                'tenant_id' => $instrument->tenant_id,
                'organization_id' => $instrument->organization_id,
                'treasury_instrument_id' => $instrument->id,
                'previous_status' => $previousStatus,
                'new_status' => $status,
                'action_date' => $actionDate,
                'amount' => $instrument->amount,
                'settlement_account_id' => $settlementAccountId,
                'holder_account_id' => $holderAccountId ?: $instrument->current_holder_account_id,
                'holder_name' => $holderName ?: $instrument->current_holder_name,
                'voucher_id' => $voucher?->id,
                'note' => $note,
                'created_by' => $user?->id,
            ]);
        });

        return $instrument->fresh(['voucher', 'counterAccount', 'currentHolderAccount', 'histories']) ?: $instrument;
    }

    private function postTreasuryInstrumentStatusVoucher(TreasuryInstrument $instrument, string $status, $user = null, ?int $settlementAccountId = null): ?Voucher
    {
        $amount = $this->money($instrument->amount);

        if ($amount <= 0 || !in_array($status, ['collected', 'spent', 'returned'], true)) {
            $this->deleteDraftTreasuryInstrumentStatusVouchers($instrument);
            return null;
        }

        $tenantId = $this->tenantIdFromSubject($instrument, $user);
        $organizationId = $this->organizationIdFromSubject($instrument, $user);
        $date = now()->toDateString();
        $this->ensureVoucherDateIsOpen($date, $tenantId);
        $documentType = 'treasury_cheque_' . $status;
        $holdingAccountId = (int) ($instrument->voucherItem?->account_id ?: $this->systemAccountId(
            $instrument->direction === 'outgoing' ? 'SYS-2102' : 'SYS-1102',
            $instrument->direction === 'outgoing' ? 'اسناد پرداختنی' : 'اسناد دریافتنی',
            $tenantId,
            $organizationId,
            $user
        ));
        $settlementAccountId = $settlementAccountId ?: $this->systemAccountId('SYS-1103', 'بانک و کارت به کارت', $tenantId, $organizationId, $user);
        $counterAccountId = (int) ($instrument->counter_account_id ?: $this->systemAccountId('SYS-1201', 'حساب های دریافتنی تجاری', $tenantId, $organizationId, $user));
        $chequeTitle = 'چک شماره ' . ($instrument->cheque_number ?: $instrument->id);

        if ($instrument->direction === 'incoming' && $status === 'collected') {
            $lines = [
                ['account_id' => $settlementAccountId, 'debit_amount' => $amount, 'credit_amount' => 0, 'description' => 'وصول ' . $chequeTitle],
                ['account_id' => $holdingAccountId, 'debit_amount' => 0, 'credit_amount' => $amount, 'description' => 'خروج از اسناد دریافتنی بابت وصول ' . $chequeTitle],
            ];
        } elseif ($instrument->direction === 'incoming' && $status === 'returned') {
            $lines = [
                ['account_id' => $counterAccountId, 'debit_amount' => $amount, 'credit_amount' => 0, 'description' => 'برگشت ' . $chequeTitle . ' به طرف حساب'],
                ['account_id' => $holdingAccountId, 'debit_amount' => 0, 'credit_amount' => $amount, 'description' => 'خروج از اسناد دریافتنی بابت برگشت ' . $chequeTitle],
            ];
        } elseif ($instrument->direction === 'incoming' && $status === 'spent') {
            $lines = [
                ['account_id' => $counterAccountId, 'debit_amount' => $amount, 'credit_amount' => 0, 'description' => 'خرج کردن ' . $chequeTitle],
                ['account_id' => $holdingAccountId, 'debit_amount' => 0, 'credit_amount' => $amount, 'description' => 'خروج از اسناد دریافتنی بابت خرج کردن ' . $chequeTitle],
            ];
        } elseif ($instrument->direction === 'outgoing' && in_array($status, ['collected', 'spent'], true)) {
            $lines = [
                ['account_id' => $holdingAccountId, 'debit_amount' => $amount, 'credit_amount' => 0, 'description' => 'تسویه اسناد پرداختنی بابت ' . $chequeTitle],
                ['account_id' => $settlementAccountId, 'debit_amount' => 0, 'credit_amount' => $amount, 'description' => 'برداشت بانک بابت ' . $chequeTitle],
            ];
        } else {
            $this->deleteDraftTreasuryInstrumentStatusVouchers($instrument);
            return null;
        }

        $existingPermanentVoucher = Voucher::where('source_type', TreasuryInstrument::class)
            ->where('source_id', $instrument->id)
            ->where('document_type', $documentType)
            ->where('is_permanent', 1)
            ->first();

        if ($existingPermanentVoucher) {
            return $existingPermanentVoucher->load('items.account');
        }

        $this->deleteDraftTreasuryInstrumentStatusVouchers($instrument);

        return DB::transaction(function () use ($instrument, $user, $tenantId, $organizationId, $date, $amount, $documentType, $status, $lines, $chequeTitle) {
            $voucher = Voucher::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => 0,
                'account_id' => $lines[0]['account_id'],
                'voucher_type' => 3,
                'voucher_number' => $this->numberingService->nextVoucherNumber($tenantId, $date, 'CHK'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $amount,
                'total_debit' => $amount,
                'total_credit' => $amount,
                'method' => 2,
                'document_type' => $documentType,
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => TreasuryInstrument::class,
                'source_id' => $instrument->id,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => 'سند وضعیت ' . $status . ' برای ' . $chequeTitle,
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
                    'method' => 2,
                    'description' => $line['description'],
                ]);
            }

            return $voucher->load('items.account');
        });
    }

    private function deleteDraftTreasuryInstrumentStatusVouchers(TreasuryInstrument $instrument): void
    {
        Voucher::where('source_type', TreasuryInstrument::class)
            ->where('source_id', $instrument->id)
            ->where('document_type', 'like', 'treasury_cheque_%')
            ->where(function ($query) {
                $query->where('is_permanent', 0)->orWhereNull('is_permanent');
            })
            ->get()
            ->each(function (Voucher $voucher) {
                $voucher->items()->delete();
                $voucher->delete();
            });
    }

    public function createTreasuryTransferVoucher(array $payload, $user): Voucher
    {
        $amount = $this->money(Arr::get($payload, 'amount'));
        $fromAccountId = (int) Arr::get($payload, 'from_account_id');
        $toAccountId = (int) Arr::get($payload, 'to_account_id');

        if ($amount <= 0 || $fromAccountId <= 0 || $toAccountId <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'برای انتقال خزانه، مبلغ، حساب مبدا و حساب مقصد الزامی است.',
            ]);
        }

        if ($fromAccountId === $toAccountId) {
            throw ValidationException::withMessages([
                'to_account_id' => 'حساب مبدا و مقصد انتقال نباید یکسان باشد.',
            ]);
        }

        $tenantId = $this->tenantId($user);
        $organizationId = $this->organizationId($user);
        $date = Arr::get($payload, 'voucher_date_en') ?: now()->toDateString();
        $this->ensureVoucherDateIsOpen($date, $tenantId);
        $description = Arr::get($payload, 'description') ?: 'انتقال بین حساب های خزانه';
        $lines = [
            [
                'account_id' => $toAccountId,
                'debit_amount' => $amount,
                'credit_amount' => 0,
                'description' => $description,
            ],
            [
                'account_id' => $fromAccountId,
                'debit_amount' => 0,
                'credit_amount' => $amount,
                'description' => $description,
            ],
        ];

        return DB::transaction(function () use ($payload, $user, $tenantId, $organizationId, $date, $amount, $description, $lines) {
            $voucher = Voucher::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'factor_id' => 0,
                'account_id' => $lines[0]['account_id'],
                'voucher_type' => 3,
                'voucher_number' => Arr::get($payload, 'voucher_number') ?: $this->numberingService->nextVoucherNumber($tenantId, $date, 'TRF'),
                'voucher_date_fa' => $this->jalaliDate($date),
                'voucher_date_en' => $date,
                'amount' => $amount,
                'total_debit' => $amount,
                'total_credit' => $amount,
                'method' => 0,
                'document_type' => 'treasury_transfer',
                'status' => 'draft',
                'is_permanent' => false,
                'source_type' => 'treasury',
                'source_id' => null,
                'fiscal_year' => $this->jalaliYear($date),
                'description' => $description,
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
                    'method' => 0,
                    'description' => $line['description'],
                ]);
            }

            return $voucher->load('items.account');
        });
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
        $contractCodes = Arr::get($payload, 'contract_code', []);
        $routeCodes = Arr::get($payload, 'route_code', []);
        $analyticNotes = Arr::get($payload, 'analytic_note', []);
        $currencyIds = Arr::get($payload, 'currency_id', []);
        $foreignDebits = Arr::get($payload, 'foreign_debit_amount', []);
        $foreignCredits = Arr::get($payload, 'foreign_credit_amount', []);
        $exchangeRates = Arr::get($payload, 'exchange_rate', []);
        $lines = [];

        foreach ($accountIds as $index => $accountId) {
            $debit = $this->money($debits[$index] ?? 0);
            $credit = $this->money($credits[$index] ?? 0);
            $currencyId = !empty($currencyIds[$index]) ? (int) $currencyIds[$index] : null;
            $foreignDebit = $this->foreignAmount($foreignDebits[$index] ?? 0);
            $foreignCredit = $this->foreignAmount($foreignCredits[$index] ?? 0);
            $exchangeRate = $this->exchangeRate($exchangeRates[$index] ?? 0);

            if ($currencyId && $exchangeRate > 0 && $debit <= 0 && $foreignDebit > 0) {
                $debit = $this->money($foreignDebit * $exchangeRate);
            }

            if ($currencyId && $exchangeRate > 0 && $credit <= 0 && $foreignCredit > 0) {
                $credit = $this->money($foreignCredit * $exchangeRate);
            }

            if (!$accountId || ($debit <= 0 && $credit <= 0)) {
                continue;
            }

            if ($debit > 0 && $credit > 0) {
                throw ValidationException::withMessages([
                    'items' => 'در هر ردیف فقط یکی از مبلغ بدهکار یا بستانکار باید پر شود.',
                ]);
            }

            if ($foreignDebit > 0 && $foreignCredit > 0) {
                throw ValidationException::withMessages([
                    'items' => 'در هر ردیف ارزی فقط یکی از مبلغ ارزی بدهکار یا بستانکار باید پر شود.',
                ]);
            }

            if (($foreignDebit > 0 || $foreignCredit > 0) && (!$currencyId || $exchangeRate <= 0)) {
                throw ValidationException::withMessages([
                    'items' => 'برای ردیف ارزی، انتخاب ارز و نرخ تبدیل الزامی است.',
                ]);
            }

            $lines[] = [
                'account_id' => (int) $accountId,
                'debit_amount' => $debit,
                'credit_amount' => $credit,
                'description' => $descriptions[$index] ?? null,
                'currency_id' => $currencyId,
                'foreign_debit_amount' => $currencyId && $foreignDebit > 0 ? $foreignDebit : null,
                'foreign_credit_amount' => $currencyId && $foreignCredit > 0 ? $foreignCredit : null,
                'exchange_rate' => $currencyId && $exchangeRate > 0 ? $exchangeRate : null,
                'cost_center_id' => !empty($costCenterIds[$index]) ? (int) $costCenterIds[$index] : null,
                'revenue_center_id' => !empty($revenueCenterIds[$index]) ? (int) $revenueCenterIds[$index] : null,
                'branch_id' => !empty($branchIds[$index]) ? (int) $branchIds[$index] : null,
                'project_code' => $this->trimNullable($projectCodes[$index] ?? null),
                'product_id' => !empty($productIds[$index]) ? (int) $productIds[$index] : null,
                'customer_id' => !empty($customerIds[$index]) ? (int) $customerIds[$index] : null,
                'employee_id' => !empty($employeeIds[$index]) ? (int) $employeeIds[$index] : null,
                'contract_code' => $this->trimNullable($contractCodes[$index] ?? null),
                'route_code' => $this->trimNullable($routeCodes[$index] ?? null),
                'analytic_note' => $this->trimNullable($analyticNotes[$index] ?? null),
            ];
        }

        return $lines;
    }

    private function analyticLineAttributes(array $line): array
    {
        return Arr::only($line, [
            'cost_center_id',
            'revenue_center_id',
            'branch_id',
            'project_code',
            'product_id',
            'customer_id',
            'employee_id',
            'contract_code',
            'route_code',
            'analytic_note',
            'currency_id',
            'foreign_debit_amount',
            'foreign_credit_amount',
            'exchange_rate',
        ]);
    }

    private function analyticItemAttributes($item): array
    {
        return [
            'cost_center_id' => $item->cost_center_id,
            'revenue_center_id' => $item->revenue_center_id,
            'expense_id' => $item->expense_id,
            'branch_id' => $item->branch_id,
            'project_code' => $item->project_code,
            'product_id' => $item->product_id,
            'customer_id' => $item->customer_id,
            'employee_id' => $item->employee_id,
            'contract_code' => $item->contract_code,
            'route_code' => $item->route_code,
            'analytic_note' => $item->analytic_note,
            'currency_id' => $item->currency_id,
            'foreign_debit_amount' => $item->foreign_debit_amount,
            'foreign_credit_amount' => $item->foreign_credit_amount,
            'exchange_rate' => $item->exchange_rate,
        ];
    }

    private function trimNullable($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function money($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 2);
    }

    private function foreignAmount($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 4);
    }

    private function exchangeRate($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 6);
    }

    private function paymentAmount(array $payment): float
    {
        $paymentType = (int) Arr::get($payment, 'payment_type', 5);

        return match ($paymentType) {
            1 => $this->money(Arr::get($payment, 'cashmoney_amount')),
            2 => $this->money(Arr::get($payment, 'chek_amount')),
            3 => $this->money(Arr::get($payment, 'kartbekart_amount')),
            4 => $this->money(Arr::get($payment, 'getway_amount')),
            default => 0,
        };
    }

    private function paymentAccountId(int $paymentType, array $payment, ?int $tenantId, ?int $organizationId, $user): int
    {
        if ($paymentType === 4 && Arr::get($payment, 'terminal_id')) {
            $terminal = PaymentTerminal::find(Arr::get($payment, 'terminal_id'));
            if ($terminal?->account_id) {
                return (int) $terminal->account_id;
            }
        }

        return match ($paymentType) {
            1 => $this->systemAccountId('SYS-1101', 'صندوق فروش', $tenantId, $organizationId, $user),
            2 => $this->systemAccountId('SYS-1102', 'اسناد دریافتنی', $tenantId, $organizationId, $user),
            3 => $this->systemAccountId('SYS-1103', 'بانک و کارت به کارت', $tenantId, $organizationId, $user),
            4 => $this->systemAccountId('SYS-1104', 'دریافت از کارتخوان', $tenantId, $organizationId, $user),
            default => $this->systemAccountId('SYS-1201', 'حساب های دریافتنی تجاری', $tenantId, $organizationId, $user),
        };
    }

    private function outgoingPaymentAccountId(int $paymentType, array $payment, ?int $tenantId, ?int $organizationId, $user): int
    {
        if ($paymentType === 4 && Arr::get($payment, 'terminal_id')) {
            $terminal = PaymentTerminal::find(Arr::get($payment, 'terminal_id'));
            if ($terminal?->account_id) {
                return (int) $terminal->account_id;
            }
        }

        return match ($paymentType) {
            1 => $this->systemAccountId('SYS-1101', 'صندوق فروش', $tenantId, $organizationId, $user),
            2 => $this->systemAccountId('SYS-2102', 'اسناد پرداختنی', $tenantId, $organizationId, $user),
            3, 4 => $this->systemAccountId('SYS-1103', 'بانک و کارت به کارت', $tenantId, $organizationId, $user),
            default => $this->systemAccountId('SYS-2202', 'حساب های پرداختنی تامین کنندگان', $tenantId, $organizationId, $user),
        };
    }

    private function purchaseReturnSettlementAccountId(PurchaseReturn $purchaseReturn, ?int $tenantId, ?int $organizationId, $user): int
    {
        $purchaseOrder = $purchaseReturn->purchaseOrder;

        if ($purchaseOrder && (float) $purchaseOrder->paid_amount > (float) $purchaseOrder->net_amount) {
            return $this->systemAccountId('SYS-1202', 'طلب از تامین کنندگان', $tenantId, $organizationId, $user);
        }

        return $this->systemAccountId('SYS-2202', 'حساب های پرداختنی تامین کنندگان', $tenantId, $organizationId, $user);
    }

    private function inventoryOffsetAccountId(Receipt $receipt, ?int $tenantId, ?int $organizationId, $user): int
    {
        if ((int) $receipt->type === 5) {
            return $this->systemAccountId('SYS-3101', 'افتتاحیه موجودی کالا', $tenantId, $organizationId, $user);
        }

        if ($receipt->depots->contains(fn($depot) => (int) $depot->status === 0)) {
            return $this->systemAccountId('SYS-5101', 'هزینه و اصلاحیه خروج انبار', $tenantId, $organizationId, $user);
        }

        return $this->systemAccountId('SYS-2201', 'حساب واسط رسید انبار', $tenantId, $organizationId, $user);
    }

    private function inventoryDepotAmount($depot): float
    {
        $quantity = abs($this->money($depot->entity));
        $unitPrice = $this->money($depot->price ?: $depot->product?->price ?: 0);

        return round($quantity * $unitPrice, 2);
    }

    private function pishfactorCostOfGoods(Pishfactor $pishfactor): float
    {
        return $this->money(InventoryMovement::where('source_type', Pishfactor::class)
            ->where('source_id', $pishfactor->id)
            ->where('movement_type', 'sale')
            ->where('direction', 'out')
            ->sum('total_cost'));
    }

    private function pishfactorReturnCostOfGoods(Pishfactor $pishfactor): float
    {
        return $this->money(InventoryMovement::where('source_type', Pishfactor::class)
            ->where('source_id', $pishfactor->id)
            ->whereIn('movement_type', ['sale_return', 'sales_return', 'return_sales', 'return'])
            ->where('direction', 'in')
            ->sum('total_cost'));
    }

    private function isSalesReturnPishfactor(Pishfactor $pishfactor): bool
    {
        return in_array((string) $pishfactor->sales_document_type, ['sales_return', 'return_sales', 'return_invoice', 'sales_return_invoice', 'return'], true);
    }

    private function pishfactorAnalyticDimensions(Pishfactor $pishfactor): array
    {
        $routeCode = $this->trimNullable($pishfactor->route_code);

        if (!$routeCode) {
            $routeCode = match (true) {
                !empty($pishfactor->shipment_id) => 'shipment:' . $pishfactor->shipment_id,
                !empty($pishfactor->area_id) => 'area:' . $pishfactor->area_id,
                !empty($pishfactor->region_id) => 'region:' . $pishfactor->region_id,
                !empty($pishfactor->city_id) => 'city:' . $pishfactor->city_id,
                default => null,
            };
        }

        return [
            'revenue_center_id' => !empty($pishfactor->revenue_center_id) ? (int) $pishfactor->revenue_center_id : null,
            'project_code' => $this->trimNullable($pishfactor->project_code) ?: (!empty($pishfactor->task_id) ? 'task:' . $pishfactor->task_id : null),
            'contract_code' => $this->trimNullable($pishfactor->contract_code),
            'route_code' => $routeCode,
            'employee_id' => !empty($pishfactor->visitor_id) ? (int) $pishfactor->visitor_id : null,
        ];
    }

    private function pishfactorSalesProductLines(Pishfactor $pishfactor, float $netSales): array
    {
        $pishfactor->loadMissing('items.product');
        $rows = $pishfactor->items
            ->map(function ($item) use ($pishfactor) {
                $product = $item->product;
                $packItems = max(1, (int) ($product?->pack_items ?: 1));
                $quantity = ($packItems * (float) ($item->pack ?: 0)) + (float) ($item->tedad ?: 0);
                $gross = round($quantity * $this->money($item->price), 2);
                $discountAmount = $this->money($item->discount_amount ?: 0);

                if ($discountAmount <= 0 && (float) ($item->discount ?: 0) > 0) {
                    $discountAmount = round($gross * (float) $item->discount / 100, 2);
                }

                return [
                    'product_id' => $item->pr_id ? (int) $item->pr_id : null,
                    'amount' => max(0, round($gross - $discountAmount, 2)),
                    'discount_amount' => $discountAmount,
                    'description' => 'درآمد فروش کالای ' . ($product?->title ?: $item->pr_id) . ' فاکتور شماره ' . $pishfactor->invoiceID,
                ];
            })
            ->filter(fn($row) => $row['amount'] > 0)
            ->values();
        $baseTotal = round((float) $rows->sum('amount'), 2);

        if ($rows->isEmpty() || $baseTotal <= 0) {
            return [[
                'product_id' => null,
                'amount' => $netSales,
                'analytic_note' => 'sales_invoice_unallocated',
                'description' => 'درآمد فروش فاکتور شماره ' . $pishfactor->invoiceID,
            ]];
        }

        $targetTotal = $netSales;
        $remaining = $netSales;

        return $rows->map(function ($row, $index) use ($rows, $baseTotal, $targetTotal, &$remaining) {
            $amount = $index === $rows->count() - 1 ? $remaining : round((float) $row['amount'] * $targetTotal / $baseTotal, 2);
            $remaining = round($remaining - $amount, 2);
            $row['amount'] = $amount;
            $row['analytic_note'] = 'sales_invoice_product' . ((float) $row['discount_amount'] > 0 ? ' discount=' . $row['discount_amount'] : '');

            return $row;
        })->all();
    }

    private function pishfactorCostProductLines(Pishfactor $pishfactor, float $totalCost): array
    {
        $rows = InventoryMovement::query()
            ->where('source_type', Pishfactor::class)
            ->where('source_id', $pishfactor->id)
            ->where('movement_type', 'sale')
            ->where('direction', 'out')
            ->select('product_id')
            ->selectRaw('COALESCE(SUM(total_cost), 0) as amount')
            ->groupBy('product_id')
            ->get()
            ->filter(fn($row) => (float) $row->amount > 0)
            ->values();

        if ($rows->isEmpty()) {
            return [[
                'product_id' => null,
                'amount' => $totalCost,
                'description' => 'بهای تمام شده فروش فاکتور شماره ' . $pishfactor->invoiceID,
            ]];
        }

        $remaining = $totalCost;

        return $rows->map(function ($row, $index) use ($rows, &$remaining, $pishfactor) {
            $amount = $index === $rows->count() - 1 ? $remaining : $this->money($row->amount);
            $remaining = round($remaining - $amount, 2);

            return [
                'product_id' => $row->product_id ? (int) $row->product_id : null,
                'amount' => $amount,
                'description' => 'بهای تمام شده فروش کالای ' . ($row->product_id ?: 'بدون کالا') . ' فاکتور شماره ' . $pishfactor->invoiceID,
            ];
        })->all();
    }

    private function pishfactorReturnCostProductLines(Pishfactor $pishfactor, float $totalCost): array
    {
        $rows = InventoryMovement::query()
            ->where('source_type', Pishfactor::class)
            ->where('source_id', $pishfactor->id)
            ->whereIn('movement_type', ['sale_return', 'sales_return', 'return_sales', 'return'])
            ->where('direction', 'in')
            ->select('product_id')
            ->selectRaw('COALESCE(SUM(total_cost), 0) as amount')
            ->groupBy('product_id')
            ->get()
            ->filter(fn($row) => (float) $row->amount > 0)
            ->values();

        if ($rows->isEmpty()) {
            return [[
                'product_id' => null,
                'amount' => $totalCost,
                'description' => 'برگشت بهای تمام شده فروش فاکتور شماره ' . $pishfactor->invoiceID,
            ]];
        }

        $remaining = $totalCost;

        return $rows->map(function ($row, $index) use ($rows, &$remaining, $pishfactor) {
            $amount = $index === $rows->count() - 1 ? $remaining : $this->money($row->amount);
            $remaining = round($remaining - $amount, 2);

            return [
                'product_id' => $row->product_id ? (int) $row->product_id : null,
                'amount' => $amount,
                'description' => 'برگشت بهای تمام شده فروش کالای ' . ($row->product_id ?: 'بدون کالا') . ' فاکتور شماره ' . $pishfactor->invoiceID,
            ];
        })->all();
    }

    private function deleteDraftVoucher(string $sourceType, int $sourceId, string $documentType): void
    {
        Voucher::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('document_type', $documentType)
            ->where(function ($query) {
                $query->where('is_permanent', 0)->orWhereNull('is_permanent');
            })
            ->get()
            ->each(function (Voucher $voucher) {
                $voucher->items()->delete();
                $voucher->delete();
            });
    }

    private function ensureVoucherDateIsOpen(string $date, ?int $tenantId): void
    {
        if (!Schema::hasTable('fiscal_years')) {
            return;
        }

        $query = FiscalYear::query()
            ->whereDate('starts_at', '<=', $date)
            ->whereDate('ends_at', '>=', $date);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        } else {
            $query->whereNull('tenant_id');
        }

        $fiscalYear = $query
            ->where('status', '<>', 'open')
            ->orderByDesc('id')
            ->first();

        if ($fiscalYear) {
            throw ValidationException::withMessages([
                'voucher_date_en' => 'سال مالی «' . $fiscalYear->title . '» بسته است و ثبت یا قطعی کردن سند در این بازه مجاز نیست.',
            ]);
        }
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
            'nature' => str_starts_with($code, 'SYS-4') || str_starts_with($code, 'SYS-2') ? 0 : 1,
            'isActive' => 1,
            'parent_id' => 0,
        ];

        if (Schema::hasColumn('accounts', 'is_system')) {
            $attributes['is_system'] = true;
        }
        if (Schema::hasColumn('accounts', 'account_category')) {
            $attributes['account_category'] = match (true) {
                str_starts_with($code, 'SYS-1') => 'asset',
                str_starts_with($code, 'SYS-2') => 'liability',
                str_starts_with($code, 'SYS-3') => 'equity',
                str_starts_with($code, 'SYS-4') => 'income',
                str_starts_with($code, 'SYS-5') => 'expense',
                default => 'system',
            };
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

    private function tenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
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

    private function tenantIdFromSubject($subject, $user): ?int
    {
        return $subject?->tenant_id ?: $subject?->tenants_id ?: $this->tenantId($user);
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

    private function organizationIdFromSubject($subject, $user): ?int
    {
        return $this->normalizeOrganizationId($subject?->organization_id) ?: $this->organizationId($user);
    }

    private function normalizeOrganizationId($organizationId): ?int
    {
        $decoded = is_string($organizationId) ? json_decode($organizationId, true) : null;

        if (is_array($decoded)) {
            return isset($decoded[0]) ? (int) $decoded[0] : null;
        }

        return $organizationId ? (int) $organizationId : null;
    }

    private function postingDate(Pishfactor $pishfactor): string
    {
        $date = $pishfactor->recive_date_en ?: $pishfactor->created_at ?: now();
        $timestamp = strtotime((string) $date);

        return $timestamp ? date('Y-m-d', $timestamp) : now()->toDateString();
    }

    private function receiptPostingDate(Receipt $receipt): string
    {
        $date = $receipt->date_en ?: $receipt->created_at ?: now();
        $timestamp = strtotime((string) $date);

        return $timestamp ? date('Y-m-d', $timestamp) : now()->toDateString();
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

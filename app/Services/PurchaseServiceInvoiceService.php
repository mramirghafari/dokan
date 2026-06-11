<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseServiceInvoice;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseServiceInvoiceService
{
    public function __construct(private AccountingPostingService $accountingPostingService) {}

    public function create(array $payload, $user): PurchaseServiceInvoice
    {
        return DB::transaction(function () use ($payload, $user) {
            $purchaseOrder = !empty($payload['purchase_order_id']) ? PurchaseOrder::find((int) $payload['purchase_order_id']) : null;
            $tenantId = $purchaseOrder?->tenant_id ?: $this->tenantId($user);
            $organizationId = $purchaseOrder?->organization_id ?: $this->organizationId($user);
            $date = Arr::get($payload, 'invoice_date_en') ?: now()->toDateString();
            $items = $this->normalizeItems($payload, $tenantId, $organizationId);

            if (empty($items)) {
                throw ValidationException::withMessages([
                    'items' => 'برای ثبت فاکتور خدمات خرید حداقل یک قلم معتبر لازم است.',
                ]);
            }

            $subtotal = round(array_sum(array_column($items, 'amount')), 2);
            $tax = round(array_sum(array_column($items, 'tax_amount')), 2);

            $invoice = PurchaseServiceInvoice::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'supplier_id' => Arr::get($payload, 'supplier_id') ?: $purchaseOrder?->supplier_id,
                'purchase_order_id' => $purchaseOrder?->id,
                'receipt_id' => Arr::get($payload, 'receipt_id'),
                'invoice_number' => $this->nextNumber($tenantId),
                'invoice_type' => Arr::get($payload, 'invoice_type', 'service'),
                'invoice_date_en' => $date,
                'invoice_date_fa' => $this->jalaliDate($date),
                'status' => 'approved',
                'subtotal_amount' => $subtotal,
                'tax_amount' => $tax,
                'total_amount' => round($subtotal + $tax, 2),
                'created_by' => $user?->id,
                'approved_by' => $user?->id,
                'approved_at' => now(),
                'description' => Arr::get($payload, 'description'),
            ]);

            foreach ($items as $item) {
                $invoice->items()->create($item);
            }

            $this->accountingPostingService->postPurchaseServiceInvoiceVoucher($invoice, $user);

            return $invoice->fresh(['items', 'supplier', 'purchaseOrder', 'accountingVoucher.items.account']) ?: $invoice;
        });
    }

    public function cancel(PurchaseServiceInvoice $invoice, $user): PurchaseServiceInvoice
    {
        return DB::transaction(function () use ($invoice, $user) {
            $invoice = $invoice->fresh(['accountingVoucher']) ?: $invoice;

            if ($invoice->status === 'canceled') {
                return $invoice;
            }

            if ($invoice->accountingVoucher && $invoice->accountingVoucher->is_permanent) {
                throw ValidationException::withMessages([
                    'invoice' => 'فاکتور دارای سند دائم است و از این مسیر قابل ابطال نیست.',
                ]);
            }

            $this->accountingPostingService->removePurchaseServiceInvoiceVoucher($invoice);

            $invoice->update([
                'status' => 'canceled',
                'canceled_at' => now(),
                'canceled_by' => $user?->id,
            ]);

            return $invoice->fresh(['items', 'supplier', 'purchaseOrder', 'accountingVoucher']) ?: $invoice;
        });
    }

    private function normalizeItems(array $payload, ?int $tenantId, ?int $organizationId): array
    {
        $titles = Arr::get($payload, 'item_title', []);
        $amounts = Arr::get($payload, 'amount', []);
        $taxes = Arr::get($payload, 'tax_amount', []);
        $costTypes = Arr::get($payload, 'cost_type', []);
        $allocationTypes = Arr::get($payload, 'allocation_type', []);
        $expenseAccountIds = Arr::get($payload, 'expense_account_id', []);
        $purchaseOrderItemIds = Arr::get($payload, 'purchase_order_item_id', []);
        $productIds = Arr::get($payload, 'product_id', []);
        $descriptions = Arr::get($payload, 'item_description', []);
        $items = [];

        foreach ($titles as $index => $title) {
            $amount = $this->money($amounts[$index] ?? 0);
            $tax = $this->money($taxes[$index] ?? 0);
            $title = trim((string) $title);

            if ($title === '' || $amount <= 0) {
                continue;
            }

            $items[] = [
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'purchase_order_item_id' => $purchaseOrderItemIds[$index] ?? null,
                'product_id' => $productIds[$index] ?? null,
                'expense_account_id' => $expenseAccountIds[$index] ?? null,
                'cost_type' => $costTypes[$index] ?? 'service',
                'allocation_type' => $allocationTypes[$index] ?? 'expense',
                'title' => $title,
                'amount' => $amount,
                'tax_amount' => $tax,
                'total_amount' => round($amount + $tax, 2),
                'description' => $descriptions[$index] ?? null,
            ];
        }

        return $items;
    }

    private function nextNumber(?int $tenantId): string
    {
        $year = verta()->format('Y');
        $base = 'PSI-' . $year . '-';
        $query = PurchaseServiceInvoice::where('invoice_number', 'like', $base . '%');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $lastNumber = $query->orderByDesc('id')->value('invoice_number');
        $next = 1;

        if ($lastNumber && preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $base . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
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

    private function money($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 2);
    }
}

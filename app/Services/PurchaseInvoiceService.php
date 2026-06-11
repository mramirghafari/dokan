<?php

namespace App\Services;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseInvoiceService
{
    public function __construct(private AccountingPostingService $accountingPostingService) {}

    public function create(PurchaseOrder $purchaseOrder, array $payload, $user): PurchaseInvoice
    {
        return DB::transaction(function () use ($purchaseOrder, $payload, $user) {
            $purchaseOrder = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();
            $purchaseOrder->loadMissing(['items.product', 'supplier', 'invoices.items']);

            if (!in_array($purchaseOrder->status, ['partial_received', 'received'], true)) {
                throw ValidationException::withMessages([
                    'purchase_order' => 'ثبت فاکتور خرید فقط برای سفارش دارای رسید انبار مجاز است.',
                ]);
            }

            $items = $this->normalizeItems($purchaseOrder, $payload);

            if (empty($items)) {
                throw ValidationException::withMessages([
                    'items' => 'برای ثبت فاکتور خرید، حداقل یک قلم دریافت شده و صورتحساب نشده لازم است.',
                ]);
            }

            $date = Arr::get($payload, 'invoice_date_en') ?: now()->toDateString();
            $goodsAmount = round(array_sum(array_column($items, 'goods_amount')), 2);
            $taxAmount = round(array_sum(array_column($items, 'tax_amount')), 2);
            $priceVarianceAmount = round(array_sum(array_column($items, 'price_variance_amount')), 2);
            $invoice = PurchaseInvoice::create([
                'tenant_id' => $purchaseOrder->tenant_id,
                'organization_id' => $purchaseOrder->organization_id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'purchase_order_id' => $purchaseOrder->id,
                'invoice_number' => $this->nextNumber($purchaseOrder->tenant_id),
                'supplier_invoice_number' => Arr::get($payload, 'supplier_invoice_number'),
                'invoice_date_en' => $date,
                'invoice_date_fa' => $this->jalaliDate($date),
                'status' => 'approved',
                'goods_amount' => $goodsAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => round($goodsAmount + $taxAmount, 2),
                'price_variance_amount' => $priceVarianceAmount,
                'created_by' => $user?->id,
                'approved_by' => $user?->id,
                'approved_at' => now(),
                'description' => Arr::get($payload, 'description'),
            ]);

            foreach ($items as $item) {
                $invoice->items()->create(array_merge($item, [
                    'tenant_id' => $purchaseOrder->tenant_id,
                    'organization_id' => $purchaseOrder->organization_id,
                ]));
            }

            $this->accountingPostingService->removePurchaseOrderPayableVoucher($purchaseOrder);
            $this->accountingPostingService->postPurchaseInvoiceVoucher($invoice, $user);

            return $invoice->fresh(['items.product', 'supplier', 'purchaseOrder', 'accountingVoucher.items.account']) ?: $invoice;
        });
    }

    private function normalizeItems(PurchaseOrder $purchaseOrder, array $payload): array
    {
        $itemIds = Arr::get($payload, 'purchase_order_item_id', []);
        $quantities = Arr::get($payload, 'invoice_quantity', []);
        $unitPrices = Arr::get($payload, 'invoice_unit_price', []);
        $taxAmounts = Arr::get($payload, 'tax_amount', []);
        $descriptions = Arr::get($payload, 'item_description', []);
        $items = [];

        foreach ($itemIds as $index => $itemId) {
            $quantity = $this->quantity($quantities[$index] ?? 0);

            if (!$itemId || $quantity <= 0) {
                continue;
            }

            $orderItem = $purchaseOrder->items->firstWhere('id', (int) $itemId);

            if (!$orderItem) {
                throw ValidationException::withMessages([
                    'items' => 'قلم انتخاب شده به این سفارش خرید تعلق ندارد.',
                ]);
            }

            $alreadyInvoiced = (float) $purchaseOrder->invoices
                ->where('status', '<>', 'canceled')
                ->flatMap->items
                ->where('purchase_order_item_id', $orderItem->id)
                ->sum('quantity');
            $availableQuantity = max(0, round((float) $orderItem->received_quantity - $alreadyInvoiced, 3));

            if ($quantity > $availableQuantity) {
                throw ValidationException::withMessages([
                    'items' => 'مقدار فاکتور خرید نمی تواند بیشتر از مقدار دریافت شده و صورتحساب نشده باشد.',
                ]);
            }

            $orderUnitPrice = $this->money($orderItem->unit_price);
            $invoiceUnitPrice = $this->money($unitPrices[$index] ?? $orderUnitPrice);
            $taxAmount = $this->money($taxAmounts[$index] ?? 0);
            $goodsAmount = round($quantity * $invoiceUnitPrice, 2);
            $priceVarianceAmount = round(($invoiceUnitPrice - $orderUnitPrice) * $quantity, 2);

            $items[] = [
                'purchase_order_item_id' => $orderItem->id,
                'product_id' => $orderItem->product_id,
                'quantity' => $quantity,
                'order_unit_price' => $orderUnitPrice,
                'invoice_unit_price' => $invoiceUnitPrice,
                'goods_amount' => $goodsAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => round($goodsAmount + $taxAmount, 2),
                'price_variance_amount' => $priceVarianceAmount,
                'match_status' => abs($priceVarianceAmount) > 0.0001 ? 'price_variance' : 'matched',
                'description' => $descriptions[$index] ?? null,
            ];
        }

        return $items;
    }

    private function nextNumber(?int $tenantId): string
    {
        $year = verta()->format('Y');
        $base = 'PIN-' . $year . '-';
        $query = PurchaseInvoice::where('invoice_number', 'like', $base . '%');

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

    private function quantity($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 3);
    }
}

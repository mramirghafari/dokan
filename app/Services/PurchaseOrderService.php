<?php

namespace App\Services;

use App\Models\Depot;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderReceipt;
use App\Models\PurchaseReturn;
use App\Models\Receipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderService
{
    public function __construct(
        private InventoryLedgerService $inventoryLedgerService,
        private AccountingPostingService $accountingPostingService
    ) {}

    public function approve(PurchaseOrder $purchaseOrder, $user): PurchaseOrder
    {
        $purchaseOrder->loadMissing(['items']);

        return $this->receive($purchaseOrder, [
            'receive_date_en' => optional($purchaseOrder->order_date_en)->toDateString() ?: now()->toDateString(),
            'description' => $purchaseOrder->description ?: 'رسید کامل سفارش خرید ' . $purchaseOrder->order_number,
            'purchase_order_item_id' => $purchaseOrder->items->pluck('id')->all(),
            'receive_quantity' => $purchaseOrder->items
                ->map(fn($item) => max(0, round((float) $item->quantity - (float) $item->received_quantity, 3)))
                ->all(),
        ], $user);
    }

    public function receive(PurchaseOrder $purchaseOrder, array $payload, $user): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $payload, $user) {
            $purchaseOrder = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();
            $purchaseOrder->loadMissing(['items.product', 'supplier', 'receipt']);

            $this->validateForReceiving($purchaseOrder);
            $lines = $this->normalizeReceiveLines($purchaseOrder, $payload);

            if (empty($lines)) {
                throw ValidationException::withMessages([
                    'items' => 'برای ثبت رسید، حداقل یک قلم با مقدار دریافت معتبر لازم است.',
                ]);
            }

            $date = $payload['receive_date_en'] ?? now()->toDateString();
            $purchaseReceipt = PurchaseOrderReceipt::create([
                'tenant_id' => $purchaseOrder->tenant_id,
                'organization_id' => $purchaseOrder->organization_id,
                'purchase_order_id' => $purchaseOrder->id,
                'receive_number' => $this->nextReceiveNumber($purchaseOrder),
                'receive_date_en' => $date,
                'receive_date_fa' => verta($date)->format('Y/m/d'),
                'status' => 'approved',
                'total_amount' => array_sum(array_column($lines, 'total_amount')),
                'description' => $payload['description'] ?? null,
                'created_by' => $user?->id,
            ]);

            $receipt = Receipt::create([
                'user_id' => $user?->id,
                'tenant_id' => $purchaseOrder->tenant_id,
                'organization_id' => $purchaseOrder->organization_id,
                'type' => 1,
                'store_id' => $purchaseOrder->store_id,
                'number' => $purchaseReceipt->id,
                'date_fa' => $purchaseReceipt->receive_date_fa,
                'date_en' => $purchaseReceipt->receive_date_en,
                'sender' => $purchaseOrder->supplier?->title ?: $purchaseOrder->supplier?->name,
                'moeen' => 'خرید و تامین',
                'tozihat' => $purchaseReceipt->description ?: 'رسید خرید مرحله ای ' . $purchaseReceipt->receive_number,
                'document_status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $user?->id,
            ]);

            foreach ($lines as $line) {
                $purchaseReceipt->items()->create(array_merge($line, [
                    'tenant_id' => $purchaseOrder->tenant_id,
                    'organization_id' => $purchaseOrder->organization_id,
                ]));

                $depot = new Depot();
                $depot->pr_id = $line['product_id'];
                $depot->receipt_id = $receipt->id;
                $depot->tenant_id = $purchaseOrder->tenant_id;
                $depot->entity = $line['quantity'];
                $depot->entity_sub_unit = 0;
                $depot->store_id = $purchaseOrder->store_id;
                $depot->warehouse_location_id = 0;
                $depot->status = 1;
                $depot->price = $line['unit_price'];
                $depot->save();

                $item = $purchaseOrder->items->firstWhere('id', (int) $line['purchase_order_item_id']);
                $item->update([
                    'received_quantity' => round((float) $item->received_quantity + (float) $line['quantity'], 3),
                ]);
            }

            $purchaseReceipt->update(['receipt_id' => $receipt->id]);

            $freshItems = $purchaseOrder->items()->get();
            $isFullyReceived = $freshItems->every(fn($item) => round((float) $item->received_quantity, 3) >= round((float) $item->quantity, 3));
            $purchaseOrder->update([
                'receipt_id' => $receipt->id,
                'status' => $isFullyReceived ? 'received' : 'partial_received',
                'approved_at' => $purchaseOrder->approved_at ?: now(),
                'approved_by' => $purchaseOrder->approved_by ?: $user?->id,
                'updated_by' => $user?->id,
            ]);

            $receipt = $receipt->fresh(['depots.product']) ?: $receipt;
            $this->inventoryLedgerService->replaceReceiptMovements($receipt, $receipt->depots()->get(), $user?->id);
            $this->accountingPostingService->postReceiptInventoryVoucher($receipt, $user);
            $this->accountingPostingService->postPurchaseOrderPayableVoucher($purchaseOrder->fresh(['items', 'supplier']) ?: $purchaseOrder, $user);

            return $purchaseOrder->fresh(['items.product', 'supplier', 'store', 'receipt', 'receiveDocuments.items']) ?: $purchaseOrder;
        });
    }

    public function paySupplier(PurchaseOrder $purchaseOrder, array $payload, $user): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $payload, $user) {
            $purchaseOrder = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();
            $purchaseOrder->loadMissing(['supplier', 'invoices', 'returns']);

            if ($purchaseOrder->status !== 'received') {
                throw ValidationException::withMessages([
                    'purchase_order' => 'پرداخت تامین کننده فقط برای سفارش خرید رسید شده مجاز است.',
                ]);
            }

            $amount = $this->money($payload['amount'] ?? 0);
            $paidAmount = $this->money($purchaseOrder->paid_amount);
            $totalAmount = $this->money($purchaseOrder->net_amount);
            $remainingAmount = max(0, round($totalAmount - $paidAmount, 2));

            if ($amount <= 0 || $amount > $remainingAmount) {
                throw ValidationException::withMessages([
                    'amount' => 'مبلغ پرداخت باید بزرگتر از صفر و حداکثر برابر مانده خرید باشد.',
                ]);
            }

            $this->accountingPostingService->createPurchaseSupplierPaymentVoucher($purchaseOrder, $payload, $user);

            $newPaidAmount = round($paidAmount + $amount, 2);
            $purchaseOrder->update([
                'paid_amount' => $newPaidAmount,
                'payment_status' => $newPaidAmount >= $totalAmount ? 'paid' : 'partial',
                'paid_at' => $newPaidAmount >= $totalAmount ? now() : null,
                'updated_by' => $user?->id,
            ]);

            return $purchaseOrder->fresh(['items.product', 'supplier', 'store', 'receipt']) ?: $purchaseOrder;
        });
    }

    public function returnItems(PurchaseOrder $purchaseOrder, array $payload, $user): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $payload, $user) {
            $purchaseOrder = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();
            $purchaseOrder->loadMissing(['items.product', 'supplier']);

            if (!in_array($purchaseOrder->status, ['partial_received', 'received'], true)) {
                throw ValidationException::withMessages([
                    'purchase_order' => 'مرجوعی خرید فقط برای سفارش دارای رسید انبار مجاز است.',
                ]);
            }

            $lines = $this->normalizeReturnLines($purchaseOrder, $payload);

            if (empty($lines)) {
                throw ValidationException::withMessages([
                    'items' => 'برای ثبت مرجوعی، حداقل یک قلم با تعداد معتبر لازم است.',
                ]);
            }

            $date = $payload['return_date_en'] ?? now()->toDateString();
            $purchaseReturn = PurchaseReturn::create([
                'tenant_id' => $purchaseOrder->tenant_id,
                'organization_id' => $purchaseOrder->organization_id,
                'purchase_order_id' => $purchaseOrder->id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'store_id' => $purchaseOrder->store_id,
                'return_number' => $this->nextReturnNumber($purchaseOrder),
                'return_date_en' => $date,
                'return_date_fa' => verta($date)->format('Y/m/d'),
                'status' => 'approved',
                'total_amount' => array_sum(array_column($lines, 'total_amount')),
                'description' => $payload['description'] ?? null,
                'created_by' => $user?->id,
            ]);

            foreach ($lines as $line) {
                $purchaseReturn->items()->create(array_merge($line, [
                    'tenant_id' => $purchaseOrder->tenant_id,
                    'organization_id' => $purchaseOrder->organization_id,
                ]));
            }

            $receipt = Receipt::create([
                'user_id' => $user?->id,
                'tenant_id' => $purchaseOrder->tenant_id,
                'organization_id' => $purchaseOrder->organization_id,
                'type' => 7,
                'store_id' => $purchaseOrder->store_id,
                'number' => $purchaseReturn->id,
                'date_fa' => $purchaseReturn->return_date_fa,
                'date_en' => $purchaseReturn->return_date_en,
                'sender' => $purchaseOrder->supplier?->title ?: $purchaseOrder->supplier?->name,
                'moeen' => 'مرجوعی خرید',
                'tozihat' => $purchaseReturn->description ?: 'مرجوعی خرید شماره ' . $purchaseReturn->return_number,
                'document_status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $user?->id,
            ]);

            foreach ($purchaseReturn->items as $item) {
                $depot = new Depot();
                $depot->pr_id = $item->product_id;
                $depot->receipt_id = $receipt->id;
                $depot->tenant_id = $purchaseOrder->tenant_id;
                $depot->entity = $item->quantity;
                $depot->entity_sub_unit = 0;
                $depot->store_id = $purchaseOrder->store_id;
                $depot->warehouse_location_id = 0;
                $depot->status = 0;
                $depot->price = $item->unit_price;
                $depot->save();
            }

            $purchaseReturn->update(['receipt_id' => $receipt->id]);

            $receipt = $receipt->fresh(['depots.product']) ?: $receipt;
            $this->inventoryLedgerService->replaceReceiptMovements($receipt, $receipt->depots()->get(), $user?->id);
            $this->accountingPostingService->postPurchaseReturnVoucher($purchaseReturn->fresh(['items', 'purchaseOrder', 'receipt']) ?: $purchaseReturn, $user);
            $this->syncPaymentStatus($purchaseOrder->fresh(['returns']) ?: $purchaseOrder, $user);

            return $purchaseOrder->fresh(['items.product', 'supplier', 'store', 'receipt', 'returns.items']) ?: $purchaseOrder;
        });
    }

    private function validateForApproval(PurchaseOrder $purchaseOrder): void
    {
        $this->validateForReceiving($purchaseOrder);
    }

    private function validateForReceiving(PurchaseOrder $purchaseOrder): void
    {
        if ($purchaseOrder->approval_status !== 'approved') {
            throw ValidationException::withMessages([
                'purchase_order' => 'قبل از رسید انبار، سفارش خرید باید در مرحله تایید مدیریتی تایید شود.',
            ]);
        }

        if ($purchaseOrder->items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'برای ثبت رسید سفارش خرید، حداقل یک قلم کالا لازم است.',
            ]);
        }

        if (!$purchaseOrder->store_id || !$purchaseOrder->supplier_id) {
            throw ValidationException::withMessages([
                'purchase_order' => 'برای ثبت رسید سفارش خرید، تامین کننده و انبار مقصد الزامی است.',
            ]);
        }

        if (!in_array($purchaseOrder->status, ['approved', 'partial_received'], true)) {
            throw ValidationException::withMessages([
                'purchase_order' => 'فقط سفارش تایید شده یا دارای دریافت ناقص قابلیت ثبت رسید دارد.',
            ]);
        }

        foreach ($purchaseOrder->items as $item) {
            if (!$item->product_id || (float) $item->quantity <= 0 || (float) $item->unit_price <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'همه اقلام سفارش خرید باید کالا، تعداد و فی معتبر داشته باشند.',
                ]);
            }

            if ($purchaseOrder->organization_id && $item->product && !$this->organizationMatches($item->product->organization_id, (int) $purchaseOrder->organization_id)) {
                throw ValidationException::withMessages([
                    'items' => 'کالای انتخاب شده با سازمان/انبار سفارش خرید همخوان نیست.',
                ]);
            }
        }
    }

    private function organizationMatches($subjectOrganizationId, int $expectedOrganizationId): bool
    {
        $decoded = is_string($subjectOrganizationId) ? json_decode($subjectOrganizationId, true) : null;
        $values = is_array($decoded) ? $decoded : [$subjectOrganizationId];

        foreach ($values as $value) {
            if ((int) $value === $expectedOrganizationId) {
                return true;
            }
        }

        return false;
    }

    private function money($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 2);
    }

    private function quantity($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 3);
    }

    private function normalizeReturnLines(PurchaseOrder $purchaseOrder, array $payload): array
    {
        $itemIds = $payload['purchase_order_item_id'] ?? [];
        $quantities = $payload['return_quantity'] ?? [];
        $lines = [];

        foreach ($itemIds as $index => $itemId) {
            $quantity = $this->quantity($quantities[$index] ?? 0);

            if (!$itemId || $quantity <= 0) {
                continue;
            }

            $item = $purchaseOrder->items->firstWhere('id', (int) $itemId);

            if (!$item) {
                throw ValidationException::withMessages([
                    'items' => 'قلم انتخاب شده به این سفارش خرید تعلق ندارد.',
                ]);
            }

            $alreadyReturned = (float) PurchaseReturn::query()
                ->join('purchase_return_items', 'purchase_returns.id', '=', 'purchase_return_items.purchase_return_id')
                ->where('purchase_returns.purchase_order_id', $purchaseOrder->id)
                ->where('purchase_return_items.purchase_order_item_id', $item->id)
                ->sum('purchase_return_items.quantity');
            $availableQuantity = max(0, round((float) $item->received_quantity - $alreadyReturned, 3));

            if ($quantity > $availableQuantity) {
                throw ValidationException::withMessages([
                    'items' => 'تعداد مرجوعی نمی تواند بیشتر از مقدار قابل برگشت کالا باشد.',
                ]);
            }

            $unitPrice = $this->money($item->unit_price);
            $lines[] = [
                'purchase_order_item_id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => round($quantity * $unitPrice, 2),
                'description' => $payload['description'] ?? null,
            ];
        }

        return $lines;
    }

    private function normalizeReceiveLines(PurchaseOrder $purchaseOrder, array $payload): array
    {
        $itemIds = $payload['purchase_order_item_id'] ?? [];
        $quantities = $payload['receive_quantity'] ?? [];
        $lines = [];

        foreach ($itemIds as $index => $itemId) {
            $quantity = $this->quantity($quantities[$index] ?? 0);

            if (!$itemId || $quantity <= 0) {
                continue;
            }

            $item = $purchaseOrder->items->firstWhere('id', (int) $itemId);

            if (!$item) {
                throw ValidationException::withMessages([
                    'items' => 'قلم انتخاب شده به این سفارش خرید تعلق ندارد.',
                ]);
            }

            $remainingQuantity = max(0, round((float) $item->quantity - (float) $item->received_quantity, 3));

            if ($quantity > $remainingQuantity) {
                throw ValidationException::withMessages([
                    'items' => 'مقدار دریافت نمی تواند بیشتر از مانده دریافت کالا باشد.',
                ]);
            }

            $unitPrice = $this->money($item->unit_price);
            $lines[] = [
                'purchase_order_item_id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => round($quantity * $unitPrice, 2),
                'description' => $payload['description'] ?? null,
            ];
        }

        return $lines;
    }

    private function nextReceiveNumber(PurchaseOrder $purchaseOrder): string
    {
        $count = PurchaseOrderReceipt::where('purchase_order_id', $purchaseOrder->id)->count() + 1;

        return $purchaseOrder->order_number . '-REC-' . str_pad((string) $count, 3, '0', STR_PAD_LEFT);
    }

    private function nextReturnNumber(PurchaseOrder $purchaseOrder): string
    {
        $count = PurchaseReturn::where('purchase_order_id', $purchaseOrder->id)->count() + 1;

        return $purchaseOrder->order_number . '-RET-' . str_pad((string) $count, 3, '0', STR_PAD_LEFT);
    }

    private function syncPaymentStatus(PurchaseOrder $purchaseOrder, $user): void
    {
        $netAmount = $this->money($purchaseOrder->net_amount);
        $paidAmount = $this->money($purchaseOrder->paid_amount);

        $purchaseOrder->update([
            'payment_status' => $paidAmount <= 0 ? 'unpaid' : ($paidAmount >= $netAmount ? 'paid' : 'partial'),
            'paid_at' => $paidAmount >= $netAmount && $netAmount > 0 ? ($purchaseOrder->paid_at ?: now()) : null,
            'updated_by' => $user?->id,
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\ForeignPurchaseOrder;
use App\Models\PurchaseOrder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ForeignPurchaseOrderService
{
    public function save(PurchaseOrder $purchaseOrder, array $payload, $user): ForeignPurchaseOrder
    {
        $purchaseOrder->loadMissing(['items.product', 'foreignImport']);

        if ($purchaseOrder->items->isEmpty()) {
            throw ValidationException::withMessages(['purchase_order_id' => 'برای تشکیل پرونده واردات، سفارش خرید باید حداقل یک قلم کالا داشته باشد.']);
        }

        $exchangeRate = $this->rate(Arr::get($payload, 'exchange_rate', 1));
        $itemRows = $this->normalizeItems($purchaseOrder, $payload, $exchangeRate);

        if (empty($itemRows)) {
            throw ValidationException::withMessages(['items' => 'برای پرونده واردات، حداقل یک قلم با مقدار و مبلغ ارزی معتبر لازم است.']);
        }

        $costRows = $this->normalizeCosts($purchaseOrder, $payload, $exchangeRate);
        $documentRows = $this->normalizeDocuments($purchaseOrder, $payload);
        $allocatedRows = $this->allocateCosts($itemRows, $costRows);
        $customsCostAmount = round((float) collect($costRows)->whereIn('cost_type', ['customs_duty', 'clearance'])->sum('base_amount'), 2);
        $additionalCostAmount = round((float) collect($costRows)->sum('base_amount'), 2);
        $baseGoodsAmount = round((float) collect($allocatedRows)->sum('base_goods_amount'), 2);

        return DB::transaction(function () use ($purchaseOrder, $payload, $user, $exchangeRate, $allocatedRows, $costRows, $documentRows, $customsCostAmount, $additionalCostAmount, $baseGoodsAmount) {
            $foreignOrder = ForeignPurchaseOrder::updateOrCreate(
                ['purchase_order_id' => $purchaseOrder->id],
                [
                    'tenant_id' => $purchaseOrder->tenant_id,
                    'organization_id' => $purchaseOrder->organization_id,
                    'supplier_id' => $purchaseOrder->supplier_id,
                    'store_id' => $purchaseOrder->store_id,
                    'currency_id' => Arr::get($payload, 'currency_id'),
                    'import_number' => Arr::get($payload, 'import_number') ?: $purchaseOrder->foreignImport?->import_number ?: $this->nextImportNumber($purchaseOrder->tenant_id),
                    'proforma_number' => Arr::get($payload, 'proforma_number'),
                    'contract_number' => Arr::get($payload, 'contract_number'),
                    'lc_number' => Arr::get($payload, 'lc_number'),
                    'customs_declaration_number' => Arr::get($payload, 'customs_declaration_number'),
                    'bill_of_lading_number' => Arr::get($payload, 'bill_of_lading_number'),
                    'origin_country' => Arr::get($payload, 'origin_country'),
                    'shipment_method' => Arr::get($payload, 'shipment_method'),
                    'status' => Arr::get($payload, 'status', $purchaseOrder->foreignImport?->status ?: 'draft'),
                    'order_date_en' => Arr::get($payload, 'order_date_en') ?: optional($purchaseOrder->order_date_en)->toDateString(),
                    'order_date_fa' => $this->jalaliDate(Arr::get($payload, 'order_date_en') ?: optional($purchaseOrder->order_date_en)->toDateString()),
                    'expected_arrival_date_en' => Arr::get($payload, 'expected_arrival_date_en'),
                    'expected_arrival_date_fa' => $this->jalaliDate(Arr::get($payload, 'expected_arrival_date_en')),
                    'customs_date_en' => Arr::get($payload, 'customs_date_en'),
                    'customs_date_fa' => $this->jalaliDate(Arr::get($payload, 'customs_date_en')),
                    'exchange_rate' => $exchangeRate,
                    'foreign_goods_amount' => round((float) collect($allocatedRows)->sum('foreign_total_amount'), 4),
                    'base_goods_amount' => $baseGoodsAmount,
                    'additional_cost_amount' => $additionalCostAmount,
                    'customs_cost_amount' => $customsCostAmount,
                    'landed_cost_amount' => round($baseGoodsAmount + $additionalCostAmount, 2),
                    'allocated_amount' => round((float) collect($allocatedRows)->sum('allocated_cost_amount'), 2),
                    'description' => Arr::get($payload, 'description'),
                    'created_by' => $purchaseOrder->foreignImport?->created_by ?: $user?->id,
                    'updated_by' => $user?->id,
                ]
            );

            $foreignOrder->items()->delete();
            $foreignOrder->costs()->delete();
            $foreignOrder->documents()->delete();

            foreach ($allocatedRows as $row) {
                $foreignOrder->items()->create($row);
            }

            foreach ($costRows as $row) {
                $foreignOrder->costs()->create($row);
            }

            foreach ($documentRows as $row) {
                $foreignOrder->documents()->create($row);
            }

            return $foreignOrder->fresh(['purchaseOrder.items.product', 'supplier', 'store', 'currency', 'items.product', 'costs', 'documents']) ?: $foreignOrder;
        });
    }

    public function updateStatus(ForeignPurchaseOrder $foreignOrder, string $status, $user): ForeignPurchaseOrder
    {
        if (!in_array($status, ['draft', 'ordered', 'in_transit', 'customs', 'cleared', 'received', 'closed', 'canceled'], true)) {
            throw ValidationException::withMessages(['status' => 'وضعیت پرونده واردات معتبر نیست.']);
        }

        $foreignOrder->update([
            'status' => $status,
            'updated_by' => $user?->id,
        ]);

        return $foreignOrder->fresh(['purchaseOrder.items.product', 'supplier', 'store', 'currency', 'items.product', 'costs', 'documents']) ?: $foreignOrder;
    }

    private function normalizeItems(PurchaseOrder $purchaseOrder, array $payload, float $exchangeRate): array
    {
        $foreignUnitPrices = Arr::get($payload, 'foreign_unit_price', []);
        $manualAllocations = Arr::get($payload, 'manual_allocation_amount', []);
        $descriptions = Arr::get($payload, 'item_description', []);
        $rows = [];

        foreach ($purchaseOrder->items as $index => $item) {
            $quantity = $this->quantity($item->quantity);
            $foreignUnitPrice = $this->foreignMoney($foreignUnitPrices[$index] ?? 0);

            if ($foreignUnitPrice <= 0 && $exchangeRate > 0) {
                $foreignUnitPrice = round((float) $item->unit_price / $exchangeRate, 6);
            }

            if ($quantity <= 0 || $foreignUnitPrice <= 0) {
                continue;
            }

            $foreignTotal = round($quantity * $foreignUnitPrice, 4);
            $baseGoodsAmount = round($foreignTotal * $exchangeRate, 2);

            $rows[] = [
                'purchase_order_item_id' => $item->id,
                'tenant_id' => $purchaseOrder->tenant_id,
                'organization_id' => $purchaseOrder->organization_id,
                'product_id' => $item->product_id,
                'quantity' => $quantity,
                'foreign_unit_price' => $foreignUnitPrice,
                'foreign_total_amount' => $foreignTotal,
                'base_goods_amount' => $baseGoodsAmount,
                'allocated_cost_amount' => 0,
                'landed_total_amount' => $baseGoodsAmount,
                'landed_unit_cost' => round($baseGoodsAmount / $quantity, 6),
                'manual_allocation_amount' => $this->nullableMoney($manualAllocations[$index] ?? null),
                'allocation_weight' => $baseGoodsAmount,
                'description' => $descriptions[$index] ?? $item->description,
            ];
        }

        return $rows;
    }

    private function normalizeCosts(PurchaseOrder $purchaseOrder, array $payload, float $defaultRate): array
    {
        $titles = Arr::get($payload, 'cost_title', []);
        $costTypes = Arr::get($payload, 'cost_type', []);
        $dates = Arr::get($payload, 'cost_date_en', []);
        $foreignAmounts = Arr::get($payload, 'cost_foreign_amount', []);
        $rates = Arr::get($payload, 'cost_exchange_rate', []);
        $baseAmounts = Arr::get($payload, 'cost_base_amount', []);
        $allocationBases = Arr::get($payload, 'allocation_basis', []);
        $documentNumbers = Arr::get($payload, 'cost_document_number', []);
        $referenceNumbers = Arr::get($payload, 'cost_reference_number', []);
        $descriptions = Arr::get($payload, 'cost_description', []);
        $rows = [];

        foreach ($titles as $index => $title) {
            $title = trim((string) $title);
            $foreignAmount = $this->foreignMoney($foreignAmounts[$index] ?? 0);
            $rate = $this->rate($rates[$index] ?? $defaultRate);
            $baseAmount = $this->money($baseAmounts[$index] ?? 0);

            if ($baseAmount <= 0 && $foreignAmount > 0) {
                $baseAmount = round($foreignAmount * $rate, 2);
            }

            if ($title === '' || $baseAmount <= 0) {
                continue;
            }

            $date = $dates[$index] ?? null;

            $rows[] = [
                'tenant_id' => $purchaseOrder->tenant_id,
                'organization_id' => $purchaseOrder->organization_id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'cost_type' => $costTypes[$index] ?? 'other',
                'title' => $title,
                'cost_date_en' => $date,
                'cost_date_fa' => $this->jalaliDate($date),
                'foreign_amount' => $foreignAmount,
                'exchange_rate' => $rate,
                'base_amount' => $baseAmount,
                'allocation_basis' => $allocationBases[$index] ?? 'value',
                'document_number' => $documentNumbers[$index] ?? null,
                'reference_number' => $referenceNumbers[$index] ?? null,
                'description' => $descriptions[$index] ?? null,
            ];
        }

        return $rows;
    }

    private function normalizeDocuments(PurchaseOrder $purchaseOrder, array $payload): array
    {
        $types = Arr::get($payload, 'document_type', []);
        $numbers = Arr::get($payload, 'document_number', []);
        $dates = Arr::get($payload, 'document_date_en', []);
        $references = Arr::get($payload, 'document_reference_number', []);
        $files = Arr::get($payload, 'document_file_path', []);
        $descriptions = Arr::get($payload, 'document_description', []);
        $rows = [];

        foreach ($types as $index => $type) {
            $number = trim((string) ($numbers[$index] ?? ''));
            $description = trim((string) ($descriptions[$index] ?? ''));

            if ($number === '' && $description === '') {
                continue;
            }

            $date = $dates[$index] ?? null;

            $rows[] = [
                'tenant_id' => $purchaseOrder->tenant_id,
                'organization_id' => $purchaseOrder->organization_id,
                'document_type' => $type ?: 'other',
                'document_number' => $number ?: null,
                'document_date_en' => $date,
                'document_date_fa' => $this->jalaliDate($date),
                'reference_number' => $references[$index] ?? null,
                'file_path' => $files[$index] ?? null,
                'description' => $description ?: null,
            ];
        }

        return $rows;
    }

    private function allocateCosts(array $itemRows, array $costRows): array
    {
        $items = collect($itemRows)->values();

        foreach ($costRows as $costRow) {
            $basis = $costRow['allocation_basis'];
            $amount = round((float) $costRow['base_amount'], 2);
            $weights = $this->allocationWeights($items, $basis);
            $weightTotal = round((float) $weights->sum(), 6);
            $allocated = 0;
            $lastIndex = $items->keys()->last();

            $items = $items->map(function ($item, $index) use ($amount, $weights, $weightTotal, $lastIndex, &$allocated) {
                $share = 0;

                if ($weightTotal > 0) {
                    $share = $index === $lastIndex ? round($amount - $allocated, 2) : round($amount * ((float) $weights->get($index) / $weightTotal), 2);
                }

                $allocated = round($allocated + $share, 2);
                $item['allocated_cost_amount'] = round((float) $item['allocated_cost_amount'] + $share, 2);
                $item['landed_total_amount'] = round((float) $item['base_goods_amount'] + (float) $item['allocated_cost_amount'], 2);
                $item['landed_unit_cost'] = (float) $item['quantity'] > 0 ? round((float) $item['landed_total_amount'] / (float) $item['quantity'], 6) : 0;
                $item['allocation_weight'] = round((float) $weights->get($index), 6);

                return $item;
            });
        }

        return $items->all();
    }

    private function allocationWeights(Collection $items, string $basis): Collection
    {
        $weights = $items->map(function ($item) use ($basis) {
            return match ($basis) {
                'quantity' => (float) $item['quantity'],
                'manual' => (float) ($item['manual_allocation_amount'] ?: 0),
                default => (float) $item['base_goods_amount'],
            };
        });

        if ($weights->sum() <= 0) {
            return $items->map(fn($item) => (float) $item['base_goods_amount']);
        }

        return $weights;
    }

    private function nextImportNumber(?int $tenantId): string
    {
        $year = verta()->format('Y');
        $base = 'IMP-' . $year . '-';
        $query = ForeignPurchaseOrder::where('import_number', 'like', $base . '%');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $lastNumber = $query->orderByDesc('id')->value('import_number');
        $next = 1;

        if ($lastNumber && preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $base . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function jalaliDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

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

    private function nullableMoney($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->money($value);
    }

    private function foreignMoney($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 4);
    }

    private function quantity($value): float
    {
        return round((float) str_replace(',', '', (string) $value), 3);
    }

    private function rate($value): float
    {
        return max(0.000001, round((float) str_replace(',', '', (string) $value), 6));
    }
}

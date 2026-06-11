<?php

namespace App\Services;

use App\Models\CompanyAssetTaxInvoice;
use App\Models\ContractingProgressStatement;
use App\Models\Pishfactor;
use App\Models\Product;
use App\Models\TaxpayerInvoice;
use App\Models\TaxpayerItemMapping;
use App\Models\TaxpayerSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaxpayerComplianceService
{
    public function __construct(private NumberingService $numberingService) {}

    public function saveSetting(array $payload, $user): TaxpayerSetting
    {
        $tenantId = $this->tenantId($user);
        $organizationId = $this->organizationId($user);

        return TaxpayerSetting::updateOrCreate(
            [
                'id' => Arr::get($payload, 'id'),
            ],
            [
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'title' => Arr::get($payload, 'title') ?: 'تنظیمات سامانه مودیان',
                'send_mode' => Arr::get($payload, 'send_mode', 'trusted_company'),
                'environment' => Arr::get($payload, 'environment', 'sandbox'),
                'memory_id' => Arr::get($payload, 'memory_id'),
                'branch_tax_code' => Arr::get($payload, 'branch_tax_code'),
                'economic_number' => Arr::get($payload, 'economic_number'),
                'seller_national_id' => Arr::get($payload, 'seller_national_id'),
                'seller_postal_code' => Arr::get($payload, 'seller_postal_code'),
                'endpoint_url' => Arr::get($payload, 'endpoint_url'),
                'trusted_company_name' => Arr::get($payload, 'trusted_company_name'),
                'certificate_alias' => Arr::get($payload, 'certificate_alias'),
                'auto_send' => (bool) Arr::get($payload, 'auto_send', false),
                'is_active' => (bool) Arr::get($payload, 'is_active', true),
                'description' => Arr::get($payload, 'description'),
                'updated_by' => $user?->id,
                'created_by' => $user?->id,
            ]
        );
    }

    public function saveMapping(array $payload, $user): TaxpayerItemMapping
    {
        $tenantId = $this->tenantId($user);
        $product = !empty($payload['product_id']) ? Product::find($payload['product_id']) : null;

        return TaxpayerItemMapping::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'product_id' => Arr::get($payload, 'product_id'),
                'local_type' => Arr::get($payload, 'local_type', 'product'),
                'local_code' => Arr::get($payload, 'local_code') ?: $product?->sku,
            ],
            [
                'organization_id' => $this->organizationId($user),
                'local_title' => Arr::get($payload, 'local_title') ?: ($product?->display_name ?: $product?->title ?: 'خدمت/کالای مالیاتی'),
                'tax_item_id' => Arr::get($payload, 'tax_item_id'),
                'tax_item_title' => Arr::get($payload, 'tax_item_title'),
                'measurement_unit_code' => Arr::get($payload, 'measurement_unit_code'),
                'invoice_pattern' => Arr::get($payload, 'invoice_pattern', 'sales'),
                'default_tax_rate' => round((float) Arr::get($payload, 'default_tax_rate', 0), 4),
                'is_active' => (bool) Arr::get($payload, 'is_active', true),
                'description' => Arr::get($payload, 'description'),
                'created_by' => $user?->id,
            ]
        );
    }

    public function prepareFromSales(Pishfactor $factor, array $payload, $user): TaxpayerInvoice
    {
        $factor->loadMissing(['customer', 'items.product', 'items.taxRate']);

        if (!in_array((int) $factor->status, [1, 4], true)) {
            throw ValidationException::withMessages(['pishfactor_id' => 'فقط فاکتور قطعی یا تکمیل شده برای سامانه مودیان آماده می شود.']);
        }

        $tenantId = $factor->tenant_id ?: $factor->tenants_id ?: $this->tenantId($user);
        $organizationId = $factor->organization_id ?: $this->organizationId($user);
        $setting = $this->activeSetting($tenantId, $organizationId, $user);
        $issueDate = Arr::get($payload, 'issue_date_en') ?: $factor->recive_date_en ?: optional($factor->created_at)->toDateString() ?: now()->toDateString();
        $invoiceSubject = in_array($factor->sales_document_type, ['return', 'sales_return', 'credit_note'], true) ? 'return' : 'main';
        $invoicePattern = $invoiceSubject === 'return' ? 'sales_return' : 'sales';
        $lines = $this->salesLines($factor, $tenantId, $organizationId);

        return $this->storePreparedInvoice([
            'setting' => $setting,
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'voucher_id' => null,
            'customer_id' => $factor->customer_id,
            'source_type' => Pishfactor::class,
            'source_id' => $factor->id,
            'source_number' => (string) $factor->invoiceID,
            'invoice_subject' => $invoiceSubject,
            'invoice_pattern' => $invoicePattern,
            'invoice_type' => 'type_1',
            'issue_date_en' => $issueDate,
            'buyer' => $this->buyerFromCustomer($factor->customer),
            'lines' => $lines,
            'invoice_number' => Arr::get($payload, 'invoice_number'),
            'description' => Arr::get($payload, 'description'),
        ], $user);
    }

    public function prepareFromContracting(ContractingProgressStatement $statement, array $payload, $user): TaxpayerInvoice
    {
        $statement->loadMissing(['project.customer', 'items', 'voucher']);

        if ((float) $statement->current_amount <= 0) {
            throw ValidationException::withMessages(['statement_id' => 'صورت وضعیت بدون مبلغ جاری برای مودیان قابل ارسال نیست.']);
        }

        $project = $statement->project;
        $tenantId = $statement->tenant_id ?: $project?->tenant_id ?: $this->tenantId($user);
        $organizationId = $statement->organization_id ?: $project?->organization_id ?: $this->organizationId($user);
        $setting = $this->activeSetting($tenantId, $organizationId, $user);
        $issueDate = Arr::get($payload, 'issue_date_en') ?: optional($statement->statement_date_en)->toDateString() ?: now()->toDateString();
        $lines = $this->contractingLines($statement, $tenantId, $organizationId);

        return $this->storePreparedInvoice([
            'setting' => $setting,
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'voucher_id' => $statement->voucher_id,
            'customer_id' => $project?->customer_id,
            'source_type' => ContractingProgressStatement::class,
            'source_id' => $statement->id,
            'source_number' => $statement->statement_number,
            'invoice_subject' => 'main',
            'invoice_pattern' => 'contracting',
            'invoice_type' => 'type_1',
            'issue_date_en' => $issueDate,
            'buyer' => $this->buyerFromCustomer($project?->customer),
            'lines' => $lines,
            'invoice_number' => Arr::get($payload, 'invoice_number'),
            'description' => Arr::get($payload, 'description'),
        ], $user);
    }

    public function prepareFromAssetInvoice(CompanyAssetTaxInvoice $assetInvoice, array $payload, $user): TaxpayerInvoice
    {
        $assetInvoice->loadMissing(['asset', 'disposal', 'voucher']);

        $tenantId = $assetInvoice->tenant_id ?: $this->tenantId($user);
        $organizationId = $assetInvoice->organization_id ?: $this->organizationId($user);
        $setting = $this->activeSetting($tenantId, $organizationId, $user);
        $issueDate = Arr::get($payload, 'issue_date_en') ?: optional($assetInvoice->issue_date_en)->toDateString() ?: now()->toDateString();
        $taxRate = round((float) $assetInvoice->tax_rate, 4);
        $saleAmount = round((float) $assetInvoice->sale_amount, 2);
        $taxAmount = round((float) $assetInvoice->tax_amount, 2);
        $mapping = $this->mappingForLocal('fixed_asset_sale', $tenantId, 'fixed_asset');
        $lines = [[
            'source_item_id' => $assetInvoice->company_asset_id,
            'product_id' => null,
            'row_number' => '1',
            'item_code' => $assetInvoice->asset_code,
            'item_title' => $assetInvoice->asset_name ?: 'فروش دارایی ثابت',
            'tax_item_id' => $mapping?->tax_item_id ?: 'FIXED-ASSET-SALE',
            'measurement_unit_code' => $mapping?->measurement_unit_code ?: 'C62',
            'quantity' => 1,
            'unit_price' => $saleAmount,
            'gross_amount' => $saleAmount,
            'discount_amount' => 0,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'net_amount' => round($saleAmount + $taxAmount, 2),
            'extra_data' => ['asset_id' => $assetInvoice->company_asset_id],
        ]];

        return $this->storePreparedInvoice([
            'setting' => $setting,
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'voucher_id' => $assetInvoice->voucher_id,
            'customer_id' => null,
            'source_type' => CompanyAssetTaxInvoice::class,
            'source_id' => $assetInvoice->id,
            'source_number' => $assetInvoice->invoice_number,
            'invoice_subject' => 'main',
            'invoice_pattern' => 'fixed_asset',
            'invoice_type' => 'type_1',
            'issue_date_en' => $issueDate,
            'buyer' => [
                'name' => Arr::get($payload, 'buyer_name') ?: $assetInvoice->buyer_name,
                'economic_number' => Arr::get($payload, 'buyer_economic_number') ?: $assetInvoice->buyer_economic_number,
                'national_id' => Arr::get($payload, 'buyer_national_id') ?: $assetInvoice->buyer_national_id,
                'postal_code' => Arr::get($payload, 'buyer_postal_code') ?: $assetInvoice->buyer_postal_code,
                'address' => Arr::get($payload, 'buyer_address') ?: $assetInvoice->buyer_address,
            ],
            'lines' => $lines,
            'invoice_number' => Arr::get($payload, 'invoice_number') ?: $assetInvoice->invoice_number,
            'description' => Arr::get($payload, 'description'),
        ], $user);
    }

    public function updateStatus(TaxpayerInvoice $invoice, array $payload, $user): TaxpayerInvoice
    {
        $status = Arr::get($payload, 'status');

        if (!in_array($status, ['sent', 'failed', 'accepted', 'rejected'], true)) {
            throw ValidationException::withMessages(['status' => 'وضعیت ارسال صورت حساب معتبر نیست.']);
        }

        return DB::transaction(function () use ($invoice, $payload, $user, $status) {
            $before = $invoice->status;
            $updates = [
                'status' => $status,
                'tax_id' => Arr::get($payload, 'tax_id') ?: $invoice->tax_id,
                'reference_number' => Arr::get($payload, 'reference_number') ?: $invoice->reference_number ?: $this->defaultReference($invoice),
                'error_message' => in_array($status, ['failed', 'rejected'], true) ? Arr::get($payload, 'error_message') : null,
                'response_json' => $this->responsePayload($invoice, $payload, $status),
                'updated_by' => $user?->id,
            ];

            if ($status === 'sent') {
                $updates['sent_at'] = now();
                $updates['retry_count'] = (int) $invoice->retry_count + 1;
            }

            if ($status === 'accepted') {
                $updates['accepted_at'] = now();
            }

            $invoice->update($updates);
            $invoice->logs()->create([
                'tenant_id' => $invoice->tenant_id,
                'organization_id' => $invoice->organization_id,
                'action' => $status === 'sent' ? 'send' : 'status_update',
                'status_before' => $before,
                'status_after' => $status,
                'reference_number' => $updates['reference_number'],
                'request_payload' => $invoice->payload_json,
                'response_payload' => $updates['response_json'],
                'message' => Arr::get($payload, 'error_message') ?: 'بروزرسانی وضعیت سامانه مودیان',
                'created_by' => $user?->id,
            ]);

            $this->syncAssetInvoiceStatus($invoice, $updates, $user);

            return $invoice->refresh()->load(['items', 'logs']);
        });
    }

    private function storePreparedInvoice(array $data, $user): TaxpayerInvoice
    {
        $existing = TaxpayerInvoice::with('items')
            ->where('source_type', $data['source_type'])
            ->where('source_id', $data['source_id'])
            ->first();

        if ($existing && in_array($existing->status, ['sent', 'accepted'], true)) {
            return $existing->load(['items', 'logs']);
        }

        $lines = array_values($data['lines']);

        if (empty($lines)) {
            throw ValidationException::withMessages(['items' => 'برای ساخت صورت حساب مودیان حداقل یک ردیف لازم است.']);
        }

        $subtotal = round(array_sum(array_column($lines, 'gross_amount')), 2);
        $discount = round(array_sum(array_column($lines, 'discount_amount')), 2);
        $tax = round(array_sum(array_column($lines, 'tax_amount')), 2);
        $total = round(array_sum(array_column($lines, 'net_amount')), 2);
        $setting = $data['setting'];
        $issueDate = $data['issue_date_en'];

        return DB::transaction(function () use ($data, $user, $existing, $lines, $subtotal, $discount, $tax, $total, $setting, $issueDate) {
            $attributes = [
                'taxpayer_setting_id' => $setting?->id,
                'voucher_id' => $data['voucher_id'],
                'customer_id' => $data['customer_id'],
                'tenant_id' => $data['tenant_id'],
                'organization_id' => $data['organization_id'],
                'invoice_number' => $data['invoice_number'] ?: $existing?->invoice_number ?: $this->numberingService->nextDocumentNumber('taxpayer_invoice', 'TAX', $data['tenant_id'], $data['organization_id'], $issueDate),
                'source_type' => $data['source_type'],
                'source_id' => $data['source_id'],
                'source_number' => $data['source_number'],
                'invoice_subject' => $data['invoice_subject'],
                'invoice_pattern' => $data['invoice_pattern'],
                'invoice_type' => $data['invoice_type'],
                'issue_date_en' => $issueDate,
                'issue_date_fa' => $this->jalaliDate($issueDate),
                'status' => 'draft',
                'send_mode' => $setting?->send_mode,
                'memory_id' => $setting?->memory_id,
                'branch_tax_code' => $setting?->branch_tax_code,
                'buyer_name' => Arr::get($data, 'buyer.name'),
                'buyer_economic_number' => Arr::get($data, 'buyer.economic_number'),
                'buyer_national_id' => Arr::get($data, 'buyer.national_id'),
                'buyer_postal_code' => Arr::get($data, 'buyer.postal_code'),
                'buyer_address' => Arr::get($data, 'buyer.address'),
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'prepared_at' => now(),
                'error_message' => null,
                'updated_by' => $user?->id,
            ];
            $attributes['payload_json'] = $this->payload($attributes, $lines, $setting, Arr::get($data, 'description'));

            if ($existing) {
                $existing->update($attributes);
                $invoice = $existing->refresh();
                $invoice->items()->delete();
            } else {
                $attributes['created_by'] = $user?->id;
                $invoice = TaxpayerInvoice::create($attributes);
            }

            foreach ($lines as $line) {
                $invoice->items()->create($line + [
                    'tenant_id' => $data['tenant_id'],
                    'organization_id' => $data['organization_id'],
                ]);
            }

            $invoice->logs()->create([
                'tenant_id' => $data['tenant_id'],
                'organization_id' => $data['organization_id'],
                'action' => 'prepare',
                'status_before' => $existing?->status,
                'status_after' => 'draft',
                'request_payload' => $attributes['payload_json'],
                'message' => 'پیش نویس صورت حساب مودیان آماده شد.',
                'created_by' => $user?->id,
            ]);

            return $invoice->refresh()->load(['items', 'logs']);
        });
    }

    private function salesLines(Pishfactor $factor, ?int $tenantId, ?int $organizationId): array
    {
        return $factor->items->values()->map(function ($item, $index) use ($tenantId, $organizationId) {
            $product = $item->product;
            $packQuantity = (float) ($item->pack ?: 0) * (float) ($product?->pack_items ?: 1);
            $quantity = round($packQuantity + (float) ($item->tedad ?: 0), 4) ?: 1;
            $unitPrice = round((float) ($item->price ?: $product?->price ?: 0), 2);
            $gross = round($quantity * $unitPrice, 2);
            $discount = round((float) ($item->discount_amount ?: $item->discount ?: 0), 2);
            $taxRate = round((float) ($item->taxRate?->rate ?: $product?->tax ?: 0), 4);
            $taxAmount = round((float) ($item->tax_amount ?: max(0, $gross - $discount) * $taxRate / 100), 2);
            $net = round((float) ($item->line_total ?: ($gross - $discount + $taxAmount)), 2);
            $mapping = $product ? $this->mappingForProduct($product->id, $tenantId) : null;

            return [
                'source_item_id' => $item->id,
                'product_id' => $product?->id,
                'row_number' => (string) ($index + 1),
                'item_code' => $product?->sku ?: (string) $product?->id,
                'item_title' => $product?->display_name ?: $product?->title ?: 'ردیف فروش',
                'tax_item_id' => $mapping?->tax_item_id ?: ($product?->sku ?: 'PR-' . $product?->id),
                'measurement_unit_code' => $mapping?->measurement_unit_code ?: 'C62',
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'gross_amount' => $gross,
                'discount_amount' => $discount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'net_amount' => $net,
                'extra_data' => ['tenant_id' => $tenantId, 'organization_id' => $organizationId],
            ];
        })->all();
    }

    private function contractingLines(ContractingProgressStatement $statement, ?int $tenantId, ?int $organizationId): array
    {
        $taxRate = (float) $statement->current_amount > 0 ? round((float) $statement->tax_amount * 100 / (float) $statement->current_amount, 4) : 0;
        $mapping = $this->mappingForLocal('contracting_progress', $tenantId, 'service');

        return $statement->items->values()->map(function ($item, $index) use ($taxRate, $mapping, $tenantId, $organizationId) {
            $gross = round((float) $item->gross_amount, 2);
            $taxAmount = round($gross * $taxRate / 100, 2);

            return [
                'source_item_id' => $item->id,
                'product_id' => null,
                'row_number' => (string) ($index + 1),
                'item_code' => $item->item_code,
                'item_title' => $item->title,
                'tax_item_id' => $mapping?->tax_item_id ?: 'CONTRACTING-PROGRESS',
                'measurement_unit_code' => $mapping?->measurement_unit_code ?: 'C62',
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'gross_amount' => $gross,
                'discount_amount' => 0,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'net_amount' => round($gross + $taxAmount, 2),
                'extra_data' => ['tenant_id' => $tenantId, 'organization_id' => $organizationId, 'unit' => $item->unit],
            ];
        })->all();
    }

    private function activeSetting(?int $tenantId, ?int $organizationId, $user): TaxpayerSetting
    {
        $setting = TaxpayerSetting::query()
            ->where('is_active', true)
            ->when($tenantId, fn($query) => $query->where('tenant_id', $tenantId))
            ->when($organizationId, fn($query) => $query->where(function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId)->orWhereNull('organization_id');
            }))
            ->orderByRaw('organization_id is null')
            ->latest('id')
            ->first();

        if ($setting) {
            return $setting;
        }

        return TaxpayerSetting::create([
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'title' => 'تنظیمات پیش فرض سامانه مودیان',
            'send_mode' => 'trusted_company',
            'environment' => 'sandbox',
            'memory_id' => 'MEM-' . ($tenantId ?: 'GLOBAL'),
            'is_active' => true,
            'created_by' => $user?->id,
        ]);
    }

    private function mappingForProduct(?int $productId, ?int $tenantId): ?TaxpayerItemMapping
    {
        if (!$productId) {
            return null;
        }

        return TaxpayerItemMapping::where('product_id', $productId)
            ->where('is_active', true)
            ->when($tenantId, fn($query) => $query->where('tenant_id', $tenantId))
            ->latest('id')
            ->first();
    }

    private function mappingForLocal(string $localCode, ?int $tenantId, string $localType): ?TaxpayerItemMapping
    {
        return TaxpayerItemMapping::where('local_code', $localCode)
            ->where('local_type', $localType)
            ->where('is_active', true)
            ->when($tenantId, fn($query) => $query->where('tenant_id', $tenantId))
            ->latest('id')
            ->first();
    }

    private function buyerFromCustomer($customer): array
    {
        return [
            'name' => $customer?->name,
            'economic_number' => $customer?->economic_number,
            'national_id' => $customer?->national_id,
            'postal_code' => $customer?->postal_code,
            'address' => $customer?->address ?: $customer?->store_address,
        ];
    }

    private function payload(array $invoice, array $lines, ?TaxpayerSetting $setting, ?string $description): array
    {
        return [
            'header' => [
                'invoice_number' => $invoice['invoice_number'],
                'invoice_subject' => $invoice['invoice_subject'],
                'invoice_pattern' => $invoice['invoice_pattern'],
                'invoice_type' => $invoice['invoice_type'],
                'issue_date' => $invoice['issue_date_en'],
                'memory_id' => $invoice['memory_id'],
                'branch_tax_code' => $invoice['branch_tax_code'],
                'send_mode' => $invoice['send_mode'],
                'description' => $description,
            ],
            'seller' => [
                'tenant_id' => $invoice['tenant_id'],
                'organization_id' => $invoice['organization_id'],
                'economic_number' => $setting?->economic_number,
                'national_id' => $setting?->seller_national_id,
                'postal_code' => $setting?->seller_postal_code,
            ],
            'buyer' => [
                'name' => $invoice['buyer_name'],
                'economic_number' => $invoice['buyer_economic_number'],
                'national_id' => $invoice['buyer_national_id'],
                'postal_code' => $invoice['buyer_postal_code'],
                'address' => $invoice['buyer_address'],
            ],
            'amounts' => [
                'subtotal_amount' => $invoice['subtotal_amount'],
                'discount_amount' => $invoice['discount_amount'],
                'tax_amount' => $invoice['tax_amount'],
                'total_amount' => $invoice['total_amount'],
            ],
            'items' => $lines,
            'trace' => [
                'source_type' => $invoice['source_type'],
                'source_id' => $invoice['source_id'],
                'source_number' => $invoice['source_number'],
                'voucher_id' => $invoice['voucher_id'],
            ],
        ];
    }

    private function responsePayload(TaxpayerInvoice $invoice, array $payload, string $status): array
    {
        return [
            'status' => $status,
            'tax_id' => Arr::get($payload, 'tax_id') ?: $invoice->tax_id,
            'reference_number' => Arr::get($payload, 'reference_number') ?: $invoice->reference_number ?: $this->defaultReference($invoice),
            'message' => Arr::get($payload, 'error_message') ?: 'وضعیت صورت حساب ثبت شد.',
            'recorded_at' => now()->toDateTimeString(),
        ];
    }

    private function syncAssetInvoiceStatus(TaxpayerInvoice $invoice, array $updates, $user): void
    {
        if ($invoice->source_type !== CompanyAssetTaxInvoice::class) {
            return;
        }

        $assetInvoice = CompanyAssetTaxInvoice::find($invoice->source_id);

        if (!$assetInvoice) {
            return;
        }

        $assetInvoice->update([
            'status' => $updates['status'],
            'tax_id' => $updates['tax_id'],
            'reference_number' => $updates['reference_number'],
            'response_json' => $updates['response_json'],
            'error_message' => $updates['error_message'],
            'sent_at' => $updates['status'] === 'sent' ? now() : $assetInvoice->sent_at,
            'accepted_at' => $updates['status'] === 'accepted' ? now() : $assetInvoice->accepted_at,
            'retry_count' => $updates['status'] === 'sent' ? (int) $assetInvoice->retry_count + 1 : $assetInvoice->retry_count,
            'updated_by' => $user?->id,
        ]);
    }

    private function defaultReference(TaxpayerInvoice $invoice): string
    {
        return 'REF-' . $invoice->invoice_number . '-' . str_pad((string) ((int) $invoice->retry_count + 1), 2, '0', STR_PAD_LEFT);
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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxpayerInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'taxpayer_invoice_id',
        'source_item_id',
        'product_id',
        'tenant_id',
        'organization_id',
        'row_number',
        'item_code',
        'item_title',
        'tax_item_id',
        'measurement_unit_code',
        'quantity',
        'unit_price',
        'gross_amount',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'net_amount',
        'extra_data',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'extra_data' => 'array',
    ];

    public function invoice()
    {
        return $this->belongsTo(TaxpayerInvoice::class, 'taxpayer_invoice_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

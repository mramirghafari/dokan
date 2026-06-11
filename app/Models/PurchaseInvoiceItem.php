<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseInvoiceItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'purchase_invoice_id',
        'purchase_order_item_id',
        'product_id',
        'quantity',
        'order_unit_price',
        'invoice_unit_price',
        'goods_amount',
        'tax_amount',
        'total_amount',
        'price_variance_amount',
        'match_status',
        'description',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'order_unit_price' => 'decimal:2',
        'invoice_unit_price' => 'decimal:2',
        'goods_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'price_variance_amount' => 'decimal:2',
    ];

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

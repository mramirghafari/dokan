<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForeignPurchaseOrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'foreign_purchase_order_id',
        'purchase_order_item_id',
        'tenant_id',
        'organization_id',
        'product_id',
        'quantity',
        'foreign_unit_price',
        'foreign_total_amount',
        'base_goods_amount',
        'allocated_cost_amount',
        'landed_total_amount',
        'landed_unit_cost',
        'manual_allocation_amount',
        'allocation_weight',
        'description',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'foreign_unit_price' => 'decimal:6',
        'foreign_total_amount' => 'decimal:4',
        'base_goods_amount' => 'decimal:2',
        'allocated_cost_amount' => 'decimal:2',
        'landed_total_amount' => 'decimal:2',
        'landed_unit_cost' => 'decimal:6',
        'manual_allocation_amount' => 'decimal:2',
        'allocation_weight' => 'decimal:6',
    ];

    public function foreignPurchaseOrder()
    {
        return $this->belongsTo(ForeignPurchaseOrder::class);
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

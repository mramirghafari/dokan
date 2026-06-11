<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionOrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'production_order_id',
        'product_id',
        'warehouse_location_id',
        'line_type',
        'quantity',
        'unit_cost',
        'total_cost',
        'movement_id',
        'description',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function movement()
    {
        return $this->belongsTo(InventoryMovement::class, 'movement_id');
    }
}

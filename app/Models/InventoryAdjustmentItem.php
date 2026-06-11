<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryAdjustmentItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'inventory_adjustment_id',
        'product_id',
        'warehouse_location_id',
        'system_quantity',
        'counted_quantity',
        'difference_quantity',
        'unit_cost',
        'amount',
        'movement_id',
        'description',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:3',
        'counted_quantity' => 'decimal:3',
        'difference_quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function adjustment()
    {
        return $this->belongsTo(\App\Models\InventoryAdjustment::class, 'inventory_adjustment_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouseLocation()
    {
        return $this->belongsTo(WarehouseLocation::class, 'warehouse_location_id');
    }

    public function movement()
    {
        return $this->belongsTo(InventoryMovement::class, 'movement_id');
    }
}

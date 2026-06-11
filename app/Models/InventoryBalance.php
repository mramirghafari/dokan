<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryBalance extends Model
{
    use HasFactory, HasOrganizationFilter;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'store_id',
        'warehouse_location_id',
        'product_id',
        'quantity',
        'quantity_sub_unit',
        'unit_cost',
        'total_cost',
        'reserved_quantity',
        'minimum_quantity',
        'maximum_quantity',
        'last_movement_at',
        'last_costed_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'quantity_sub_unit' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'reserved_quantity' => 'decimal:3',
        'minimum_quantity' => 'decimal:3',
        'maximum_quantity' => 'decimal:3',
        'last_movement_at' => 'datetime',
        'last_costed_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function warehouseLocation()
    {
        return $this->belongsTo(WarehouseLocation::class, 'warehouse_location_id');
    }
}

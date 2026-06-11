<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTraceBalance extends Model
{
    use HasFactory, HasOrganizationFilter;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'store_id',
        'warehouse_location_id',
        'product_id',
        'batch_no',
        'lot_no',
        'serial_no',
        'manufactured_at',
        'expiry_date',
        'color',
        'size',
        'quality_grade',
        'weight',
        'trace_status',
        'quantity',
        'quantity_sub_unit',
        'reserved_quantity',
        'unit_cost',
        'total_cost',
        'last_movement_at',
        'tracking_notes',
    ];

    protected $casts = [
        'manufactured_at' => 'date',
        'expiry_date' => 'date',
        'quantity' => 'decimal:3',
        'quantity_sub_unit' => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'last_movement_at' => 'datetime',
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

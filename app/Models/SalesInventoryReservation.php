<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesInventoryReservation extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;

    protected $fillable = ['tenant_id', 'organization_id', 'pishfactor_id', 'pish_factor_item_id', 'store_id', 'warehouse_location_id', 'product_id', 'batch_no', 'lot_no', 'serial_no', 'manufactured_at', 'expiry_date', 'color', 'size', 'quality_grade', 'quantity', 'available_quantity_snapshot', 'shortage_quantity', 'status', 'reserved_at', 'released_at', 'release_reason', 'created_by'];

    protected $casts = [
        'manufactured_at' => 'date',
        'expiry_date' => 'date',
        'quantity' => 'decimal:3',
        'available_quantity_snapshot' => 'decimal:3',
        'shortage_quantity' => 'decimal:3',
        'reserved_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function pishfactor()
    {
        return $this->belongsTo(Pishfactor::class, 'pishfactor_id');
    }

    public function item()
    {
        return $this->belongsTo(PishFactorItems::class, 'pish_factor_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function warehouseLocation()
    {
        return $this->belongsTo(WarehouseLocation::class, 'warehouse_location_id');
    }
}

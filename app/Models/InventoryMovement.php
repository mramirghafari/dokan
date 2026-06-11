<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory, HasOrganizationFilter;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'store_id',
        'warehouse_location_id',
        'product_id',
        'receipt_id',
        'transfer_id',
        'source_id',
        'source_type',
        'movement_type',
        'direction',
        'quantity',
        'quantity_sub_unit',
        'unit_cost',
        'total_cost',
        'valuation_method',
        'unit',
        'reference_no',
        'occurred_at',
        'description',
        'batch_no',
        'lot_no',
        'serial_no',
        'manufactured_at',
        'expiry_date',
        'color',
        'size',
        'quality_grade',
        'weight',
        'tracking_notes',
        'trace_status',
        'user_id',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'manufactured_at' => 'date',
        'expiry_date' => 'date',
        'quantity' => 'decimal:3',
        'quantity_sub_unit' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'weight' => 'decimal:3',
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

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }

    public function transfer()
    {
        return $this->belongsTo(Transfer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function traceBalance()
    {
        return $this->hasOne(InventoryTraceBalance::class, 'product_id', 'product_id')
            ->whereColumn('inventory_trace_balances.store_id', 'inventory_movements.store_id')
            ->whereColumn('inventory_trace_balances.warehouse_location_id', 'inventory_movements.warehouse_location_id')
            ->whereColumn('inventory_trace_balances.batch_no', 'inventory_movements.batch_no')
            ->whereColumn('inventory_trace_balances.serial_no', 'inventory_movements.serial_no');
    }
}

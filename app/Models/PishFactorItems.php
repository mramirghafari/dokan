<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PishFactorItems extends Model
{
    use HasFactory;
    protected $fillable = ['pishfactor_id', 'tenant_id', 'pr_id', 'unit_id', 'warehouse_location_id', 'tax_rate_id', 'pack', 'tedad', 'price', 'discount', 'discount_amount', 'tax_amount', 'line_total', 'reserved_quantity', 'batch_no', 'lot_no', 'serial_no', 'manufactured_at', 'expiry_date', 'color', 'size', 'quality_grade', 'weight', 'tracking_notes'];

    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class, 'pr_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }

    public function reservations()
    {
        return $this->hasMany(SalesInventoryReservation::class, 'pish_factor_item_id');
    }

    public function pishfactor()
    {
        return $this->belongsTo(Pishfactor::class, 'pishfactor_id');
    }
}

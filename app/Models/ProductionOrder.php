<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionOrder extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'store_id',
        'production_formula_id',
        'user_id',
        'number',
        'date_fa',
        'date_en',
        'status',
        'planned_quantity',
        'actual_quantity',
        'material_cost',
        'finished_unit_cost',
        'approved_at',
        'approved_by',
        'canceled_at',
        'canceled_by',
        'notes',
    ];

    protected $casts = [
        'date_en' => 'date',
        'planned_quantity' => 'decimal:3',
        'actual_quantity' => 'decimal:3',
        'material_cost' => 'decimal:2',
        'finished_unit_cost' => 'decimal:2',
        'approved_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(ProductionOrderItem::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function formula()
    {
        return $this->belongsTo(ProductionFormula::class, 'production_formula_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function accountingVoucher()
    {
        return $this->hasOne(Voucher::class, 'source_id')->where('source_type', self::class)->where('document_type', 'production_cost');
    }
}

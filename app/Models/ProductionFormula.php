<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionFormula extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'product_id',
        'code',
        'title',
        'version',
        'base_quantity',
        'standard_waste_percent',
        'is_active',
        'description',
    ];

    protected $casts = [
        'base_quantity' => 'decimal:3',
        'standard_waste_percent' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function items()
    {
        return $this->hasMany(ProductionFormulaItem::class);
    }
}

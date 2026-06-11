<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionFormulaItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'production_formula_id',
        'material_product_id',
        'store_id',
        'quantity',
        'waste_percent',
        'sort_order',
        'description',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'waste_percent' => 'decimal:3',
    ];

    public function formula()
    {
        return $this->belongsTo(ProductionFormula::class, 'production_formula_id');
    }

    public function materialProduct()
    {
        return $this->belongsTo(Product::class, 'material_product_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}

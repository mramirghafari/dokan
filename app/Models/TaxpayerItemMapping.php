<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxpayerItemMapping extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'product_id',
        'local_type',
        'local_code',
        'local_title',
        'tax_item_id',
        'tax_item_title',
        'measurement_unit_code',
        'invoice_pattern',
        'default_tax_rate',
        'is_active',
        'description',
        'created_by',
    ];

    protected $casts = [
        'default_tax_rate' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

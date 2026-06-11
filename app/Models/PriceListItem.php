<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceListItem extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;

    protected $fillable = ['tenant_id', 'organization_id', 'price_list_id', 'product_id', 'unit_id', 'price', 'discount', 'tax', 'consumer_fee', 'valid_from', 'valid_to', 'isActive'];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'consumer_fee' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'isActive' => 'boolean',
    ];

    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}

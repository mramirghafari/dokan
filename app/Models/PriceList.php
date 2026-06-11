<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceList extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;

    protected $fillable = ['tenant_id', 'organization_id', 'price_type_id', 'code', 'title', 'currency_type', 'valid_from', 'valid_to', 'status', 'is_default', 'description'];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_default' => 'boolean',
    ];

    public function priceType()
    {
        return $this->belongsTo(PriceType::class);
    }

    public function items()
    {
        return $this->hasMany(PriceListItem::class);
    }
}

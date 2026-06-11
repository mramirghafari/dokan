<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceType extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;

    protected $fillable = ['tenant_id', 'organization_id', 'code', 'title', 'price_basis', 'priority', 'is_default', 'isActive', 'description'];

    protected $casts = [
        'is_default' => 'boolean',
        'isActive' => 'boolean',
    ];

    public function priceLists()
    {
        return $this->hasMany(PriceList::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationFilter;


class Brand extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;
    protected $fillable = ['title', 'isActive', 'organization_id', 'tenant_id', 'store_id'];

    public function product()
    {
        return $this->belongsToMany(Product::class);
    }
}

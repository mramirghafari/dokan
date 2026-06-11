<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationFilter;

class Category extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;
    protected $fillable = ['title', 'description', 'parent_id', 'isActive', 'organization_id', 'tenant_id', 'store_id'];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function repairs()
    {
        return $this->belongsToMany(Repair::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationFilter;


class Material extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;
    protected $fillable = ['name', 'unit', 'sub_unit', 'price', 'entity', 'entity_sub_unit', 'pack_items', 'pack_weight', 'pack_weight_unit', 'material_store_id', 'organization_id', 'tenant_id', 'updated_at'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationFilter;


class City extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;
    protected $fillable = ['name', 'organization_id', 'tenant_id', 'created_at', 'updated_at', 'deleted_at'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}

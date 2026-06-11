<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'key',
        'title',
        'description',
        'type',
        'default_value',
        'is_active',
        'sort_order',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function tenantFeatures()
    {
        return $this->hasMany(TenantFeature::class);
    }

    public function organizationFeatures()
    {
        return $this->hasMany(OrganizationFeature::class);
    }
}

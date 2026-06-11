<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'feature_id',
        'key',
        'is_enabled',
        'value',
        'is_locked',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenants::class, 'tenant_id');
    }

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }
}

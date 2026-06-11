<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationScope extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'scopeable_type',
        'scopeable_id',
        'is_primary',
        'source',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeable()
    {
        return $this->morphTo();
    }
}

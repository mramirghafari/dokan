<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
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

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }
}

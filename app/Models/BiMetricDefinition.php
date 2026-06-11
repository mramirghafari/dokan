<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiMetricDefinition extends Model
{
    use HasFactory;

    protected $fillable = ['metric_key', 'title', 'domain', 'unit', 'refresh_frequency', 'owner_role', 'permission_key', 'formula', 'source', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

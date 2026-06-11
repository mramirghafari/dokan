<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiDailySummary extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter;

    protected $fillable = ['summary_date', 'tenant_id', 'organization_id', 'domain', 'metric_key', 'dimension_type', 'dimension_id', 'value', 'comparison_value', 'metadata', 'refreshed_at'];

    protected $casts = [
        'summary_date' => 'date',
        'value' => 'decimal:4',
        'comparison_value' => 'decimal:4',
        'metadata' => 'array',
        'refreshed_at' => 'datetime',
    ];
}

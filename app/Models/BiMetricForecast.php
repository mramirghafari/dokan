<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiMetricForecast extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter;

    protected $fillable = ['tenant_id', 'organization_id', 'forecast_date', 'domain', 'metric_key', 'horizon_days', 'method', 'actual_value', 'forecast_value', 'lower_bound', 'upper_bound', 'confidence_score', 'trend_direction', 'metadata', 'generated_at'];

    protected $casts = [
        'forecast_date' => 'date',
        'actual_value' => 'decimal:4',
        'forecast_value' => 'decimal:4',
        'lower_bound' => 'decimal:4',
        'upper_bound' => 'decimal:4',
        'confidence_score' => 'decimal:2',
        'metadata' => 'array',
        'generated_at' => 'datetime',
    ];
}

<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BiAlertRule extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'domain',
        'metric_key',
        'rule_type',
        'operator',
        'threshold_value',
        'severity',
        'lookback_days',
        'comparison_days',
        'title',
        'suggestion',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'threshold_value' => 'decimal:4',
        'lookback_days' => 'integer',
        'comparison_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public const TYPES = ['threshold' => 'آستانه', 'drop' => 'افت غیرعادی', 'spike' => 'رشد غیرعادی'];
    public const SEVERITIES = ['low' => 'کم', 'medium' => 'متوسط', 'high' => 'مهم', 'critical' => 'بحرانی'];

    public function typeText(): string
    {
        return self::TYPES[$this->rule_type] ?? $this->rule_type;
    }

    public function severityText(): string
    {
        return self::SEVERITIES[$this->severity] ?? $this->severity;
    }
}

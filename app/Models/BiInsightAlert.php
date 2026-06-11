<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiInsightAlert extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter;

    protected $fillable = ['tenant_id', 'organization_id', 'bi_alert_rule_id', 'summary_date', 'domain', 'metric_key', 'alert_type', 'severity', 'status', 'title', 'message', 'current_value', 'baseline_value', 'deviation_percent', 'suggestion', 'metadata', 'detected_at', 'acknowledged_at', 'acknowledged_by', 'resolved_at', 'resolved_by'];

    protected $casts = [
        'summary_date' => 'date',
        'current_value' => 'decimal:4',
        'baseline_value' => 'decimal:4',
        'deviation_percent' => 'decimal:2',
        'metadata' => 'array',
        'detected_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public const STATUSES = ['open' => 'باز', 'acknowledged' => 'در حال پیگیری', 'resolved' => 'رسیدگی شده'];

    public function rule()
    {
        return $this->belongsTo(BiAlertRule::class, 'bi_alert_rule_id');
    }
}

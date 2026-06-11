<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BiReportSchedule extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'bi_report_template_id',
        'title',
        'frequency',
        'delivery_format',
        'recipients',
        'channels',
        'next_run_at',
        'last_run_at',
        'last_status',
        'is_active',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'recipients' => 'array',
        'channels' => 'array',
        'next_run_at' => 'datetime',
        'last_run_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(BiReportTemplate::class, 'bi_report_template_id');
    }

    public function deliveries()
    {
        return $this->hasMany(BiReportDelivery::class, 'bi_report_schedule_id');
    }
}

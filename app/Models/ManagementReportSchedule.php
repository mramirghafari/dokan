<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManagementReportSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'management_report_template_id',
        'title',
        'frequency',
        'delivery_format',
        'recipients_json',
        'filters_json',
        'next_run_at',
        'last_run_at',
        'last_status',
        'is_active',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'recipients_json' => 'array',
        'filters_json' => 'array',
        'next_run_at' => 'datetime',
        'last_run_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(ManagementReportTemplate::class, 'management_report_template_id');
    }
}

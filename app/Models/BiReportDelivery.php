<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiReportDelivery extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter;

    protected $fillable = [
        'bi_report_schedule_id',
        'bi_report_template_id',
        'tenant_id',
        'organization_id',
        'delivery_token',
        'title',
        'dataset_key',
        'delivery_format',
        'recipient_count',
        'channels',
        'filters',
        'output_snapshot',
        'row_count',
        'status',
        'message',
        'generated_by',
        'generated_at',
        'expires_at',
    ];

    protected $casts = [
        'recipient_count' => 'integer',
        'channels' => 'array',
        'filters' => 'array',
        'output_snapshot' => 'array',
        'row_count' => 'integer',
        'generated_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(BiReportSchedule::class, 'bi_report_schedule_id');
    }

    public function template()
    {
        return $this->belongsTo(BiReportTemplate::class, 'bi_report_template_id');
    }
}

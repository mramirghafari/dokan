<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiReportRun extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter;

    protected $fillable = [
        'template_id',
        'tenant_id',
        'organization_id',
        'dataset_key',
        'requested_by',
        'status',
        'row_count',
        'filters',
        'output_summary',
        'started_at',
        'finished_at',
        'message',
    ];

    protected $casts = [
        'row_count' => 'integer',
        'filters' => 'array',
        'output_summary' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}

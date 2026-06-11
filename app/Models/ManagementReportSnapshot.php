<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagementReportSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'report_key',
        'title',
        'period_start',
        'period_end',
        'created_by',
        'filters',
        'metrics',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'filters' => 'array',
        'metrics' => 'array',
    ];
}

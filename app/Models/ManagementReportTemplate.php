<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManagementReportTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'template_key',
        'title',
        'sections_json',
        'filters_json',
        'chart_settings_json',
        'default_export_format',
        'is_shared',
        'is_active',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'sections_json' => 'array',
        'filters_json' => 'array',
        'chart_settings_json' => 'array',
        'is_shared' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function schedules()
    {
        return $this->hasMany(ManagementReportSchedule::class);
    }
}

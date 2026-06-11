<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BiReportTemplate extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'title',
        'dataset_key',
        'dimensions',
        'measures',
        'filters',
        'chart_type',
        'visibility',
        'shared_role_id',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'dimensions' => 'array',
        'measures' => 'array',
        'filters' => 'array',
        'is_active' => 'boolean',
    ];
}

<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiRefreshLog extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter;

    protected $fillable = ['dataset_key', 'tenant_id', 'organization_id', 'status', 'started_at', 'finished_at', 'rows_count', 'message', 'metadata'];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'rows_count' => 'integer',
        'metadata' => 'array',
    ];
}

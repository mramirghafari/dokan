<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmPublicApiRequestLog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'crm_public_api_client_id',
        'tenant_id',
        'organization_id',
        'endpoint',
        'method',
        'external_id',
        'status',
        'ip_address',
        'reference_id',
        'reference_type',
        'payload_snapshot',
        'message',
    ];

    protected $casts = [
        'payload_snapshot' => 'array',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionSyncQueue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'user_id',
        'client_uid',
        'entity_type',
        'entity_id',
        'action',
        'payload_json',
        'status',
        'attempts',
        'conflict_reason',
        'received_at',
        'processed_at',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
}

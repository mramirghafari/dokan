<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmIntegrationSyncLog extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter;

    protected $fillable = [
        'crm_integration_connection_id',
        'tenant_id',
        'organization_id',
        'integration_type',
        'provider',
        'direction',
        'operation',
        'status',
        'external_id',
        'syncable_type',
        'syncable_id',
        'payload_snapshot',
        'response_snapshot',
        'message',
        'attempted_at',
        'synced_at',
        'created_by',
    ];

    protected $casts = [
        'payload_snapshot' => 'array',
        'response_snapshot' => 'array',
        'attempted_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public const STATUSES = [
        'queued' => 'در صف ارسال',
        'synced' => 'همگام شده',
        'skipped' => 'رد شده',
        'failed' => 'ناموفق',
    ];

    public function connection()
    {
        return $this->belongsTo(CrmIntegrationConnection::class, 'crm_integration_connection_id');
    }

    public function syncable()
    {
        return $this->morphTo();
    }

    public function statusText(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}

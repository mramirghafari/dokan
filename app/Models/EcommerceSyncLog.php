<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ecommerce_channel_id',
        'tenant_id',
        'organization_id',
        'direction',
        'entity_type',
        'entity_key',
        'entity_id',
        'action',
        'status',
        'attempts',
        'request_payload',
        'response_payload',
        'message',
        'processed_at',
        'created_by',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function channel()
    {
        return $this->belongsTo(EcommerceChannel::class, 'ecommerce_channel_id');
    }
}

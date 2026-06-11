<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EcommerceChannel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'code',
        'title',
        'platform',
        'base_url',
        'api_token_hash',
        'price_policy',
        'default_store_id',
        'default_visitor_id',
        'default_leader_id',
        'default_payment_method',
        'order_status_policy',
        'auto_create_customer',
        'auto_reserve_inventory',
        'is_active',
        'settings_json',
        'last_sync_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'auto_create_customer' => 'boolean',
        'auto_reserve_inventory' => 'boolean',
        'is_active' => 'boolean',
        'settings_json' => 'array',
        'last_sync_at' => 'datetime',
    ];

    public function productMappings()
    {
        return $this->hasMany(EcommerceProductMapping::class);
    }

    public function orderMappings()
    {
        return $this->hasMany(EcommerceOrderMapping::class);
    }

    public function syncLogs()
    {
        return $this->hasMany(EcommerceSyncLog::class);
    }
}

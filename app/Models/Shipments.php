<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationFilter;

class Shipments extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'owner_id',
        'driver_id',
        'number',
        'tozihat',
        'date_fa',
        'date_en',
        'hours',
        'mabda',
        'origin_lat',
        'origin_lang',
        'status',
        'shipment_status',
        'loading_status',
        'total_orders',
        'total_packs',
        'vehicle_capacity',
        'loaded_at',
        'departed_at',
        'completed_at'
    ];

    protected $casts = [
        'loaded_at' => 'datetime',
        'departed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * 📦 راننده مرتبط با شیپمنت
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id', 'id');
    }

    /**
     * 🚚 حمل‌ونقل‌های مربوط به این شیپمنت
     */
    public function routes()
    {
        return $this->hasMany(\App\Models\ShipmentRoute::class, 'shipment_id', 'id');
    }

    public function events()
    {
        return $this->hasMany(ShipmentEvent::class, 'shipment_id');
    }
}

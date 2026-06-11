<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentRoute extends Model
{
    protected $fillable = [
        'shipment_id',
        'factor_id',
        'stop_type',
        'route_index',
        'origin_lat',
        'origin_lng',
        'destination_lat',
        'destination_lng',
        'distance_meters',
        'duration_minutes',
        'path_json',
        'status',
        'delivery_status',
        'planned_packs',
        'delivered_packs',
        'arrived_at',
        'delivered_at',
        'failed_reason',
        'receiver_name',
        'signature_path',
        'extra_info',
    ];

    protected $casts = [
        'path_json' => 'array',
        'extra_info' => 'array',
        'arrived_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * 🚚 ارتباط با سفر مربوطه
     */
    public function shipment()
    {
        return $this->belongsTo(Shipments::class, 'shipment_id', 'id');
    }

    /**
     * 📦 ارتباط با پیش‌فاکتور مربوطه
     */
    public function pishfactor()
    {
        return $this->belongsTo(\App\Models\Pishfactor::class, 'factor_id', 'id');
    }

    public function events()
    {
        return $this->hasMany(\App\Models\ShipmentEvent::class, 'shipment_route_id');
    }
}

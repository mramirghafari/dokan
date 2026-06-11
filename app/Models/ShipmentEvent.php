<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentEvent extends Model
{
    use HasFactory;

    protected $fillable = ['tenant_id', 'organization_id', 'shipment_id', 'shipment_route_id', 'event_type', 'from_status', 'to_status', 'description', 'created_by'];

    public function shipment()
    {
        return $this->belongsTo(Shipments::class, 'shipment_id');
    }

    public function route()
    {
        return $this->belongsTo(ShipmentRoute::class, 'shipment_route_id');
    }
}

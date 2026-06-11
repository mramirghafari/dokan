<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionMobileOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'pishfactor_id',
        'distribution_visit_plan_id',
        'distribution_visit_stop_id',
        'visitor_id',
        'customer_id',
        'client_order_uid',
        'order_type',
        'sale_mode',
        'payment_method',
        'gross_amount',
        'discount_amount',
        'tax_amount',
        'net_amount',
        'location_lat',
        'location_lng',
        'offline_payload_json',
        'offline_created_at',
        'sync_status',
        'synced_at',
        'conflict_reason',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'location_lat' => 'decimal:8',
        'location_lng' => 'decimal:8',
        'offline_payload_json' => 'array',
        'offline_created_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function pishfactor()
    {
        return $this->belongsTo(Pishfactor::class, 'pishfactor_id');
    }

    public function plan()
    {
        return $this->belongsTo(DistributionVisitPlan::class, 'distribution_visit_plan_id');
    }

    public function stop()
    {
        return $this->belongsTo(DistributionVisitStop::class, 'distribution_visit_stop_id');
    }
}

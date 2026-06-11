<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionVisitStop extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'distribution_visit_plan_id',
        'customer_id',
        'pishfactor_id',
        'sequence',
        'visit_status',
        'planned_at',
        'checked_in_at',
        'checked_out_at',
        'collection_amount',
        'no_order_reason',
        'lat',
        'lng',
        'signature_path',
        'client_uid',
        'extra_json',
    ];

    protected $casts = [
        'planned_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'collection_amount' => 'decimal:2',
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'extra_json' => 'array',
    ];

    public function plan()
    {
        return $this->belongsTo(DistributionVisitPlan::class, 'distribution_visit_plan_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function pishfactor()
    {
        return $this->belongsTo(Pishfactor::class, 'pishfactor_id');
    }
}

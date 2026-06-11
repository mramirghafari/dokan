<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionVisitPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'visitor_id',
        'plan_number',
        'title',
        'route_code',
        'area_id',
        'region_id',
        'planned_date_en',
        'planned_date_fa',
        'sales_mode',
        'status',
        'planned_customers_count',
        'visited_count',
        'ordered_count',
        'no_order_count',
        'collected_amount',
        'description',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'planned_date_en' => 'date',
        'approved_at' => 'datetime',
        'collected_amount' => 'decimal:2',
    ];

    public function visitor()
    {
        return $this->belongsTo(User::class, 'visitor_id');
    }

    public function stops()
    {
        return $this->hasMany(DistributionVisitStop::class)->orderBy('sequence');
    }

    public function mobileOrders()
    {
        return $this->hasMany(DistributionMobileOrder::class);
    }
}

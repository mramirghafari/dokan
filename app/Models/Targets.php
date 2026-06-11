<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationFilter;

class Targets extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;
    protected $fillable = ['leader_id', 'user_id', 'target_price', 'target_type', 'period_type', 'calculation_scope', 'commission_plan_id', 'achievement_threshold_percent', 'bonus_amount', 'penalty_amount', 'settlement_status', 'orders_count', 'min_order_price', 'start_date_fa', 'start_date_en', 'end_date_fa', 'end_date_en', 'status', 'organization_id', 'tenant_id', 'approved_by', 'approved_at', 'closed_at', 'notes', 'updated_at'];

    protected $casts = [
        'approved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function products()
    {
        return $this->hasMany(TargetProducts::class, 'target_id');
    }

    public function commissionPlan()
    {
        return $this->belongsTo(CommissionPlan::class, 'commission_plan_id');
    }

    public function commissionSettlements()
    {
        return $this->hasMany(CommissionSettlement::class, 'target_id');
    }

    public function scopeWithAchievedAmount($query)
    {
        return $query->withSum([], ''); // trick برای initialize
    }

    public function getAchievedAmountAttribute()
    {
        $service = app(\App\Services\TargetProgressService::class);
        $user = $this->user_id;

        $tree = $service->getSubUsersWithFactors($user, $this);


        return $service->totalFactorPrice($tree);
    }
}

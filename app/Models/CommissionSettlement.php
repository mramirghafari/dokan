<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'commission_plan_id',
        'target_id',
        'user_id',
        'period_start',
        'period_end',
        'sales_amount',
        'tax_amount',
        'discount_amount',
        'net_amount',
        'collected_amount',
        'gross_profit_amount',
        'target_amount',
        'achievement_percent',
        'base_commission_amount',
        'tier_commission_amount',
        'bonus_amount',
        'penalty_amount',
        'payable_amount',
        'status',
        'calculated_at',
        'approved_at',
        'approved_by',
        'paid_at',
        'paid_by',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'calculated_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(CommissionPlan::class, 'commission_plan_id');
    }

    public function target()
    {
        return $this->belongsTo(Targets::class, 'target_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lines()
    {
        return $this->hasMany(CommissionSettlementLine::class);
    }
}

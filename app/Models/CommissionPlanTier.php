<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionPlanTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'commission_plan_id',
        'from_achievement_percent',
        'to_achievement_percent',
        'rate_percent',
        'fixed_bonus_amount',
        'amount_per_unit',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function plan()
    {
        return $this->belongsTo(CommissionPlan::class, 'commission_plan_id');
    }
}

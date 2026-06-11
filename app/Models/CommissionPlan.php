<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'code',
        'title',
        'applies_to_role',
        'calculation_base',
        'trigger_status',
        'period_type',
        'requires_target',
        'min_achievement_percent',
        'base_rate_percent',
        'fixed_amount',
        'cap_amount',
        'include_tax',
        'include_discount',
        'valid_from',
        'valid_to',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'requires_target' => 'boolean',
        'include_tax' => 'boolean',
        'include_discount' => 'boolean',
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function tiers()
    {
        return $this->hasMany(CommissionPlanTier::class)->orderBy('sort_order');
    }

    public function settlements()
    {
        return $this->hasMany(CommissionSettlement::class);
    }
}

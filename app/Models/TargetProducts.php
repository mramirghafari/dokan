<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TargetProducts extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['target_id', 'tenant_id', 'pr_id', 'target_type', 'order_count', 'order_price', 'weight_percent', 'commission_rate_percent', 'commission_amount_per_unit', 'achievement_count', 'achievement_amount', 'status', 'notes'];


    public function target()
    {
        return $this->belongsTo(Targets::class, 'target_id');
    }

    public function products()
    {
        return $this->belongsTo(Product::class, 'pr_id');
    }
}

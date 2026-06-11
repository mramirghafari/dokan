<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseApprovalEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'purchase_order_id',
        'event_type',
        'from_status',
        'to_status',
        'order_amount',
        'budget_amount',
        'budget_consumed_amount',
        'budget_status',
        'description',
        'created_by',
    ];

    protected $casts = [
        'order_amount' => 'decimal:2',
        'budget_amount' => 'decimal:2',
        'budget_consumed_amount' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}

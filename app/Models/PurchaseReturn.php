<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturn extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'purchase_order_id',
        'supplier_id',
        'store_id',
        'receipt_id',
        'return_number',
        'return_date_en',
        'return_date_fa',
        'status',
        'total_amount',
        'description',
        'created_by',
    ];

    protected $casts = [
        'return_date_en' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }
}

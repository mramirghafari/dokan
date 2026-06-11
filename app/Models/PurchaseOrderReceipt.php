<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderReceipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'purchase_order_id',
        'receipt_id',
        'receive_number',
        'receive_date_en',
        'receive_date_fa',
        'status',
        'total_amount',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'receive_date_en' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderReceiptItem::class);
    }
}

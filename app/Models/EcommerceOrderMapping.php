<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EcommerceOrderMapping extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ecommerce_channel_id',
        'tenant_id',
        'organization_id',
        'pishfactor_id',
        'customer_id',
        'external_order_id',
        'external_order_number',
        'external_customer_id',
        'order_status',
        'payment_status',
        'delivery_status',
        'gross_amount',
        'discount_amount',
        'shipping_amount',
        'tax_amount',
        'net_amount',
        'payload_json',
        'response_json',
        'sync_status',
        'attempts',
        'conflict_reason',
        'received_at',
        'processed_at',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'payload_json' => 'array',
        'response_json' => 'array',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function channel()
    {
        return $this->belongsTo(EcommerceChannel::class, 'ecommerce_channel_id');
    }

    public function pishfactor()
    {
        return $this->belongsTo(Pishfactor::class, 'pishfactor_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }
}

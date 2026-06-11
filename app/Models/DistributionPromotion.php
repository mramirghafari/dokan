<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionPromotion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'code',
        'title',
        'promotion_type',
        'starts_at',
        'ends_at',
        'customer_segment_id',
        'product_id',
        'min_order_amount',
        'discount_percent',
        'discount_amount',
        'gift_product_id',
        'gift_quantity',
        'max_uses',
        'used_count',
        'status',
        'rules_json',
        'description',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'min_order_amount' => 'decimal:2',
        'discount_percent' => 'decimal:4',
        'discount_amount' => 'decimal:2',
        'gift_quantity' => 'decimal:3',
        'rules_json' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function giftProduct()
    {
        return $this->belongsTo(Product::class, 'gift_product_id');
    }
}

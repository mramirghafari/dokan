<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class PriceLog extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['tenant_id', 'organization_id', 'product_id', 'pr_id', 'price', 'sale_price', 'purchase_price', 'cost_price', 'representative_price', 'wholesale_price', 'consumer_price', 'discount', 'tax', 'fee_masraf', 'price_from_fa', 'price_exp_fa', 'price_from_en', 'price_exp_en', 'change_source', 'user_id', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'representative_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'consumer_price' => 'decimal:2',
        'price_from_en' => 'datetime',
        'price_exp_en' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'pr_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EcommerceProductMapping extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ecommerce_channel_id',
        'tenant_id',
        'organization_id',
        'product_id',
        'external_product_id',
        'external_variant_id',
        'external_sku',
        'sync_direction',
        'price_override',
        'stock_buffer',
        'sync_price',
        'sync_stock',
        'is_active',
        'last_export_payload',
        'last_import_payload',
        'last_synced_at',
    ];

    protected $casts = [
        'price_override' => 'decimal:2',
        'stock_buffer' => 'decimal:4',
        'sync_price' => 'boolean',
        'sync_stock' => 'boolean',
        'is_active' => 'boolean',
        'last_export_payload' => 'array',
        'last_import_payload' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function channel()
    {
        return $this->belongsTo(EcommerceChannel::class, 'ecommerce_channel_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

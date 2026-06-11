<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForeignPurchaseOrderCost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'foreign_purchase_order_id',
        'tenant_id',
        'organization_id',
        'supplier_id',
        'cost_type',
        'title',
        'cost_date_en',
        'cost_date_fa',
        'foreign_amount',
        'exchange_rate',
        'base_amount',
        'allocation_basis',
        'reference_number',
        'document_number',
        'description',
    ];

    protected $casts = [
        'cost_date_en' => 'date',
        'foreign_amount' => 'decimal:4',
        'exchange_rate' => 'decimal:6',
        'base_amount' => 'decimal:2',
    ];

    public function foreignPurchaseOrder()
    {
        return $this->belongsTo(ForeignPurchaseOrder::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}

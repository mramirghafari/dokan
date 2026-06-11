<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForeignPurchaseOrder extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'purchase_order_id',
        'tenant_id',
        'organization_id',
        'supplier_id',
        'store_id',
        'currency_id',
        'import_number',
        'proforma_number',
        'contract_number',
        'lc_number',
        'customs_declaration_number',
        'bill_of_lading_number',
        'origin_country',
        'shipment_method',
        'status',
        'order_date_en',
        'order_date_fa',
        'expected_arrival_date_en',
        'expected_arrival_date_fa',
        'customs_date_en',
        'customs_date_fa',
        'exchange_rate',
        'foreign_goods_amount',
        'base_goods_amount',
        'additional_cost_amount',
        'customs_cost_amount',
        'landed_cost_amount',
        'allocated_amount',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'order_date_en' => 'date',
        'expected_arrival_date_en' => 'date',
        'customs_date_en' => 'date',
        'exchange_rate' => 'decimal:6',
        'foreign_goods_amount' => 'decimal:4',
        'base_goods_amount' => 'decimal:2',
        'additional_cost_amount' => 'decimal:2',
        'customs_cost_amount' => 'decimal:2',
        'landed_cost_amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function items()
    {
        return $this->hasMany(ForeignPurchaseOrderItem::class);
    }

    public function costs()
    {
        return $this->hasMany(ForeignPurchaseOrderCost::class);
    }

    public function documents()
    {
        return $this->hasMany(ForeignPurchaseOrderDocument::class);
    }
}

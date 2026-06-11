<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierQuotationItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'supplier_quotation_id',
        'purchase_requisition_item_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_amount',
        'description',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function supplierQuotation()
    {
        return $this->belongsTo(SupplierQuotation::class);
    }

    public function requisitionItem()
    {
        return $this->belongsTo(PurchaseRequisitionItem::class, 'purchase_requisition_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

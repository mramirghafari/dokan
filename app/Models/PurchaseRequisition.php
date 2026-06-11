<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequisition extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'store_id',
        'selected_supplier_id',
        'selected_supplier_quotation_id',
        'converted_purchase_order_id',
        'request_number',
        'request_date_en',
        'request_date_fa',
        'status',
        'priority',
        'description',
        'requested_by',
        'selected_by',
        'selected_at',
    ];

    protected $casts = [
        'request_date_en' => 'date',
        'selected_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseRequisitionItem::class);
    }

    public function quotations()
    {
        return $this->hasMany(SupplierQuotation::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function selectedSupplier()
    {
        return $this->belongsTo(Supplier::class, 'selected_supplier_id');
    }

    public function selectedQuotation()
    {
        return $this->belongsTo(SupplierQuotation::class, 'selected_supplier_quotation_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'converted_purchase_order_id');
    }
}

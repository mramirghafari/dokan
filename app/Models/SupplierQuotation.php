<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierQuotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'purchase_requisition_id',
        'supplier_id',
        'quotation_number',
        'quotation_date_en',
        'quotation_date_fa',
        'valid_until',
        'status',
        'total_amount',
        'description',
        'created_by',
        'selected_by',
        'selected_at',
    ];

    protected $casts = [
        'quotation_date_en' => 'date',
        'valid_until' => 'date',
        'selected_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(SupplierQuotationItem::class);
    }
}

<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseServiceInvoice extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'supplier_id',
        'purchase_order_id',
        'receipt_id',
        'invoice_number',
        'invoice_type',
        'invoice_date_en',
        'invoice_date_fa',
        'status',
        'subtotal_amount',
        'tax_amount',
        'total_amount',
        'created_by',
        'approved_by',
        'approved_at',
        'canceled_at',
        'canceled_by',
        'description',
    ];

    protected $casts = [
        'invoice_date_en' => 'date',
        'subtotal_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseServiceInvoiceItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }

    public function accountingVoucher()
    {
        return $this->hasOne(Voucher::class, 'source_id')->where('source_type', self::class)->where('document_type', 'purchase_service_invoice');
    }
}

<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'supplier_id',
        'store_id',
        'receipt_id',
        'order_number',
        'order_date_en',
        'order_date_fa',
        'status',
        'approval_status',
        'approval_level',
        'approval_requested_at',
        'approval_requested_by',
        'approval_reviewed_at',
        'approval_reviewed_by',
        'approval_note',
        'budget_status',
        'budget_period',
        'budget_amount',
        'budget_consumed_amount',
        'budget_remaining_amount',
        'total_amount',
        'paid_amount',
        'payment_status',
        'procurement_source',
        'direct_supply_type',
        'direct_supply_reason',
        'source_reference',
        'paid_at',
        'description',
        'approved_at',
        'approved_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'order_date_en' => 'date',
        'approval_requested_at' => 'datetime',
        'approval_reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'budget_amount' => 'decimal:2',
        'budget_consumed_amount' => 'decimal:2',
        'budget_remaining_amount' => 'decimal:2',
    ];

    public function getRemainingAmountAttribute(): float
    {
        return max(0, round($this->net_amount - (float) $this->paid_amount, 2));
    }

    public function getReturnedAmountAttribute(): float
    {
        if ($this->relationLoaded('returns')) {
            return round((float) $this->returns->sum('total_amount'), 2);
        }

        return round((float) $this->returns()->sum('total_amount'), 2);
    }

    public function getInvoicedAmountAttribute(): float
    {
        if ($this->relationLoaded('invoices')) {
            return round((float) $this->invoices->where('status', '<>', 'canceled')->sum('total_amount'), 2);
        }

        return round((float) $this->invoices()->where('status', '<>', 'canceled')->sum('total_amount'), 2);
    }

    public function getNetAmountAttribute(): float
    {
        $invoiceBaseAmount = $this->invoiced_amount > 0 ? $this->invoiced_amount : (float) $this->total_amount;

        return max(0, round($invoiceBaseAmount - $this->returned_amount, 2));
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }

    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function receiveDocuments()
    {
        return $this->hasMany(PurchaseOrderReceipt::class);
    }

    public function invoices()
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function approvalEvents()
    {
        return $this->hasMany(PurchaseApprovalEvent::class);
    }

    public function foreignImport()
    {
        return $this->hasOne(ForeignPurchaseOrder::class);
    }
}

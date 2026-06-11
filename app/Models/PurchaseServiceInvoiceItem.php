<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseServiceInvoiceItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_service_invoice_id',
        'tenant_id',
        'organization_id',
        'purchase_order_item_id',
        'product_id',
        'expense_account_id',
        'cost_type',
        'allocation_type',
        'title',
        'amount',
        'tax_amount',
        'total_amount',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(PurchaseServiceInvoice::class, 'purchase_service_invoice_id');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function expenseAccount()
    {
        return $this->belongsTo(Accounts::class, 'expense_account_id');
    }
}

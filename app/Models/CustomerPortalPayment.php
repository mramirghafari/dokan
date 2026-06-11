<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerPortalPayment extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'customer_portal_account_id',
        'customer_id',
        'pishfactor_id',
        'customer_portal_request_id',
        'amount',
        'payable_amount',
        'status',
        'payment_method',
        'gateway_provider',
        'authority',
        'reference_number',
        'accounting_voucher_id',
        'gateway_settlement_status',
        'gateway_settled_at',
        'proof_text',
        'metadata',
        'requested_at',
        'submitted_at',
        'verified_at',
        'rejected_at',
        'verified_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payable_amount' => 'decimal:2',
        'metadata' => 'array',
        'requested_at' => 'datetime',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'rejected_at' => 'datetime',
        'gateway_settled_at' => 'datetime',
    ];

    public const STATUSES = [
        'initiated' => 'در انتظار پرداخت',
        'submitted' => 'رسید ارسال شده',
        'verified' => 'تایید شده',
        'rejected' => 'رد شده',
        'expired' => 'منقضی',
    ];

    public const METHODS = [
        'bank_transfer' => 'واریز بانکی',
        'card_to_card' => 'کارت به کارت',
        'online_gateway' => 'درگاه آنلاین',
        'pos' => 'POS',
    ];

    public function account()
    {
        return $this->belongsTo(CustomerPortalAccount::class, 'customer_portal_account_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(Pishfactor::class, 'pishfactor_id');
    }

    public function request()
    {
        return $this->belongsTo(CustomerPortalRequest::class, 'customer_portal_request_id');
    }

    public function accountingVoucher()
    {
        return $this->belongsTo(Voucher::class, 'accounting_voucher_id');
    }

    public function statusText(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function methodText(): string
    {
        return self::METHODS[$this->payment_method] ?? $this->payment_method;
    }
}

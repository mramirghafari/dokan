<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxpayerInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'taxpayer_setting_id',
        'voucher_id',
        'customer_id',
        'tenant_id',
        'organization_id',
        'invoice_number',
        'tax_id',
        'reference_number',
        'source_type',
        'source_id',
        'source_number',
        'invoice_subject',
        'invoice_pattern',
        'invoice_type',
        'issue_date_en',
        'issue_date_fa',
        'status',
        'send_mode',
        'memory_id',
        'branch_tax_code',
        'buyer_name',
        'buyer_economic_number',
        'buyer_national_id',
        'buyer_postal_code',
        'buyer_address',
        'subtotal_amount',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'payload_json',
        'response_json',
        'error_message',
        'prepared_at',
        'sent_at',
        'accepted_at',
        'retry_count',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issue_date_en' => 'date',
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'payload_json' => 'array',
        'response_json' => 'array',
        'prepared_at' => 'datetime',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function setting()
    {
        return $this->belongsTo(TaxpayerSetting::class, 'taxpayer_setting_id');
    }

    public function items()
    {
        return $this->hasMany(TaxpayerInvoiceItem::class);
    }

    public function logs()
    {
        return $this->hasMany(TaxpayerSubmissionLog::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyAssetTaxInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_asset_disposal_id',
        'company_asset_id',
        'voucher_id',
        'tenant_id',
        'organization_id',
        'invoice_number',
        'tax_id',
        'reference_number',
        'issue_date_en',
        'issue_date_fa',
        'status',
        'sale_amount',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'buyer_name',
        'buyer_economic_number',
        'buyer_national_id',
        'buyer_postal_code',
        'buyer_address',
        'asset_code',
        'asset_name',
        'payload_json',
        'response_json',
        'error_message',
        'sent_at',
        'accepted_at',
        'retry_count',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issue_date_en' => 'date',
        'sale_amount' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'payload_json' => 'array',
        'response_json' => 'array',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function disposal()
    {
        return $this->belongsTo(CompanyAssetDisposal::class, 'company_asset_disposal_id');
    }

    public function asset()
    {
        return $this->belongsTo(CompanyAsset::class, 'company_asset_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}

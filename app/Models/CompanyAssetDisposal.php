<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyAssetDisposal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_asset_id',
        'tenant_id',
        'organization_id',
        'disposal_type',
        'disposal_date_en',
        'disposal_date_fa',
        'acquisition_cost',
        'accumulated_depreciation',
        'book_value',
        'proceeds_amount',
        'gain_amount',
        'loss_amount',
        'proceeds_account_id',
        'gain_account_id',
        'loss_account_id',
        'voucher_id',
        'event_id',
        'status_before',
        'status_after',
        'buyer_name',
        'description',
        'created_by',
    ];

    protected $casts = [
        'disposal_date_en' => 'date',
        'acquisition_cost' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'book_value' => 'decimal:2',
        'proceeds_amount' => 'decimal:2',
        'gain_amount' => 'decimal:2',
        'loss_amount' => 'decimal:2',
    ];

    public function asset()
    {
        return $this->belongsTo(CompanyAsset::class, 'company_asset_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function event()
    {
        return $this->belongsTo(CompanyAssetEvent::class, 'event_id');
    }

    public function taxInvoice()
    {
        return $this->hasOne(CompanyAssetTaxInvoice::class, 'company_asset_disposal_id');
    }
}

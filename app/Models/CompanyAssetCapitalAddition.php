<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyAssetCapitalAddition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_asset_id',
        'tenant_id',
        'organization_id',
        'voucher_id',
        'event_id',
        'addition_type',
        'addition_date_en',
        'addition_date_fa',
        'amount',
        'asset_cost_before',
        'asset_cost_after',
        'asset_account_id',
        'credit_account_id',
        'supplier_name',
        'reference_number',
        'description',
        'created_by',
    ];

    protected $casts = [
        'addition_date_en' => 'date',
        'amount' => 'decimal:2',
        'asset_cost_before' => 'decimal:2',
        'asset_cost_after' => 'decimal:2',
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

    public function assetAccount()
    {
        return $this->belongsTo(Accounts::class, 'asset_account_id');
    }

    public function creditAccount()
    {
        return $this->belongsTo(Accounts::class, 'credit_account_id');
    }
}

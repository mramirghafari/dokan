<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyAssetDepreciationPolicy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_asset_id',
        'tenant_id',
        'organization_id',
        'effective_date_en',
        'effective_date_fa',
        'depreciation_method',
        'useful_life_months',
        'salvage_value',
        'annual_rate_percent',
        'accumulated_depreciation_account_id',
        'depreciation_expense_account_id',
        'reason',
        'description',
        'created_by',
    ];

    protected $casts = [
        'effective_date_en' => 'date',
        'salvage_value' => 'decimal:2',
        'annual_rate_percent' => 'decimal:4',
    ];

    public function asset()
    {
        return $this->belongsTo(CompanyAsset::class, 'company_asset_id');
    }

    public function accumulatedDepreciationAccount()
    {
        return $this->belongsTo(Accounts::class, 'accumulated_depreciation_account_id');
    }

    public function depreciationExpenseAccount()
    {
        return $this->belongsTo(Accounts::class, 'depreciation_expense_account_id');
    }
}

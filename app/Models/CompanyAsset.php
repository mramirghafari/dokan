<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyAsset extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'store_id',
        'cost_center_id',
        'custodian_employee_id',
        'asset_account_id',
        'accumulated_depreciation_account_id',
        'depreciation_expense_account_id',
        'asset_code',
        'plaque_number',
        'name',
        'asset_category',
        'serial_number',
        'location',
        'acquisition_date_en',
        'acquisition_date_fa',
        'in_service_date_en',
        'in_service_date_fa',
        'acquisition_cost',
        'salvage_value',
        'useful_life_months',
        'depreciation_method',
        'accumulated_depreciation',
        'status',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'acquisition_date_en' => 'date',
        'in_service_date_en' => 'date',
        'acquisition_cost' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function custodian()
    {
        return $this->belongsTo(Employee::class, 'custodian_employee_id');
    }

    public function assetAccount()
    {
        return $this->belongsTo(Accounts::class, 'asset_account_id');
    }

    public function accumulatedDepreciationAccount()
    {
        return $this->belongsTo(Accounts::class, 'accumulated_depreciation_account_id');
    }

    public function depreciationExpenseAccount()
    {
        return $this->belongsTo(Accounts::class, 'depreciation_expense_account_id');
    }

    public function attachments()
    {
        return $this->hasMany(CompanyAssetAttachment::class);
    }

    public function events()
    {
        return $this->hasMany(CompanyAssetEvent::class);
    }

    public function depreciations()
    {
        return $this->hasMany(CompanyAssetDepreciation::class);
    }

    public function disposals()
    {
        return $this->hasMany(CompanyAssetDisposal::class);
    }

    public function taxInvoices()
    {
        return $this->hasMany(CompanyAssetTaxInvoice::class);
    }

    public function capitalAdditions()
    {
        return $this->hasMany(CompanyAssetCapitalAddition::class);
    }

    public function depreciationPolicies()
    {
        return $this->hasMany(CompanyAssetDepreciationPolicy::class);
    }

    public function bookValue(): float
    {
        return round(max(0, (float) $this->acquisition_cost - (float) $this->accumulated_depreciation), 2);
    }

    public function monthlyDepreciationEstimate(): float
    {
        if (!$this->useful_life_months) {
            return 0;
        }

        return round(max(0, ((float) $this->acquisition_cost - (float) $this->salvage_value) / (int) $this->useful_life_months), 2);
    }
}

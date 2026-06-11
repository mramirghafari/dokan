<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractingProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'project_code',
        'title',
        'customer_id',
        'contract_number',
        'contract_type',
        'status',
        'start_date_en',
        'start_date_fa',
        'end_date_en',
        'end_date_fa',
        'contract_amount',
        'approved_budget',
        'retention_percent',
        'advance_payment_percent',
        'performance_bond_percent',
        'vat_percent',
        'receivable_account_id',
        'revenue_account_id',
        'advance_account_id',
        'retention_account_id',
        'tax_account_id',
        'cost_account_id',
        'payable_account_id',
        'guarantee_control_account_id',
        'guarantee_commitment_account_id',
        'cost_center_id',
        'project_manager_id',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date_en' => 'date',
        'end_date_en' => 'date',
        'contract_amount' => 'decimal:2',
        'approved_budget' => 'decimal:2',
        'retention_percent' => 'decimal:4',
        'advance_payment_percent' => 'decimal:4',
        'performance_bond_percent' => 'decimal:4',
        'vat_percent' => 'decimal:4',
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    public function items()
    {
        return $this->hasMany(ContractingProjectItem::class, 'contracting_project_id');
    }

    public function progressStatements()
    {
        return $this->hasMany(ContractingProgressStatement::class, 'contracting_project_id');
    }

    public function guarantees()
    {
        return $this->hasMany(ContractingGuarantee::class, 'contracting_project_id');
    }

    public function costEntries()
    {
        return $this->hasMany(ContractingCostEntry::class, 'contracting_project_id');
    }
}

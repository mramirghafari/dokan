<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperationalExpense extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'store_id',
        'cost_center_id',
        'expense_type_id',
        'specialized_kind',
        'expense_account_id',
        'settlement_account_id',
        'voucher_id',
        'expense_number',
        'expense_date_en',
        'expense_date_fa',
        'status',
        'workflow_status',
        'payment_status',
        'allocation_target_type',
        'allocation_target_id',
        'allocation_basis',
        'product_id',
        'project_code',
        'contract_code',
        'allocation_note',
        'workflow_note',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'amount',
        'tax_amount',
        'total_amount',
        'reference_number',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'expense_date_en' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class);
    }

    public function expenseAccount()
    {
        return $this->belongsTo(Accounts::class, 'expense_account_id');
    }

    public function settlementAccount()
    {
        return $this->belongsTo(Accounts::class, 'settlement_account_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function financialAttachments()
    {
        return $this->morphMany(FinancialAttachment::class, 'attachable')->latest();
    }

    public function allocations()
    {
        return $this->hasMany(ExpenseAllocation::class, 'operational_expense_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VoucherItems extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['voucher_id', 'tenant_id', 'organization_id', 'amount', 'debit_amount', 'credit_amount', 'account_id', 'currency_id', 'foreign_debit_amount', 'foreign_credit_amount', 'exchange_rate', 'cost_center_id', 'revenue_center_id', 'expense_id', 'expense_allocation_id', 'income_id', 'branch_id', 'project_code', 'product_id', 'customer_id', 'employee_id', 'contract_code', 'route_code', 'analytic_note', 'method', 'payment_terminal_id', 'issuing_bank', 'due_date', 'cheque_photo', 'description'];

    protected $casts = [
        'foreign_debit_amount' => 'decimal:4',
        'foreign_credit_amount' => 'decimal:4',
        'exchange_rate' => 'decimal:6',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function account()
    {
        return $this->belongsTo(Accounts::class, 'account_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function revenueCenter()
    {
        return $this->belongsTo(RevenueCenter::class);
    }

    public function expense()
    {
        return $this->belongsTo(OperationalExpense::class, 'expense_id');
    }

    public function expenseAllocation()
    {
        return $this->belongsTo(ExpenseAllocation::class, 'expense_allocation_id');
    }

    public function income()
    {
        return $this->belongsTo(OperationalIncome::class, 'income_id');
    }

    public function branch()
    {
        return $this->belongsTo(Store::class, 'branch_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}

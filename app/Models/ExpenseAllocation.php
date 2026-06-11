<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseAllocation extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'operational_expense_id',
        'voucher_id',
        'voucher_item_id',
        'cost_center_id',
        'expense_type_id',
        'allocation_basis',
        'allocation_target_type',
        'allocation_target_id',
        'target_type',
        'target_id',
        'product_id',
        'project_code',
        'contract_code',
        'basis_quantity',
        'basis_value',
        'allocation_percent',
        'allocated_amount',
        'amount',
        'note',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'basis_quantity' => 'decimal:4',
        'basis_value' => 'decimal:4',
        'allocation_percent' => 'decimal:4',
        'allocated_amount' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function expense()
    {
        return $this->belongsTo(OperationalExpense::class, 'operational_expense_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function voucherItem()
    {
        return $this->belongsTo(VoucherItems::class, 'voucher_item_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

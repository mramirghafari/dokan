<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PettyCashTransaction extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'petty_cash_fund_id',
        'voucher_id',
        'expense_id',
        'counter_account_id',
        'cost_center_id',
        'expense_type_id',
        'transaction_type',
        'transaction_date_en',
        'transaction_date_fa',
        'amount',
        'tax_amount',
        'total_amount',
        'status',
        'reference_number',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'transaction_date_en' => 'date',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function fund()
    {
        return $this->belongsTo(PettyCashFund::class, 'petty_cash_fund_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function expense()
    {
        return $this->belongsTo(OperationalExpense::class, 'expense_id');
    }

    public function counterAccount()
    {
        return $this->belongsTo(Accounts::class, 'counter_account_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VoucherTemplateItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'voucher_template_id',
        'tenant_id',
        'organization_id',
        'account_id',
        'cost_center_id',
        'revenue_center_id',
        'expense_id',
        'branch_id',
        'project_code',
        'product_id',
        'customer_id',
        'employee_id',
        'contract_code',
        'route_code',
        'analytic_note',
        'amount',
        'debit_amount',
        'credit_amount',
        'method',
        'payment_terminal_id',
        'issuing_bank',
        'due_date',
        'cheque_photo',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function voucherTemplate()
    {
        return $this->belongsTo(VoucherTemplate::class);
    }

    public function account()
    {
        return $this->belongsTo(Accounts::class, 'account_id');
    }
}

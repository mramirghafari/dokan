<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollRunPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payroll_run_id',
        'voucher_id',
        'tenant_id',
        'organization_id',
        'payment_number',
        'payment_date_en',
        'payment_date_fa',
        'amount',
        'payment_method',
        'treasury_account_id',
        'payment_terminal_id',
        'issuing_bank',
        'cheque_number',
        'due_date',
        'status',
        'description',
        'created_by',
        'canceled_by',
        'canceled_at',
    ];

    protected $casts = [
        'payment_date_en' => 'date',
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'canceled_at' => 'datetime',
    ];

    public function payrollRun()
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}

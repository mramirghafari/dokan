<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialPeriodClosing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'fiscal_year_id',
        'next_fiscal_year_id',
        'period_start',
        'period_end',
        'closing_voucher_id',
        'opening_voucher_id',
        'total_debit',
        'total_credit',
        'opening_total_debit',
        'opening_total_credit',
        'accounts_count',
        'opening_accounts_count',
        'status',
        'balances_snapshot',
        'description',
        'closed_by',
        'closed_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'closed_at' => 'datetime',
        'balances_snapshot' => 'array',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'opening_total_debit' => 'decimal:2',
        'opening_total_credit' => 'decimal:2',
    ];

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function nextFiscalYear()
    {
        return $this->belongsTo(FiscalYear::class, 'next_fiscal_year_id');
    }

    public function closingVoucher()
    {
        return $this->belongsTo(Voucher::class, 'closing_voucher_id');
    }

    public function openingVoucher()
    {
        return $this->belongsTo(Voucher::class, 'opening_voucher_id');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}

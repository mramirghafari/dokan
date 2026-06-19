<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['factor_id', 'tenant_id', 'organization_id', 'account_id', 'voucher_type', 'voucher_number', 'reference_number', 'voucher_date_fa', 'voucher_date_en', 'amount', 'total_debit', 'total_credit', 'method', 'document_type', 'status', 'is_permanent', 'source_type', 'source_id', 'original_voucher_id', 'reversal_voucher_id', 'merged_into_voucher_id', 'fiscal_year', 'fiscal_year_id', 'description', 'reversal_reason', 'posted_at', 'approved_by', 'created_by', 'updated_by', 'reversed_at', 'reversed_by', 'merged_at', 'merged_by', 'cancelled_at', 'cancelled_by'];
    protected $casts = [
        'is_permanent' => 'boolean',
        'posted_at' => 'datetime',
        'reversed_at' => 'datetime',
        'merged_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'voucher_date_en' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(VoucherItems::class, 'voucher_id');
    }

    public function treasuryInstruments()
    {
        return $this->hasMany(TreasuryInstrument::class, 'voucher_id');
    }

    public function account()
    {
        return $this->belongsTo(Accounts::class, 'account_id');
    }

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class, 'fiscal_year_id');
    }

    public function isOpening(): bool
    {
        return $this->document_type === 'period_opening' || (int) $this->voucher_type === 1;
    }

    public function originalVoucher()
    {
        return $this->belongsTo(self::class, 'original_voucher_id');
    }

    public function reversalVoucher()
    {
        return $this->belongsTo(self::class, 'reversal_voucher_id');
    }

    public function mergedIntoVoucher()
    {
        return $this->belongsTo(self::class, 'merged_into_voucher_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Pishfactor::class, 'factor_id');
    }
}

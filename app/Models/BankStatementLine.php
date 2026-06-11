<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankStatementLine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'account_id',
        'voucher_id',
        'voucher_item_id',
        'statement_date',
        'reference_no',
        'debit_amount',
        'credit_amount',
        'amount',
        'status',
        'description',
        'matched_at',
        'matched_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'statement_date' => 'date',
        'matched_at' => 'datetime',
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function account()
    {
        return $this->belongsTo(Accounts::class, 'account_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function voucherItem()
    {
        return $this->belongsTo(VoucherItems::class, 'voucher_item_id');
    }
}

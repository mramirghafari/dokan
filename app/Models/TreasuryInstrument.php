<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TreasuryInstrument extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'voucher_id',
        'voucher_item_id',
        'treasury_cheque_book_id',
        'treasury_cheque_leaf_id',
        'counter_account_id',
        'current_holder_account_id',
        'instrument_type',
        'direction',
        'status',
        'current_holder_name',
        'amount',
        'issuing_bank',
        'cheque_number',
        'due_date',
        'status_date',
        'last_status_note',
        'last_status_changed_at',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'status_date' => 'date',
        'last_status_changed_at' => 'datetime',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function voucherItem()
    {
        return $this->belongsTo(VoucherItems::class, 'voucher_item_id');
    }

    public function chequeBook()
    {
        return $this->belongsTo(TreasuryChequeBook::class, 'treasury_cheque_book_id');
    }

    public function chequeLeaf()
    {
        return $this->belongsTo(TreasuryChequeLeaf::class, 'treasury_cheque_leaf_id');
    }

    public function counterAccount()
    {
        return $this->belongsTo(Accounts::class, 'counter_account_id');
    }

    public function currentHolderAccount()
    {
        return $this->belongsTo(Accounts::class, 'current_holder_account_id');
    }

    public function histories()
    {
        return $this->hasMany(TreasuryInstrumentHistory::class, 'treasury_instrument_id')->latest('id');
    }
}

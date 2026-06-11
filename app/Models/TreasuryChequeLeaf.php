<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TreasuryChequeLeaf extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'treasury_cheque_book_id',
        'account_id',
        'treasury_instrument_id',
        'payee_account_id',
        'leaf_number',
        'status',
        'issued_date',
        'due_date',
        'amount',
        'payee_name',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function book()
    {
        return $this->belongsTo(TreasuryChequeBook::class, 'treasury_cheque_book_id');
    }

    public function account()
    {
        return $this->belongsTo(Accounts::class, 'account_id');
    }

    public function instrument()
    {
        return $this->belongsTo(TreasuryInstrument::class, 'treasury_instrument_id');
    }

    public function payeeAccount()
    {
        return $this->belongsTo(Accounts::class, 'payee_account_id');
    }
}

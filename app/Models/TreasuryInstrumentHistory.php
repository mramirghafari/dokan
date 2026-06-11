<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreasuryInstrumentHistory extends Model
{
    use HasFactory, HasOrganizationFilter;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'treasury_instrument_id',
        'previous_status',
        'new_status',
        'action_date',
        'amount',
        'settlement_account_id',
        'holder_account_id',
        'holder_name',
        'voucher_id',
        'note',
        'created_by',
    ];

    protected $casts = [
        'action_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function instrument()
    {
        return $this->belongsTo(TreasuryInstrument::class, 'treasury_instrument_id');
    }

    public function settlementAccount()
    {
        return $this->belongsTo(Accounts::class, 'settlement_account_id');
    }

    public function holderAccount()
    {
        return $this->belongsTo(Accounts::class, 'holder_account_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}

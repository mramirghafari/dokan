<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PettyCashFund extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'account_id',
        'custodian_user_id',
        'fund_code',
        'title',
        'custodian_name',
        'ceiling_amount',
        'status',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'ceiling_amount' => 'decimal:2',
    ];

    public function account()
    {
        return $this->belongsTo(Accounts::class, 'account_id');
    }

    public function custodian()
    {
        return $this->belongsTo(User::class, 'custodian_user_id');
    }

    public function transactions()
    {
        return $this->hasMany(PettyCashTransaction::class)->latest('transaction_date_en')->latest('id');
    }
}

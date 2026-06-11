<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'legacy_code',
        'code',
        'title',
        'method_type',
        'requires_terminal',
        'requires_due_date',
        'requires_bank_name',
        'default_account_id',
        'sort_order',
        'isActive',
    ];

    protected $casts = [
        'requires_terminal' => 'boolean',
        'requires_due_date' => 'boolean',
        'requires_bank_name' => 'boolean',
        'isActive' => 'boolean',
    ];

    public function defaultAccount()
    {
        return $this->belongsTo(Accounts::class, 'default_account_id');
    }
}

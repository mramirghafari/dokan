<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerListScopeSummary extends Model
{
    protected $fillable = [
        'tenant_id',
        'organization_id',
        'scope_type',
        'scope_key',
        'total_customers',
        'customers_with_purchase',
        'restricted_customers',
        'banned_customers',
        'computed_at',
    ];

    protected $casts = [
        'total_customers' => 'integer',
        'customers_with_purchase' => 'integer',
        'restricted_customers' => 'integer',
        'banned_customers' => 'integer',
        'computed_at' => 'datetime',
    ];
}

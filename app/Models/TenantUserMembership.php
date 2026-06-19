<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantUserMembership extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'organization_id',
        'is_admin',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenants::class, 'tenant_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }
}

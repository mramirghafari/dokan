<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerPortalAccount extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = ['tenant_id', 'organization_id', 'customer_id', 'user_id', 'role', 'access_token', 'status', 'title', 'contact_name', 'contact_mobile', 'contact_email', 'permissions', 'last_login_at', 'expires_at', 'created_by', 'updated_by'];

    protected $casts = [
        'permissions' => 'array',
        'last_login_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public const ROLES = ['customer' => 'مشتری', 'representative' => 'نماینده'];
    public const STATUSES = ['active' => 'فعال', 'suspended' => 'مسدود', 'expired' => 'منقضی'];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function requests()
    {
        return $this->hasMany(CustomerPortalRequest::class, 'customer_portal_account_id');
    }

    public function roleText(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function statusText(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isAccessible(): bool
    {
        return $this->status === 'active' && (!$this->expires_at || $this->expires_at->isFuture());
    }
}

<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmPublicApiClient extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'code',
        'title',
        'token_hash',
        'scopes',
        'allowed_ips',
        'is_active',
        'request_count',
        'last_used_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'scopes' => 'array',
        'is_active' => 'boolean',
        'request_count' => 'integer',
        'last_used_at' => 'datetime',
    ];

    public const SCOPES = [
        'leads.write' => 'ثبت سرنخ',
        'tickets.write' => 'ثبت تیکت خدمات',
        'opportunities.write' => 'ثبت فرصت فروش',
    ];

    public function hasScope(string $scope): bool
    {
        return in_array($scope, (array) $this->scopes, true);
    }
}

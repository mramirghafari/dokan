<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmIntegrationConnection extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'type',
        'provider',
        'title',
        'endpoint_url',
        'webhook_secret_hash',
        'settings',
        'credentials',
        'scopes',
        'is_active',
        'last_synced_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'credentials' => 'encrypted:array',
        'scopes' => 'array',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public const TYPES = [
        'voip' => 'VoIP و مرکز تماس',
        'calendar' => 'تقویم کاری',
        'drive' => 'Drive و فایل بیرونی',
    ];

    public const PROVIDERS = [
        'generic' => 'Generic Webhook/API',
        'asterisk' => 'Asterisk/Issabel',
        'google_calendar' => 'Google Calendar',
        'microsoft_calendar' => 'Microsoft Calendar',
        'google_drive' => 'Google Drive',
        'dropbox' => 'Dropbox',
    ];

    public function logs()
    {
        return $this->hasMany(CrmIntegrationSyncLog::class, 'crm_integration_connection_id');
    }

    public function typeText(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function providerText(): string
    {
        return self::PROVIDERS[$this->provider] ?? $this->provider;
    }

    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes ?: [], true);
    }
}

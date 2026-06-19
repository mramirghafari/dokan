<?php

namespace App\Services;

class TenantSettings
{
    public static function get(string $key, ?int $tenantId = null, $default = null)
    {
        $tenantId = $tenantId ?: static::currentTenantId();
        $context = [
            'tenant_id' => $tenantId,
            'organization_id' => static::currentOrganizationId(),
        ];

        return app(SettingService::class)->get($key, $context, $default);
    }

    public static function enabled(string $key, ?int $tenantId = null): bool
    {
        return static::get($key, $tenantId, 'no') === 'yes';
    }

    public static function userMatchesRoleSetting(string $settingKey, ?\App\Models\User $user = null, ?int $tenantId = null): bool
    {
        $user = $user ?: auth()->user();

        if (!$user) {
            return false;
        }

        $definition = (array) config("panel_settings.definitions.{$settingKey}", []);
        $default = $definition['default'] ?? [];
        $allowed = static::get($settingKey, $tenantId, $default);

        if (is_string($allowed)) {
            $decoded = json_decode($allowed, true);
            $allowed = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($allowed) || $allowed === []) {
            return false;
        }

        if (in_array('all', $allowed, true)) {
            return true;
        }

        if ((int) ($user->isGod ?? 0) === 1 && in_array('god', $allowed, true)) {
            return true;
        }

        $user->loadMissing('roles');

        foreach ($user->roles as $role) {
            $title = (string) ($role->title ?? '');

            if ($title !== '' && in_array($title, $allowed, true)) {
                return true;
            }
        }

        if ((int) ($user->isAdmin ?? 0) === 1 && in_array('panel_manager', $allowed, true)) {
            return true;
        }

        return false;
    }

    public static function shouldCaptureInvoiceLocation(?\App\Models\User $user = null, ?int $tenantId = null): bool
    {
        return static::enabled('feature_gps_tracking', $tenantId)
            && static::userMatchesRoleSetting('invoice_location_roles', $user, $tenantId);
    }

    public static function all(?int $tenantId = null): array
    {
        $settings = [];

        foreach ((array) config('panel_settings.definitions', []) as $key => $definition) {
            $settings[$key] = static::get($key, $tenantId, $definition['default'] ?? null);
        }

        return $settings;
    }

    private static function currentTenantId(): ?int
    {
        return app(TenantContextService::class)->tenantId();
    }

    private static function currentOrganizationId(): ?int
    {
        return app(TenantContextService::class)->organizationId();
    }
}

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
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        return $user->tenant_id ?: $user->tenants_id;
    }

    private static function currentOrganizationId(): ?int
    {
        $user = auth()->user();

        if (!$user || empty($user->organization_id)) {
            return null;
        }

        $decoded = json_decode((string) $user->organization_id, true);

        if (is_array($decoded)) {
            return (int) ($decoded[0] ?? 0) ?: null;
        }

        return (int) $user->organization_id ?: null;
    }
}

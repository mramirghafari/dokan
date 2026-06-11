<?php

namespace App\Services;

use App\Scopes\TenantScope;

class TenantContextService
{
    public function fromUser($user = null): array
    {
        $user = $user ?: auth()->user();

        if (!$user) {
            return ['tenant_id' => null, 'organization_id' => null, 'user_id' => null];
        }

        return [
            'tenant_id' => $this->tenantId($user),
            'organization_id' => $this->organizationId($user),
            'user_id' => $user->id,
        ];
    }

    public function tenantId($user = null): ?int
    {
        $user = $user ?: auth()->user();

        if (!$user) {
            return null;
        }

        return (int) ($user->tenant_id ?: $user->tenants_id ?: 0) ?: null;
    }

    public function organizationId($user = null): ?int
    {
        $user = $user ?: auth()->user();

        if (!$user || empty($user->organization_id)) {
            return null;
        }

        $decoded = json_decode((string) $user->organization_id, true);

        if (is_array($decoded)) {
            return (int) ($decoded[0] ?? 0) ?: null;
        }

        return (int) $user->organization_id ?: null;
    }

    public function settingContext($user = null, array $extra = []): array
    {
        return array_merge($this->fromUser($user), $extra);
    }

    /**
     * @template TReturn
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public function runAsTenant(?int $tenantId, callable $callback)
    {
        return TenantScope::forTenant($tenantId, $callback);
    }

    /**
     * @template TReturn
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public function runWithoutTenantScope(callable $callback)
    {
        return TenantScope::runWithout($callback);
    }
}

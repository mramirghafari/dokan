<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class PermissionBootstrapService
{
    public function __construct(private PermissionNamingService $naming)
    {
    }

    public function ensureCatalog(): int
    {
        $created = 0;

        foreach ((array) config('permission_catalog', []) as $title => $description) {
            $permission = Permission::query()->where('title', $title)->first();

            if ($permission) {
                continue;
            }

            $parts = $this->naming->payload($title);

            $permission = Permission::create(array_merge($parts, [
                'title' => $title,
                'description' => $description,
                'isActive' => 1,
            ]));

            $this->naming->syncAliases($permission);
            $created++;
        }

        return $created;
    }

    public function syncRolePermissions(Role $role, bool $excludeTenants = true): void
    {
        $query = Permission::query()->where('isActive', 1);

        if ($excludeTenants) {
            $query->where('title', '!=', 'tenants');
        }

        $role->permissions()->sync($query->pluck('id'));
    }

    public function syncGlobalAdminRole(): void
    {
        $adminRole = Role::query()
            ->whereNull('tenant_id')
            ->where('title', 'admin')
            ->first();

        if (!$adminRole) {
            return;
        }

        $this->syncRolePermissions($adminRole, false);
    }
}

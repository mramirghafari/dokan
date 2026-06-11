<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleScope;
use App\Models\User;
use App\Models\UserScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PermissionScopeService
{
    public function tenantIdForUser($user): ?int
    {
        if (!$user) {
            return null;
        }

        return $user->tenant_id ?: $user->tenants_id;
    }

    public function targetTenantId(Request $request): ?int
    {
        $user = auth()->user();

        if ($user && $user->isGod == 1 && $request->filled('tenant_id')) {
            return (int) $request->tenant_id;
        }

        return $this->tenantIdForUser($user);
    }

    public function rolesForUser($user): Builder
    {
        $query = Role::query();

        if (!$this->hasTenantColumn('roles') || ($user && $user->isGod == 1)) {
            return $query;
        }

        $tenantId = $this->tenantIdForUser($user);

        return $query->where(function ($query) use ($tenantId) {
            $query->whereNull('tenant_id');

            if ($tenantId) {
                $query->orWhere('tenant_id', $tenantId);
            }
        });
    }

    public function permissionsForUser($user): Builder
    {
        $query = Permission::query();

        if (!$this->hasTenantColumn('permissions') || ($user && $user->isGod == 1)) {
            return $query;
        }

        $tenantId = $this->tenantIdForUser($user);

        return $query->where(function ($query) use ($tenantId) {
            $query->whereNull('tenant_id');

            if ($tenantId) {
                $query->orWhere('tenant_id', $tenantId);
            }
        });
    }

    public function ensureTenantScope(Role $role, ?int $tenantId, ?int $userId = null): void
    {
        if (!Schema::hasTable('role_scopes')) {
            return;
        }

        RoleScope::updateOrCreate(
            [
                'role_id' => $role->id,
                'scope_type' => 'tenant',
                'scope_id' => (int) ($tenantId ?: 0),
            ],
            [
                'tenant_id' => $tenantId,
                'updated_by' => $userId,
            ]
        );
    }

    public function scopeLabels(): array
    {
        return [
            'organization' => 'شعبه / واحد پخش',
            'store' => 'انبار',
            'region' => 'منطقه',
            'area' => 'مسیر',
        ];
    }

    public function scopeOptions(?int $tenantId): array
    {
        return [
            'organization' => $this->organizationOptions($tenantId),
            'store' => $this->storeOptions($tenantId),
            'region' => $this->regionOptions($tenantId),
            'area' => $this->areaOptions($tenantId),
        ];
    }

    public function roleScopeValues(Role $role): array
    {
        if (!$role->exists || !Schema::hasTable('role_scopes')) {
            return collect($this->scopeLabels())->mapWithKeys(fn($label, $type) => [$type => []])->toArray();
        }

        $scopes = RoleScope::where('role_id', $role->id)
            ->whereIn('scope_type', array_keys($this->scopeLabels()))
            ->get()
            ->groupBy('scope_type');

        return collect($this->scopeLabels())->mapWithKeys(function ($label, $type) use ($scopes) {
            return [$type => $scopes->get($type, collect())->pluck('scope_id')->map(fn($id) => (int) $id)->toArray()];
        })->toArray();
    }

    public function syncRoleScopes(Role $role, ?int $tenantId, array $inputScopes, ?int $userId = null): string
    {
        $this->ensureTenantScope($role, $tenantId, $userId);

        if (!Schema::hasTable('role_scopes')) {
            return 'tenant';
        }

        $specificScopeTypes = array_keys($this->scopeLabels());
        RoleScope::where('role_id', $role->id)
            ->whereIn('scope_type', $specificScopeTypes)
            ->delete();

        $firstSpecificScope = null;

        foreach ($specificScopeTypes as $scopeType) {
            $scopeIds = collect($inputScopes[$scopeType] ?? [])
                ->filter(fn($scopeId) => (int) $scopeId > 0)
                ->map(fn($scopeId) => (int) $scopeId)
                ->unique()
                ->values();

            if ($firstSpecificScope === null && $scopeIds->isNotEmpty()) {
                $firstSpecificScope = $scopeType;
            }

            foreach ($scopeIds as $scopeId) {
                RoleScope::create([
                    'role_id' => $role->id,
                    'tenant_id' => $tenantId,
                    'scope_type' => $scopeType,
                    'scope_id' => $scopeId,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }
        }

        return $firstSpecificScope ?: 'tenant';
    }

    public function describeRoleScopes(Role $role): string
    {
        $values = $this->roleScopeValues($role);
        $labels = $this->scopeLabels();
        $parts = [];

        foreach ($labels as $type => $label) {
            $count = count($values[$type] ?? []);

            if ($count > 0) {
                $parts[] = $label . ': ' . $count;
            }
        }

        return $parts ? implode('، ', $parts) : 'کل پنل';
    }

    public function userScopeValues(User $user): array
    {
        if (!$user->exists || !Schema::hasTable('user_scopes')) {
            return collect($this->scopeLabels())->mapWithKeys(fn($label, $type) => [$type => []])->toArray();
        }

        $scopes = UserScope::where('user_id', $user->id)
            ->whereIn('scope_type', array_keys($this->scopeLabels()))
            ->get()
            ->groupBy('scope_type');

        return collect($this->scopeLabels())->mapWithKeys(function ($label, $type) use ($scopes) {
            return [$type => $scopes->get($type, collect())->pluck('scope_id')->map(fn($id) => (int) $id)->toArray()];
        })->toArray();
    }

    public function syncUserScopes(User $user, ?int $tenantId, array $inputScopes, ?int $actorId = null): void
    {
        if (!Schema::hasTable('user_scopes')) {
            return;
        }

        $specificScopeTypes = array_keys($this->scopeLabels());
        UserScope::where('user_id', $user->id)
            ->whereIn('scope_type', $specificScopeTypes)
            ->delete();

        foreach ($specificScopeTypes as $scopeType) {
            $scopeIds = collect($inputScopes[$scopeType] ?? [])
                ->filter(fn($scopeId) => (int) $scopeId > 0)
                ->map(fn($scopeId) => (int) $scopeId)
                ->unique()
                ->values();

            foreach ($scopeIds as $scopeId) {
                UserScope::create([
                    'user_id' => $user->id,
                    'tenant_id' => $tenantId,
                    'scope_type' => $scopeType,
                    'scope_id' => $scopeId,
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                ]);
            }
        }
    }

    public function describeUserScopes(User $user): string
    {
        $values = $this->userScopeValues($user);
        $labels = $this->scopeLabels();
        $parts = [];

        foreach ($labels as $type => $label) {
            $count = count($values[$type] ?? []);

            if ($count > 0) {
                $parts[] = $label . ': ' . $count;
            }
        }

        return $parts ? implode('، ', $parts) : 'از نقش';
    }

    public function scopeIdsForUser($user, string $scopeType)
    {
        if (!$user) {
            return collect();
        }

        $userScopeIds = Schema::hasTable('user_scopes')
            ? UserScope::query()
            ->where('user_id', $user->id)
            ->where('scope_type', $scopeType)
            ->pluck('scope_id')
            : collect();

        $roleScopeIds = Schema::hasTable('role_scopes')
            ? RoleScope::query()
            ->where('scope_type', $scopeType)
            ->whereIn('role_id', $user->roles()->pluck('roles.id'))
            ->pluck('scope_id')
            : collect();

        return collect($userScopeIds)
            ->merge($roleScopeIds)
            ->filter(fn($scopeId) => (int) $scopeId > 0)
            ->map(fn($scopeId) => (int) $scopeId)
            ->unique()
            ->values();
    }

    public function canAccessScope($user, string $scopeType, int $scopeId): bool
    {
        if ($user && $user->isGod == 1) {
            return true;
        }

        return $this->scopeIdsForUser($user, $scopeType)
            ->contains(fn($allowedScopeId) => (int) $allowedScopeId === $scopeId);
    }

    public function applyOperationalScopes(Builder $query, $user, array $columns = []): Builder
    {
        if (!$user || ($user && $user->isGod == 1)) {
            return $query;
        }

        $table = $query->getModel()->getTable();
        $tenantId = $this->tenantIdForUser($user);

        if ($tenantId) {
            $this->applyTenantFilter($query, $table, $tenantId);
        }

        if ($user->isAdmin == 1) {
            return $query;
        }

        $scopeColumns = [
            'organization' => $columns['organization'] ?? 'organization_id',
            'region' => $columns['region'] ?? 'region_id',
            'area' => $columns['area'] ?? ($table === 'customers' ? 'area' : 'area_id'),
            'store' => $columns['store'] ?? ($table === 'stores' ? 'id' : 'store_id'),
        ];

        foreach ($scopeColumns as $scopeType => $column) {
            if (!$this->hasColumn($table, $column)) {
                continue;
            }

            $scopeIds = $this->scopeIdsForUser($user, $scopeType)
                ->map(fn($scopeId) => (int) $scopeId)
                ->filter(fn($scopeId) => $scopeId > 0)
                ->unique()
                ->values();

            if ($scopeIds->isEmpty()) {
                continue;
            }

            $this->applyScopeIdFilter($query, $this->qualifiedColumn($table, $column), $scopeIds->all());
        }

        return $query;
    }

    private function hasTenantColumn(string $table): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id');
    }

    private function hasColumn(string $table, string $column): bool
    {
        $column = $this->plainColumn($column);

        return Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }

    private function plainColumn(string $column): string
    {
        return str_contains($column, '.') ? substr(strrchr($column, '.'), 1) : $column;
    }

    private function qualifiedColumn(string $table, string $column): string
    {
        return str_contains($column, '.') ? $column : $table . '.' . $column;
    }

    private function applyTenantFilter(Builder $query, string $table, int $tenantId): void
    {
        $hasTenantId = $this->hasColumn($table, 'tenant_id');
        $hasLegacyTenantId = $this->hasColumn($table, 'tenants_id');

        if (!$hasTenantId && !$hasLegacyTenantId) {
            return;
        }

        $query->where(function ($query) use ($table, $tenantId, $hasTenantId, $hasLegacyTenantId) {
            if ($hasTenantId) {
                $query->where($table . '.tenant_id', $tenantId);
            }

            if ($hasLegacyTenantId) {
                $method = $hasTenantId ? 'orWhere' : 'where';
                $query->{$method}($table . '.tenants_id', $tenantId);
            }
        });
    }

    private function applyScopeIdFilter(Builder $query, string $column, array $scopeIds): void
    {
        $query->where(function ($query) use ($column, $scopeIds) {
            foreach ($scopeIds as $scopeId) {
                $query->orWhere($column, $scopeId)
                    ->orWhere(function ($query) use ($column, $scopeId) {
                        $query->whereRaw("JSON_VALID({$column})")
                            ->whereJsonContains($column, $scopeId);
                    })
                    ->orWhere(function ($query) use ($column, $scopeId) {
                        $query->whereRaw("JSON_VALID({$column})")
                            ->whereJsonContains($column, (string) $scopeId);
                    });
            }
        });
    }

    private function organizationOptions(?int $tenantId)
    {
        if (!Schema::hasTable('organizations')) {
            return collect();
        }

        return DB::table('organizations')
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where(function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId)->orWhere('tenants_id', $tenantId);
                });
            })
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    private function storeOptions(?int $tenantId)
    {
        if (!Schema::hasTable('stores')) {
            return collect();
        }

        return DB::table('stores')
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where(function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId)->orWhere('tenants_id', $tenantId);
                });
            })
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    private function regionOptions(?int $tenantId)
    {
        if (!Schema::hasTable('regions')) {
            return collect();
        }

        return DB::table('regions')
            ->when($tenantId && Schema::hasColumn('regions', 'tenant_id'), fn($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->get(['id', 'name as title']);
    }

    private function areaOptions(?int $tenantId)
    {
        if (!Schema::hasTable('areas')) {
            return collect();
        }

        return DB::table('areas')
            ->when($tenantId && Schema::hasTable('regions') && Schema::hasColumn('regions', 'tenant_id'), function ($query) use ($tenantId) {
                $query->join('regions', 'regions.id', '=', 'areas.region_id')
                    ->where('regions.tenant_id', $tenantId);
            })
            ->orderBy('areas.name')
            ->get(['areas.id', 'areas.name as title']);
    }
}

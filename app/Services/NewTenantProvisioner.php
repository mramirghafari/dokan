<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Role;
use App\Models\Tenants;
use App\Models\TenantUserMembership;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NewTenantProvisioner
{
    public function __construct(
        private SettingService $settings,
        private PermissionBootstrapService $permissions,
        private FiscalYearService $fiscalYears
    ) {
    }

    public function provision(Tenants $tenant, Organization $organization, User $admin): array
    {
        return DB::transaction(function () use ($tenant, $organization, $admin) {
            $tenant->update([
                'currency_type' => $tenant->currency_type ?: 'rial',
            ]);

            $fiscalYear = $this->fiscalYears->ensureDefaultForTenant($tenant->id);
            $role = $this->ensurePanelManagerRole($tenant, $admin);
            $this->ensureMembership($tenant, $organization, $admin);
            $settingsCount = $this->applyDefaultSettings($tenant, $organization, $admin);

            return [
                'tenant_id' => $tenant->id,
                'fiscal_year_id' => $fiscalYear?->id,
                'admin_role_id' => $role->id,
                'settings_count' => $settingsCount,
            ];
        });
    }

    public function repair(Tenants $tenant, User $admin): array
    {
        $organization = Organization::query()
            ->where('tenant_id', $tenant->id)
            ->orWhere('tenants_id', $tenant->id)
            ->orderBy('id')
            ->first();

        if (!$organization) {
            throw new \RuntimeException('سازمان مرتبط با پنل یافت نشد.');
        }

        return $this->provision($tenant, $organization, $admin);
    }

    private function ensurePanelManagerRole(Tenants $tenant, User $admin): Role
    {
        $role = Role::query()
            ->where('tenant_id', $tenant->id)
            ->where('title', 'panel_manager')
            ->first();

        if (!$role) {
            $role = Role::create([
                'title' => 'panel_manager',
                'description' => 'مدیر کل',
                'tenant_id' => $tenant->id,
                'isActive' => 1,
            ]);
        } else {
            $role->update([
                'description' => 'مدیر کل',
                'isActive' => 1,
            ]);
        }

        $this->permissions->ensureCatalog();
        $this->permissions->syncRolePermissions($role);
        $admin->roles()->syncWithoutDetaching([$role->id]);

        return $role;
    }

    private function ensureMembership(Tenants $tenant, Organization $organization, User $admin): void
    {
        TenantUserMembership::query()->updateOrCreate(
            [
                'user_id' => $admin->id,
                'tenant_id' => $tenant->id,
            ],
            [
                'organization_id' => $organization->id,
                'is_admin' => true,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );
    }

    private function applyDefaultSettings(Tenants $tenant, Organization $organization, User $admin): int
    {
        $context = [
            'tenant_id' => $tenant->id,
            'organization_id' => $organization->id,
            'updated_by' => $admin->id,
        ];

        $tenantContext = [
            'tenant_id' => $tenant->id,
            'updated_by' => $admin->id,
        ];

        $settings = [
            'currency_type' => ['value' => 'rial', 'type' => 'select'],
            'panel_onboarding_active' => ['value' => 'yes', 'type' => 'boolean'],
            'panel_welcome_seen' => ['value' => 'no', 'type' => 'boolean'],
            'panel_tour_completed' => ['value' => 'no', 'type' => 'boolean'],
            'dashboard_widget_panel_manager_full_mode' => ['value' => 'no', 'type' => 'boolean'],
            'dashboard_widget_setup_card' => ['value' => 'yes', 'type' => 'boolean'],
        ];

        foreach ($settings as $key => $meta) {
            $saveContext = $this->shouldSaveAtTenantScope($key) ? $tenantContext : $context;

            $this->settings->set(
                $key,
                $meta['value'],
                $saveContext,
                $meta['type'],
                config("panel_settings.definitions.$key.group", 'general')
            );
        }

        return count($settings);
    }

    private function shouldSaveAtTenantScope(string $key): bool
    {
        return str_starts_with($key, 'panel_')
            || str_starts_with($key, 'dashboard_widget_');
    }
}

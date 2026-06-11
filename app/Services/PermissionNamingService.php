<?php

namespace App\Services;

use App\Models\Permission;

class PermissionNamingService
{
    public function parts(string $title): array
    {
        $normalized = $this->slug($title);
        $segments = array_values(array_filter(explode('-', $normalized)));
        $actionMap = $this->actionMap();
        $last = end($segments) ?: 'manage';
        $action = $actionMap[$last] ?? 'manage';

        if (isset($actionMap[$last]) && count($segments) > 1) {
            array_pop($segments);
        }

        $resource = $this->slug(implode('-', $segments) ?: $normalized ?: 'general');
        $module = $this->moduleFor($resource);

        return [
            'module_key' => $module,
            'resource_key' => $resource,
            'action_key' => $action,
            'canonical_title' => $module . '.' . str_replace('-', '_', $resource) . '.' . $action,
        ];
    }

    public function payload(string $title, array $overrides = []): array
    {
        $parts = $this->parts($title);

        foreach (['canonical_title', 'module_key', 'resource_key', 'action_key'] as $key) {
            if (!empty($overrides[$key])) {
                $parts[$key] = $this->normalizePart($key, $overrides[$key]);
            }
        }

        if (empty($parts['canonical_title'])) {
            $parts['canonical_title'] = $parts['module_key'] . '.' . str_replace('-', '_', $parts['resource_key']) . '.' . $parts['action_key'];
        }

        $parts['naming_status'] = 'mapped';

        return $parts;
    }

    public function syncAliases(Permission $permission): void
    {
        $aliases = collect([
            $permission->title,
            $permission->canonical_title,
        ])->filter()->unique()->values();

        foreach ($aliases as $alias) {
            $permission->aliases()->updateOrCreate(
                ['alias_title' => $alias],
                [
                    'tenant_id' => $permission->tenant_id,
                    'alias_type' => $alias === $permission->title ? 'legacy' : 'canonical',
                    'is_active' => true,
                ]
            );
        }
    }

    public function canonicalRule(): string
    {
        return 'regex:/^[a-z0-9_]+\.[a-z0-9_]+\.[a-z0-9_]+$/';
    }

    private function normalizePart(string $key, string $value): string
    {
        $value = strtolower(trim($value));

        if ($key === 'canonical_title') {
            $value = preg_replace('/[^a-z0-9_.]+/i', '_', $value) ?: '';

            return trim($value, '_.');
        }

        return $this->slug($value);
    }

    private function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?: 'general';

        return trim($value, '-') ?: 'general';
    }

    private function actionMap(): array
    {
        return [
            'add' => 'create',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'update',
            'update' => 'update',
            'delete' => 'delete',
            'destroy' => 'delete',
            'show' => 'view',
            'view' => 'view',
            'index' => 'view',
            'list' => 'view',
            'approve' => 'approve',
            'approved' => 'approve',
            'cancel' => 'cancel',
            'read' => 'read',
            'report' => 'report',
            'reports' => 'report',
            'export' => 'export',
            'import' => 'import',
        ];
    }

    private function moduleFor(string $resource): string
    {
        $first = explode('-', $resource)[0] ?? $resource;

        return [
            'roles' => 'admin',
            'permissions' => 'admin',
            'users' => 'admin',
            'tenants' => 'admin',
            'customers' => 'crm',
            'tasks' => 'crm',
            'targets' => 'sales',
            'commissions' => 'sales',
            'invoices' => 'sales',
            'factor' => 'sales',
            'factors' => 'sales',
            'products' => 'inventory',
            'stocks' => 'inventory',
            'receipt' => 'inventory',
            'receipts' => 'inventory',
            'transfers' => 'inventory',
            'materials' => 'inventory',
            'stores' => 'inventory',
            'production' => 'production',
            'account' => 'finance',
            'accounting' => 'finance',
            'reports' => 'reports',
            'report' => 'reports',
            'distribution' => 'distribution',
            'shipments' => 'distribution',
            'ecommerce' => 'sales',
        ][$first] ?? $first;
    }
}

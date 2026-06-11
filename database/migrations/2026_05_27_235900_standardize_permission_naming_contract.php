<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->extendPermissions();
        $this->createPermissionAliases();
        $this->backfillPermissionNames();
    }

    public function down(): void
    {
        // Non-destructive rollback: keep standardized permission names and aliases intact.
    }

    private function extendPermissions(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'canonical_title')) {
                $table->string('canonical_title', 190)->nullable()->after('title')->index();
            }
            if (!Schema::hasColumn('permissions', 'module_key')) {
                $table->string('module_key', 80)->nullable()->after('canonical_title')->index();
            }
            if (!Schema::hasColumn('permissions', 'resource_key')) {
                $table->string('resource_key', 120)->nullable()->after('module_key')->index();
            }
            if (!Schema::hasColumn('permissions', 'action_key')) {
                $table->string('action_key', 80)->nullable()->after('resource_key')->index();
            }
            if (!Schema::hasColumn('permissions', 'naming_status')) {
                $table->string('naming_status', 30)->default('mapped')->after('action_key')->index();
            }
        });
    }

    private function createPermissionAliases(): void
    {
        if (Schema::hasTable('permission_aliases')) {
            return;
        }

        Schema::create('permission_aliases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permission_id')->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('alias_title', 190)->index();
            $table->string('alias_type', 30)->default('legacy')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['tenant_id', 'alias_title', 'is_active'], 'permission_alias_lookup_index');
        });
    }

    private function backfillPermissionNames(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        DB::table('permissions')->orderBy('id')->get()->each(function ($permission) {
            $parts = $this->standardParts((string) $permission->title);

            DB::table('permissions')->where('id', $permission->id)->update([
                'canonical_title' => $permission->canonical_title ?: $parts['canonical_title'],
                'module_key' => $permission->module_key ?: $parts['module_key'],
                'resource_key' => $permission->resource_key ?: $parts['resource_key'],
                'action_key' => $permission->action_key ?: $parts['action_key'],
                'naming_status' => $permission->naming_status ?: 'mapped',
                'updated_at' => now(),
            ]);

            if (Schema::hasTable('permission_aliases')) {
                foreach (array_filter([(string) $permission->title, $parts['canonical_title']]) as $alias) {
                    DB::table('permission_aliases')->updateOrInsert(
                        ['permission_id' => $permission->id, 'alias_title' => $alias],
                        [
                            'tenant_id' => $permission->tenant_id ?? null,
                            'alias_type' => $alias === $permission->title ? 'legacy' : 'canonical',
                            'is_active' => true,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }
        });
    }

    private function standardParts(string $title): array
    {
        $normalized = $this->slug($title);
        $segments = array_values(array_filter(explode('-', $normalized)));
        $actionMap = [
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

    private function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?: 'general';

        return trim($value, '-') ?: 'general';
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
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $this->extendRolesTable();
        $this->extendPermissionsTable();
        $this->createRoleScopesTable();
        $this->backfillRoleScopes();
    }

    public function down()
    {
        // Non-destructive migration: keep scoped RBAC data if a rollback is attempted.
    }

    private function extendRolesTable(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('description')->index();
            }

            if (!Schema::hasColumn('roles', 'scope_type')) {
                $table->string('scope_type')->default('tenant')->after('tenant_id')->index();
            }
        });
    }

    private function extendPermissionsTable(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('description')->index();
            }
        });
    }

    private function createRoleScopesTable(): void
    {
        if (Schema::hasTable('role_scopes')) {
            return;
        }

        Schema::create('role_scopes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('scope_type')->default('tenant')->index();
            $table->unsignedBigInteger('scope_id')->default(0)->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['role_id', 'scope_type', 'scope_id'], 'role_scopes_unique_scope');
        });
    }

    private function backfillRoleScopes(): void
    {
        if (!Schema::hasTable('role_scopes') || !Schema::hasTable('roles')) {
            return;
        }

        DB::table('roles')->orderBy('id')->get(['id', 'tenant_id'])->each(function ($role) {
            DB::table('role_scopes')->updateOrInsert(
                [
                    'role_id' => $role->id,
                    'scope_type' => 'tenant',
                    'scope_id' => (int) ($role->tenant_id ?: 0),
                ],
                [
                    'tenant_id' => $role->tenant_id,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        });

        if (Schema::hasTable('role_store')) {
            DB::table('role_store')->orderBy('role_id')->get()->each(function ($scope) {
                DB::table('role_scopes')->updateOrInsert(
                    [
                        'role_id' => $scope->role_id,
                        'scope_type' => 'store',
                        'scope_id' => (int) $scope->store_id,
                    ],
                    [
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            });
        }

        if (Schema::hasTable('region_role')) {
            DB::table('region_role')->orderBy('role_id')->get()->each(function ($scope) {
                DB::table('role_scopes')->updateOrInsert(
                    [
                        'role_id' => $scope->role_id,
                        'scope_type' => 'region',
                        'scope_id' => (int) $scope->region_id,
                    ],
                    [
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            });
        }
    }
};

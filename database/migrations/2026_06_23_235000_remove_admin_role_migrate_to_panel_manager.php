<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove the global "admin" (مدیریت سیستم, id=1) role and migrate all its users to
     * "panel_manager" (مدیر کل پنل).
     *
     * Context:
     *  - role id=1 (admin, tenant_id=NULL): held by 10 users, all on tenant 1 (پنل دارمینو).
     *  - role id=9 (panel_manager, tenant_id=4): the desired replacement concept.
     *
     * Strategy:
     *  1. Create a panel_manager role for tenant 1, copying all permissions from role 9.
     *  2. Reassign all role_user rows from role 1 → new tenant-1 panel_manager.
     *  3. Clean up pivot rows for the old admin role.
     *  4. Soft-delete role id=1.
     */
    public function up(): void
    {
        $adminRoleId  = 1;
        $sourcePmRoleId = 9;   // panel_manager for tenant 4 – used as permission template
        $tenant1Id    = 1;

        // 1. Ensure there is no existing panel_manager for tenant 1 (idempotent).
        $existing = DB::table('roles')
            ->where('title', 'panel_manager')
            ->where('tenant_id', $tenant1Id)
            ->whereNull('deleted_at')
            ->first();

        if (!$existing) {
            $newRoleId = DB::table('roles')->insertGetId([
                'title'       => 'panel_manager',
                'description' => 'مدیر کل پنل',
                'isActive'    => 1,
                'tenant_id'   => $tenant1Id,
                'scope_type'  => 'tenant',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // Copy permissions from the tenant-4 panel_manager (id=9) template.
            $permissions = DB::table('permission_role')
                ->where('role_id', $sourcePmRoleId)
                ->pluck('permission_id');

            $rows = $permissions->map(fn ($pid) => [
                'role_id'       => $newRoleId,
                'permission_id' => $pid,
            ])->all();

            if (!empty($rows)) {
                DB::table('permission_role')->insert($rows);
            }
        } else {
            $newRoleId = $existing->id;
        }

        // 2. Move all users who hold role 1 (admin) to the new tenant-1 panel_manager.
        //    Avoid creating duplicates if a user already holds the new role.
        $alreadyHasNew = DB::table('role_user')
            ->where('role_id', $newRoleId)
            ->pluck('user_id')
            ->all();

        DB::table('role_user')
            ->where('role_id', $adminRoleId)
            ->whereNotIn('user_id', $alreadyHasNew)
            ->update(['role_id' => $newRoleId]);

        // Remove any leftover admin role_user rows (those who already had panel_manager).
        DB::table('role_user')
            ->where('role_id', $adminRoleId)
            ->delete();

        // 3. Clean up all pivot / child rows for the old admin role.
        DB::table('permission_role')->where('role_id', $adminRoleId)->delete();
        DB::table('role_scopes')->where('role_id', $adminRoleId)->delete();

        foreach (['role_store', 'region_role', 'material_store_role'] as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->where('role_id', $adminRoleId)->delete();
            }
        }

        // 4. Soft-delete the admin role.
        DB::table('roles')
            ->where('id', $adminRoleId)
            ->update(['deleted_at' => now()]);
    }

    public function down(): void
    {
        // Data migration — not safely reversible without a full backup restore.
    }
};

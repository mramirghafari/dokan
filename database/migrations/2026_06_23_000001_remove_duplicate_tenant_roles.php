<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Find tenant-specific roles that duplicate a global role with the same title.
        // "Duplicate" means: same title, tenant_id IS NOT NULL, and a global (tenant_id IS NULL)
        // version exists. When permissions are identical, the tenant copy is redundant.
        $duplicates = DB::table('roles as t')
            ->join('roles as g', function ($join) {
                $join->on('g.title', '=', 't.title')
                    ->whereNull('g.tenant_id')
                    ->whereNull('g.deleted_at');
            })
            ->whereNotNull('t.tenant_id')
            ->whereNull('t.deleted_at')
            ->select('t.id as dup_id', 'g.id as global_id', 't.title', 't.tenant_id')
            ->get();

        foreach ($duplicates as $dup) {
            // Re-point every user that holds the redundant role to the global role.
            DB::table('role_user')
                ->where('role_id', $dup->dup_id)
                ->update(['role_id' => $dup->global_id]);

            // Clean up pivot / child rows before soft-deleting the role.
            DB::table('permission_role')->where('role_id', $dup->dup_id)->delete();
            DB::table('role_scopes')->where('role_id', $dup->dup_id)->delete();

            if (DB::getSchemaBuilder()->hasTable('role_store')) {
                DB::table('role_store')->where('role_id', $dup->dup_id)->delete();
            }

            if (DB::getSchemaBuilder()->hasTable('region_role')) {
                DB::table('region_role')->where('role_id', $dup->dup_id)->delete();
            }

            if (DB::getSchemaBuilder()->hasTable('material_store_role')) {
                DB::table('material_store_role')->where('role_id', $dup->dup_id)->delete();
            }

            // Soft-delete the duplicate role (roles table uses SoftDeletes).
            DB::table('roles')
                ->where('id', $dup->dup_id)
                ->update(['deleted_at' => now()]);
        }
    }

    public function down(): void
    {
        // Data migration — not safely reversible.
    }
};

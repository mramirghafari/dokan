<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clarify role descriptions for genuinely-different look-alike pairs,
     * and soft-delete the unused "agent" role whose permissions are a strict
     * subset of "reseller" and which has zero users assigned.
     *
     * Pairs resolved:
     *  - admin  (global, id=1)       → description: "مدیریت سیستم"  (was "مدیریت")
     *  - panel_manager (tenant_id=4) → description: "مدیر کل پنل"   (was "مدیر کل")
     *  - agent  (global, id=8, 0 users, 3 perms ⊂ reseller) → soft-deleted
     */
    public function up(): void
    {
        // Clarify admin vs panel_manager labels
        DB::table('roles')
            ->where('title', 'admin')
            ->whereNull('tenant_id')
            ->whereNull('deleted_at')
            ->update(['description' => 'مدیریت سیستم', 'updated_at' => now()]);

        DB::table('roles')
            ->where('title', 'panel_manager')
            ->whereNull('deleted_at')
            ->update(['description' => 'مدیر کل پنل', 'updated_at' => now()]);

        // Soft-delete the unused "agent" role (0 users, permissions ⊂ reseller)
        DB::table('roles')
            ->where('title', 'agent')
            ->whereNull('tenant_id')
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Restore original descriptions
        DB::table('roles')
            ->where('title', 'admin')
            ->whereNull('tenant_id')
            ->update(['description' => 'مدیریت', 'updated_at' => now()]);

        DB::table('roles')
            ->where('title', 'panel_manager')
            ->update(['description' => 'مدیر کل', 'updated_at' => now()]);

        // Restore agent role
        DB::table('roles')
            ->where('title', 'agent')
            ->whereNull('tenant_id')
            ->update(['deleted_at' => null, 'updated_at' => now()]);
    }
};

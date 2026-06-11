<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $existingRole = DB::table('roles')->where('title', 'agent')->first();

        if (!$existingRole) {
            $roleId = DB::table('roles')->insertGetId([
                'title' => 'agent',
                'description' => 'نماینده',
                'isActive' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } else {
            $roleId = $existingRole->id;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('title', ['products', 'invoice-add', 'neworder'])
            ->pluck('id');

        foreach ($permissionIds as $permissionId) {
            $exists = DB::table('permission_role')
                ->where('permission_id', $permissionId)
                ->where('role_id', $roleId)
                ->exists();

            if (!$exists) {
                DB::table('permission_role')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    public function down()
    {
        $role = DB::table('roles')->where('title', 'agent')->first();

        if (!$role) {
            return;
        }

        DB::table('permission_role')->where('role_id', $role->id)->delete();
        DB::table('role_store')->where('role_id', $role->id)->delete();
        DB::table('role_user')->where('role_id', $role->id)->delete();
        DB::table('roles')->where('id', $role->id)->delete();
    }
};

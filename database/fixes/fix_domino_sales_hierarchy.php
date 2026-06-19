<?php

/**
 * One-time scoped fix for Domino panel (tenant_id=1) sales hierarchy.
 * Run: php database/fixes/fix_domino_sales_hierarchy.php
 */

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$tenantId = 1;
$managerId = 36; // میلاد سامنی
$supervisorId = 33; // میلاد سامنی سرپرست

$roleIds = DB::table('roles')
    ->whereNull('tenant_id')
    ->whereIn('title', ['admin', 'expert', 'leader', 'visitor'])
    ->pluck('id', 'title');

echo "Applying Domino sales hierarchy fix...\n";

DB::transaction(function () use ($tenantId, $managerId, $supervisorId, $roleIds) {
    // میلاد سامنی سرپرست => سرپرست (leader) under manager
    DB::table('users')->where('id', $supervisorId)->update([
        'leader_id' => $managerId,
        'organization_id' => json_encode(['1', '4']),
        'tenant_id' => 1,
        'tenants_id' => 1,
        'updated_at' => now(),
    ]);

    DB::table('role_user')
        ->where('user_id', $supervisorId)
        ->where('role_id', $roleIds['admin'])
        ->update(['role_id' => $roleIds['leader']]);

    // امیر سرپرست (active leader in تیسا) reports to میلاد سامنی
    DB::table('users')->where('id', 3)->where('tenant_id', $tenantId)->update([
        'leader_id' => $managerId,
        'updated_at' => now(),
    ]);

    // Ensure manager has active target (already exists id=82); deactivate duplicate leader targets on supervisor if conflicting
    $activeManagerTarget = DB::table('targets')
        ->where('user_id', $managerId)
        ->where('status', 1)
        ->first();

    if (!$activeManagerTarget) {
        DB::table('targets')->insert([
            'user_id' => $managerId,
            'leader_id' => $managerId,
            'target_price' => '1000000000000',
            'target_type' => 'sales_amount',
            'period_type' => 'custom',
            'calculation_scope' => 'own_and_children',
            'achievement_threshold_percent' => '100.00',
            'bonus_amount' => '0.00',
            'penalty_amount' => '0.00',
            'settlement_status' => 'open',
            'start_date_fa' => '1404/12/21',
            'start_date_en' => '2026-03-12 00:00:00',
            'end_date_fa' => '1405/12/29',
            'end_date_en' => '2027-03-20 00:00:00',
            'status' => 1,
            'organization_id' => '1',
            'tenant_id' => $tenantId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "Created manager target for user {$managerId}\n";
    }

    // Panel memberships stay scoped to tenant 1
    foreach ([$managerId, $supervisorId] as $userId) {
        $exists = DB::table('tenant_user_memberships')
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->exists();

        if (!$exists) {
            DB::table('tenant_user_memberships')->insert([
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'organization_id' => 1,
                'is_admin' => $userId === $managerId ? 1 : 0,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    DB::table('tenant_user_memberships')
        ->where('user_id', $supervisorId)
        ->where('tenant_id', $tenantId)
        ->update(['is_admin' => 0, 'is_active' => 1, 'updated_at' => now()]);
});

echo "Done.\n";

// Verify
$leaderRoleId = $roleIds['leader'];
$visitorRoleId = $roleIds['visitor'];

$leaders = DB::table('users')
    ->where('leader_id', $managerId)
    ->whereIn('id', DB::table('role_user')->where('role_id', $leaderRoleId)->pluck('user_id'))
    ->get(['id', 'name', 'leader_id']);

echo "\nLeaders under manager {$managerId}:\n";
foreach ($leaders as $l) {
    echo "  - {$l->id}: {$l->name}\n";
}

$leaderIds = $leaders->pluck('id');
$visitors = DB::table('users')
    ->whereIn('leader_id', $leaderIds)
    ->where('isActive', 1)
    ->whereIn('id', DB::table('role_user')->where('role_id', $visitorRoleId)->pluck('user_id'))
    ->get(['id', 'name', 'leader_id']);

echo "\nActive marketers under those leaders:\n";
foreach ($visitors as $v) {
    echo "  - {$v->id}: {$v->name} (leader_id={$v->leader_id})\n";
}

echo "\nRoles after fix:\n";
foreach ([$managerId, $supervisorId] as $uid) {
    $u = DB::table('users')->where('id', $uid)->first();
    $roles = DB::table('role_user')
        ->join('roles', 'roles.id', '=', 'role_user.role_id')
        ->where('role_user.user_id', $uid)
        ->pluck('roles.description')
        ->implode(', ');
    echo "  {$u->name} (id={$uid}): leader_id={$u->leader_id}, roles={$roles}\n";
}

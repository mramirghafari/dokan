<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\Tenants;
use App\Models\TenantUserMembership;
use App\Models\User;
use App\Services\PanelMembershipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PanelMembershipServiceTest extends TestCase
{
    use RefreshDatabase;

    private PanelMembershipService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PanelMembershipService::class);
    }

    public function test_user_with_single_membership_gets_one_panel(): void
    {
        $tenant = Tenants::create([
            'name' => 'پنل الف',
            'display_name' => 'پنل الف',
            'status' => 1,
        ]);

        $user = User::create([
            'name' => 'کاربر تست',
            'username' => 'user_a',
            'mobile' => '09120000001',
            'password' => Hash::make('secret'),
            'tenant_id' => $tenant->id,
            'tenants_id' => $tenant->id,
            'isActive' => 1,
        ]);

        TenantUserMembership::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);

        $panels = $this->service->accessiblePanelsForUser($user);

        $this->assertCount(1, $panels);
        $this->assertSame($tenant->id, $panels->first()['tenant_id']);
    }

    public function test_same_mobile_on_two_panels_returns_two_entries(): void
    {
        $tenantA = Tenants::create(['name' => 'پنل A', 'display_name' => 'پنل A', 'status' => 1]);
        $tenantB = Tenants::create(['name' => 'پنل B', 'display_name' => 'پنل B', 'status' => 1]);

        $userA = User::create([
            'name' => 'ممد',
            'username' => 'mamad_a',
            'mobile' => '09123334444',
            'password' => Hash::make('secret'),
            'tenant_id' => $tenantA->id,
            'tenants_id' => $tenantA->id,
            'isActive' => 1,
        ]);

        $userB = User::create([
            'name' => 'ممد',
            'username' => 'mamad_b',
            'mobile' => '09123334444',
            'password' => Hash::make('secret'),
            'tenant_id' => $tenantB->id,
            'tenants_id' => $tenantB->id,
            'isActive' => 1,
        ]);

        TenantUserMembership::create(['user_id' => $userA->id, 'tenant_id' => $tenantA->id, 'is_active' => true]);
        TenantUserMembership::create(['user_id' => $userB->id, 'tenant_id' => $tenantB->id, 'is_active' => true]);

        $panels = $this->service->accessiblePanelsForMobile('09123334444');

        $this->assertCount(2, $panels);
        $this->assertEqualsCanonicalizing(
            [$tenantA->id, $tenantB->id],
            $panels->pluck('tenant_id')->all()
        );
    }

    public function test_activate_panel_sets_session_and_switches_user_context(): void
    {
        $tenant = Tenants::create(['name' => 'پنل فعال', 'display_name' => 'پنل فعال', 'status' => 1]);

        $role = Role::create([
            'title' => 'accountant',
            'description' => 'مسئول حسابداری',
            'tenant_id' => $tenant->id,
            'isActive' => 1,
        ]);

        $user = User::create([
            'name' => 'حسابدار',
            'username' => 'acc1',
            'mobile' => '09125556666',
            'password' => Hash::make('secret'),
            'tenant_id' => $tenant->id,
            'tenants_id' => $tenant->id,
            'isActive' => 1,
        ]);

        $user->roles()->attach($role->id);

        TenantUserMembership::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);

        $this->be($user);
        $activated = $this->service->activatePanel($user, $tenant->id);

        $this->assertSame($tenant->id, session(PanelMembershipService::SESSION_TENANT_KEY));
        $this->assertSame($tenant->id, $activated->tenant_id);
        $this->assertSame('مسئول حسابداری', $this->service->roleLabelForActivePanel($activated));
    }
}

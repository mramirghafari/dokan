<?php

namespace Tests\Feature;

use App\Models\Customers;
use App\Models\Product;
use App\Models\User;
use App\Scopes\TenantScope;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    public function test_belongs_to_tenant_trait_is_registered_on_core_models(): void
    {
        $this->assertTrue(method_exists(Customers::class, 'withoutTenantScope'));
        $this->assertTrue(method_exists(Product::class, 'withoutTenantScope'));
    }

    public function test_god_user_queries_are_not_restricted_by_empty_result_guard(): void
    {
        if (!Schema::hasTable('customers') || !Schema::hasColumn('customers', 'tenant_id')) {
            $this->markTestSkipped('customers.tenant_id is not available.');
        }

        $god = new User([
            'isGod' => 1,
            'tenant_id' => null,
            'tenants_id' => null,
        ]);
        $god->id = 1;

        $this->actingAs($god);

        $sql = Customers::query()->toSql();

        $this->assertStringNotContainsString('1 = 0', $sql);
    }

    public function test_non_god_user_without_tenant_gets_empty_scope(): void
    {
        if (!Schema::hasTable('customers') || !Schema::hasColumn('customers', 'tenant_id')) {
            $this->markTestSkipped('customers.tenant_id is not available.');
        }

        $user = new User([
            'isGod' => 0,
            'tenant_id' => null,
            'tenants_id' => null,
        ]);
        $user->id = 2;

        $this->actingAs($user);

        $sql = Customers::query()->toSql();

        $this->assertStringContainsString('1 = 0', $sql);
    }

    public function test_tenant_scope_can_be_disabled_via_config(): void
    {
        config(['erp_scale.tenant_scope.enabled' => false]);

        $user = new User([
            'isGod' => 0,
            'tenant_id' => 15,
            'tenants_id' => 15,
        ]);
        $user->id = 3;

        $this->actingAs($user);

        if (!Schema::hasTable('customers')) {
            $this->markTestSkipped('customers table is not available.');
        }

        $sql = Customers::query()->toSql();

        $this->assertStringNotContainsString('tenant_id', $sql);
    }

    public function test_run_without_allows_cross_tenant_reads(): void
    {
        if (!Schema::hasTable('customers') || !Schema::hasColumn('customers', 'tenant_id')) {
            $this->markTestSkipped('customers.tenant_id is not available.');
        }

        $user = new User([
            'isGod' => 0,
            'tenant_id' => 3,
            'tenants_id' => 3,
        ]);
        $user->id = 4;

        $this->actingAs($user);

        $scopedSql = Customers::query()->toSql();

        $unscopedSql = TenantScope::runWithout(fn () => Customers::query()->toSql());

        $this->assertStringContainsString('tenant_id', $scopedSql);
        $this->assertStringNotContainsString('tenant_id', $unscopedSql);
    }
}

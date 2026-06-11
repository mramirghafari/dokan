<?php

namespace Tests\Unit;

use App\Scopes\TenantScope;
use App\Services\TenantContextService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    protected function tearDown(): void
    {
        TenantScope::bypass(false);
        Mockery::close();
        parent::tearDown();
    }

    public function test_resolve_tenant_id_uses_tenant_context_service(): void
    {
        $service = Mockery::mock(TenantContextService::class);
        $service->shouldReceive('tenantId')->once()->andReturn(42);
        $this->app->instance(TenantContextService::class, $service);

        $this->assertSame(42, TenantScope::resolveTenantId());
    }

    public function test_run_without_bypasses_scope_application(): void
    {
        TenantScope::bypass(false);

        $applied = TenantScope::runWithout(function () {
            return TenantScope::isBypassed();
        });

        $this->assertTrue($applied);
        $this->assertFalse(TenantScope::isBypassed());
    }

    public function test_for_tenant_forces_scope_context(): void
    {
        $resolved = TenantScope::forTenant(7, function () {
            return TenantScope::resolveTenantId();
        });

        $this->assertSame(7, $resolved);
        $this->assertNull(TenantScope::resolveTenantId());
    }

    public function test_apply_tenant_constraint_filters_legacy_and_primary_columns(): void
    {
        Schema::dropIfExists('tenant_scope_probe');
        Schema::create('tenant_scope_probe', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('tenants_id')->nullable();
        });

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('where')->once()->with(Mockery::type('Closure'))->andReturnSelf();

        TenantScope::applyTenantConstraint($builder, 'tenant_scope_probe', 9);

        Schema::dropIfExists('tenant_scope_probe');
        $this->assertTrue(true);
    }
}

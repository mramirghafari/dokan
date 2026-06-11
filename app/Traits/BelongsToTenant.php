<?php

namespace App\Traits;

use App\Models\Tenants;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Global tenant isolation via {@see TenantScope}.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(TenantScope::class, new TenantScope());

        static::creating(function (Model $model) {
            static::assignTenantOnCreate($model);
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenants::class, 'tenant_id');
    }

    public function scopeForTenant($query, int $tenantId)
    {
        $table = $query->getModel()->getTable();

        return $query->where(function ($inner) use ($table, $tenantId) {
            if (Schema::hasColumn($table, 'tenant_id')) {
                $inner->where($table . '.tenant_id', $tenantId);
            }

            if (Schema::hasColumn($table, 'tenants_id')) {
                $method = Schema::hasColumn($table, 'tenant_id') ? 'orWhere' : 'where';
                $inner->{$method}($table . '.tenants_id', $tenantId);
            }
        });
    }

    public static function withoutTenantScope()
    {
        return static::withoutGlobalScope(TenantScope::class);
    }

    protected static function assignTenantOnCreate(Model $model): void
    {
        if (!config('erp_scale.tenant_scope.enabled', true)) {
            return;
        }

        $tenantId = TenantScope::resolveTenantId();

        if (!$tenantId) {
            return;
        }

        $table = $model->getTable();

        if (
            Schema::hasColumn($table, 'tenant_id')
            && ($model->getAttribute('tenant_id') === null || $model->getAttribute('tenant_id') === '')
        ) {
            $model->setAttribute('tenant_id', $tenantId);
        }

        if (
            Schema::hasColumn($table, 'tenants_id')
            && ($model->getAttribute('tenants_id') === null || $model->getAttribute('tenants_id') === '')
        ) {
            $model->setAttribute('tenants_id', $tenantId);
        }
    }
}

<?php

namespace App\Scopes;

use App\Services\TenantContextService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;

class TenantScope implements Scope
{
    protected static bool $bypass = false;

    protected static ?int $forcedTenantId = null;

    public function apply(Builder $builder, Model $model): void
    {
        if (!config('erp_scale.tenant_scope.enabled', true)) {
            return;
        }

        if (static::$bypass) {
            return;
        }

        $user = auth()->user();

        if ($user && (int) $user->isGod === 1) {
            return;
        }

        $tenantId = static::resolveTenantId();

        if (!$tenantId) {
            if ($user) {
                $builder->whereRaw('1 = 0');
            }

            return;
        }

        static::applyTenantConstraint($builder, $model->getTable(), $tenantId);
    }

    public static function resolveTenantId(): ?int
    {
        if (static::$forcedTenantId) {
            return static::$forcedTenantId;
        }

        return app(TenantContextService::class)->tenantId();
    }

    public static function bypass(bool $bypass = true): void
    {
        static::$bypass = $bypass;
    }

    public static function isBypassed(): bool
    {
        return static::$bypass;
    }

    /**
     * @template TReturn
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function runWithout(callable $callback)
    {
        $previous = static::$bypass;
        static::$bypass = true;

        try {
            return $callback();
        } finally {
            static::$bypass = $previous;
        }
    }

    /**
     * @template TReturn
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function forTenant(?int $tenantId, callable $callback)
    {
        $previousForced = static::$forcedTenantId;
        $previousBypass = static::$bypass;

        static::$forcedTenantId = $tenantId;
        static::$bypass = false;

        try {
            return $callback();
        } finally {
            static::$forcedTenantId = $previousForced;
            static::$bypass = $previousBypass;
        }
    }

    public static function applyTenantConstraint(Builder $builder, string $table, int $tenantId): void
    {
        $hasTenantId = Schema::hasColumn($table, 'tenant_id');
        $hasLegacyTenantId = Schema::hasColumn($table, 'tenants_id');

        if (!$hasTenantId && !$hasLegacyTenantId) {
            return;
        }

        $builder->where(function (Builder $query) use ($table, $tenantId, $hasTenantId, $hasLegacyTenantId) {
            if ($hasTenantId) {
                $query->where($table . '.tenant_id', $tenantId);
            }

            if ($hasLegacyTenantId) {
                $method = $hasTenantId ? 'orWhere' : 'where';
                $query->{$method}($table . '.tenants_id', $tenantId);
            }
        });
    }
}

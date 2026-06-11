<?php

namespace App\Traits;

trait SyncsTenantColumns
{
    public function setTenantIdAttribute($value): void
    {
        $attributes = $this->getAttributes();
        $attributes['tenant_id'] = $value;

        if (!array_key_exists('tenants_id', $attributes) || empty($attributes['tenants_id'])) {
            $attributes['tenants_id'] = $value;
        }

        $this->setRawAttributes($attributes);
    }

    public function setTenantsIdAttribute($value): void
    {
        $attributes = $this->getAttributes();
        $attributes['tenants_id'] = $value;

        if (!array_key_exists('tenant_id', $attributes) || empty($attributes['tenant_id'])) {
            $attributes['tenant_id'] = $value;
        }

        $this->setRawAttributes($attributes);
    }

    public function getEffectiveTenantIdAttribute()
    {
        return $this->getAttribute('tenant_id') ?: $this->getAttribute('tenants_id');
    }
}

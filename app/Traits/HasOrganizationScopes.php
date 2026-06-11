<?php

namespace App\Traits;

use App\Models\Organization;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasOrganizationScopes
{
    public function organizationScopes()
    {
        return $this->morphMany(\App\Models\OrganizationScope::class, 'scopeable');
    }

    public function scopedOrganizations()
    {
        return $this->morphToMany(Organization::class, 'scopeable', 'organization_scopes')
            ->withPivot(['tenant_id', 'is_primary', 'source'])
            ->withTimestamps();
    }

    public function primaryOrganizationScope()
    {
        return $this->morphOne(\App\Models\OrganizationScope::class, 'scopeable')->where('is_primary', true);
    }

    public function primaryOrganizationId()
    {
        $scope = $this->primaryOrganizationScope()->first();

        return $scope?->organization_id ?: $this->firstLegacyOrganizationId();
    }

    public function firstLegacyOrganizationId()
    {
        foreach ($this->legacyOrganizationIds() as $organizationId) {
            return $organizationId;
        }

        return null;
    }

    public function legacyOrganizationIds(): array
    {
        $raw = $this->getAttribute('organization_id');

        if ($raw === null || $raw === '') {
            return [];
        }

        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        $values = is_array($decoded) ? $decoded : [$raw];
        $ids = [];

        foreach ($values as $value) {
            if (is_array($value)) {
                foreach ($value as $nestedValue) {
                    if (is_numeric($nestedValue)) {
                        $ids[] = (int) $nestedValue;
                    }
                }
                continue;
            }

            if (is_numeric($value)) {
                $ids[] = (int) $value;
            }
        }

        return array_values(array_unique(array_filter($ids)));
    }
}

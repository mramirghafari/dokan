<?php

namespace App\Services;

use App\Models\Feature;
use App\Models\OrganizationFeature;
use App\Models\Setting;
use App\Models\TenantFeature;
use Illuminate\Support\Facades\Schema;

class SettingService
{
    public function get(string $key, array $context = [], $default = null)
    {
        if (str_starts_with($key, 'feature_')) {
            $featureValue = $this->featureValue($key, $context);

            if ($featureValue !== null) {
                return $featureValue;
            }
        }

        $setting = $this->findScopedSetting($key, $context);

        if ($setting) {
            return $this->castValue($setting->value, $setting->type ?? 'string');
        }

        return config("panel_settings.definitions.$key.default", $default);
    }

    public function enabled(string $key, array $context = []): bool
    {
        return $this->get($key, $context, 'no') === 'yes';
    }

    public function set(string $key, $value, array $context = [], string $type = 'string', string $category = 'general'): Setting
    {
        $scope = $this->scopeForContext($context);

        return Setting::updateOrCreate(
            [
                'scope' => $scope,
                'tenant_id' => $context['tenant_id'] ?? null,
                'organization_id' => $context['organization_id'] ?? null,
                'store_id' => $context['store_id'] ?? null,
                'user_id' => $context['user_id'] ?? null,
                'key' => $key,
            ],
            [
                'title' => $key,
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'type' => $type,
                'category' => $category,
                'updated_by' => $context['updated_by'] ?? null,
            ]
        );
    }

    private function featureValue(string $key, array $context): ?string
    {
        if (!Schema::hasTable('features')) {
            return null;
        }

        $feature = Feature::where('key', $key)->where('is_active', true)->first();

        if (!$feature) {
            return null;
        }

        if (!empty($context['organization_id']) && Schema::hasTable('organization_features')) {
            $organizationFeature = OrganizationFeature::where('organization_id', $context['organization_id'])
                ->where('feature_id', $feature->id)
                ->first();

            if ($organizationFeature) {
                return $organizationFeature->is_enabled ? 'yes' : 'no';
            }
        }

        if (!empty($context['tenant_id']) && Schema::hasTable('tenant_features')) {
            $tenantFeature = TenantFeature::where('tenant_id', $context['tenant_id'])
                ->where('feature_id', $feature->id)
                ->first();

            if ($tenantFeature) {
                return $tenantFeature->is_enabled ? 'yes' : 'no';
            }
        }

        $globalSetting = $this->globalSettingValue($key);

        if ($globalSetting !== null) {
            return $globalSetting;
        }

        return $feature->default_value;
    }

    private function globalSettingValue(string $key): ?string
    {
        if (!Schema::hasTable('settings')) {
            return null;
        }

        $setting = Setting::query()
            ->whereNull('tenant_id')
            ->where(function ($query) use ($key) {
                $query->where('key', $key)->orWhere('title', $key);
            })
            ->first();

        return $setting ? $setting->value : null;
    }

    private function findScopedSetting(string $key, array $context): ?Setting
    {
        if (!Schema::hasTable('settings')) {
            return null;
        }

        foreach ($this->scopeCandidates($context) as $candidate) {
            $query = Setting::query()
                ->where('scope', $candidate['scope'])
                ->where('tenant_id', $candidate['tenant_id'])
                ->where('organization_id', $candidate['organization_id'])
                ->where('store_id', $candidate['store_id'])
                ->where('user_id', $candidate['user_id'])
                ->where(function ($query) use ($key) {
                    $query->where('key', $key)->orWhere('title', $key);
                });

            $setting = $query->first();

            if ($setting) {
                return $setting;
            }
        }

        return null;
    }

    private function scopeCandidates(array $context): array
    {
        return [
            [
                'scope' => 'user',
                'tenant_id' => $context['tenant_id'] ?? null,
                'organization_id' => $context['organization_id'] ?? null,
                'store_id' => $context['store_id'] ?? null,
                'user_id' => $context['user_id'] ?? null,
            ],
            [
                'scope' => 'store',
                'tenant_id' => $context['tenant_id'] ?? null,
                'organization_id' => $context['organization_id'] ?? null,
                'store_id' => $context['store_id'] ?? null,
                'user_id' => null,
            ],
            [
                'scope' => 'organization',
                'tenant_id' => $context['tenant_id'] ?? null,
                'organization_id' => $context['organization_id'] ?? null,
                'store_id' => null,
                'user_id' => null,
            ],
            [
                'scope' => 'tenant',
                'tenant_id' => $context['tenant_id'] ?? null,
                'organization_id' => null,
                'store_id' => null,
                'user_id' => null,
            ],
            [
                'scope' => 'global',
                'tenant_id' => null,
                'organization_id' => null,
                'store_id' => null,
                'user_id' => null,
            ],
        ];
    }

    private function scopeForContext(array $context): string
    {
        if (!empty($context['user_id'])) {
            return 'user';
        }

        if (!empty($context['store_id'])) {
            return 'store';
        }

        if (!empty($context['organization_id'])) {
            return 'organization';
        }

        if (!empty($context['tenant_id'])) {
            return 'tenant';
        }

        return 'global';
    }

    private function castValue($value, string $type)
    {
        if ($type === 'boolean') {
            return in_array($value, ['yes', '1', 1, true, 'on'], true) ? 'yes' : 'no';
        }

        if ($type === 'json') {
            $decoded = json_decode((string) $value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        if ($type === 'multiselect') {
            $decoded = json_decode((string) $value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            return $value !== null && $value !== '' ? [(string) $value] : [];
        }

        return $value;
    }
}

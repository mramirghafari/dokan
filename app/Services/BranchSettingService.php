<?php

namespace App\Services;

class BranchSettingService
{
    public function __construct(private SettingService $settings) {}

    public function get(string $key, array $context = [], $default = null)
    {
        return $this->settings->get($key, $this->normalizeContext($context), $default);
    }

    public function set(string $key, $value, array $context = [], string $type = 'string', string $category = 'branch')
    {
        return $this->settings->set($key, $value, $this->normalizeContext($context), $type, $category);
    }

    public function normalizeContext(array $context): array
    {
        if (!empty($context['branch_id']) && empty($context['organization_id'])) {
            $context['organization_id'] = $context['branch_id'];
        }

        if (!empty($context['warehouse_id']) && empty($context['store_id'])) {
            $context['store_id'] = $context['warehouse_id'];
        }

        return $context;
    }
}

<?php

namespace App\Services;

use App\Models\factorMaker;
use stdClass;

class InvoiceLayoutService
{
    public function profileOptions(): array
    {
        $profiles = config('invoice_layouts.profiles', []);

        return collect($profiles)
            ->map(fn (array $profile, string $key) => [
                'key' => $key,
                'label' => $profile['label'] ?? $key,
                'description' => $profile['description'] ?? '',
            ])
            ->values()
            ->all();
    }

    public function profileLabel(?string $profileKey): string
    {
        $key = $this->normalizeProfileKey($profileKey);

        return config("invoice_layouts.profiles.{$key}.label", $key);
    }

    /**
     * گزینه‌های «نوع محصولات فاکتور» به همراه پروفایل ستون‌بندی متناظر.
     *
     * @return array<int, array{key:string,label:string,description:string,profile:string}>
     */
    public function productTypeOptions(): array
    {
        $types = config('factor_product_types.types', []);

        return collect($types)
            ->map(fn (array $type, string $key) => [
                'key' => $key,
                'label' => $type['label'] ?? $key,
                'description' => $type['description'] ?? '',
                'profile' => $this->normalizeProfileKey($type['profile'] ?? null),
            ])
            ->values()
            ->all();
    }

    public function normalizeProductType(?string $value): string
    {
        $default = config('factor_product_types.default', 'non_refrigerated');
        $value = $value === null ? '' : trim((string) $value);

        if ($value === '') {
            return $default;
        }

        $legacy = config('factor_product_types.legacy_map', []);
        $value = $legacy[$value] ?? $value;

        return array_key_exists($value, config('factor_product_types.types', []))
            ? $value
            : $default;
    }

    public function productTypeLabel(?string $value): string
    {
        $key = $this->normalizeProductType($value);

        return config("factor_product_types.types.{$key}.label", $key);
    }

    /**
     * پروفایل ستون‌بندی پیشنهادی برای یک نوع محصول.
     */
    public function profileForProductType(?string $value): string
    {
        $key = $this->normalizeProductType($value);
        $profile = config("factor_product_types.types.{$key}.profile");

        return $this->normalizeProfileKey($profile);
    }

    /**
     * نگاشت کلید نوع محصول به کلید پروفایل، برای استفاده در جاوااسکریپت فرم.
     *
     * @return array<string, string>
     */
    public function productTypeProfileMap(): array
    {
        return collect($this->productTypeOptions())
            ->mapWithKeys(fn (array $type) => [$type['key'] => $type['profile']])
            ->all();
    }

    public function resolveLayout(factorMaker $factor): array
    {
        $profileKey = $this->normalizeProfileKey($factor->business_profile);
        $profile = config("invoice_layouts.profiles.{$profileKey}", []);
        $labelOverrides = is_array($factor->line_layout['labels'] ?? null)
            ? $factor->line_layout['labels']
            : [];

        $columns = [];
        foreach ($profile['columns'] ?? [] as $column) {
            if (!$this->columnIsVisible($factor, $column)) {
                continue;
            }

            $key = $column['key'];
            $columns[] = array_merge($column, [
                'label' => $labelOverrides[$key]
                    ?? ($profile['default_labels'][$key] ?? null)
                    ?? config("invoice_layouts.column_labels.{$key}", $key),
            ]);
        }

        return [
            'profile' => $profileKey,
            'profile_label' => $profile['label'] ?? $profileKey,
            'quantity_mode' => $profile['quantity_mode'] ?? 'pack_tedad',
            'columns' => $columns,
        ];
    }

    /**
     * @param  object|array|null  $product
     */
    public function buildLineValues($item, $product, array $layout): array
    {
        $product = $this->toObject($product);
        $packItems = max(1, (int) ($product->pack_items ?? $product->unit_conversion_factor ?? 1));
        $pack = (int) ($item->pack ?? 0);
        $tedad = (int) ($item->tedad ?? 0);
        $price = (int) ($item->price ?? 0);
        $discountPercent = (int) ($item->discount ?? 0);
        $taxRate = (int) ($product->tax ?? 0);
        $weight = $this->resolveWeight($item, $product, $tedad);

        $packQuantity = ($pack * $packItems) + $tedad;

        $quantity = match ($layout['quantity_mode']) {
            'physical' => $weight > 0 ? $weight : $packQuantity,
            'weight' => $weight,
            'count' => max(1, $tedad ?: 1),
            'subscription' => max(1, $pack ?: 1) * max(1, $tedad ?: 1),
            default => $packQuantity,
        };

        $gross = $quantity * $price;
        $discountAmount = (int) round(($gross * $discountPercent) / 100);
        $afterDiscount = $gross - $discountAmount;
        $taxAmount = (int) round(($afterDiscount * $taxRate) / 100);
        $net = $afterDiscount + $taxAmount;

        return [
            'sku' => (string) ($product->sku ?? '---'),
            'title' => trim(((string) ($product->title ?? '')) . ' ' . ((string) ($product->display_name ?? ''))),
            'moadian' => '---',
            'pack' => $pack,
            'tedad' => $tedad,
            'total_qty' => $quantity,
            'weight' => $weight,
            'course' => (string) ($product->title ?? '---'),
            'section' => (string) ($product->display_name ?? '---'),
            'participants' => max(1, $tedad ?: 1),
            'plan' => (string) ($product->title ?? '---'),
            'duration' => max(1, $pack ?: 1),
            'seats' => max(1, $tedad ?: 1),
            'billing_units' => $quantity,
            'unit_price' => $price,
            'gross' => $gross,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'after_discount' => $afterDiscount,
            'tax' => $taxAmount,
            'net' => $net,
            'quantity' => $quantity,
            'tax_rate' => $taxRate,
        ];
    }

    public function formatValue(string $columnKey, $value): string
    {
        if (in_array($columnKey, ['row', 'discount_percent', 'tax_rate'], true)) {
            return (string) (int) $value;
        }

        if (in_array($columnKey, ['sku', 'title', 'moadian', 'course', 'section', 'plan'], true)) {
            return (string) $value;
        }

        if (in_array($columnKey, ['weight'], true)) {
            return number_format((float) $value, 2);
        }

        return number_format((int) $value);
    }

    public function buildLabelOverridesFromRequest(array $input): ?array
    {
        $labels = array_filter([
            'pack' => trim((string) ($input['label_pack'] ?? '')),
            'tedad' => trim((string) ($input['label_tedad'] ?? '')),
            'total_qty' => trim((string) ($input['label_total_qty'] ?? '')),
            'weight' => trim((string) ($input['label_weight'] ?? '')),
            'course' => trim((string) ($input['label_course'] ?? '')),
            'section' => trim((string) ($input['label_section'] ?? '')),
            'participants' => trim((string) ($input['label_participants'] ?? '')),
            'plan' => trim((string) ($input['label_plan'] ?? '')),
            'duration' => trim((string) ($input['label_duration'] ?? '')),
            'seats' => trim((string) ($input['label_seats'] ?? '')),
            'unit_price' => trim((string) ($input['label_unit_price'] ?? '')),
        ]);

        return empty($labels) ? null : ['labels' => $labels];
    }

    public function labelFieldsForProfile(string $profileKey): array
    {
        $profile = config('invoice_layouts.profiles.' . $this->normalizeProfileKey($profileKey), []);
        $keys = array_keys($profile['default_labels'] ?? []);

        return collect($keys)
            ->map(fn (string $key) => $this->labelFieldDefinition($key, $profile))
            ->values()
            ->all();
    }

    public function labelFieldMeta(): array
    {
        return config('invoice_layouts.label_field_meta', []);
    }

    public function labelFieldGroups(): array
    {
        return config('invoice_layouts.label_field_groups', []);
    }

    private function labelFieldDefinition(string $key, array $profile): array
    {
        $meta = config("invoice_layouts.label_field_meta.{$key}", []);

        return [
            'key' => $key,
            'input' => 'label_' . $key,
            'label' => $meta['title'] ?? config("invoice_layouts.column_labels.{$key}", $key),
            'default' => $profile['default_labels'][$key] ?? $key,
            'hint' => $meta['hint'] ?? '',
            'presets' => $meta['presets'] ?? [],
            'group' => $meta['group'] ?? 'other',
            'group_label' => $meta['group_label']
                ?? config('invoice_layouts.label_field_groups.' . ($meta['group'] ?? 'other'), ''),
        ];
    }

    private function columnIsVisible(factorMaker $factor, array $column): bool
    {
        if (!empty($column['always'])) {
            return true;
        }

        if (!empty($column['toggle'])) {
            return (int) ($factor->{$column['toggle']} ?? 0) === 1;
        }

        return true;
    }

    private function normalizeProfileKey(?string $profileKey): string
    {
        $key = $profileKey ?: 'distribution';
        $legacyMap = config('invoice_layouts.legacy_profile_map', []);
        $key = $legacyMap[$key] ?? $key;

        return array_key_exists($key, config('invoice_layouts.profiles', []))
            ? $key
            : 'distribution';
    }

    /**
     * @param  object|array|null  $product
     */
    private function resolveWeight($item, $product, int $tedad): float
    {
        if (!empty($item->weight)) {
            return (float) $item->weight;
        }

        if (!empty($product->pr_weight)) {
            return (float) $product->pr_weight * max(1, $tedad ?: 1);
        }

        return 0.0;
    }

    /**
     * @param  object|array|null  $value
     */
    private function toObject($value): stdClass
    {
        if ($value instanceof stdClass) {
            return $value;
        }

        if (is_array($value)) {
            return (object) $value;
        }

        if (is_object($value)) {
            return $value;
        }

        return new stdClass();
    }
}

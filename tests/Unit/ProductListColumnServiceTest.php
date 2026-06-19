<?php

namespace Tests\Unit;

use App\Services\ProductListColumnService;
use Tests\TestCase;

class ProductListColumnServiceTest extends TestCase
{
    public function test_default_visible_keys_include_core_columns(): void
    {
        $service = app(ProductListColumnService::class);
        $keys = $service->defaultVisibleKeys();

        $this->assertContains('sku', $keys);
        $this->assertContains('price', $keys);
        $this->assertContains('pr_unit', $keys);
    }

    public function test_resolved_columns_include_extended_fields(): void
    {
        $service = app(ProductListColumnService::class);
        $keys = array_column($service->resolvedColumns(), 'key');

        $this->assertContains('sku', $keys);
        $this->assertContains('brand', $keys);
        $this->assertContains('purchase_price', $keys);
        $this->assertContains('created_at', $keys);
    }

    public function test_normalize_keys_filters_unknown_columns(): void
    {
        $service = app(ProductListColumnService::class);
        $keys = $service->normalizeKeys(['sku', 'unknown_column', 'price']);

        $this->assertContains('sku', $keys);
        $this->assertContains('price', $keys);
        $this->assertNotContains('unknown_column', $keys);
    }

    public function test_hidden_column_indexes_match_visible_keys(): void
    {
        $service = app(ProductListColumnService::class);
        $catalog = $service->catalog();
        $visible = $service->visibleKeys();
        $hidden = $service->hiddenColumnIndexes();

        foreach ($catalog as $key => $column) {
            $shouldBeHidden = !in_array($key, $visible, true);
            $this->assertSame($shouldBeHidden, in_array($column['index'], $hidden, true), $key);
        }
    }
}

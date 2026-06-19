<?php

namespace Tests\Unit;

use App\Services\CustomerListColumnService;
use Tests\TestCase;

class CustomerListColumnServiceTest extends TestCase
{
    public function test_default_visible_keys_include_core_columns(): void
    {
        $service = app(CustomerListColumnService::class);
        $keys = $service->defaultVisibleKeys();

        $this->assertContains('customer_code', $keys);
        $this->assertContains('mobile', $keys);
        $this->assertContains('segment_channel', $keys);
        $this->assertContains('orders_count', $keys);
    }

    public function test_resolved_columns_include_extended_fields(): void
    {
        $service = app(CustomerListColumnService::class);
        $keys = array_column($service->resolvedColumns(), 'key');

        $this->assertContains('mobile', $keys);
        $this->assertContains('national_id', $keys);
        $this->assertContains('joined_at', $keys);
        $this->assertContains('purchases_count', $keys);
        $this->assertContains('purchases_sum', $keys);
        $this->assertContains('marketer', $keys);
        $this->assertContains('account_balance', $keys);
    }

    public function test_subscription_balance_only_on_subscription_panel(): void
    {
        $service = app(CustomerListColumnService::class);
        $keys = array_column($service->resolvedColumns(999999), 'key');

        $this->assertNotContains('subscription_balance', $keys);
    }

    public function test_normalize_keys_filters_unknown_columns(): void
    {
        $service = app(CustomerListColumnService::class);
        $keys = $service->normalizeKeys(['customer_code', 'unknown_column', 'tablo']);

        $this->assertContains('customer_code', $keys);
        $this->assertNotContains('unknown_column', $keys);
    }

    public function test_hidden_column_indexes_match_visible_keys(): void
    {
        $service = app(CustomerListColumnService::class);
        $catalog = $service->catalog();
        $visible = $service->visibleKeys();
        $hidden = $service->hiddenColumnIndexes();

        foreach ($catalog as $key => $column) {
            $shouldBeHidden = !in_array($key, $visible, true);
            $this->assertSame($shouldBeHidden, in_array($column['index'], $hidden, true), $key);
        }
    }
}

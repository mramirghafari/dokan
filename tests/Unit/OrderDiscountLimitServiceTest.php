<?php

namespace Tests\Unit;

use App\Models\Customers;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\OrderDiscountLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDiscountLimitServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderDiscountLimitService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OrderDiscountLimitService::class);
    }

    public function test_detects_roohi_style_plan_with_none_mode(): void
    {
        $product = new Product([
            'sku' => 'ROOHI-SUB-3M',
            'product_type' => 'service',
            'pr_unit' => 'ماه',
            'pack_items' => 3,
            'order_quantity_mode' => 'none',
        ]);

        $this->assertTrue($this->service->isSubscriptionDurationProduct($product));
    }

    public function test_detects_service_month_products_with_none_mode(): void
    {
        $product = new Product([
            'sku' => 'PLAN-12M',
            'product_type' => 'service',
            'pr_unit' => 'ماه',
            'pack_items' => 12,
            'order_quantity_mode' => 'none',
            'pack_sale_status' => 0,
        ]);

        $this->assertTrue($this->service->isSubscriptionDurationProduct($product));
        $this->assertSame(['pack' => 12, 'tedad' => 1], $product->fixedOrderQuantities());
    }

    public function test_resolve_order_quantity_mode_from_legacy_flags(): void
    {
        $product = new Product(['item_sale_status' => 1, 'pack_sale_status' => 1]);

        $this->assertSame('both', $product->resolveOrderQuantityMode());
    }

    public function test_uses_most_restrictive_discount_percent(): void
    {
        $product = new Product(['title' => 'کالا', 'discount' => 10, 'max_discount_amount' => null]);
        $role = new Role(['max_discount_percent' => 6, 'max_discount_amount' => null]);
        $user = new User();
        $user->setRelation('roles', collect([$role]));

        $limits = $this->service->effectiveLimits($user, $product);

        $this->assertSame(6.0, $limits['max_discount_percent']);
    }

    public function test_validate_line_returns_persian_error_when_percent_exceeded(): void
    {
        $product = new Product(['title' => 'اشتراک', 'discount' => 5]);
        $user = new User();
        $user->setRelation('roles', collect());

        $message = $this->service->validateLine($user, $product, null, 8, 1_000_000);

        $this->assertNotNull($message);
        $this->assertStringContainsString('تخفیف', $message);
        $this->assertStringContainsString('اشتراک', $message);
    }

    public function test_validate_purchase_amount_for_customer(): void
    {
        $customer = new Customers(['max_purchase_amount' => 5_000_000]);

        $message = $this->service->validatePurchaseAmount($customer, 6_000_000);

        $this->assertNotNull($message);
        $this->assertStringContainsString('سقف مجاز خرید مشتری', $message);
    }
}

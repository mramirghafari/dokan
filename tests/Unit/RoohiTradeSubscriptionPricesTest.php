<?php

namespace Tests\Unit;

use App\Services\ProductPricePeriodService;
use Tests\TestCase;

class RoohiTradeSubscriptionPricesTest extends TestCase
{
    public function test_roohi_subscription_price_ranges_are_valid_and_non_overlapping(): void
    {
        $service = app(ProductPricePeriodService::class);
        $definitions = config('roohi_trade_subscription_prices');

        $this->assertNotEmpty($definitions);

        foreach ($definitions as $sku => $rows) {
            $normalized = $service->normalizeRows($rows);
            $this->assertNotEmpty($normalized, "SKU {$sku} has no price rows");

            foreach ($normalized as $row) {
                $this->assertNotNull($row['starts_at'], "{$sku}: missing starts_at");
                $this->assertNotNull($row['ends_at'], "{$sku}: missing ends_at");
            }
        }
    }

    public function test_split_payment_period_has_prepayment_and_completion_amounts(): void
    {
        $rows = config('roohi_trade_subscription_prices.ROOHI-SUB-2M');
        $splitRows = collect($rows)->filter(fn (array $row) => $row['starts_at'] === '1405/02/18');

        $this->assertCount(2, $splitRows);

        $amounts = $splitRows->pluck('amount', 'price_type');
        $this->assertSame(2_900_000, $amounts['prepayment']);
        $this->assertSame(5_900_000, $amounts['completion']);
    }
}

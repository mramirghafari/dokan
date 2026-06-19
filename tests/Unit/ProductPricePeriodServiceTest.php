<?php

namespace Tests\Unit;

use App\Services\ProductPricePeriodService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductPricePeriodServiceTest extends TestCase
{
    public function test_normalize_rows_accepts_persian_price_type_label(): void
    {
        $service = app(ProductPricePeriodService::class);

        $rows = $service->normalizeRows([[
            "price_type" => "قیمت فروش",
            "amount" => "1,250,000",
            "starts_at" => "1405/01/01",
            "ends_at" => "1405/01/15",
            "priority" => 2,
        ]]);

        $this->assertCount(1, $rows);
        $this->assertSame("sale", $rows[0]["price_type"]);
        $this->assertSame(1250000.0, $rows[0]["amount"]);
        $this->assertNotNull($rows[0]["starts_at"]);
    }

    public function test_normalize_rows_rejects_overlapping_ranges(): void
    {
        $service = app(ProductPricePeriodService::class);

        $this->expectException(ValidationException::class);

        $service->normalizeRows([
            [
                "price_type" => "sale",
                "amount" => "1000",
                "starts_at" => "1405/01/01",
                "ends_at" => "1405/01/20",
            ],
            [
                "price_type" => "sale",
                "amount" => "1200",
                "starts_at" => "1405/01/10",
                "ends_at" => "1405/01/30",
            ],
        ]);
    }
}


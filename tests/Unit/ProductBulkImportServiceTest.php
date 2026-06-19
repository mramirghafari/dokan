<?php

namespace Tests\Unit;

use App\Services\ProductBulkImportService;
use Tests\TestCase;

class ProductBulkImportServiceTest extends TestCase
{
    public function test_extract_price_ranges_from_row_reads_range_columns(): void
    {
        $service = app(ProductBulkImportService::class);

        $ranges = $service->extractPriceRangesFromRow([
            "بازه قیمت نوع" => "sale",
            "بازه قیمت مبلغ" => "950000",
            "بازه قیمت از تاریخ" => "1405/02/01",
            "بازه قیمت تا تاریخ" => "1405/03/01",
        ]);

        $this->assertCount(1, $ranges);
        $this->assertSame("sale", $ranges[0]["price_type"]);
        $this->assertSame("950000", $ranges[0]["amount"]);
        $this->assertSame("1405/02/01", $ranges[0]["starts_at"]);
    }
}


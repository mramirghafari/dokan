<?php

namespace Tests\Unit;

use App\Models\factorMaker;
use App\Services\InvoiceLayoutService;
use Tests\TestCase;

class InvoiceLayoutServiceTest extends TestCase
{
    private InvoiceLayoutService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(InvoiceLayoutService::class);
    }

    public function test_distribution_profile_uses_pack_and_tedad_quantity(): void
    {
        $factor = new factorMaker([
            'business_profile' => 'distribution',
            'column_pr_code' => 1,
            'column_sub_unit' => 1,
            'column_discount' => 1,
            'column_tax' => 1,
        ]);

        $layout = $this->service->resolveLayout($factor);
        $line = $this->service->buildLineValues(
            (object) ['pack' => 2, 'tedad' => 3, 'price' => 1000, 'discount' => 10],
            (object) ['pack_items' => 6, 'tax' => 9, 'title' => 'ماست', 'sku' => 'P1'],
            $layout
        );

        $this->assertSame(15, $line['quantity']);
        $this->assertSame(15000, $line['gross']);
        $this->assertSame(1500, $line['discount_amount']);
        $this->assertSame(14715, $line['net']);
    }

    public function test_distribution_profile_uses_weight_when_weight_is_present(): void
    {
        $factor = new factorMaker([
            'business_profile' => 'distribution',
            'column_discount' => 2,
            'column_tax' => 2,
        ]);

        $layout = $this->service->resolveLayout($factor);
        $line = $this->service->buildLineValues(
            (object) ['pack' => 2, 'tedad' => 3, 'weight' => 12.5, 'price' => 2000, 'discount' => 0],
            (object) ['pack_items' => 6, 'tax' => 0, 'title' => 'گوشت'],
            $layout
        );

        $this->assertSame(12.5, $line['weight']);
        $this->assertSame(12.5, $line['quantity']);
        $this->assertEquals(25000, $line['gross']);
        $this->assertStringContainsString('12.50', $this->service->formatValue('weight', 12.5));
    }

    public function test_legacy_weight_profile_maps_to_distribution(): void
    {
        $factor = new factorMaker(['business_profile' => 'weight']);

        $layout = $this->service->resolveLayout($factor);

        $this->assertSame('distribution', $layout['profile']);
        $this->assertTrue(collect($layout['columns'])->contains(fn ($c) => $c['key'] === 'weight'));
    }

    public function test_subscription_profile_multiplies_duration_and_seats(): void
    {
        $factor = new factorMaker([
            'business_profile' => 'subscription',
            'column_discount' => 2,
            'column_tax' => 2,
        ]);

        $layout = $this->service->resolveLayout($factor);
        $line = $this->service->buildLineValues(
            (object) ['pack' => 3, 'tedad' => 5, 'price' => 10000, 'discount' => 0],
            (object) ['tax' => 0, 'title' => 'پلن طلایی'],
            $layout
        );

        $this->assertSame(3, $line['duration']);
        $this->assertSame(5, $line['seats']);
        $this->assertSame(15, $line['billing_units']);
        $this->assertSame(150000, $line['gross']);
    }

    public function test_custom_labels_are_applied_to_layout(): void
    {
        $factor = new factorMaker([
            'business_profile' => 'distribution',
            'column_sub_unit' => 1,
            'line_layout' => ['labels' => ['pack' => 'بسته', 'tedad' => 'عدد']],
        ]);

        $layout = $this->service->resolveLayout($factor);
        $labels = collect($layout['columns'])->pluck('label', 'key');

        $this->assertSame('بسته', $labels['pack']);
        $this->assertSame('عدد', $labels['tedad']);
    }

    public function test_custom_weight_and_unit_price_labels_are_applied(): void
    {
        $factor = new factorMaker([
            'business_profile' => 'distribution',
            'column_sub_unit' => 1,
            'line_layout' => [
                'labels' => [
                    'pack' => 'جعبه',
                    'tedad' => 'بطری',
                    'weight' => 'وزن (گرم)',
                    'unit_price' => 'فی هر بطری',
                ],
            ],
        ]);

        $layout = $this->service->resolveLayout($factor);
        $labels = collect($layout['columns'])->pluck('label', 'key');

        $this->assertSame('جعبه', $labels['pack']);
        $this->assertSame('بطری', $labels['tedad']);
        $this->assertSame('وزن (گرم)', $labels['weight']);
        $this->assertSame('فی هر بطری', $labels['unit_price']);
    }

    public function test_distribution_label_fields_include_unit_presets(): void
    {
        $fields = $this->service->labelFieldsForProfile('distribution');
        $packField = collect($fields)->firstWhere('key', 'pack');
        $weightField = collect($fields)->firstWhere('key', 'weight');

        $this->assertSame('unit', $packField['group']);
        $this->assertContains('جعبه', $packField['presets']);
        $this->assertTrue(collect($weightField['presets'])->contains(fn ($preset) => str_contains($preset, 'گرم')));
    }
}

<?php

namespace Modules\Billing\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Billing\Services\PricingEngineService;
use Modules\Billing\Models\Company;
use Modules\Inventory\Models\Product;
use Modules\Billing\Models\PriceOverride;

use Illuminate\Support\Facades\Cache;

class PricingEngineServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PricingEngineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PricingEngineService();
        Cache::flush();
    }

    public function test_calculates_base_price_when_no_overrides_or_tiers()
    {
        $company = Company::factory()->create(['pricing_tier' => 'standard']);
        $product = Product::factory()->create([
            'base_price' => 100.00,
            'cost_price' => 50.00,
        ]);

        $result = $this->service->calculateEffectivePrice($company, $product);

        $this->assertEquals(100.00, $result->price);
        $this->assertEquals('base', $result->source);
        $this->assertEquals(50.00, $result->margin_percent);
    }

    public function test_calculates_override_fixed_price()
    {
        $company = Company::factory()->create();
        $product = Product::factory()->create(['base_price' => 100.00]);
        
        PriceOverride::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'type' => 'fixed',
            'value' => 80.00,
        ]);

        // dump(PriceOverride::all()->toArray());

        $result = $this->service->calculateEffectivePrice($company, $product);

        $this->assertEquals(80.00, $result->price);
        $this->assertEquals('override', $result->source);
    }

    public function test_calculates_override_discount_percent()
    {
        $company = Company::factory()->create();
        $product = Product::factory()->create(['base_price' => 100.00]);
        
        PriceOverride::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'type' => 'discount_percent',
            'value' => 10.00, // 10% off
        ]);

        $result = $this->service->calculateEffectivePrice($company, $product);

        $this->assertEquals(90.00, $result->price);
        $this->assertEquals('override', $result->source);
    }

    public function test_calculates_override_markup_percent()
    {
        $company = Company::factory()->create();
        $product = Product::factory()->create(['base_price' => 100.00]);
        
        PriceOverride::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'type' => 'markup_percent',
            'value' => 20.00, // 20% markup
        ]);

        $result = $this->service->calculateEffectivePrice($company, $product);

        $this->assertEquals(120.00, $result->price);
        $this->assertEquals('override', $result->source);
    }

    public function test_validates_margin_floor()
    {
        $company = Company::factory()->create(['margin_floor_percent' => 20.00]);
        $product = Product::factory()->create([
            'base_price' => 100.00,
            'cost_price' => 90.00, // 10% margin at base price
        ]);

        // Proposed price of 100 (10% margin) should fail
        $result = $this->service->validateMargin($company, $product, 100.00);
        $this->assertFalse($result->is_safe);
        $this->assertNotEmpty($result->warnings);

        // Proposed price of 120 (25% margin) should pass
        // (120 - 90) / 120 = 0.25
        $result = $this->service->validateMargin($company, $product, 120.00);
        $this->assertTrue($result->is_safe);
        $this->assertEmpty($result->warnings);
    }
}

<?php

namespace Modules\Billing\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Billing\Services\PricingEngineService;
use Modules\Billing\Models\Company;
use Modules\Inventory\Models\Product;
use Modules\Billing\Models\ProductTierPrice;
use Modules\Billing\Models\PriceOverride;
use Illuminate\Support\Facades\Cache;

class PricingEngineServiceTaxCreditTest extends TestCase
{
    use RefreshDatabase;

    protected PricingEngineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PricingEngineService();
        Cache::flush();
    }

    public function test_calculates_tax_credit_for_non_profit_with_base_price_as_standard()
    {
        $company = Company::factory()->create(['pricing_tier' => 'non_profit']);
        $product = Product::factory()->create([
            'base_price' => 100.00,
        ]);

        // Create a tier price for non_profit
        ProductTierPrice::create([
            'product_id' => $product->id,
            'tier' => 'non_profit',
            'price' => 80.00,
        ]);

        $result = $this->service->calculateEffectivePrice($company, $product);

        $this->assertEquals(80.00, $result->price);
        $this->assertEquals('tier', $result->source);
        // Standard price is base_price (100) because no standard tier price exists
        // Tax credit = 100 - 80 = 20
        $this->assertEquals(20.00, $result->tax_credit);
    }

    public function test_calculates_tax_credit_for_non_profit_with_standard_tier_price()
    {
        $company = Company::factory()->create(['pricing_tier' => 'non_profit']);
        $product = Product::factory()->create([
            'base_price' => 120.00, // Base price is higher, but standard tier is what matters
        ]);

        // Standard tier price
        ProductTierPrice::create([
            'product_id' => $product->id,
            'tier' => 'standard',
            'price' => 100.00,
        ]);

        // Non-profit tier price
        ProductTierPrice::create([
            'product_id' => $product->id,
            'tier' => 'non_profit',
            'price' => 70.00,
        ]);

        $result = $this->service->calculateEffectivePrice($company, $product);

        $this->assertEquals(70.00, $result->price);
        // Tax credit = 100 (standard tier) - 70 (non-profit tier) = 30
        $this->assertEquals(30.00, $result->tax_credit);
    }

    public function test_no_tax_credit_for_standard_company()
    {
        $company = Company::factory()->create(['pricing_tier' => 'standard']);
        $product = Product::factory()->create([
            'base_price' => 100.00,
        ]);

        $result = $this->service->calculateEffectivePrice($company, $product);

        $this->assertEquals(100.00, $result->price);
        $this->assertEquals(0.00, $result->tax_credit);
    }

    public function test_calculates_tax_credit_with_override_for_non_profit()
    {
        $company = Company::factory()->create(['pricing_tier' => 'non_profit']);
        $product = Product::factory()->create([
            'base_price' => 100.00,
        ]);

        // Override to 50
        PriceOverride::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'type' => 'fixed',
            'value' => 50.00,
        ]);

        $result = $this->service->calculateEffectivePrice($company, $product);

        $this->assertEquals(50.00, $result->price);
        $this->assertEquals('override', $result->source);
        // Standard price is base_price (100)
        // Tax credit = 100 - 50 = 50
        $this->assertEquals(50.00, $result->tax_credit);
    }
}

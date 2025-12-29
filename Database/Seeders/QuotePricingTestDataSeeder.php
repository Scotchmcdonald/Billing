<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Billing\Models\Company;
use Modules\Inventory\Models\Product;
use Modules\Billing\Models\ProductTierPrice;

class QuotePricingTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates test data for the quote pricing tier feature.
     */
    public function run(): void
    {
        // Ensure we have companies with different pricing tiers
        $standardCompany = Company::firstOrCreate(
            ['name' => 'Acme Corp (Standard Test)'],
            [
                'pricing_tier' => 'standard',
                'margin_floor_percent' => 20.00,
                'is_active' => true,
            ]
        );

        $nonprofitCompany = Company::firstOrCreate(
            ['name' => 'Charity Foundation (Non-Profit Test)'],
            [
                'pricing_tier' => 'non_profit',
                'margin_floor_percent' => 10.00,
                'is_active' => true,
            ]
        );

        $consumerCompany = Company::firstOrCreate(
            ['name' => 'John Smith (Consumer Test)'],
            [
                'pricing_tier' => 'consumer',
                'margin_floor_percent' => 30.00,
                'is_active' => true,
            ]
        );

        $this->command->info('Created test companies with different pricing tiers.');

        // Ensure we have at least one product with tier pricing
        $product = Product::firstOrCreate(
            ['sku' => 'TEST-QUOTE-001'],
            [
                'name' => 'Test Service Package',
                'description' => 'A test service package for quote pricing',
                'type' => 'service',
                'base_price' => 100.00,
                'cost_price' => 60.00,
                'is_active' => true,
            ]
        );

        // Create tier prices
        ProductTierPrice::updateOrCreate(
            ['product_id' => $product->id, 'tier' => 'standard'],
            ['price' => 100.00]
        );

        ProductTierPrice::updateOrCreate(
            ['product_id' => $product->id, 'tier' => 'non_profit'],
            ['price' => 80.00] // 20% discount
        );

        ProductTierPrice::updateOrCreate(
            ['product_id' => $product->id, 'tier' => 'consumer'],
            ['price' => 110.00] // 10% premium
        );

        $this->command->info('Created test product with tier pricing:');
        $this->command->info("  - Standard: $100.00");
        $this->command->info("  - Non-Profit: $80.00 (20% discount)");
        $this->command->info("  - Consumer: $110.00 (10% premium)");
        $this->command->info('');
        $this->command->info('Test data ready! Visit /billing/finance/quotes/create to test.');
    }
}

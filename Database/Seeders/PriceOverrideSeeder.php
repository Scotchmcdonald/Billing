<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Billing\Models\PriceOverride;
use Modules\Billing\Models\Company;
use Modules\Inventory\Models\Product;
use Carbon\Carbon;

class PriceOverrideSeeder extends Seeder
{
    /**
     * Seed price overrides for companies.
     */
    public function run(): void
    {
        $companies = Company::all();
        $products = Product::all();

        if ($companies->isEmpty() || $products->isEmpty()) {
            $this->command->warn('⚠ No companies or products found. Please run seeders first.');
            return;
        }

        $overrideTypes = ['fixed', 'discount_percent', 'markup_percent'];
        $overrideCount = 0;

        foreach ($companies as $company) {
            // 30% chance company has price overrides
            if (rand(1, 100) > 30) {
                continue;
            }

            // Each company gets 1-4 overrides
            $numOverrides = rand(1, 4);
            $companyProducts = $products->random(min($numOverrides, $products->count()));

            foreach ($companyProducts as $product) {
                $basePrice = (float) $product->base_price;
                $overrideType = $overrideTypes[array_rand($overrideTypes)];
                
                // Calculate override price based on type
                [$overridePrice, $reason] = $this->calculateOverridePrice($basePrice, $overrideType);

                // For fixed type, value is the actual price
                // For percent types, value is the percentage
                $valueToStore = ($overrideType === 'fixed') ? $overridePrice : $overridePrice;
                $customPrice = ($overrideType === 'fixed') ? $overridePrice : round($basePrice * (1 + ($overrideType === 'markup_percent' ? 1 : -1) * $overridePrice / 100), 2);
                
                PriceOverride::create([
                    'company_id' => $company->id,
                    'product_id' => $product->id,
                    'custom_price' => $customPrice,
                    'value' => $valueToStore,
                    'notes' => $reason,
                    'is_active' => rand(1, 100) <= 85, // 85% active
                    'approved_by' => 1,
                    'type' => $overrideType,
                    'start_date' => Carbon::now()->subDays(rand(1, 90)),
                ]);

                $overrideCount++;
            }
        }

        $this->command->info("✓ Created {$overrideCount} price overrides");
        $this->showOverrideStats();
    }

    /**
     * Calculate override price based on type
     */
    private function calculateOverridePrice(float $basePrice, string $type): array
    {
        switch ($type) {
            case 'fixed':
                // Fixed custom price (±30%)
                $variance = rand(-30, 30);
                $price = round($basePrice * (1 + $variance / 100), 2);
                $reason = "Custom fixed price - negotiated rate";
                break;

            case 'discount_percent':
                // Discount percentage (10-40%)
                $discount = rand(10, 40);
                $price = $discount; // Store the percentage, not the calculated price
                $reason = "{$discount}% discount - volume customer";
                break;

            case 'markup_percent':
                // Markup percentage (5-25%)
                $markup = rand(5, 25);
                $price = $markup; // Store the percentage, not the calculated price
                $reason = "{$markup}% markup - premium support tier";
                break;

            default:
                $price = $basePrice;
                $reason = "Standard pricing";
        }

        return [$price, $reason];
    }

    /**
     * Show override statistics
     */
    private function showOverrideStats(): void
    {
        $active = PriceOverride::where('is_active', true)->count();
        $inactive = PriceOverride::where('is_active', false)->count();
        
        $avgDiscount = PriceOverride::selectRaw('
            AVG(
                (SELECT base_price FROM products WHERE products.id = price_overrides.product_id) - value
            ) as avg_discount
        ')->value('avg_discount');

        $this->command->info("\nPrice Override Statistics:");
        $this->command->info("  • Active: {$active}");
        $this->command->info("  • Inactive: {$inactive}");
        $this->command->info("  • Average discount: \$" . number_format($avgDiscount ?? 0, 2));

        // Companies with overrides
        $companiesWithOverrides = PriceOverride::distinct('company_id')->count('company_id');
        $totalCompanies = Company::count();
        $percentage = round(($companiesWithOverrides / $totalCompanies) * 100, 1);
        $this->command->info("  • Companies with overrides: {$companiesWithOverrides}/{$totalCompanies} ({$percentage}%)");
    }
}

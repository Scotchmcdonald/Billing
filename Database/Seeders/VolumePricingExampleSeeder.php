<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\ProductVolumePricingTier;
use Modules\Billing\Models\ProductTierPrice;

class VolumePricingExampleSeeder extends Seeder
{
    /**
     * Seed volume pricing examples for MSP products.
     * 
     * Example: Managed IT Support with volume discounts
     * - 1-10 users: $85/user/month
     * - 11-25 users: $75/user/month  
     * - 26-50 users: $65/user/month
     * - 51+ users: $60/user/month
     * - Floor price: $55/user (maintains 20% margin above $45 cost)
     */
    public function run(): void
    {
        // Create or update the Managed IT Support product
        $managedSupport = Product::updateOrCreate(
            ['sku' => 'MSP-SUPPORT-001'],
            [
                'name' => 'Managed IT Support (Per User)',
                'description' => 'Comprehensive managed IT support with volume pricing. Includes 24/7 helpdesk, remote support, monitoring, patch management, and security updates.',
                'category' => 'Managed Services',
                'type' => 'service',
                'pricing_model' => 'per_user',
                'unit_of_measure' => 'user',
                'billing_frequency' => 'monthly',
                'min_quantity' => 1,
                'included_quantity' => 0,
                'base_price' => 85.00,
                'cost_price' => 45.00,
                'floor_unit_price' => 55.00, // Explicit floor
                'min_margin_percent' => 20.00, // Also enforce 20% minimum margin
                'is_active' => true,
                'is_addon' => false,
            ]
        );

        // Create tier prices (base pricing before volume discounts)
        $tierPrices = [
            ['tier' => 'standard', 'price' => 85.00],
            ['tier' => 'non_profit', 'price' => 75.00],
            ['tier' => 'consumer', 'price' => 70.00],
        ];

        foreach ($tierPrices as $tierPrice) {
            ProductTierPrice::updateOrCreate(
                [
                    'product_id' => $managedSupport->id,
                    'tier' => $tierPrice['tier'],
                ],
                [
                    'price' => $tierPrice['price'],
                    'starts_at' => now(),
                    'ends_at' => null,
                ]
            );
        }

        // Create volume pricing tiers for STANDARD tier
        $standardVolumeTiers = [
            ['min' => 1, 'max' => 10, 'price' => 85.00],
            ['min' => 11, 'max' => 25, 'price' => 75.00],
            ['min' => 26, 'max' => 50, 'price' => 65.00],
            ['min' => 51, 'max' => null, 'price' => 60.00], // 51+ users
        ];

        foreach ($standardVolumeTiers as $tier) {
            ProductVolumePricingTier::updateOrCreate(
                [
                    'product_id' => $managedSupport->id,
                    'pricing_tier' => 'standard',
                    'min_quantity' => $tier['min'],
                ],
                [
                    'max_quantity' => $tier['max'],
                    'unit_price' => $tier['price'],
                ]
            );
        }

        // Create volume pricing tiers for NON_PROFIT tier (more aggressive discounts)
        $nonProfitVolumeTiers = [
            ['min' => 1, 'max' => 10, 'price' => 75.00],
            ['min' => 11, 'max' => 25, 'price' => 68.00],
            ['min' => 26, 'max' => 50, 'price' => 60.00],
            ['min' => 51, 'max' => null, 'price' => 55.00], // At floor price
        ];

        foreach ($nonProfitVolumeTiers as $tier) {
            ProductVolumePricingTier::updateOrCreate(
                [
                    'product_id' => $managedSupport->id,
                    'pricing_tier' => 'non_profit',
                    'min_quantity' => $tier['min'],
                ],
                [
                    'max_quantity' => $tier['max'],
                    'unit_price' => $tier['price'],
                ]
            );
        }

        // Create volume pricing tiers for CONSUMER tier
        $consumerVolumeTiers = [
            ['min' => 1, 'max' => 5, 'price' => 70.00],
            ['min' => 6, 'max' => 15, 'price' => 65.00],
            ['min' => 16, 'max' => null, 'price' => 60.00],
        ];

        foreach ($consumerVolumeTiers as $tier) {
            ProductVolumePricingTier::updateOrCreate(
                [
                    'product_id' => $managedSupport->id,
                    'pricing_tier' => 'consumer',
                    'min_quantity' => $tier['min'],
                ],
                [
                    'max_quantity' => $tier['max'],
                    'unit_price' => $tier['price'],
                ]
            );
        }

        $this->command->info('✓ Created Managed IT Support product with volume pricing');

        // Create another example: Device Monitoring (per device)
        $deviceMonitoring = Product::updateOrCreate(
            ['sku' => 'MSP-MON-001'],
            [
                'name' => 'Device Monitoring (Per Device)',
                'description' => 'Advanced device monitoring and alerting. Volume discounts for larger deployments.',
                'category' => 'Monitoring',
                'type' => 'service',
                'pricing_model' => 'per_device',
                'unit_of_measure' => 'device',
                'billing_frequency' => 'monthly',
                'min_quantity' => 1,
                'included_quantity' => 0,
                'base_price' => 12.00,
                'cost_price' => 6.00,
                'floor_unit_price' => 7.50, // 25% margin floor
                'min_margin_percent' => 25.00,
                'is_active' => true,
                'is_addon' => false,
            ]
        );

        // Tier prices for device monitoring
        ProductTierPrice::updateOrCreate(
            ['product_id' => $deviceMonitoring->id, 'tier' => 'standard'],
            ['price' => 12.00, 'starts_at' => now()]
        );
        ProductTierPrice::updateOrCreate(
            ['product_id' => $deviceMonitoring->id, 'tier' => 'non_profit'],
            ['price' => 10.00, 'starts_at' => now()]
        );
        ProductTierPrice::updateOrCreate(
            ['product_id' => $deviceMonitoring->id, 'tier' => 'consumer'],
            ['price' => 9.00, 'starts_at' => now()]
        );

        // Volume tiers for standard pricing
        $monitoringStandardTiers = [
            ['min' => 1, 'max' => 20, 'price' => 12.00],
            ['min' => 21, 'max' => 50, 'price' => 10.50],
            ['min' => 51, 'max' => 100, 'price' => 9.00],
            ['min' => 101, 'max' => null, 'price' => 8.00],
        ];

        foreach ($monitoringStandardTiers as $tier) {
            ProductVolumePricingTier::updateOrCreate(
                [
                    'product_id' => $deviceMonitoring->id,
                    'pricing_tier' => 'standard',
                    'min_quantity' => $tier['min'],
                ],
                [
                    'max_quantity' => $tier['max'],
                    'unit_price' => $tier['price'],
                ]
            );
        }

        $this->command->info('✓ Created Device Monitoring product with volume pricing');
        $this->command->info('');
        $this->command->info('Volume pricing examples:');
        $this->command->info('  • 15 users @ standard: 10 × $85 + 5 × $75 = $1,225 ($81.67/user avg)');
        $this->command->info('  • 30 users @ standard: 10 × $85 + 15 × $75 + 5 × $65 = $2,300 ($76.67/user avg)');
        $this->command->info('  • 60 users @ standard: 10 × $85 + 15 × $75 + 25 × $65 + 10 × $60 = $4,100 ($68.33/user avg)');
        $this->command->info('  • 100 devices @ standard: 20 × $12 + 30 × $10.50 + 50 × $9 = $1,005 ($10.05/device avg)');
        $this->command->info('');
        $this->command->info('Floor pricing protects margins:');
        $this->command->info('  • Managed Support floor: $55/user (20% margin above $45 cost)');
        $this->command->info('  • Device Monitoring floor: $7.50/device (25% margin above $6 cost)');
    }
}

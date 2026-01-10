<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Enums\ProductType;
use Modules\Inventory\Enums\BillingFrequency;
use Modules\Inventory\Enums\PricingModel;

class AssetCreditProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Silver Laptop Scaffold',
                'sku' => 'SC-LAPTOP-SILVER',
                'description' => 'Pre-payment credit for Silver Laptop',
                'base_price' => 500.00,
                'type' => ProductType::SERVICE->value,
                'category' => 'Asset Credit',
            ],
            [
                'name' => 'Gold Laptop Scaffold',
                'sku' => 'SC-LAPTOP-GOLD',
                'description' => 'Pre-payment credit for Gold Laptop',
                'base_price' => 1000.00,
                'type' => ProductType::SERVICE->value,
                'category' => 'Asset Credit',
            ],
             [
                'name' => 'Custom Laptop Scaffold',
                'sku' => 'SC-LAPTOP-CUSTOM',
                'description' => 'Pre-payment credit for Custom Laptop',
                'base_price' => 1500.00,
                'type' => ProductType::SERVICE->value,
                'category' => 'Asset Credit',
            ],
            [
                'name' => 'Bronze Server Scaffold',
                'sku' => 'SC-SERVER-BRONZE',
                'description' => 'Pre-payment credit for Bronze Server',
                'base_price' => 500.00,
                'type' => ProductType::SERVICE->value,
                'category' => 'Asset Credit',
            ],
            [
                 'name' => 'Silver Server Scaffold',
                'sku' => 'SC-SERVER-SILVER',
                'description' => 'Pre-payment credit for Silver Server',
                'base_price' => 1000.00,
                'type' => ProductType::SERVICE->value,
                'category' => 'Asset Credit',
            ],
             [
                 'name' => 'Gold Server Scaffold',
                'sku' => 'SC-SERVER-GOLD',
                'description' => 'Pre-payment credit for Gold Server',
                'base_price' => 1500.00,
                'type' => ProductType::SERVICE->value,
                'category' => 'Asset Credit',
            ],
        ];

        foreach ($products as $data) {
            Product::firstOrCreate(
                ['sku' => $data['sku']],
                array_merge($data, [
                    'billing_frequency' => BillingFrequency::ONE_TIME->value,
                    'pricing_model' => PricingModel::FLAT_FEE->value,
                    'is_active' => true,
                    'min_quantity' => 1,
                    'included_quantity' => 0,
                    'cost_price' => 0 // purely credit
                ])
            );
        }
    }
}

<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Enums\ProductType;
use Modules\Inventory\Enums\BillingFrequency;
use Modules\Inventory\Enums\PricingModel;

class AdHocProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Ad-hoc Service - Tier I',
                'sku' => 'SVC-ADHOC-T1',
                'description' => 'Tier I Resolution',
                'base_price' => 50.00,
                'type' => ProductType::SERVICE->value,
                'category' => 'Ad-Hoc Service',
            ],
            [
                'name' => 'Ad-hoc Service - Tier II',
                'sku' => 'SVC-ADHOC-T2',
                'description' => 'Tier II Resolution',
                'base_price' => 100.00,
                'type' => ProductType::SERVICE->value,
                'category' => 'Ad-Hoc Service',
            ],
            [
                'name' => 'Ad-hoc Service - Tier III',
                'sku' => 'SVC-ADHOC-T3',
                'description' => 'Tier III Resolution',
                'base_price' => 150.00,
                'type' => ProductType::SERVICE->value,
                'category' => 'Ad-Hoc Service',
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
                    'cost_price' => 0
                ])
            );
        }
    }
}

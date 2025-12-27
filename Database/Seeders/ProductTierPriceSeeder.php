<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\Product;
use Modules\Billing\Models\ProductTierPrice;

class ProductTierPriceSeeder extends Seeder
{
    public function run()
    {
        $products = Product::all();

        foreach ($products as $product) {
            // Standard: base_price * 1.0
            ProductTierPrice::updateOrCreate(
                ['product_id' => $product->id, 'tier' => 'standard'],
                ['price' => $product->base_price]
            );

            // Non-Profit: base_price * 0.85 (15% discount)
            ProductTierPrice::updateOrCreate(
                ['product_id' => $product->id, 'tier' => 'non_profit'],
                ['price' => $product->base_price * 0.85]
            );

            // Consumer: base_price * 1.10 (10% premium)
            ProductTierPrice::updateOrCreate(
                ['product_id' => $product->id, 'tier' => 'consumer'],
                ['price' => $product->base_price * 1.10]
            );
        }
    }
}

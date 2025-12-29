<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\PriceOverride;
use Modules\Inventory\Models\Product;

class PriceOverrideSeeder extends Seeder
{
    public function run()
    {
        $company = Company::where('name', 'Acme Corp (Standard)')->first();
        $product = Product::first();

        if ($company && $product) {
            // Fixed price override
            PriceOverride::create([
                'company_id' => $company->id,
                'product_id' => $product->id,
                'type' => 'fixed',
                'value' => $product->base_price * 0.9, // 10% off fixed
                'notes' => 'Special deal for Acme',
                'is_active' => true,
            ]);
        }

        $company2 = Company::where('name', 'Tech Startups Inc (Standard)')->first();
        if ($company2 && $product) {
            // Discount percent override
            PriceOverride::create([
                'company_id' => $company2->id,
                'product_id' => $product->id,
                'type' => 'discount_percent',
                'value' => 20.00, // 20% off
                'notes' => 'Startup discount',
                'is_active' => true,
            ]);
        }
    }
}

<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Models\PriceOverride;
use Modules\Billing\Models\Company;
use Modules\Inventory\Models\Product;

class PriceOverrideFactory extends Factory
{
    protected $model = PriceOverride::class;

    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'product_id' => Product::factory(),
            'type' => 'fixed',
            'value' => 100.00,
            'is_active' => true,
        ];
    }
}

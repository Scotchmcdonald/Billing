<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Models\Company;
use Modules\Inventory\Models\Product;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'name' => 'default',
            'stripe_id' => 'sub_' . $this->faker->randomAscii,
            'stripe_status' => 'active',
            'stripe_price' => 'price_' . $this->faker->randomAscii,
            'quantity' => 1,
            'trial_ends_at' => null,
            'ends_at' => null,
            'product_id' => Product::factory(),
            'billing_frequency' => 'monthly',
            'effective_price' => 100.00,
            'is_active' => true,
            'next_billing_date' => now(),
        ];
    }
}

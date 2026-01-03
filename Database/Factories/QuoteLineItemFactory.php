<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Models\QuoteLineItem;
use Modules\Billing\Models\Quote;

class QuoteLineItemFactory extends Factory
{
    protected $model = QuoteLineItem::class;

    public function definition()
    {
        $price = $this->faker->randomFloat(2, 100, 2000);
        return [
            'quote_id' => Quote::factory(),
            'description' => $this->faker->sentence,
            'quantity' => 1,
            'unit_price' => $price,
            'standard_price' => $price,
            'subtotal' => $price,
            'is_recurring' => false,
        ];
    }
}

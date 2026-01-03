<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Models\InvoiceLineItem;
use Modules\Billing\Models\Invoice;

class InvoiceLineItemFactory extends Factory
{
    protected $model = InvoiceLineItem::class;

    public function definition()
    {
        $price = $this->faker->randomFloat(2, 10, 500);
        return [
            'invoice_id' => Invoice::factory(),
            'description' => $this->faker->sentence,
            'quantity' => $this->faker->numberBetween(1, 10),
            'unit_price' => $price,
            'standard_unit_price' => $price, // Default to same
            'subtotal' => $price, // Will be recalc
            'tax_amount' => 0,
            'is_disputed' => false,
        ];
    }

    public function disputed()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_disputed' => true,
                'dispute_reason' => $this->faker->sentence,
            ];
        });
    }
}

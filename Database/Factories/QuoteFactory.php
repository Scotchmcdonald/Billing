<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Models\Quote;
use Modules\Billing\Models\Company;

class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'client_id' => null,
            'quote_number' => $this->faker->unique()->bothify('Q-####'),
            'status' => 'draft',
            'total' => 0,
            'valid_until' => now()->addDays(30),
        ];
    }
}

<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Models\ServiceContract;
use Modules\Billing\Models\Company;

class ServiceContractFactory extends Factory
{
    protected $model = ServiceContract::class;

    public function definition()
    {
        return [
            'client_id' => null,
            'name' => $this->faker->words(3, true),
            'status' => 'active', // Lowercase to match migration default
            'standard_rate' => $this->faker->randomFloat(2, 50, 500),
        ];
    }
}

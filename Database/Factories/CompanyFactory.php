<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Models\Company;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'pricing_tier' => 'standard',
            'margin_floor_percent' => 20.00,
            'is_active' => true,
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->stateAbbr,
            'postal_code' => $this->faker->postcode,
            'country' => 'USA',
        ];
    }

    public function smallBusiness()
    {
        return $this->state(function (array $attributes) {
            return [
                'pricing_tier' => 'standard',
                'margin_floor_percent' => 20.00,
            ];
        });
    }

    public function nonProfit()
    {
        return $this->state(function (array $attributes) {
            return [
                'pricing_tier' => 'non_profit',
                'margin_floor_percent' => 10.00,
            ];
        });
    }

    public function consumer()
    {
        return $this->state(function (array $attributes) {
            return [
                'pricing_tier' => 'consumer',
                'margin_floor_percent' => 30.00,
            ];
        });
    }
}

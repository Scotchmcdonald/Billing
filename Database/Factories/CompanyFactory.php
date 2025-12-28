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
        ];
    }
}

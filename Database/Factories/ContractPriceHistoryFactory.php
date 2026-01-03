<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Models\ContractPriceHistory;
use Modules\Billing\Models\ServiceContract;

class ContractPriceHistoryFactory extends Factory
{
    protected $model = ContractPriceHistory::class;

    public function definition()
    {
        return [
            'contract_id' => ServiceContract::factory(), // Assuming ServiceContract factory exists or we create it
            'unit_price' => $this->faker->randomFloat(2, 50, 500),
            'started_at' => now()->subMonths(6),
            'ended_at' => null,
        ];
    }
}

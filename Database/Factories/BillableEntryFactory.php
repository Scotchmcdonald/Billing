<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Models\BillableEntry;
use Modules\Billing\Models\Company;
use App\Models\User;

class BillableEntryFactory extends Factory
{
    protected $model = BillableEntry::class;

    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'description' => $this->faker->sentence,
            'quantity' => 1,
            'rate' => 100.00,
            'subtotal' => 100.00,
            'type' => 'product',
            'is_billable' => true,
            'date' => now(),
        ];
    }
}

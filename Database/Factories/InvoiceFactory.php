<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Company;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'client_id' => null, // Should be set by seeder if needed
            'invoice_number' => $this->faker->unique()->bothify('INV-####'),
            'status' => 'draft',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'total' => 0, // Calculated from items
            'subtotal' => 0,
            'tax_total' => 0,
            'anomaly_score' => 0,
        ];
    }

    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
                'approved_at' => now(),
            ];
        });
    }

    public function sent()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'sent',
                'sent_at' => now(),
            ];
        });
    }
}

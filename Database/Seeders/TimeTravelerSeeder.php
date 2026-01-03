<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\InvoiceLineItem;

class TimeTravelerSeeder extends Seeder
{
    public function run()
    {
        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->command->info('No companies found. Please create a company first.');
            return;
        }

        $this->command->info("Generating 12 months of historical data for {$companies->count()} companies...");

        foreach ($companies as $company) {
            // Determine a base amount based on company type or random
            $baseAmount = $company->pricing_tier === 'non_profit' ? 800 : 2500;

            $months = range(1, 12);
            foreach ($months as $month) {
                // Go back $month months
                $date = now()->subMonths($month)->startOfMonth();
                
                // Simulate growth: older invoices are smaller
                $growthFactor = (12 - $month) * 50; 
                $amount = $baseAmount + $growthFactor + rand(-100, 100);
                
                // Skip some months for realism if it's not a subscription model (optional)
                // But for MSPs, MRR is usually consistent.

                $invoice = Invoice::factory()->create([
                    'company_id' => $company->id,
                    'client_id' => $company->client_id, // Ensure link if exists
                    'status' => 'approved', // Historical data is usually closed/approved
                    'issue_date' => $date,
                    'due_date' => $date->copy()->addDays(30),
                    'created_at' => $date,
                    'updated_at' => $date,
                    'total' => $amount,
                    'subtotal' => $amount,
                    'anomaly_score' => 0, // Assume historical data is clean
                ]);

                InvoiceLineItem::factory()->create([
                    'invoice_id' => $invoice->id,
                    'description' => 'Monthly Managed Services (Historical)',
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'standard_unit_price' => $amount,
                    'subtotal' => $amount,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }

        $this->command->info('Time travel complete. Executive dashboards should now show trend lines.');
    }
}

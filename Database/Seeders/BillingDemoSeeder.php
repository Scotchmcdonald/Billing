<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\InvoiceLineItem;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Models\PriceOverride;
use Modules\Billing\Models\Quote;
use Modules\Billing\Models\QuoteLineItem;
use Modules\Inventory\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class BillingDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->command->info('Seeding Billing Demo Data...');

        // 1. Create Products (Service Plans & Add-ons)
        $products = $this->createProducts();

        // 2. Create Companies
        $companies = $this->createCompanies();

        // 3. Create Price Overrides
        $this->createPriceOverrides($companies, $products);

        // 4. Create Subscriptions & Invoices for each company
        foreach ($companies as $index => $company) {
            // Clean up existing billing data for this company to avoid duplicates if re-running
            $invoices = Invoice::where('company_id', $company->id)->get();
            foreach ($invoices as $invoice) {
                InvoiceLineItem::where('invoice_id', $invoice->id)->delete();
                Payment::where('invoice_id', $invoice->id)->delete();
                $invoice->delete();
            }
            Subscription::where('company_id', $company->id)->delete();
            
            // Clean up quotes
            $quotes = Quote::where('company_id', $company->id)->get();
            foreach ($quotes as $quote) {
                QuoteLineItem::where('quote_id', $quote->id)->delete();
                $quote->delete();
            }

            $this->setupCompanyBilling($company, $products, $index);
            $this->createQuotes($company, $products);
        }

        $this->command->info('Billing Demo Data Seeded Successfully!');
    }

    private function createPriceOverrides($companies, $products)
    {
        $this->command->info('Creating Price Overrides...');
        
        $companies = collect($companies);

        // Clear existing overrides
        PriceOverride::truncate();

        // Convert array to Collection if it isn't one
        $companiesCollection = collect($companies);

        foreach ($products as $product) {
            // Randomly assign overrides to some companies (approx 30% of companies)
            $randomCompanies = $companiesCollection->random(max(1, (int)($companiesCollection->count() * 0.3)));
            
            foreach ($randomCompanies as $company) {
                // 50% chance of discount, 50% chance of premium
                $multiplier = rand(0, 1) ? rand(80, 95) : rand(105, 120);
                $value = $product->base_price * ($multiplier / 100);

                PriceOverride::create([
                    'company_id' => $company->id,
                    'product_id' => $product->id,
                    'type' => 'fixed',
                    'value' => $value,
                    'custom_price' => $value,
                    'notes' => 'Negotiated rate ' . ($multiplier < 100 ? 'Discount' : 'Premium'),
                    'is_active' => true,
                    'approved_by' => User::first()->id ?? 1,
                ]);
            }
        }
    }

    private function createProducts()
    {
        $products = [
            [
                'sku' => 'SVC-BASIC',
                'name' => 'Basic Support Plan',
                'description' => 'Entry level support package',
                'category' => 'Service',
                'type' => 'recurring',
                'base_price' => 99.00,
            ],
            [
                'sku' => 'SVC-PRO',
                'name' => 'Professional Support Plan',
                'description' => 'Standard business support package',
                'category' => 'Service',
                'type' => 'recurring',
                'base_price' => 299.00,
            ],
            [
                'sku' => 'SVC-ENT',
                'name' => 'Enterprise Support Plan',
                'description' => '24/7 dedicated support package',
                'category' => 'Service',
                'type' => 'recurring',
                'base_price' => 999.00,
            ],
            [
                'sku' => 'ADD-USER',
                'name' => 'Additional User License',
                'description' => 'Per user monthly license',
                'category' => 'License',
                'type' => 'recurring',
                'base_price' => 15.00,
            ],
            [
                'sku' => 'HR-CONSULT',
                'name' => 'Hourly Consultation',
                'description' => 'Ad-hoc consultation services',
                'category' => 'Service',
                'type' => 'one-time',
                'base_price' => 150.00,
            ],
            [
                'sku' => 'SETUP-FEE',
                'name' => 'Implementation Fee',
                'description' => 'One-time setup and onboarding',
                'category' => 'Service',
                'type' => 'one-time',
                'base_price' => 500.00,
            ],
        ];

        $createdProducts = [];

        foreach ($products as $data) {
            $product = Product::firstOrCreate(
                ['sku' => $data['sku']],
                array_merge($data, [
                    'cost_price' => $data['base_price'] * 0.3, // 70% margin
                    'is_active' => true,
                    'tax_code' => 'tx_service',
                ])
            );
            $createdProducts[$data['sku']] = $product;
        }

        return $createdProducts;
    }

    private function createCompanies()
    {
        $companiesData = [
            [
                'name' => 'Acme Corp',
                'email' => 'billing@acmecorp.com',
                'address' => [
                    'line1' => '123 Industrial Way',
                    'city' => 'Metropolis',
                    'state' => 'NY',
                    'postal_code' => '10001',
                    'country' => 'US',
                ],
            ],
            [
                'name' => 'Globex Corporation',
                'email' => 'accounts@globex.com',
                'address' => [
                    'line1' => '456 Global Blvd',
                    'city' => 'Cypress Creek',
                    'state' => 'CA',
                    'postal_code' => '90210',
                    'country' => 'US',
                ],
            ],
            [
                'name' => 'Soylent Corp',
                'email' => 'finance@soylent.com',
                'address' => [
                    'line1' => '789 Future Lane',
                    'city' => 'New York',
                    'state' => 'NY',
                    'postal_code' => '10012',
                    'country' => 'US',
                ],
            ],
            [
                'name' => 'Initech',
                'email' => 'bill.lumbergh@initech.com',
                'address' => [
                    'line1' => '100 TPS Report Dr',
                    'city' => 'Austin',
                    'state' => 'TX',
                    'postal_code' => '73301',
                    'country' => 'US',
                ],
            ],
            [
                'name' => 'Umbrella Corporation',
                'email' => 'red.queen@umbrella.com',
                'address' => [
                    'line1' => '500 Hive Underground',
                    'city' => 'Raccoon City',
                    'state' => 'MO',
                    'postal_code' => '63101',
                    'country' => 'US',
                ],
            ],
        ];

        $companies = [];

        foreach ($companiesData as $data) {
            $company = Company::updateOrCreate(
                ['name' => $data['name']],
                [
                    'email' => $data['email'],
                    'billing_address' => json_encode($data['address']),
                    'is_active' => true,
                    'pricing_tier' => 'standard',
                    'stripe_id' => 'cus_' . Str::random(14), // Fake Stripe ID for demo
                    'pm_type' => 'card',
                    'pm_last_four' => '4242',
                ]
            );
            $companies[] = $company;
        }

        return $companies;
    }

    private function setupCompanyBilling($company, $products, $index)
    {
        // Assign different plans based on index to show variety
        $planSku = match ($index % 3) {
            0 => 'SVC-BASIC',
            1 => 'SVC-PRO',
            2 => 'SVC-ENT',
        };

        $plan = $products[$planSku];
        
        // Create Subscription
        $startDate = Carbon::now()->subMonths(rand(3, 12));
        
        Subscription::create([
            'company_id' => $company->id,
            'name' => 'default',
            'stripe_id' => 'sub_' . Str::random(14),
            'stripe_status' => 'active',
            'stripe_price' => 'price_' . Str::random(14),
            'quantity' => 1,
            'starts_at' => $startDate,
            'ends_at' => null, // Auto-renew
            'next_billing_date' => Carbon::now()->addDays(rand(1, 30)),
            'effective_price' => $plan->base_price,
            'product_id' => $plan->id, // Assuming we added this relation or column
        ]);

        // Create Past Invoices (Monthly)
        $currentDate = clone $startDate;
        $invoiceCount = 0;

        while ($currentDate->lt(Carbon::now())) {
            $invoiceCount++;
            $invoiceTotal = $plan->base_price;
            $status = 'paid';
            
            // Occasionally add extra items or make the latest one open
            $extraItems = [];
            if (rand(1, 5) === 1) {
                $extraItems[] = [
                    'product' => $products['HR-CONSULT'],
                    'qty' => rand(1, 5),
                ];
            }
            if ($index === 0 && $invoiceCount === 1) {
                 $extraItems[] = [
                    'product' => $products['SETUP-FEE'],
                    'qty' => 1,
                ];
            }

            // Calculate total with extras
            foreach ($extraItems as $item) {
                $invoiceTotal += $item['product']->base_price * $item['qty'];
            }

            // Make the most recent invoice potentially sent/unpaid
            if ($currentDate->copy()->addMonth()->gt(Carbon::now())) {
                $status = rand(0, 1) ? 'sent' : 'paid';
            }

            $invoice = Invoice::create([
                'company_id' => $company->id,
                'invoice_number' => 'INV-' . $company->id . '-' . str_pad($invoiceCount, 4, '0', STR_PAD_LEFT),
                'issue_date' => $currentDate->copy(),
                'due_date' => $currentDate->copy()->addDays(14),
                'status' => $status,
                'subtotal' => $invoiceTotal,
                'tax_total' => $invoiceTotal * 0.08, // 8% tax
                'total' => $invoiceTotal * 1.08,
                'notes' => 'Thank you for your business.',
            ]);

            // Line Items
            // 1. Main Plan
            InvoiceLineItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $plan->id,
                'description' => $plan->name . ' - ' . $currentDate->format('M Y'),
                'quantity' => 1,
                'unit_price' => $plan->base_price,
                'subtotal' => $plan->base_price,
                'tax_amount' => $plan->base_price * 0.08,
                'is_fee' => false,
            ]);

            // 2. Extras
            foreach ($extraItems as $item) {
                $subtotal = $item['product']->base_price * $item['qty'];
                InvoiceLineItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product']->id,
                    'description' => $item['product']->name,
                    'quantity' => $item['qty'],
                    'unit_price' => $item['product']->base_price,
                    'subtotal' => $subtotal,
                    'tax_amount' => $subtotal * 0.08,
                    'is_fee' => false,
                ]);
            }

            // 3. Create Payment if Paid
            if ($status === 'paid') {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'company_id' => $company->id,
                    'amount' => $invoice->total,
                    'payment_method' => 'stripe_card',
                    'payment_reference' => 'ch_' . Str::random(24),
                    'payment_date' => $currentDate->copy()->addDays(rand(0, 5)), // Paid within 5 days
                    'notes' => 'Automatic payment',
                    'created_by' => User::first()->id ?? 1, // Fallback to ID 1 if no user
                ]);
            }

            $currentDate->addMonth();
        }
    }

    private function createQuotes($company, $products)
    {
        // Create 1-2 quotes per company
        $count = rand(1, 2);
        
        for ($i = 0; $i < $count; $i++) {
            $status = ['draft', 'sent', 'accepted', 'expired'][rand(0, 3)];
            $subtotal = 0;
            
            $quote = Quote::create([
                'company_id' => $company->id,
                'quote_number' => 'Q-' . $company->id . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'title' => 'Proposal for Additional Services',
                'valid_until' => Carbon::now()->addDays(30),
                'subtotal' => 0, // Will update
                'tax_total' => 0,
                'total' => 0,
                'status' => $status,
                'notes' => 'Valid for 30 days.',
                'public_token' => Str::random(32),
            ]);

            // Add items
            $items = [
                ['product' => $products['SVC-ENT'], 'qty' => 1],
                ['product' => $products['HR-CONSULT'], 'qty' => 10],
            ];

            foreach ($items as $item) {
                $lineTotal = $item['product']->base_price * $item['qty'];
                $subtotal += $lineTotal;
                
                QuoteLineItem::create([
                    'quote_id' => $quote->id,
                    'product_id' => $item['product']->id,
                    'description' => $item['product']->name,
                    'quantity' => $item['qty'],
                    'unit_price' => $item['product']->base_price,
                    'subtotal' => $lineTotal,
                ]);
            }

            $quote->update([
                'subtotal' => $subtotal,
                'tax_total' => $subtotal * 0.08,
                'total' => $subtotal * 1.08,
            ]);
        }
    }
}

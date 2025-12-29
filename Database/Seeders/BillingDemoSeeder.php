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
use Modules\Billing\Models\Dispute;
use Modules\Billing\Models\DisputeAttachment;
use Modules\Billing\Models\CreditNote;
use Modules\Billing\Models\Retainer;
use Modules\Inventory\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BillingDemoSeeder extends Seeder
{
    private $user;
    
    // Demo company names for easy identification
    private $demoCompanyNames = [
        'Acme Corporation',
        'Globex Corporation',
        'Initech',
        'Soylent Industries',
        'Umbrella Corporation',
        'Wayne Enterprises',
        'Stark Industries',
        'Wonka Industries',
    ];

    /**
     * Run the database seeds - Enhanced with all feature showcase
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->command->info('🚀 Seeding Enhanced Billing Demo Data...');
        
        // Get or create a user for activities
        $this->user = User::first() ?? User::factory()->create([
            'name' => 'Demo Admin',
            'email' => 'admin@demo.local',
        ]);

        DB::beginTransaction();
        
        try {
            // 1. Create Products (Service Plans & Add-ons)
            $this->command->info('📦 Creating Products...');
            $products = $this->createProducts();

            // 2. Create Diverse Companies
            $this->command->info('🏢 Creating Companies...');
            $companies = $this->createCompanies();

            // 3. Create Price Overrides (showcase negotiated rates)
            $this->command->info('�� Creating Price Overrides...');
            $this->createPriceOverrides($companies, $products);

            // 4. Setup detailed billing for each company
            foreach ($companies as $index => $company) {
                $this->command->info("   Setting up billing for {$company->name}...");
                $this->cleanupCompanyData($company);
                $this->setupCompanyBillingScenario($company, $products, $index);
            }

            DB::commit();
            $this->command->info('✅ Enhanced Billing Data Seeded Successfully!');
            $this->printSummary($companies);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Clean up all demo data - run with: php artisan billing:clean-demo
     */
    public function cleanup()
    {
        $this->command->info('🧹 Cleaning up demo billing data...');
        
        DB::beginTransaction();
        
        try {
            // Find all demo companies
            $demoCompanies = Company::whereIn('name', $this->demoCompanyNames)->get();
            
            $this->command->info("Found " . $demoCompanies->count() . " demo companies to clean");
            
            foreach ($demoCompanies as $company) {
                $this->command->info("  Cleaning {$company->name}...");
                $this->cleanupCompanyData($company);
                $company->delete();
            }
            
            // Clean up demo products
            $demoSkus = [
                'SVC-BASIC', 'SVC-PRO', 'SVC-ENT', 'SVC-PREMIUM',
                'LIC-USER', 'LIC-DEVICE', 'LIC-SERVER',
                'CLOUD-BASIC', 'CLOUD-PRO', 'CLOUD-STORAGE',
                'PS-CONSULT', 'PS-PROJECT', 'PS-EMERGENCY',
                'FEE-SETUP', 'FEE-MIGRATION', 'FEE-TRAINING',
            ];
            
            // Force delete any invoice line items referencing these products (orphaned or from other companies)
            $productIds = Product::whereIn('sku', $demoSkus)->pluck('id');
            if ($productIds->isNotEmpty()) {
                InvoiceLineItem::whereIn('product_id', $productIds)->delete();
            }
            
            $deletedProducts = Product::whereIn('sku', $demoSkus)->delete();
            $this->command->info("  Cleaned {$deletedProducts} demo products");
            
            DB::commit();
            $this->command->info('✅ Demo data cleaned successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Cleanup failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function cleanupCompanyData($company)
    {
        // Clean up existing billing data
        $invoices = Invoice::where('company_id', $company->id)->get();
        foreach ($invoices as $invoice) {
            Dispute::where('invoice_id', $invoice->id)->delete();
            
            // Billable entries might reference invoice line items, so we need to unlink them first
            // Assuming BillableEntry model exists and has relationship, or use DB query
            // Since we are in a seeder, we can use DB facade if models are not fully set up, but let's try Eloquent first if models exist.
            // However, to be safe against missing models, let's use DB for cleanup where possible or just handle the FK.
            // The error was: Cannot delete or update a parent row: a foreign key constraint fails (`freescout`.`invoice_line_items`, CONSTRAINT `invoice_line_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`))
            // This error happened when deleting PRODUCTS. It means InvoiceLineItems still exist that reference the products.
            // So we MUST delete InvoiceLineItems first.
            
            InvoiceLineItem::where('invoice_id', $invoice->id)->delete();
            Payment::where('invoice_id', $invoice->id)->delete();
            CreditNote::where('invoice_id', $invoice->id)->delete();
            $invoice->delete();
        }
        
        Subscription::where('company_id', $company->id)->delete();
        Retainer::where('company_id', $company->id)->delete();
        PriceOverride::where('company_id', $company->id)->delete();
        
        $quotes = Quote::where('company_id', $company->id)->get();
        foreach ($quotes as $quote) {
            QuoteLineItem::where('quote_id', $quote->id)->delete();
            $quote->delete();
        }
        
        // Also delete billable entries for the company
        // BillableEntry::where('company_id', $company->id)->delete(); 
    }

    private function createProducts()
    {
        $productsData = [
            // Service Plans
            [
                'sku' => 'SVC-BASIC',
                'name' => 'Basic Support Plan',
                'description' => 'Entry level support package - 8x5 support, 4-hour response time',
                'category' => 'Managed Services',
                'type' => 'recurring',
                'base_price' => 99.00,
            ],
            [
                'sku' => 'SVC-PRO',
                'name' => 'Professional Support Plan',
                'description' => 'Standard business support package - 24x5 support, 2-hour response time',
                'category' => 'Managed Services',
                'type' => 'recurring',
                'base_price' => 299.00,
            ],
            [
                'sku' => 'SVC-ENT',
                'name' => 'Enterprise Support Plan',
                'description' => '24/7 dedicated support package with 1-hour response time',
                'category' => 'Managed Services',
                'type' => 'recurring',
                'base_price' => 999.00,
            ],
            [
                'sku' => 'SVC-PREMIUM',
                'name' => 'Premium Enterprise Plus',
                'description' => 'White-glove service with dedicated account manager',
                'category' => 'Managed Services',
                'type' => 'recurring',
                'base_price' => 2499.00,
            ],
            
            // Per-Unit Licenses
            [
                'sku' => 'LIC-USER',
                'name' => 'User License',
                'description' => 'Per user monthly license',
                'category' => 'Licenses',
                'type' => 'recurring',
                'base_price' => 15.00,
            ],
            [
                'sku' => 'LIC-DEVICE',
                'name' => 'Device Management License',
                'description' => 'Per device monitoring and management',
                'category' => 'Licenses',
                'type' => 'recurring',
                'base_price' => 5.00,
            ],
            [
                'sku' => 'LIC-SERVER',
                'name' => 'Server Management License',
                'description' => 'Per server advanced monitoring',
                'category' => 'Licenses',
                'type' => 'recurring',
                'base_price' => 50.00,
            ],
            
            // Cloud Services
            [
                'sku' => 'CLOUD-BASIC',
                'name' => 'Cloud Hosting - Basic',
                'description' => '2 vCPU, 4GB RAM, 50GB SSD',
                'category' => 'Cloud Hosting',
                'type' => 'recurring',
                'base_price' => 49.00,
            ],
            [
                'sku' => 'CLOUD-PRO',
                'name' => 'Cloud Hosting - Professional',
                'description' => '4 vCPU, 8GB RAM, 100GB SSD',
                'category' => 'Cloud Hosting',
                'type' => 'recurring',
                'base_price' => 99.00,
            ],
            [
                'sku' => 'CLOUD-STORAGE',
                'name' => 'Additional Cloud Storage',
                'description' => 'Per 100GB additional storage',
                'category' => 'Cloud Hosting',
                'type' => 'recurring',
                'base_price' => 10.00,
            ],
            
            // Professional Services
            [
                'sku' => 'PS-CONSULT',
                'name' => 'Technical Consultation',
                'description' => 'Hourly consulting services',
                'category' => 'Professional Services',
                'type' => 'one-time',
                'base_price' => 150.00,
            ],
            [
                'sku' => 'PS-PROJECT',
                'name' => 'Project Services',
                'description' => 'Custom project work - per hour',
                'category' => 'Professional Services',
                'type' => 'one-time',
                'base_price' => 175.00,
            ],
            [
                'sku' => 'PS-EMERGENCY',
                'name' => 'Emergency Support',
                'description' => 'After-hours emergency support - per hour',
                'category' => 'Professional Services',
                'type' => 'one-time',
                'base_price' => 250.00,
            ],
            
            // One-Time Fees
            [
                'sku' => 'FEE-SETUP',
                'name' => 'Implementation Fee',
                'description' => 'One-time setup and onboarding',
                'category' => 'Setup Fees',
                'type' => 'one-time',
                'base_price' => 500.00,
            ],
            [
                'sku' => 'FEE-MIGRATION',
                'name' => 'Email Migration Service',
                'description' => 'Full email migration service',
                'category' => 'Migration Services',
                'type' => 'one-time',
                'base_price' => 1500.00,
            ],
            [
                'sku' => 'FEE-TRAINING',
                'name' => 'Staff Training Session',
                'description' => 'On-site or virtual training session',
                'category' => 'Training',
                'type' => 'one-time',
                'base_price' => 750.00,
            ],
        ];

        $createdProducts = [];

        foreach ($productsData as $data) {
            $product = Product::updateOrCreate(
                ['sku' => $data['sku']],
                array_merge($data, [
                    'cost_price' => $data['base_price'] * 0.35, // 65% margin
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
                'name' => 'Acme Corporation',
                'email' => 'billing@acmecorp.com',
                'phone' => '(555) 123-4567',
                'address' => [
                    'line1' => '123 Industrial Way',
                    'city' => 'Metropolis',
                    'state' => 'NY',
                    'postal_code' => '10001',
                    'country' => 'US',
                ],
                'tier' => 'standard',
                'scenario' => 'happy_long_term', // 2+ years, always paid on time
            ],
            [
                'name' => 'Globex Corporation',
                'email' => 'accounts@globex.com',
                'phone' => '(555) 234-5678',
                'address' => [
                    'line1' => '456 Global Blvd',
                    'city' => 'Cypress Creek',
                    'state' => 'CA',
                    'postal_code' => '90210',
                    'country' => 'US',
                ],
                'tier' => 'standard',
                'scenario' => 'growth', // Growing company, upgrading services
            ],
            [
                'name' => 'Initech',
                'email' => 'bill.lumbergh@initech.com',
                'phone' => '(555) 345-6789',
                'address' => [
                    'line1' => '100 TPS Report Dr',
                    'city' => 'Austin',
                    'state' => 'TX',
                    'postal_code' => '73301',
                    'country' => 'US',
                ],
                'tier' => 'consumer',
                'scenario' => 'disputed', // Has disputed invoices
            ],
            [
                'name' => 'Soylent Industries',
                'email' => 'finance@soylent.com',
                'phone' => '(555) 456-7890',
                'address' => [
                    'line1' => '789 Future Lane',
                    'city' => 'New York',
                    'state' => 'NY',
                    'postal_code' => '10012',
                    'country' => 'US',
                ],
                'tier' => 'standard',
                'scenario' => 'overdue', // Has overdue invoices
            ],
            [
                'name' => 'Umbrella Corporation',
                'email' => 'red.queen@umbrella.com',
                'phone' => '(555) 567-8901',
                'address' => [
                    'line1' => '500 Hive Underground',
                    'city' => 'Raccoon City',
                    'state' => 'MO',
                    'postal_code' => '63101',
                    'country' => 'US',
                ],
                'tier' => 'standard',
                'scenario' => 'premium_negotiated', // Premium tier with custom pricing
            ],
            [
                'name' => 'Wayne Enterprises',
                'email' => 'lucius.fox@wayneent.com',
                'phone' => '(555) 678-9012',
                'address' => [
                    'line1' => '1 Wayne Plaza',
                    'city' => 'Gotham',
                    'state' => 'NJ',
                    'postal_code' => '07001',
                    'country' => 'US',
                ],
                'tier' => 'standard',
                'scenario' => 'retainer', // Uses retainer model
            ],
            [
                'name' => 'Stark Industries',
                'email' => 'pepper.potts@stark.com',
                'phone' => '(555) 789-0123',
                'address' => [
                    'line1' => '200 Park Avenue',
                    'city' => 'Malibu',
                    'state' => 'CA',
                    'postal_code' => '90265',
                    'country' => 'US',
                ],
                'tier' => 'standard',
                'scenario' => 'new_onboarding', // Recently onboarded
            ],
            [
                'name' => 'Wonka Industries',
                'email' => 'oompaloompa@wonka.com',
                'phone' => '(555) 890-1234',
                'address' => [
                    'line1' => '1 Chocolate Factory Ln',
                    'city' => 'Denver',
                    'state' => 'CO',
                    'postal_code' => '80014',
                    'country' => 'US',
                ],
                'tier' => 'consumer',
                'scenario' => 'credit_note', // Has credit notes
            ],
        ];

        $companies = [];

        foreach ($companiesData as $data) {
            $company = Company::updateOrCreate(
                ['name' => $data['name']],
                [
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'billing_address' => json_encode($data['address']),
                    'is_active' => true,
                    'pricing_tier' => $data['tier'],
                    'stripe_id' => 'cus_' . Str::random(14),
                    'pm_type' => 'card',
                    'pm_last_four' => ['4242', '5555', '4111', '6011'][rand(0, 3)],
                ]
            );
            
            $company->scenario = $data['scenario'];
            $companies[] = $company;
        }

        return $companies;
    }

    private function createPriceOverrides($companies, $products)
    {
        foreach ($companies as $company) {
            // Premium and Enterprise tiers get negotiated rates
            if (in_array($company->pricing_tier, ['enterprise', 'professional'])) {
                $overrideCount = rand(2, 5);
                $productsToOverride = collect($products)->random($overrideCount);
                
                foreach ($productsToOverride as $product) {
                    // Enterprise gets discounts, Professional gets moderate discounts
                    $multiplier = $company->pricing_tier === 'enterprise' 
                        ? rand(75, 85) 
                        : rand(88, 95);
                    
                    $value = round($product->base_price * ($multiplier / 100), 2);

                    PriceOverride::updateOrCreate(
                        [
                            'company_id' => $company->id,
                            'product_id' => $product->id,
                        ],
                        [
                            'type' => 'fixed',
                            'value' => $value,
                            'custom_price' => $value,
                            'notes' => sprintf(
                                '%s rate - %d%% of list price',
                                $company->pricing_tier === 'enterprise' ? 'Volume discount' : 'Negotiated',
                                $multiplier
                            ),
                            'is_active' => true,
                            'approved_by' => $this->user->id,
                            'approved_at' => Carbon::now()->subDays(rand(30, 180)),
                        ]
                    );
                }
            }
        }
    }

    private function setupCompanyBillingScenario($company, $products, $index)
    {
        switch ($company->scenario) {
            case 'happy_long_term':
                $this->createHappyLongTermCustomer($company, $products);
                break;
            case 'growth':
                $this->createGrowthScenario($company, $products);
                break;
            case 'disputed':
                $this->createDisputedScenario($company, $products);
                break;
            case 'overdue':
                $this->createOverdueScenario($company, $products);
                break;
            case 'premium_negotiated':
                $this->createPremiumNegotiatedScenario($company, $products);
                break;
            case 'retainer':
                $this->createRetainerScenario($company, $products);
                break;
            case 'new_onboarding':
                $this->createNewOnboardingScenario($company, $products);
                break;
            case 'credit_note':
                $this->createCreditNoteScenario($company, $products);
                break;
        }
        
        // All companies get quotes
        $this->createQuotes($company, $products);
    }

    private function createHappyLongTermCustomer($company, $products)
    {
        // 24 months of perfect payment history
        $startDate = Carbon::now()->subMonths(24);
        $plan = $products['SVC-ENT'];
        
        $this->createSubscription($company, $plan, $startDate);
        $this->createMonthlyInvoices($company, $plan, $startDate, 24, 'paid', $products);
    }

    private function createGrowthScenario($company, $products)
    {
        // Started Basic, upgraded to Pro after 3 months, added services
        $startDate = Carbon::now()->subMonths(8);
        
        // First 3 months on Basic
        $basicPlan = $products['SVC-BASIC'];
        $this->createMonthlyInvoices($company, $basicPlan, $startDate, 3, 'paid', $products);
        
        // Upgraded to Pro
        $proPlan = $products['SVC-PRO'];
        $upgradeDate = $startDate->copy()->addMonths(3);
        $this->createSubscription($company, $proPlan, $upgradeDate);
        $this->createMonthlyInvoices($company, $proPlan, $upgradeDate, 5, 'paid', $products, [
            // Added cloud hosting and extra licenses
            ['product' => $products['CLOUD-PRO'], 'qty' => 1],
            ['product' => $products['LIC-USER'], 'qty' => 10],
        ]);
    }

    private function createDisputedScenario($company, $products)
    {
        // Recent dispute on an invoice
        $startDate = Carbon::now()->subMonths(6);
        $plan = $products['SVC-PRO'];
        
        $this->createSubscription($company, $plan, $startDate);
        $invoices = $this->createMonthlyInvoices($company, $plan, $startDate, 6, 'paid', $products);
        
        // Dispute the 4th invoice (2 months ago)
        if (isset($invoices[3])) {
            $disputedInvoice = $invoices[3];
            $disputedInvoice->update(['status' => 'disputed']);
            
            Dispute::create([
                'invoice_id' => $disputedInvoice->id,
                'reason' => 'Service Quality Issue',
                'explanation' => 'We experienced significant downtime during the billing period (October 15-17) that was not adequately communicated. Our service level agreement specifies compensation for outages exceeding 4 hours. We are requesting a credit for 3 days of service.',
                'status' => 'open',
                'submitted_by' => $this->user->id,
                'submitted_at' => Carbon::now()->subDays(15),
            ]);
            
            // Add internal note
            $disputedInvoice->notes .= "\n\nDISPUTE FILED: Service quality issue - investigating SLA breach.";
            $disputedInvoice->save();
        }
    }

    private function createOverdueScenario($company, $products)
    {
        // Some paid history, recent invoices overdue
        $startDate = Carbon::now()->subMonths(7);
        $plan = $products['SVC-PRO'];
        
        $this->createSubscription($company, $plan, $startDate);
        
        // First 4 months paid
        $this->createMonthlyInvoices($company, $plan, $startDate, 4, 'paid', $products);
        
        // Last 3 months overdue/sent
        $overdueStart = $startDate->copy()->addMonths(4);
        $invoices = $this->createMonthlyInvoices($company, $plan, $overdueStart, 3, 'sent', $products);
        
        // Mark invoices as overdue based on due date
        foreach ($invoices as $invoice) {
            if ($invoice->due_date->lt(Carbon::now())) {
                $invoice->update(['status' => 'overdue']);
            }
        }
    }

    private function createPremiumNegotiatedScenario($company, $products)
    {
        // Premium customer with custom bundle
        $startDate = Carbon::now()->subMonths(12);
        $plan = $products['SVC-PREMIUM'];
        
        $this->createSubscription($company, $plan, $startDate);
        $this->createMonthlyInvoices($company, $plan, $startDate, 12, 'paid', $products, [
            ['product' => $products['CLOUD-PRO'], 'qty' => 5],
            ['product' => $products['LIC-USER'], 'qty' => 50],
            ['product' => $products['LIC-SERVER'], 'qty' => 10],
            ['product' => $products['CLOUD-STORAGE'], 'qty' => 10],
        ]);
    }

    private function createRetainerScenario($company, $products)
    {
        // Uses retainer for professional services
        $startDate = Carbon::now()->subMonths(10);
        $plan = $products['SVC-ENT'];
        
        $this->createSubscription($company, $plan, $startDate);
        $this->createMonthlyInvoices($company, $plan, $startDate, 10, 'paid', $products);
        
        // Create retainer for professional services
        Retainer::create([
            'company_id' => $company->id,
            'name' => 'Professional Services Retainer',
            'amount' => 10000.00,
            'balance' => 4500.00,
            'billing_cycle' => 'monthly',
            'start_date' => Carbon::now()->subMonths(6),
            'end_date' => Carbon::now()->addMonths(6),
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
    }

    private function createNewOnboardingScenario($company, $products)
    {
        // Recently onboarded - has setup fee and first invoice
        $startDate = Carbon::now()->subMonths(1);
        $plan = $products['SVC-ENT'];
        
        $this->createSubscription($company, $plan, $startDate);
        
        // Onboarding invoice with setup fees
        $onboardingInvoice = $this->createInvoice($company, $startDate, 'paid', [
            ['product' => $plan, 'qty' => 1, 'description' => 'Enterprise Support Plan - First Month'],
            ['product' => $products['FEE-SETUP'], 'qty' => 1],
            ['product' => $products['FEE-MIGRATION'], 'qty' => 1],
            ['product' => $products['FEE-TRAINING'], 'qty' => 2, 'description' => 'Initial staff training sessions'],
            ['product' => $products['LIC-USER'], 'qty' => 25],
        ]);
        
        // Current month invoice
        $currentInvoice = $this->createInvoice($company, Carbon::now(), 'sent', [
            ['product' => $plan, 'qty' => 1],
            ['product' => $products['LIC-USER'], 'qty' => 25],
        ]);
    }

    private function createCreditNoteScenario($company, $products)
    {
        // Has credit notes from service issues
        $startDate = Carbon::now()->subMonths(9);
        $plan = $products['SVC-BASIC'];
        
        $this->createSubscription($company, $plan, $startDate);
        $invoices = $this->createMonthlyInvoices($company, $plan, $startDate, 9, 'paid', $products);
        
        // Create credit note for month 5
        if (isset($invoices[4])) {
            CreditNote::create([
                'company_id' => $company->id,
                'invoice_id' => $invoices[4]->id,
                'credit_note_number' => 'CN-' . $company->id . '-001',
                'amount' => 49.50, // Half month credit
                'reason' => 'Service disruption - 2 days downtime',
                'status' => 'applied',
                'issued_date' => Carbon::now()->subMonths(4),
                'issued_by' => $this->user->id,
            ]);
        }
    }

    private function createSubscription($company, $product, $startDate)
    {
        return Subscription::updateOrCreate(
            [
                'company_id' => $company->id,
                'name' => 'default',
            ],
            [
                'stripe_id' => 'sub_' . Str::random(14),
                'stripe_status' => 'active',
                'stripe_price' => 'price_' . Str::random(14),
                'quantity' => 1,
                'starts_at' => $startDate,
                'ends_at' => null,
                'next_billing_date' => Carbon::now()->addDays(rand(1, 30)),
                'effective_price' => $product->base_price,
            ]
        );
    }

    private function createMonthlyInvoices($company, $plan, $startDate, $months, $defaultStatus = 'paid', $products = [], $extraItems = [])
    {
        $invoices = [];
        $currentDate = $startDate->copy();
        $invoiceNumber = 1;

        for ($i = 0; $i < $months; $i++) {
            $items = [
                ['product' => $plan, 'qty' => 1, 'description' => $plan->name . ' - ' . $currentDate->format('M Y')],
            ];
            
            // Add extra recurring items
            foreach ($extraItems as $extra) {
                $items[] = $extra;
            }
            
            // Randomly add one-time services (30% chance)
            if (rand(1, 100) <= 30 && isset($products['PS-CONSULT'])) {
                $items[] = ['product' => $products['PS-CONSULT'], 'qty' => rand(2, 8)];
            }
            
            $status = $defaultStatus;
            
            // If default is paid but invoice is recent, might be sent instead
            if ($defaultStatus === 'paid' && $currentDate->copy()->addMonth()->gt(Carbon::now())) {
                $status = rand(0, 1) ? 'sent' : 'paid';
            }
            
            $invoice = $this->createInvoice($company, $currentDate, $status, $items);
            $invoices[] = $invoice;
            
            $currentDate->addMonth();
            $invoiceNumber++;
        }

        return $invoices;
    }

    private function createInvoice($company, $date, $status, $items)
    {
        $subtotal = 0;
        
        // Calculate subtotal
        foreach ($items as $item) {
            $price = $this->getEffectivePrice($company, $item['product']);
            $qty = $item['qty'] ?? 1;
            $subtotal += $price * $qty;
        }
        
        $taxRate = 0.08; // 8% tax
        $taxTotal = $subtotal * $taxRate;
        $total = $subtotal + $taxTotal;
        
        // Create invoice
        $invoiceCount = Invoice::where('company_id', $company->id)->count() + 1;
        $invoice = Invoice::create([
            'company_id' => $company->id,
            'invoice_number' => sprintf('INV-%04d-%04d', $company->id, $invoiceCount),
            'issue_date' => $date->copy(),
            'due_date' => $date->copy()->addDays(30),
            'status' => $status,
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'total' => $total,
            'notes' => 'Thank you for your business. Payment is due within 30 days.',
        ]);
        
        // Create line items
        foreach ($items as $item) {
            $product = $item['product'];
            $qty = $item['qty'] ?? 1;
            $price = $this->getEffectivePrice($company, $product);
            $lineSubtotal = $price * $qty;
            $lineTax = $lineSubtotal * $taxRate;
            
            InvoiceLineItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $product->id,
                'description' => $item['description'] ?? $product->name,
                'quantity' => $qty,
                'unit_price' => $price,
                'subtotal' => $lineSubtotal,
                'tax_amount' => $lineTax,
                'is_fee' => in_array($product->sku, ['FEE-SETUP', 'FEE-MIGRATION', 'FEE-TRAINING']),
            ]);
        }
        
        // Create payment if paid
        if ($status === 'paid') {
            Payment::create([
                'invoice_id' => $invoice->id,
                'company_id' => $company->id,
                'amount' => $total,
                'payment_method' => 'stripe_card',
                'payment_reference' => 'ch_' . Str::random(24),
                'payment_date' => $date->copy()->addDays(rand(1, 10)),
                'notes' => 'Automatic payment - ' . $company->pm_type . ' ending in ' . $company->pm_last_four,
                'created_by' => $this->user->id,
            ]);
        }
        
        return $invoice;
    }

    private function getEffectivePrice($company, $product)
    {
        // Check for price override
        $override = PriceOverride::where('company_id', $company->id)
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->first();
        
        return $override ? $override->custom_price : $product->base_price;
    }

    private function createQuotes($company, $products)
    {
        // Create 1-3 quotes per company in various states
        $quoteCount = rand(1, 3);
        $statuses = ['draft', 'sent', 'accepted', 'expired'];
        
        for ($i = 0; $i < $quoteCount; $i++) {
            $status = $statuses[array_rand($statuses)];
            $createdAt = Carbon::now()->subDays(rand(5, 90));
            $validUntil = $createdAt->copy()->addDays(30);
            
            // If expired, set date in past
            if ($status === 'expired') {
                $validUntil = Carbon::now()->subDays(rand(1, 30));
            }
            
            $items = [];
            $itemCount = rand(2, 5);
            $productKeys = array_keys($products);
            
            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products[$productKeys[array_rand($productKeys)]];
                $items[] = [
                    'product' => $product,
                    'qty' => $product->type === 'recurring' ? 1 : rand(1, 20),
                ];
            }
            
            $this->createQuote($company, $status, $items, $createdAt, $validUntil);
        }
    }

    private function createQuote($company, $status, $items, $createdAt, $validUntil)
    {
        $subtotal = 0;
        
        foreach ($items as $item) {
            $price = $this->getEffectivePrice($company, $item['product']);
            $subtotal += $price * $item['qty'];
        }
        
        $taxTotal = $subtotal * 0.08;
        $total = $subtotal + $taxTotal;
        
        $quoteCount = Quote::where('company_id', $company->id)->count() + 1;
        $quote = Quote::create([
            'company_id' => $company->id,
            'quote_number' => sprintf('Q-%04d-%04d', $company->id, $quoteCount),
            'title' => $this->generateQuoteTitle($items),
            'valid_until' => $validUntil,
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'total' => $total,
            'status' => $status,
            'notes' => 'This quote is valid for 30 days from the date of issue. All prices in USD.',
            'public_token' => Str::random(32),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
        
        if ($status === 'accepted') {
            $quote->update([
                'accepted_at' => $createdAt->copy()->addDays(rand(1, 15)),
                'accepted_by' => $this->user->id,
            ]);
        } elseif ($status === 'sent') {
            $quote->update([
                'sent_at' => $createdAt->copy()->addDays(1),
                'viewed_at' => $createdAt->copy()->addDays(rand(2, 10)),
            ]);
        }
        
        // Create line items
        foreach ($items as $item) {
            $product = $item['product'];
            $price = $this->getEffectivePrice($company, $product);
            $lineTotal = $price * $item['qty'];
            
            QuoteLineItem::create([
                'quote_id' => $quote->id,
                'product_id' => $product->id,
                'description' => $product->name,
                'quantity' => $item['qty'],
                'unit_price' => $price,
                'subtotal' => $lineTotal,
            ]);
        }
        
        return $quote;
    }

    private function generateQuoteTitle($items)
    {
        $titles = [
            'Proposal for Additional Services',
            'Custom Service Package Quote',
            'Infrastructure Upgrade Proposal',
            'Annual Service Agreement',
            'Professional Services Engagement',
            'Cloud Migration Proposal',
            'Managed Services Package',
        ];
        
        return $titles[array_rand($titles)];
    }

    private function printSummary($companies)
    {
        $this->command->info("\n" . str_repeat('=', 60));
        $this->command->info('📊 SEEDING SUMMARY');
        $this->command->info(str_repeat('=', 60));
        
        foreach ($companies as $company) {
            $invoiceCount = Invoice::where('company_id', $company->id)->count();
            $paidCount = Invoice::where('company_id', $company->id)->where('status', 'paid')->count();
            $overdueCount = Invoice::where('company_id', $company->id)->where('status', 'overdue')->count();
            $disputeCount = Dispute::whereHas('invoice', function($q) use ($company) {
                $q->where('company_id', $company->id);
            })->count();
            $quoteCount = Quote::where('company_id', $company->id)->count();
            
            $this->command->info("\n{$company->name} ({$company->scenario}):");
            $this->command->info("  └─ Invoices: {$invoiceCount} total ({$paidCount} paid, {$overdueCount} overdue)");
            if ($disputeCount > 0) $this->command->info("  └─ Disputes: {$disputeCount}");
            $this->command->info("  └─ Quotes: {$quoteCount}");
        }
        
        $this->command->info("\n" . str_repeat('=', 60));
        $this->command->info("\n�� Quick Commands:");
        $this->command->info("   php artisan db:seed --class=Modules\\\\Billing\\\\Database\\\\Seeders\\\\BillingDemoSeeder");
        $this->command->info("   php artisan billing:clean-demo");
        $this->command->info("\n" . str_repeat('=', 60));
    }
}

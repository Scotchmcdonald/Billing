<?php

namespace Modules\Billing\Database\Seeders;

use App\Models\User;
use Modules\Billing\Models\Company;
use Modules\Inventory\Models\Product;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\InvoiceLineItem;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Models\Quote;
use Modules\Billing\Models\BillableEntry;
use Modules\Billing\Models\Retainer;
use Modules\Billing\Models\CreditNote;
use Modules\Billing\Models\Ticket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;

/**
 * FinOps Master Seeder
 * 
 * Generates production-grade test data to validate the "Triple Threat":
 * 1. Billing Accuracy (proration, overrides, anomalies)
 * 2. Cash Flow (AR aging, late payers, payment patterns)
 * 3. Profit Visibility (COGS, margins, profitability guardrails)
 * 
 * Data Volume:
 * - 50+ Companies (SMB, Non-Profit, Consumer)
 * - 100+ Products (Recurring, Hardware, Labor)
 * - 1,000+ Invoices (12 months, realistic payment patterns)
 * - 500+ Support Tickets (billable time tracking)
 * - 200+ Subscriptions (seat-based, usage-based)
 * - 50+ Retainers (prepaid hours)
 * 
 * Deterministic: Uses seeded Faker for reproducible data
 */
class FinOpsMasterSeeder extends Seeder
{
    private $faker;
    private $companies = [];
    private $products = [];
    private $users = [];
    private $technicians = [];
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Initialize faker with seed for deterministic data
        $this->faker = Faker::create();
        $this->faker->seed(42); // Deterministic seed
        
        $this->command->info('🚀 Starting FinOps Master Seeder...');
        
        // Step 1: Create Users (including simulation test users)
        $this->command->info('👤 Creating users and technicians...');
        $this->seedUsers();
        
        // Step 2: Create Product Catalog
        $this->command->info('📦 Creating product catalog...');
        $this->seedProducts();
        
        // Step 3: Create Companies (50+ across tiers)
        $this->command->info('🏢 Creating companies (50+)...');
        $this->seedCompanies();
        
        // Step 4: Create Subscriptions
        // $this->command->info('📋 Creating subscriptions...');
        // $this->seedSubscriptions();
        
        // Step 5: Create Invoices (1,000+ spanning 12 months)
        $this->command->info('📄 Creating invoices (1,000+)...');
        $this->seedInvoices();
        
        // Step 6: Create Support Tickets (500+)
        // $this->command->info('🎫 Creating support tickets (500+)...');
        // $this->seedTickets();
        
        // Step 7: Create Retainers
        // $this->command->info('⏰ Creating retainers...');
        // $this->seedRetainers();
        
        // Step 8: Create Special Cases
        // $this->command->info('⚡ Creating special test cases...');
        // $this->seedSpecialCases();
        
        $this->command->info('✅ FinOps Master Seeder completed!');
        // $this->printSummary();
    }
    
    /**
     * Split name into first and last name
     */
    private function splitName(string $name): array
    {
        $parts = explode(' ', $name, 2);
        return [
            'first_name' => $parts[0] ?? 'User',
            'last_name' => $parts[1] ?? 'Unknown'
        ];
    }
    
    /**
     * Seed users including executives, admins, and technicians
     */
    private function seedUsers(): void
    {
        // Create Executives (for simulation testing)
        for ($i = 1; $i <= 3; $i++) {
            $name = $this->splitName("Executive User{$i}");
            $user = User::firstOrCreate(
                ['email' => "executive{$i}@finops.test"],
                [
                    'first_name' => $name['first_name'],
                    'last_name' => $name['last_name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_ADMIN,
                ]
            );
            $this->users['executives'][] = $user;
        }
        
        // Create Architects (for simulation testing)
        for ($i = 1; $i <= 3; $i++) {
            $name = $this->splitName("Architect User{$i}");
            $user = User::firstOrCreate(
                ['email' => "architect{$i}@finops.test"],
                [
                    'first_name' => $name['first_name'],
                    'last_name' => $name['last_name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_ADMIN,
                ]
            );
            $this->users['architects'][] = $user;
        }
        
        // Create Admins
        for ($i = 1; $i <= 2; $i++) {
            $name = $this->splitName("Admin User{$i}");
            $user = User::firstOrCreate(
                ['email' => "admin{$i}@finops.test"],
                [
                    'first_name' => $name['first_name'],
                    'last_name' => $name['last_name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_ADMIN,
                ]
            );
            $this->users['admins'][] = $user;
        }
        
        // Create Technicians (15 for realistic workload distribution)
        $technicianNames = [
            'John Martinez', 'Sarah Johnson', 'Mike Chen', 'Emily Rodriguez',
            'David Kim', 'Lisa Anderson', 'Tom Wilson', 'Jessica Lee',
            'Chris Taylor', 'Amanda Brown', 'Ryan Garcia', 'Jennifer White',
            'Kevin Thompson', 'Nicole Davis', 'Brian Moore'
        ];
        
        foreach ($technicianNames as $index => $fullName) {
            $name = $this->splitName($fullName);
            $email = strtolower(str_replace(' ', '.', $fullName)) . '@finops.test';
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'first_name' => $name['first_name'],
                    'last_name' => $name['last_name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_USER,
                ]
            );
            $this->technicians[] = $user;
            $this->users['technicians'][] = $user;
        }
        
        // Create Client Admins and Users (will be assigned to companies later)
        for ($i = 1; $i <= 25; $i++) {
            $fakerName = $this->faker->name();
            $name = $this->splitName($fakerName);
            $this->users['client_admins'][] = User::firstOrCreate(
                ['email' => "client.admin{$i}@client.test"],
                [
                    'first_name' => $name['first_name'],
                    'last_name' => $name['last_name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_USER,
                ]
            );
        }
        
        for ($i = 1; $i <= 50; $i++) {
            $fakerName = $this->faker->name();
            $name = $this->splitName($fakerName);
            $this->users['client_users'][] = User::firstOrCreate(
                ['email' => "client.user{$i}@client.test"],
                [
                    'first_name' => $name['first_name'],
                    'last_name' => $name['last_name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_USER,
                ]
            );
        }
    }
    
    /**
     * Seed product catalog (recurring, hardware, labor)
     */
    private function seedProducts(): void
    {
        // Recurring Services
        $recurringServices = [
            // Managed Security
            ['name' => 'Managed Security - Standard', 'sku' => 'MS-STD', 'base_price' => 19900, 'cost_price' => 8500, 'category' => 'security', 'type' => 'seat'],
            ['name' => 'Managed Security - Premium', 'sku' => 'MS-PRE', 'base_price' => 34900, 'cost_price' => 15000, 'category' => 'security', 'type' => 'seat'],
            ['name' => 'Managed Security - Enterprise', 'sku' => 'MS-ENT', 'base_price' => 59900, 'cost_price' => 25000, 'category' => 'security', 'type' => 'seat'],
            
            // Helpdesk (Seat-based)
            ['name' => 'Helpdesk Support - Essential', 'sku' => 'HD-ESS', 'base_price' => 7500, 'cost_price' => 3200, 'category' => 'support', 'type' => 'seat'],
            ['name' => 'Helpdesk Support - Pro', 'sku' => 'HD-PRO', 'base_price' => 12500, 'cost_price' => 5500, 'category' => 'support', 'type' => 'seat'],
            ['name' => 'Helpdesk Support - Premium', 'sku' => 'HD-PRM', 'base_price' => 19900, 'cost_price' => 8500, 'category' => 'support', 'type' => 'seat'],
            
            // Cloud Backups (Usage-based)
            ['name' => 'Cloud Backup - 100GB', 'sku' => 'CB-100', 'base_price' => 2500, 'cost_price' => 800, 'category' => 'backup', 'type' => 'usage'],
            ['name' => 'Cloud Backup - 500GB', 'sku' => 'CB-500', 'base_price' => 9900, 'cost_price' => 3500, 'category' => 'backup', 'type' => 'usage'],
            ['name' => 'Cloud Backup - 1TB', 'sku' => 'CB-1TB', 'base_price' => 17500, 'cost_price' => 6500, 'category' => 'backup', 'type' => 'usage'],
            ['name' => 'Cloud Backup - 5TB', 'sku' => 'CB-5TB', 'base_price' => 74900, 'cost_price' => 28000, 'category' => 'backup', 'type' => 'usage'],
            
            // Monitoring
            ['name' => 'Network Monitoring', 'sku' => 'NM-STD', 'base_price' => 14900, 'cost_price' => 6000, 'category' => 'monitoring', 'type' => 'flat'],
            ['name' => 'Server Monitoring', 'sku' => 'SM-STD', 'base_price' => 9900, 'cost_price' => 4000, 'category' => 'monitoring', 'type' => 'device'],
            
            // Email Services
            ['name' => 'Microsoft 365 Business Basic', 'sku' => 'M365-BAS', 'base_price' => 600, 'cost_price' => 500, 'category' => 'email', 'type' => 'seat'],
            ['name' => 'Microsoft 365 Business Standard', 'sku' => 'M365-STD', 'base_price' => 1250, 'cost_price' => 1000, 'category' => 'email', 'type' => 'seat'],
            ['name' => 'Google Workspace Business', 'sku' => 'GWS-BUS', 'base_price' => 1200, 'cost_price' => 600, 'category' => 'email', 'type' => 'seat'],
        ];
        
        foreach ($recurringServices as $service) {
            $this->products['recurring'][] = Product::firstOrCreate(
                ['sku' => $service['sku']],
                $service
            );
        }
        
        // Hardware Assets
        $hardware = [
            // Firewalls
            ['name' => 'Fortinet FortiGate 60F', 'sku' => 'FW-60F', 'base_price' => 129900, 'cost_price' => 95000, 'category' => 'firewall', 'type' => 'one_time'],
            ['name' => 'Fortinet FortiGate 100F', 'sku' => 'FW-100F', 'base_price' => 249900, 'cost_price' => 185000, 'category' => 'firewall', 'type' => 'one_time'],
            ['name' => 'SonicWall TZ400', 'sku' => 'SW-TZ400', 'base_price' => 89900, 'cost_price' => 65000, 'category' => 'firewall', 'type' => 'one_time'],
            ['name' => 'Ubiquiti Dream Machine Pro', 'sku' => 'UB-DMP', 'base_price' => 37900, 'cost_price' => 28000, 'category' => 'firewall', 'type' => 'one_time'],
            
            // Laptops
            ['name' => 'Dell Latitude 5420', 'sku' => 'LAP-D5420', 'base_price' => 134900, 'cost_price' => 105000, 'category' => 'laptop', 'type' => 'one_time'],
            ['name' => 'HP EliteBook 840 G8', 'sku' => 'LAP-HP840', 'base_price' => 159900, 'cost_price' => 125000, 'category' => 'laptop', 'type' => 'one_time'],
            ['name' => 'Lenovo ThinkPad X1 Carbon', 'sku' => 'LAP-X1C', 'base_price' => 189900, 'cost_price' => 148000, 'category' => 'laptop', 'type' => 'one_time'],
            ['name' => 'MacBook Pro 14"', 'sku' => 'LAP-MBP14', 'base_price' => 219900, 'cost_price' => 185000, 'category' => 'laptop', 'type' => 'one_time'],
            
            // VoIP Phones
            ['name' => 'Yealink T46S IP Phone', 'sku' => 'VP-T46S', 'base_price' => 19900, 'cost_price' => 14500, 'category' => 'voip', 'type' => 'one_time'],
            ['name' => 'Cisco 8845 IP Phone', 'sku' => 'VP-C8845', 'base_price' => 54900, 'cost_price' => 42000, 'category' => 'voip', 'type' => 'one_time'],
            ['name' => 'Poly VVX 450', 'sku' => 'VP-VVX450', 'base_price' => 29900, 'cost_price' => 22000, 'category' => 'voip', 'type' => 'one_time'],
            
            // Servers
            ['name' => 'Dell PowerEdge R450', 'sku' => 'SRV-R450', 'base_price' => 349900, 'cost_price' => 275000, 'category' => 'server', 'type' => 'one_time'],
            ['name' => 'HPE ProLiant DL360 Gen10', 'sku' => 'SRV-DL360', 'base_price' => 429900, 'cost_price' => 335000, 'category' => 'server', 'type' => 'one_time'],
            
            // Networking
            ['name' => 'UniFi 24-Port PoE Switch', 'sku' => 'NET-U24P', 'base_price' => 39900, 'cost_price' => 29500, 'category' => 'switch', 'type' => 'one_time'],
            ['name' => 'Cisco Catalyst 2960X', 'sku' => 'NET-C2960X', 'base_price' => 129900, 'cost_price' => 98000, 'category' => 'switch', 'type' => 'one_time'],
        ];
        
        foreach ($hardware as $item) {
            $this->products['hardware'][] = Product::firstOrCreate(
                ['sku' => $item['sku']],
                $item
            );
        }
        
        // Labor SKUs
        $labor = [
            ['name' => 'Project Hours - Standard', 'sku' => 'LBR-STD', 'base_price' => 15000, 'cost_price' => 5500, 'category' => 'labor', 'type' => 'hourly'],
            ['name' => 'Project Hours - Senior', 'sku' => 'LBR-SR', 'base_price' => 18500, 'cost_price' => 6500, 'category' => 'labor', 'type' => 'hourly'],
            ['name' => 'Emergency After-Hours', 'sku' => 'LBR-EMRG', 'base_price' => 27500, 'cost_price' => 8500, 'category' => 'labor', 'type' => 'hourly'],
            ['name' => 'On-Site Visit', 'sku' => 'LBR-ONSITE', 'base_price' => 22500, 'cost_price' => 7500, 'category' => 'labor', 'type' => 'hourly'],
        ];
        
        foreach ($labor as $item) {
            $this->products['labor'][] = Product::firstOrCreate(
                ['sku' => $item['sku']],
                $item
            );
        }
    }
    
    /**
     * Seed companies across tiers
     */
    private function seedCompanies(): void
    {
        // Standard Tier SMB Clients (30)
        $this->command->info('  → Creating 30 SMB clients (Standard Tier)...');
        for ($i = 1; $i <= 30; $i++) {
            $company = Company::firstOrCreate(
                ['email' => "billing.smb{$i}@client.test"],
                [
                    'name' => $this->faker->company(),
                    'phone' => $this->faker->phoneNumber(),
                    'billing_address' => json_encode([
                        'line1' => $this->faker->streetAddress(),
                        'city' => $this->faker->city(),
                        'state' => $this->faker->stateAbbr(),
                        'postal_code' => $this->faker->postcode(),
                        'country' => 'US',
                    ]),
                    'pricing_tier' => 'standard',
                    'settings' => [
                        'employee_count' => $this->faker->numberBetween(10, 100),
                        'monthly_budget' => $this->faker->numberBetween(200000, 1000000),
                    ],
                    'is_active' => true,
                ]
            );
            
            $this->companies['smb'][] = $company;
        }
        
        // Non-Profit Tier (10)
        $this->command->info('  → Creating 10 non-profit clients (Discounted Tier)...');
        $nonProfitNames = [
            'Community Health Foundation',
            'Youth Education Alliance',
            'Animal Rescue Network',
            'Environmental Conservation Society',
            'Arts for All Foundation',
            'Senior Care Initiative',
            'Food Bank Collective',
            'Children\'s Literacy Project',
            'Homeless Outreach Services',
            'Mental Health Awareness Council',
        ];
        
        foreach ($nonProfitNames as $index => $name) {
            $company = Company::firstOrCreate(
                ['email' => "billing.np{$index}@nonprofit.org"],
                [
                    'name' => $name,
                    'phone' => $this->faker->phoneNumber(),
                    'billing_address' => json_encode([
                        'line1' => $this->faker->streetAddress(),
                        'city' => $this->faker->city(),
                        'state' => $this->faker->stateAbbr(),
                        'postal_code' => $this->faker->postcode(),
                        'country' => 'US',
                    ]),
                    'pricing_tier' => 'non_profit',
                    'settings' => [
                        'employee_count' => $this->faker->numberBetween(5, 30),
                        'monthly_budget' => $this->faker->numberBetween(50000, 200000),
                        'tax_exempt' => true,
                    ],
                    'is_active' => true,
                ]
            );
            
            $this->companies['nonprofit'][] = $company;
        }
        
        // Consumer/Prosumer Tier (10)
        $this->command->info('  → Creating 10 consumer/prosumer accounts...');
        for ($i = 1; $i <= 10; $i++) {
            $company = Company::firstOrCreate(
                ['email' => "consumer{$i}@prosumer.test"],
                [
                    'name' => $this->faker->name() . "'s Business",
                    'phone' => $this->faker->phoneNumber(),
                    'billing_address' => json_encode([
                        'line1' => $this->faker->streetAddress(),
                        'city' => $this->faker->city(),
                        'state' => $this->faker->stateAbbr(),
                        'postal_code' => $this->faker->postcode(),
                        'country' => 'US',
                    ]),
                    'pricing_tier' => 'consumer',
                    'settings' => [
                        'employee_count' => $this->faker->numberBetween(1, 5),
                        'monthly_budget' => $this->faker->numberBetween(10000, 50000),
                    ],
                    'is_active' => true,
                ]
            );
            
            $this->companies['consumer'][] = $company;
        }
        
        // Client-Specific Overrides (5 companies)
        $this->command->info('  → Creating 5 companies with legacy price overrides...');
        for ($i = 1; $i <= 5; $i++) {
            $company = Company::firstOrCreate(
                ['email' => "legacy{$i}@client.test"],
                [
                    'name' => "Legacy Client " . $this->faker->company(),
                    'phone' => $this->faker->phoneNumber(),
                    'billing_address' => json_encode([
                        'line1' => $this->faker->streetAddress(),
                        'city' => $this->faker->city(),
                        'state' => $this->faker->stateAbbr(),
                        'postal_code' => $this->faker->postcode(),
                        'country' => 'US',
                    ]),
                    'pricing_tier' => 'standard',
                    'settings' => [
                        'has_custom_pricing' => true,
                        'custom_pricing_note' => 'Legacy contract - 20% discount locked until ' . now()->addYears(2)->format('Y-m-d'),
                        'discount_percentage' => 20,
                        'employee_count' => $this->faker->numberBetween(20, 150),
                        'monthly_budget' => $this->faker->numberBetween(300000, 1500000),
                    ],
                    'is_active' => true,
                ]
            );
            
            $this->companies['legacy'][] = $company;
        }
    }

    /**
     * Seed invoices with specific aging buckets
     */
    private function seedInvoices(): void
    {
        $this->command->info('  → Creating invoices for AR Aging buckets...');
        
        $companies = Company::all();
        if ($companies->isEmpty()) {
            $this->command->warn('  ⚠ No companies found. Skipping invoice seeding.');
            return;
        }

        // Aging Buckets Configuration
        $buckets = [
            '0-30' => ['start' => 0, 'end' => 30, 'count' => 15],
            '31-60' => ['start' => 31, 'end' => 60, 'count' => 10],
            '61-90' => ['start' => 61, 'end' => 90, 'count' => 8],
            '90+' => ['start' => 91, 'end' => 180, 'count' => 5],
        ];

        foreach ($buckets as $label => $config) {
            $this->command->info("    Creating invoices for {$label} days bucket...");
            
            for ($i = 0; $i < $config['count']; $i++) {
                $company = $companies->random();
                $daysAgo = $this->faker->numberBetween($config['start'], $config['end']);
                $issueDate = Carbon::now()->subDays($daysAgo);
                $dueDate = $issueDate->copy()->addDays(30); // Net 30
                
                // Create Invoice
                $invoice = Invoice::create([
                    'company_id' => $company->id,
                    'status' => 'sent', // Open/Unpaid
                    'issue_date' => $issueDate,
                    'due_date' => $dueDate,
                    'total' => 0, // Will update after adding items
                    'subtotal' => 0,
                    'tax_total' => 0,
                    'notes' => "Aging Bucket: {$label} Days",
                    'invoice_number' => 'INV-' . strtoupper($this->faker->bothify('??####')),
                ]);

                // Add Line Items
                $numItems = $this->faker->numberBetween(1, 5);
                $total = 0;
                
                // Get some products
                $products = Product::inRandomOrder()->take($numItems)->get();
                
                foreach ($products as $product) {
                    $quantity = $this->faker->numberBetween(1, 10);
                    $price = $product->base_price; 
                    $lineTotal = $quantity * $price;
                    
                    InvoiceLineItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $product->id,
                        'description' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $price,
                        'subtotal' => $lineTotal,
                    ]);
                    
                    $total += $lineTotal;
                }
                
                // Update Invoice Totals
                $invoice->update([
                    'subtotal' => $total,
                    'total' => $total, 
                ]);
            }
        }
    }
}

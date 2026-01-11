<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\InvoiceLineItem;
use Modules\Billing\Models\Quote;
use Modules\Billing\Models\QuoteLineItem;
use Modules\Billing\Models\ServiceContract;
use Modules\Billing\Models\ContractPriceHistory;
use Modules\Crm\Models\Client;
use Modules\Inventory\Models\Asset;
use Modules\Inventory\Models\SoftwareProduct;
use Modules\Inventory\Models\ProductPrototype;
use Modules\Inventory\Models\ProcurementRecord;

class MspScenarioSeeder extends Seeder
{
    // Define scenario keys to identify data
    const SCENARIO_TAG = 'msp_demo_v1';
    
    const COMPANY_A_NAME = 'Acme Corp (SB)';
    const COMPANY_B_NAME = 'Charity Org (NP)';
    const COMPANY_C_NAME = 'Complex Industries';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting MSP Scenario Deployment...');
        
        // 1. Purge existing Billing/Inventory data to ensure a clean slate
        $this->purgeModuleData();

        // 2. Global Config: Software Products (Burden)
        // We use firstOrCreate to avoid duplicating global catalog items if they exist
        $softwareProducts = collect([
            ['name' => 'Avast Antivirus', 'monthly_cost' => 2.00],
            ['name' => '1Password', 'monthly_cost' => 3.00],
            ['name' => 'Office 365 E3', 'monthly_cost' => 20.00],
            ['name' => 'RMM Agent', 'monthly_cost' => 1.50],
            ['name' => 'Backup Agent', 'monthly_cost' => 5.00],
        ])->map(function ($item) {
            return SoftwareProduct::firstOrCreate(
                ['name' => $item['name']],
                ['monthly_cost' => $item['monthly_cost']]
            );
        });

        // 3. Product Prototypes
        $laptopProto = ProductPrototype::factory()->laptop()->create();
        $serverProto = ProductPrototype::factory()->server()->create();
        $workstationProto = ProductPrototype::factory()->workstation()->create();

        // 4. Procurement History
        ProcurementRecord::factory()->count(10)->create([
            'product_prototype_id' => $laptopProto->id,
            'cost_price' => 800.00,
        ]);

        // ==========================================
        // Company A: Small Business (Standard)
        // ==========================================
        $clientA = Client::firstOrCreate(
            ['name' => self::COMPANY_A_NAME],
            ['tier' => 'Small Business']
        );

        $companyA = Company::factory()->smallBusiness()->create([
            'name' => self::COMPANY_A_NAME,
            'scenario' => self::SCENARIO_TAG,
            'client_id' => $clientA->id,
        ]);
        
        // Assets (Windows Laptops)
        $assetsA = Asset::factory()->count(20)->windows()->create([
            'client_id' => $clientA->id, 
        ]);

        // Attach Software Burden
        foreach ($assetsA as $asset) {
            $asset->softwareProducts()->attach($softwareProducts->random(2));
        }

        // Pending Quote
        $quoteA = Quote::factory()->create([
            'company_id' => $companyA->id,
            'status' => 'draft',
        ]);
        QuoteLineItem::factory()->create([
            'quote_id' => $quoteA->id,
            'description' => 'New Laptops Batch',
            'quantity' => 5,
            'unit_price' => 1200.00,
            'standard_price' => 1200.00,
            'is_recurring' => false,
        ]);

        // Historical Data (Time Traveler)
        $this->generateHistory($companyA, 2000, 100);

        // ==========================================
        // Company B: Non-Profit (Tax Credit)
        // ==========================================
        $clientB = Client::firstOrCreate(
            ['name' => self::COMPANY_B_NAME],
            ['tier' => 'Non-Profit']
        );

        $companyB = Company::factory()->nonProfit()->create([
            'name' => self::COMPANY_B_NAME,
            'scenario' => self::SCENARIO_TAG,
            'client_id' => $clientB->id,
        ]);
        
        // Assets (Linux Servers)
        $assetsB = Asset::factory()->count(2)->linux()->create([
            'client_id' => $clientB->id,
        ]);
        
        // Current Invoice with Tax Credit
        $invoiceB = Invoice::factory()->create([
            'company_id' => $companyB->id,
            'status' => 'sent', // Changed from 'approved' to 'sent'
            'total' => 800.00,
        ]);
        
        InvoiceLineItem::factory()->create([
            'invoice_id' => $invoiceB->id,
            'description' => 'Managed Service Fee (Non-Profit Rate)',
            'quantity' => 10,
            'unit_price' => 80.00, // Discounted
            'standard_unit_price' => 100.00, // FMV
            'subtotal' => 800.00,
        ]);

        // Historical Data (Time Traveler - Seasonal)
        $this->generateHistory($companyB, 5000, 500, true);

        // ==========================================
        // Company C: Complex (Disputes & Anomalies)
        // ==========================================
        $clientC = Client::firstOrCreate(
            ['name' => self::COMPANY_C_NAME],
            ['tier' => 'Small Business']
        );

        $companyC = Company::factory()->create([
            'name' => self::COMPANY_C_NAME,
            'scenario' => self::SCENARIO_TAG,
            'client_id' => $clientC->id,
        ]);
        
        // Invoice with Disputes & High Anomaly
        $invoiceC = Invoice::factory()->create([
            'company_id' => $companyC->id,
            'status' => 'draft',
            'anomaly_score' => 85, // High Risk
            'total' => 1500.00,
        ]);
        
        InvoiceLineItem::factory()->disputed()->create([
            'invoice_id' => $invoiceC->id,
            'description' => 'Unrecognized Charge',
            'unit_price' => 500.00,
            'standard_unit_price' => 500.00,
            'subtotal' => 500.00,
        ]);
        
        InvoiceLineItem::factory()->create([
            'invoice_id' => $invoiceC->id,
            'description' => 'Standard Service',
            'unit_price' => 1000.00,
            'standard_unit_price' => 1000.00,
            'subtotal' => 1000.00,
        ]);

        // ==========================================
        // Financial Accuracy: Contract Price History
        // ==========================================
        $contract = ServiceContract::factory()->create([
            'client_id' => $clientA->id,
            'name' => 'Legacy Support Contract',
            'standard_rate' => 150.00,
        ]);

        // Old Price (6 months ago)
        ContractPriceHistory::factory()->create([
            'contract_id' => $contract->id,
            'unit_price' => 120.00,
            'started_at' => now()->subMonths(12),
            'ended_at' => now()->subMonths(3),
        ]);

        // New Price (Current)
        ContractPriceHistory::factory()->create([
            'contract_id' => $contract->id,
            'unit_price' => 150.00,
            'started_at' => now()->subMonths(3),
            'ended_at' => null,
        ]);

        // MSP Admin User (if not exists)
        if (!User::where('email', 'admin@msp.com')->exists()) {
            User::factory()->mspAdmin()->create([
                'email' => 'admin@msp.com',
                'password' => bcrypt('password'),
            ]);
        }

        $this->command->info('MSP Scenario Deployed Successfully.');
    }

    private function purgeModuleData()
    {
        $this->command->warn('Purging all Billing and Inventory data...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Billing Tables
        DB::table('invoice_line_items')->truncate();
        DB::table('invoices')->truncate();
        DB::table('quote_line_items')->truncate();
        DB::table('quotes')->truncate();
        DB::table('contract_price_histories')->truncate();
        DB::table('service_contracts')->truncate();
        DB::table('companies')->truncate();
        
        // Inventory Tables
        DB::table('assets')->truncate();
        DB::table('procurement_records')->truncate();
        DB::table('product_prototypes')->truncate();
        DB::table('software_products')->truncate();
        
        // Note: We are NOT truncating 'clients' (CRM) or 'products' (Shared) 
        // to avoid affecting other modules, although this might leave some orphaned data 
        // if not carefully managed.

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('Purge complete.');
    }

    /**
     * Undo the seed data.
     */
    public function reverse(): void
    {
        $this->command->info('Cleaning up MSP Scenario data...');

        // Delete Companies (Cascading should handle most, but we'll be explicit where needed)
        $companies = Company::whereIn('name', [
            self::COMPANY_A_NAME,
            self::COMPANY_B_NAME,
            self::COMPANY_C_NAME
        ])->get();

        foreach ($companies as $company) {
            // Manually delete related if cascade isn't perfect
            $company->invoices()->delete();
            $company->quotes()->delete();
            // $company->assets()->delete(); // If relation exists
            $company->delete();
        }

        // Delete Clients
        Client::whereIn('name', [
            self::COMPANY_A_NAME,
            self::COMPANY_B_NAME,
            self::COMPANY_C_NAME
        ])->delete();

        // Clean up prototypes created by factory (optional, might want to keep)
        // For now, we leave prototypes as they might be used by others, 
        // or we could tag them too.
        
        $this->command->info('Cleanup Complete.');
    }

    private function generateHistory($company, $baseAmount, $variance, $seasonal = false)
    {
        $months = range(1, 12);
        foreach ($months as $month) {
            // Go back $month months
            $date = now()->subMonths($month)->startOfMonth();
            
            // Skip if seasonal and not a quarter
            if ($seasonal && $month % 3 !== 0) {
                continue;
            }

            // Simulate growth: older invoices are smaller
            $growthFactor = (12 - $month) * ($variance / 2); 
            $amount = $baseAmount + $growthFactor + rand(-$variance, $variance);
            
            $invoice = Invoice::factory()->create([
                'company_id' => $company->id,
                'issue_date' => $date,
                'due_date' => $date->copy()->addDays(30),
                'created_at' => $date,
                'updated_at' => $date,
                'total' => $amount,
                'subtotal' => $amount,
                'status' => 'paid', // Changed from 'approved' to 'paid' to match enum
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
}

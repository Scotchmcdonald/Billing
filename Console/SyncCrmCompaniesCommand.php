<?php

declare(strict_types=1);

namespace Modules\Billing\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\BillingLog;
use Exception;

class SyncCrmCompaniesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:sync-crm-companies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync unique company names from CRM customers to the Billing Companies table.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting CRM Company Sync...');

        try {
            // Get unique company names from customers table where company is not null/empty
            $crmCompanies = DB::table('customers')
                ->whereNotNull('company')
                ->where('company', '!=', '')
                ->distinct()
                ->pluck('company');

            $count = 0;

            foreach ($crmCompanies as $companyName) {
                $companyName = trim($companyName);
                
                if (empty($companyName)) {
                    continue;
                }

                // Check if company already exists in billing companies
                $exists = Company::where('name', $companyName)->exists();

                if (!$exists) {
                    try {
                        $company = Company::create([
                            'name' => $companyName,
                            // We don't have an email for the company itself easily, 
                            // maybe we could pick the first customer's email, but better leave null for now.
                        ]);
                        
                        BillingLog::create([
                            'company_id' => $company->id,
                            'action' => 'sync.created',
                            'description' => "Company synced from CRM: {$companyName}",
                            'ip_address' => '127.0.0.1', // Console
                        ]);

                        $this->line("Created company: {$companyName}");
                        $count++;
                    } catch (Exception $e) {
                        $this->error("Failed to create company {$companyName}: " . $e->getMessage());
                        // Continue to next company
                    }
                }
            }

            $this->info("Sync complete. Created {$count} new companies.");

            // Cleanup Report
            $this->info('Generating Cleanup Report...');
            
            $orphanedCustomers = DB::table('customers')
                ->whereNull('company')
                ->orWhere('company', '')
                ->get(['id', 'first_name', 'last_name', 'email']);

            if ($orphanedCustomers->isNotEmpty()) {
                $this->warn("Found {$orphanedCustomers->count()} customers without a Company Name:");
                $this->table(
                    ['ID', 'Name', 'Email'],
                    $orphanedCustomers->map(fn($c) => [
                        $c->id, 
                        "{$c->first_name} {$c->last_name}", 
                        $c->email
                    ])
                );
                
                // Log to BillingLog for Finance Team
                BillingLog::create([
                    'company_id' => null, // System level
                    'action' => 'crm_sync.cleanup_report',
                    'description' => "Found {$orphanedCustomers->count()} customers without a Company Name.",
                    'payload' => ['customer_ids' => $orphanedCustomers->pluck('id')->toArray()],
                ]);
            } else {
                $this->info('No orphaned customers found.');
            }

        } catch (Exception $e) {
            $this->error("Critical error during sync: " . $e->getMessage());
            BillingLog::create([
                'action' => 'sync.failed',
                'description' => "Critical sync error: " . $e->getMessage(),
                'ip_address' => '127.0.0.1',
            ]);
            return;
        }
    }
}

<?php

namespace Modules\Billing\Console;

use Illuminate\Console\Command;
use Modules\Billing\Services\InvoiceGenerationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:generate-invoices {--date= : The billing date (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly invoices for all active companies.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(InvoiceGenerationService $invoiceService)
    {
        $dateInput = $this->option('date');
        $billingDate = $dateInput ? Carbon::parse($dateInput) : Carbon::now();

        $this->info("Starting invoice generation for billing date: " . $billingDate->toDateString());
        Log::info("Starting invoice generation for billing date: " . $billingDate->toDateString());

        try {
            $invoices = $invoiceService->generateMonthlyInvoices($billingDate);
            
            $count = $invoices->count();
            $this->info("Successfully generated {$count} invoices.");
            Log::info("Successfully generated {$count} invoices.");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Error generating invoices: " . $e->getMessage());
            Log::error("Error generating invoices: " . $e->getMessage());
            return 1;
        }
    }
}

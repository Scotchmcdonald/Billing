<?php

namespace Modules\Billing\Console\Commands;

use Illuminate\Console\Command;
use Modules\Billing\Models\Company;
use Modules\Billing\Services\BillingReconciliationService;
use Illuminate\Support\Carbon;

class RunBillingReconciliation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:reconcile {--month= : The month to reconcile (YYYY-MM)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate draft invoices for all active companies, aggregating subscriptions and usage.';

    /**
     * Execute the console command.
     */
    public function handle(BillingReconciliationService $service)
    {
        $this->info("Starting Billing Reconciliation...");

        $month = $this->option('month');
        $periodEnd = $month ? Carbon::parse($month)->endOfMonth() : now()->subMonth()->endOfMonth();
        $periodStart = $periodEnd->copy()->startOfMonth();

        $this->info("Period: {$periodStart->toDateString()} to {$periodEnd->toDateString()}");

        $companies = Company::where('is_active', true)->get();

        foreach ($companies as $company) {
            $this->info("Processing Company: {$company->name}...");
            try {
                $invoice = $service->generateDraftInvoice($company, $periodStart, $periodEnd);
                $this->info("  -> Created Draft Invoice #{$invoice->invoice_number} Total: \${$invoice->total}");
            } catch (\Exception $e) {
                $this->error("  -> Failed: " . $e->getMessage());
            }
        }

        $this->info("Reconciliation Complete.");
    }
}

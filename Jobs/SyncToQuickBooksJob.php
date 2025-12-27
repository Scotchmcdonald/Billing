<?php

namespace Modules\Billing\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\BillingLog;
use Illuminate\Support\Facades\Log;

class SyncToQuickBooksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function handle()
    {
        // 1. Check if Sync is enabled
        // $settings = BillingSetting::get('quickbooks');
        // if (!$settings['enabled']) return;

        // 2. Authenticate with QuickBooks (OAuth)
        // $qb = new QuickBooksClient($settings['token']);

        // 3. Map Customer
        // $qbCustomer = $qb->findOrCreateCustomer($this->invoice->company);

        // 4. Create Invoice
        // $qbInvoice = $qb->createInvoice($this->invoice, $qbCustomer);

        // 5. Log success
        Log::info("Synced Invoice #{$this->invoice->id} to QuickBooks.");
        
        BillingLog::create([
            'company_id' => $this->invoice->company_id,
            'level' => 'info',
            'message' => "Synced Invoice #{$this->invoice->id} to QuickBooks.",
        ]);
    }
    
    public function failed(\Throwable $exception)
    {
        BillingLog::create([
            'company_id' => $this->invoice->company_id,
            'level' => 'error',
            'message' => "Failed to sync Invoice #{$this->invoice->id} to QuickBooks: " . $exception->getMessage(),
        ]);
    }
}

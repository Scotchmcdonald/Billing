<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Invoice;
use Modules\Billing\Services\CreditLedgerService;
use Modules\Billing\Services\HelcimService;
use Modules\Billing\Models\Payment;
use Illuminate\Support\Facades\Log;

class ReconciliationService
{
    protected $creditLedger;
    protected $helcim;

    public function __construct(CreditLedgerService $creditLedger, HelcimService $helcim)
    {
        $this->creditLedger = $creditLedger;
        $this->helcim = $helcim;
    }

    /**
     * Perform Pre-Bill Reconciliation, Apply Credits, and Dispatch Payment.
     */
    public function reconcileAndBill(Invoice $invoice): void
    {
        if ($invoice->status === 'paid' || $invoice->status === 'cancelled') {
            return;
        }

        Log::info("Starting reconciliation for Invoice #{$invoice->id}");

        // 1. Apply Credits (if any)
        $this->applyCredits($invoice);

        // 2. Check remaining balance
        $invoice->refresh(); // update totals / paid_amount
        $amountDue = $invoice->total - $invoice->paid_amount;

        // If paid in full by credits
        if ($amountDue <= 0.01) { // Floating point safety
            $invoice->update([
                'status' => 'paid',
                'paid_amount' => $invoice->total // Ensure it matches exactly
            ]);
            Log::info("Invoice #{$invoice->id} paid in full via credits.");
            return;
        }

        // 3. Dispatch to Helcim
        $this->processPayment($invoice, $amountDue);
    }

    protected function applyCredits(Invoice $invoice): void
    {
        $company = $invoice->company;
        $balanceCents = $this->creditLedger->getBalance($company);

        if ($balanceCents <= 0) {
            return;
        }

        $amountDueDollars = $invoice->total - $invoice->paid_amount;
        $amountDueCents = (int) round($amountDueDollars * 100);

        if ($amountDueCents <= 0) {
            return;
        }

        // Deduct min(balance, due)
        $toDeductCents = min($balanceCents, $amountDueCents);
        $toDeductDollars = $toDeductCents / 100;

        // Attempt burn
        if ($this->creditLedger->burnCredits($company, $toDeductCents, "Applied to Invoice #{$invoice->invoice_number}", 'invoice', $invoice->id)) {
            
            Payment::create([
                'company_id' => $company->id,
                'invoice_id' => $invoice->id,
                'amount' => $toDeductDollars,
                'payment_date' => now(),
                'payment_method' => 'credit_balance',
                'notes' => 'Applied from Pre-Paid Balance',
                'created_by' => 1, // System
            ]);

            $invoice->increment('paid_amount', $toDeductDollars);
            Log::info("Applied \${$toDeductDollars} credits to Invoice #{$invoice->id}");
        }
    }

    protected function processPayment(Invoice $invoice, float $amount): void
    {
        $company = $invoice->company;

        // We need a customer code or card token
        $customerCode = $company->helcim_id;
        $cardToken = $company->helcim_card_token;

        if (!$customerCode && !$cardToken) {
            Log::warning("No Helcim payment method for Company #{$company->id}. Invoice #{$invoice->id} remains 'sent'.");
            $invoice->update(['status' => 'sent']); // Ready for manual payment
            return;
        }

        Log::info("Attempting Helcim charge of \${$amount} for Invoice #{$invoice->id}");

        $response = $this->helcim->purchase(
            amount: $amount,
            ipAddress: '127.0.0.1',
            customerCode: $customerCode,
            cardToken: $cardToken,
            invoiceNumber: $invoice->invoice_number ?? "DNS-{$invoice->id}" // Draft invoices might not have number yet? InvoiceGen assigns DRAFT-ID-Date.
        );

        if ($response && $response->isSuccess()) {
             Payment::create([
                'company_id' => $company->id,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'payment_date' => now(),
                'payment_method' => 'credit_card',
                'gateway_payment_id' => $response->transactionId,
                'payment_reference' => $response->approvalCode,
                'notes' => 'Auto-charged via Helcim',
                'created_by' => 1,
            ]);

            $invoice->update([
                'status' => 'paid',
                'paid_amount' => $invoice->paid_amount + $amount,
            ]);
            
            Log::info("Helcim Payment Success: {$response->transactionId}");
        } else {
            $invoice->update(['status' => 'payment_failed']);
            Log::error("Helcim Payment Failed for Invoice #{$invoice->id}");
        }
    }
}

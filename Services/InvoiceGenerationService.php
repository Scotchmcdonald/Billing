<?php

namespace Modules\Billing\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\InvoiceLineItem;
use Modules\Billing\Models\BillableEntry;

class InvoiceGenerationService
{
    protected $reconciliationService;

    public function __construct(ReconciliationService $reconciliationService)
    {
        $this->reconciliationService = $reconciliationService;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function generateMonthlyInvoices(Carbon $billingDate): Collection
    {
        $companies = Company::where('is_active', true)->get();
        $invoices = collect();

        foreach ($companies as $company) {
            // Check if there's anything to bill
            $hasSubscriptions = $company->subscriptions()->where('is_active', true)->exists();
            $hasUnbilledEntries = $company->billableEntries()->unbilled()->exists();

            if ($hasSubscriptions || $hasUnbilledEntries) {
                $invoice = $this->generateInvoiceForCompany($company, $billingDate);
                $invoices->push($invoice);

                // Auto-Reconcile and Bill if ready
                if ($invoice->status === 'pending_send') {
                    // We finalize the draft number first to ensure a proper INV number
                    $invoice = $this->finalizeDraftInvoice($invoice);
                    $this->reconciliationService->reconcileAndBill($invoice);
                }
            }
        }

        return $invoices;
    }

    public function generateInvoiceForCompany(Company $company, Carbon $billingDate): Invoice
    {
        return DB::transaction(function () use ($company, $billingDate) {
            $invoice = Invoice::create([
                'company_id' => $company->id,
                'invoice_number' => 'DRAFT-' . $company->id . '-' . $billingDate->format('YmdHis'),
                'issue_date' => $billingDate,
                'due_date' => $billingDate->copy()->addDays(30), // Default 30 days
                'status' => 'draft',
                'subtotal' => 0,
                'tax_total' => 0,
                'total' => 0,
            ]);

            $subtotal = 0;

            // 1. Process Subscriptions
            $subscriptions = $company->subscriptions()
                ->where('is_active', true)
                ->where(function ($query) use ($billingDate) {
                    $query->whereNull('next_billing_date')
                          ->orWhere('next_billing_date', '<=', $billingDate);
                })
                ->get();

            foreach ($subscriptions as $subscription) {
                $lineTotal = $subscription->effective_price * $subscription->quantity;
                
                InvoiceLineItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $subscription->product->name . ' (' . ucfirst($subscription->billing_frequency) . ')',
                    'quantity' => $subscription->quantity,
                    'unit_price' => $subscription->effective_price,
                    'subtotal' => $lineTotal,
                    'type' => 'subscription',
                ]);

                $subtotal += $lineTotal;

                // Update next billing date
                $nextDate = $billingDate->copy();
                if ($subscription->billing_frequency === 'monthly') {
                    $nextDate->addMonth();
                } elseif ($subscription->billing_frequency === 'quarterly') {
                    $nextDate->addQuarter();
                } elseif ($subscription->billing_frequency === 'annual') {
                    $nextDate->addYear();
                }
                $subscription->update(['next_billing_date' => $nextDate]);
            }

            // 2. Process Billable Entries
            $entries = $company->billableEntries()->unbilled()->get();
            foreach ($entries as $entry) {
                $lineItem = InvoiceLineItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $entry->description,
                    'quantity' => $entry->quantity,
                    'unit_price' => $entry->rate,
                    'subtotal' => $entry->subtotal,
                    'type' => $entry->type,
                ]);

                $entry->update(['invoice_line_item_id' => $lineItem->id]);
                $subtotal += $entry->subtotal;
            }

            // 3. Update Invoice Totals
            // Simple tax calculation placeholder (e.g., 0% for now or configurable)
            $taxRate = 0.0; 
            $taxTotal = $subtotal * $taxRate;
            $total = $subtotal + $taxTotal;

            // Check Support Limits (Phase 3: Post-Paid Aggregation)
            $status = 'pending_send'; // Ready to be finalized
            
            $supportTotal = $entries->whereNotNull('ticket_tier')->sum('subtotal');
            if ($company->monthly_support_limit > 0 && $supportTotal > $company->monthly_support_limit) {
                $status = 'pending_approval';
            }

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'total' => $total,
                'status' => $status,
            ]);
            
            // Auto-finalize if integration is tight, otherwise leave as pending_send for job runner
            if ($status === 'pending_send') {
               // Optional: Trigger direct Helcim push here or return for batch processing
            }

            return $invoice;
        });
    }

    public function finalizeDraftInvoice(Invoice $invoice): Invoice
    {
        if ($invoice->status !== 'draft' && $invoice->status !== 'pending_review') {
            return $invoice;
        }

        return DB::transaction(function () use ($invoice) {
            // Generate real invoice number
            $invoiceNumber = 'INV-' . now()->format('Y') . '-' . str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT);
            
            $invoice->update([
                'invoice_number' => $invoiceNumber,
                'status' => 'sent',
            ]);

            // TODO: Post to Helcim if configured
            // TODO: Send email notification

            return $invoice;
        });
    }
}

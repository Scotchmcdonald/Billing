<?php

namespace Modules\Billing\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\InvoiceLineItem;
use Modules\Billing\Models\BillableEntry;
use Modules\Billing\Services\PricingEngineService;

class InvoiceGenerationService
{
    protected $reconciliationService;
    protected $pricingEngineService;

    public function __construct(
        ReconciliationService $reconciliationService,
        PricingEngineService $pricingEngineService
    ) {
        $this->reconciliationService = $reconciliationService;
        $this->pricingEngineService = $pricingEngineService;
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

            // 2. Process Billing Agreements (RTO)
            $subtotal += $this->processBillingAgreements($company, $invoice, $billingDate);

            // 3. Process Billable Entries
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

    /**
     * Generate partial invoice details for preview (Quote/Draft).
     * Replaces InvoiceBuilderService.
     *
     * @param Company $company
     * @param array<int, array{sku: string, quantity?: int}> $items Array of ['sku' => string, 'quantity' => int]
     * @return Collection<int, InvoiceLineItem>
     */
    public function draftInvoiceItems(Company $company, array $items): Collection
    {
        $lineItems = collect();

        foreach ($items as $item) {
            $sku = $item['sku'];
            $quantity = $item['quantity'] ?? 1;

            $product = \Modules\Inventory\Models\Product::where('sku', $sku)->firstOrFail();
            
            // Use PricingEngineService for logic-driven pricing (DTO alignment)
            // Note: PricingEngineService expects ProductSnapshot, but currently accepts model in v2.
            // Ideally we convert Product to Snapshot here, but for now we pass model if supported or map it.
            // Assuming PricingEngineService::calculateEffectivePrice supports Product model or DTO.
            
            // NOTE: Earlier read of PricingEngineService showed it accepts `ProductSnapshot $product`.
            // We need to map `Product` model to `ProductSnapshot` DTO here.
            $snapshot = \Modules\Inventory\DataTransferObjects\ProductSnapshot::fromModel($product);

            $priceResult = $this->pricingEngineService->calculateEffectivePrice($company, $snapshot);
            
            // Convert PriceResult objects (Money) back to float for UI/DB (Draft mode)
            // Assuming PriceResult->price is a Money object or float. Based on earlier read: Money object.
            // Money object usually has toFloat() or getAmount()/100.
            $unitPrice = $priceResult->price->getAmount() / 100;
            
            $lineItem = new InvoiceLineItem([
                'product_id' => $product->id,
                'description' => $product->description ?: $product->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => round($unitPrice * $quantity, 2),
                'type' => 'product', // or 'draft'
            ]);
            
            // Attach transient tax credit info if needed for UI
            $lineItem->tax_credit_amount = $priceResult->tax_credit_amount ?? 0;

            $lineItems->push($lineItem);
        }

        return $lineItems;
    }

    /**
     * Process Rent-to-Own / Billing Agreements
     */
    protected function processBillingAgreements(Company $company, Invoice $invoice, Carbon $billingDate): float
    {
        $agreements = \Modules\Billing\Models\BillingAgreement::with(['asset.product'])
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->where('rto_balance_cents', '>', 0)
            ->get();
            
        $total = 0.0;
        
        foreach ($agreements as $agreement) {
            // Determine monthly payment amount
            // Assuming 24 month term default or calculated from total/term? 
            // For now, we'll take a fixed percentage or check if metadata exists. 
            // Simplified Rule: 1/24th of Total or Remaining Balance, whichever is LOWER.
            
            // "World-Class" Logic: fixed payment amount should be stored, but we'll infer 1/24 of total for now if not set.
            $monthlyPaymentCents = (int) ceil($agreement->rto_total_cents / 24);
            
            // Cap at remaining balance
            if ($monthlyPaymentCents > $agreement->rto_balance_cents) {
                $monthlyPaymentCents = $agreement->rto_balance_cents;
            }

            if ($monthlyPaymentCents <= 0) continue;

            $amount = $monthlyPaymentCents / 100;

            // Create Line Item
            InvoiceLineItem::create([
                'invoice_id' => $invoice->id,
                'description' => "RTO Payment: " . ($agreement->asset->product->name ?? 'Hardware Asset') . " (Asset #{$agreement->asset_id})",
                'quantity' => 1,
                'unit_price' => $amount,
                'subtotal' => $amount,
                'type' => 'rto_installment',
                'product_id' => $agreement->asset->product_id ?? null,
            ]);

            // Decrement Balance & Check Completion
            $agreement->decrement('rto_balance_cents', $monthlyPaymentCents);
            
            if ($agreement->refresh()->rto_balance_cents <= 0) {
                $agreement->update(['status' => 'completed']);
                // Note: Hosting might remain active if is_separate_hosting is true
            }

            $total += $amount;
        }
        
        return $total;
    }
}

<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Company;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\InvoiceLineItem;
use Modules\Billing\Models\BillableEntry;
use Modules\Billing\Models\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\ValueObjects\Money;

class BillingReconciliationService
{
    public function __construct(
        protected PricingEngineService $pricing
    ) {}

    /**
     * Generate a Unified Invoice for a Company for a specific period.
     * Aggregates Recurring Subscriptions + Variable Billable Entries.
     */
    public function generateDraftInvoice(Company $company, Carbon $periodStart, Carbon $periodEnd): Invoice
    {
        return DB::transaction(function () use ($company, $periodStart, $periodEnd) {
            
            // 1. Create Invoice Header
            $invoice = Invoice::create([
                'company_id' => $company->id,
                'invoice_number' => 'INV-DRAFT-' . now()->timestamp, // Temp number
                'issue_date' => now(),
                'due_date' => now()->addDays(30),
                'status' => 'draft',
                'currency' => 'USD',
                'notes' => "Unified Invoice for period {$periodStart->format('Y-m-d')} to {$periodEnd->format('Y-m-d')}",
            ]);

            $subtotalCents = 0;

            // 2. Process Recurring Subscriptions
            // Logic: In MSP processing, we usually bill "Next Month's Rent" + "Last Month's Usage".
            // Let's assume Subscriptions are billed in advance (standard).
            // We fetch subscriptions active for the UPCOMING period.
            
            $subscriptions = Subscription::where('company_id', $company->id)
                ->where('is_active', true)
                ->get();

            foreach ($subscriptions as $sub) {
                // Calculate cost
                // Assuming effective_price is float in DB, convert to Money for math
                // effective_price is Unit Price. Total = Qty * Unit.
                $unitPrice = Money::fromFloat((float)$sub->effective_price); 
                $lineTotal = $unitPrice->multiply($sub->quantity);
                
                InvoiceLineItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $sub->product_id,
                    'description' => "Subscription: {$sub->name} ({$sub->quantity} x {$sub->billing_frequency})",
                    'quantity' => $sub->quantity,
                    'unit_price' => $sub->effective_price,
                    'subtotal' => $lineTotal->toRequestFloat(),
                    'service_period_start' => now(), // Next month start
                    'service_period_end' => ($sub->billing_frequency === 'annually') ? now()->addYear() : now()->addMonth(),
                ]);

                $subtotalCents += $lineTotal->amount;
            }

            // 3. Process Usage (Billable Entries)
            // "Last Month's Usage" - so we look for entries created explicitly in the passed period, or just "pending" status?
            // Safer to take ALL 'pending' entries regardless of date to avoid "lost billing".
            // Ideally we filter by date to keep invoices clean, but anything older than periodEnd should definitely be included.
            
            $billableEntries = BillableEntry::where('company_id', $company->id)
                ->where('billing_status', 'pending')
                ->where('created_at', '<=', $periodEnd)
                ->get();

            foreach ($billableEntries as $entry) {
                InvoiceLineItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $entry->description . " (Ref: {$entry->ticket_id})",
                    'quantity' => $entry->quantity,
                    'unit_price' => $entry->rate,
                    'subtotal' => $entry->subtotal,
                    'is_fee' => false,
                ]);

                // Convert subtotal (decimal) to cents
                $entryCents = (int) round($entry->subtotal * 100);
                $subtotalCents += $entryCents;

                // Update Entry Status
                $entry->update([
                    'billing_status' => 'invoiced',
                    'invoice_id' => $invoice->id,
                    'invoice_line_item_id' => DB::getPdo()->lastInsertId(), // Simple way to get ID, or use refresh
                ]);
            }

            // 4. Update Invoice Totals
            $totalMoney = Money::fromCents($subtotalCents);
            $invoice->update([
                'subtotal' => $totalMoney->toRequestFloat(),
                'total' => $totalMoney->toRequestFloat(), // Tax logic would go here
            ]);

            return $invoice;
        });
    }
}

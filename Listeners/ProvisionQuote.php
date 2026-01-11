<?php

namespace Modules\Billing\Listeners;

use Modules\Billing\Events\QuoteApproved;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Contracts\InventoryTransactionServiceInterface;
use Illuminate\Support\Facades\Log;

class ProvisionQuote
{
    protected InventoryTransactionServiceInterface $inventory;

    public function __construct(InventoryTransactionServiceInterface $inventory)
    {
        $this->inventory = $inventory;
    }

    public function handle(QuoteApproved $event)
    {
        $quote = $event->quote;
        
        // 1. Create Company (if new prospect)
        if (!$quote->company_id) {
            $company = Company::create([
                'name' => $quote->prospect_name,
                'email' => $quote->prospect_email,
                'is_active' => true,
            ]);
            $quote->company_id = $company->id;
            $quote->save();
        } else {
            $company = $quote->company;
        }

        // 2. Create Subscriptions & Allocate Stock
        foreach ($quote->lineItems as $item) {
            if ($item->product_id) {
                
                // Attempt to decrement stock first
                try {
                    // For now, we assume direct decrement since reservations weren't made in the Quote Flow 1.0
                    // In Quote Flow 2.0, we would look for a reservation_id on the quote item
                    $this->inventory->decrementStock(
                        (string)$item->product_id, // Using ID as SKU/Identifier for now
                        $item->quantity,
                        'Quote Provisioning',
                        (string)$quote->id
                    );
                } catch (\Exception $e) {
                    Log::error("Failed to provision stock for Quote #{$quote->id}, Item {$item->product_id}: " . $e->getMessage());
                    // Decide: Stop? Continue? For now, we Log and potentially Continue but mark an error?
                    // "Critical" review says we should stop to prevent overselling.
                    // Throwing exception here handles the rollback if this listener is queued inside a job.
                    // If sync, it stops the request.
                    throw $e;
                }

                // Create subscription with quote details
                Subscription::create([
                    'company_id' => $company->id,
                    'name' => $item->description,
                    'stripe_status' => 'active',
                    'quantity' => $item->quantity,
                    'billing_frequency' => $quote->billing_frequency,
                    'effective_price' => $item->unit_price, // This is already updated to the correct frequency price
                    'starts_at' => now(),
                    'next_billing_date' => $quote->billing_frequency === 'annually' ? now()->addYear() : now()->addMonth(),
                ]);
            }
        }

        // 3. Create Invoice
        $invoice = \Modules\Billing\Models\Invoice::create([
            'company_id' => $company->id,
            'invoice_number' => 'INV-' . strtoupper(uniqid()),
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'draft',
            'currency' => 'USD',
            'notes' => 'Generated from Quote #' . $quote->quote_number,
        ]);

        $subtotal = 0;
        foreach ($quote->lineItems as $item) {
            $lineTotal = $item->quantity * $item->unit_price;
            $subtotal += $lineTotal;
            
            \Modules\Billing\Models\InvoiceLineItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => $lineTotal,
            ]);
        }
        
        $invoice->update([
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ]);

        Log::info("Quote #{$quote->id} provisioned for Company #{$company->id}. Invoice #{$invoice->id} created.");
    }
}

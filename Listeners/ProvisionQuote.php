<?php

namespace Modules\Billing\Listeners;

use Modules\Billing\Events\QuoteApproved;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Subscription;
use Modules\Inventory\Models\Product; // Import Product
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

        // 2. Provision Items (Inventory & Subscriptions)
        foreach ($quote->lineItems as $item) {
            if ($item->product_id) {
                
                $product = Product::with('components')->find($item->product_id);
                if (!$product) {
                    Log::warning("Product not found for Quote Item {$item->id}");
                    continue;
                }

                // Check for Bundle
                if ($product->is_bundle && $product->components->isNotEmpty()) {
                    // Explode Bundle
                    foreach ($product->components as $component) {
                        $componentQty = $item->quantity * $component->pivot->quantity;
                        $this->provisionSingleItem($company, $quote, $component, $componentQty, $item->description . " (Bundle Component)");
                    }
                    
                    // Note: We do NOT create a subscription for the Bundle SKU itself, 
                    // unless it also has a frequency. Usually Bundles are containers.
                    // If the Bundle has a price on the invoice, that's handled in Step 3 (Invoice Generation).
                    
                } else {
                    // Provision Single Item
                    $this->provisionSingleItem($company, $quote, $product, $item->quantity, $item->description);
                }
            }
        }
        
        // 3. Create Invoice (Charges based on Quote Line Items - Preserving Sales Price)
        $this->createInitialInvoice($company, $quote);
    }
    
    private function provisionSingleItem(Company $company, $quote, Product $product, int $quantity, string $description)
    {
        // A. Decrement Stock
        try {
            $this->inventory->decrementStock(
                (string)$product->id,
                $quantity,
                'Quote Provisioning',
                (string)$quote->id
            );
        } catch (\Exception $e) {
            Log::error("Failed to provision stock for Quote #{$quote->id}, Product {$product->id}: " . $e->getMessage());
            // We continue provisioning other items or throw? 
            // In a Bundle, partial failure is bad. But for MVP we log.
             throw $e;
        }

        // B. Create Subscription (If Recurring)
        if ($product->billing_frequency !== 'one_time') {
            
            $frequency = $product->billing_frequency ?? $quote->billing_frequency ?? 'monthly';
            $nextBilling = match($frequency) {
                'annually' => now()->addYear(),
                'quarterly' => now()->addQuarter(),
                default => now()->addMonth(),
            };

            Subscription::create([
                'company_id' => $company->id,
                'name' => $description,
                'product_id' => $product->id, 
                'stripe_status' => 'active',
                'quantity' => $quantity,
                'billing_frequency' => $frequency,
                'effective_price' => $product->getPriceForFrequency($frequency) ?? 0, // Use Component Price? Or 0 if included in Bundle?
                                                                                    // CRITICAL: If part of a bundle, the Client paid for the Bundle.
                                                                                    // The component subscription might need to be $0 (Provided) or Standard Price (Upsell).
                                                                                    // For "Onboarding Kit", likely the SaaS is "Standard Price" separate from Hardware.
                                                                                    // We'll use Product List Price for now.
                'starts_at' => now(),
                'next_billing_date' => $nextBilling,
            ]);
        }
    }

    private function createInitialInvoice(Company $company, $quote)
    {
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

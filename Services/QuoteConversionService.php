<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Quote;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Models\InvoiceLineItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class QuoteConversionService
{
    /**
     * Convert a quote to an invoice.
     */
    public function convertToInvoice(Quote $quote): Invoice
    {
        if (!$quote->accepted_at) {
            throw new \RuntimeException('Quote must be accepted before conversion');
        }

        return DB::transaction(function () use ($quote) {
            // Create invoice
            $invoice = Invoice::create([
                'company_id' => $quote->company_id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'status' => 'draft',
                'issue_date' => now(),
                'due_date' => now()->addDays(30), // Default 30-day terms
                'subtotal' => $quote->subtotal ?? 0,
                'tax_total' => $quote->tax ?? 0,
                'total' => $quote->total ?? 0,
                'paid_amount' => 0,
            ]);

            // Copy line items from quote to invoice
            foreach ($quote->lineItems as $quoteItem) {
                InvoiceLineItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $quoteItem->product_id,
                    'description' => $quoteItem->description,
                    'quantity' => $quoteItem->quantity,
                    'unit_price' => $quoteItem->unit_price,
                    'subtotal' => $quoteItem->subtotal,
                ]);
            }

            // Update quote status
            $quote->update(['status' => 'converted']);

            Log::info('Quote converted to invoice', [
                'quote_id' => $quote->id,
                'invoice_id' => $invoice->id,
            ]);

            // Dispatch event
            // event(new QuoteConverted($quote, $invoice));

            return $invoice;
        });
    }

    /**
     * Convert a quote to a subscription.
     */
    public function convertToSubscription(Quote $quote): Subscription
    {
        if (!$quote->accepted_at) {
            throw new \RuntimeException('Quote must be accepted before conversion');
        }

        return DB::transaction(function () use ($quote) {
            // For simplicity, take the first product as the subscription product
            $firstLineItem = $quote->lineItems->first();
            
            if (!$firstLineItem) {
                throw new \RuntimeException('Quote must have at least one line item');
            }

            $subscription = Subscription::create([
                'company_id' => $quote->company_id,
                'product_id' => $firstLineItem->product_id,
                'quantity' => $firstLineItem->quantity,
                'effective_price' => $firstLineItem->unit_price,
                'billing_frequency' => 'monthly', // Default, should be configurable
                'starts_at' => now(),
                'next_billing_date' => now()->addMonth(),
                'is_active' => true,
                'contract_start_date' => now(),
                'contract_end_date' => now()->addYear(), // Default 1 year
                'renewal_status' => 'active',
            ]);

            // Update quote status
            $quote->update(['status' => 'converted']);

            Log::info('Quote converted to subscription', [
                'quote_id' => $quote->id,
                'subscription_id' => $subscription->id,
            ]);

            return $subscription;
        });
    }

    /**
     * Convert a quote to both an invoice and subscription.
     * Useful for initial invoice + recurring subscription.
     */
    public function convertToInvoiceAndSubscription(Quote $quote): array
    {
        if (!$quote->accepted_at) {
            throw new \RuntimeException('Quote must be accepted before conversion');
        }

        return DB::transaction(function () use ($quote) {
            $invoice = $this->convertToInvoice($quote);
            
            // Reset status for subscription creation
            $quote->update(['status' => 'accepted']);
            
            $subscription = $this->convertToSubscription($quote);

            Log::info('Quote converted to invoice and subscription', [
                'quote_id' => $quote->id,
                'invoice_id' => $invoice->id,
                'subscription_id' => $subscription->id,
            ]);

            return [
                'invoice' => $invoice,
                'subscription' => $subscription,
            ];
        });
    }

    /**
     * Trigger procurement for hardware items in the quote.
     * This would integrate with a procurement system or create purchase orders.
     */
    public function triggerProcurement(Quote $quote): void
    {
        $hardwareItems = $quote->lineItems->filter(function ($item) {
            // Assuming products have a 'category' or 'type' field
            return $item->product && in_array($item->product->category ?? '', ['hardware', 'equipment']);
        });

        if ($hardwareItems->isEmpty()) {
            Log::debug('No hardware items to procure', ['quote_id' => $quote->id]);
            return;
        }

        foreach ($hardwareItems as $item) {
            // In real implementation, create purchase orders or trigger procurement workflow
            Log::info('Procurement triggered', [
                'quote_id' => $quote->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
            ]);
        }

        // Dispatch event or queue job
        // event(new ProcurementTriggered($quote, $hardwareItems));
    }

    /**
     * Generate a unique invoice number.
     */
    protected function generateInvoiceNumber(): string
    {
        $date = now();
        $prefix = 'INV-' . $date->format('Y-m-');
        
        // Get the last invoice for this month
        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            // Extract the sequence number and increment
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}

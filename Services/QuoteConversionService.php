<?php

namespace Modules\Billing\Services;

use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Quote;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\InvoiceLineItem;
use Modules\Billing\Models\ServiceContract;
use Modules\Billing\Models\ContractPriceHistory;

class QuoteConversionService
{
    public function convert(Quote $quote)
    {
        return DB::transaction(function () use ($quote) {
            // 1. Handle One-Time Items (Hardware) -> Invoice
            $oneTimeItems = $quote->lineItems()->where('is_recurring', false)->get();
            
            if ($oneTimeItems->isNotEmpty()) {
                $invoice = Invoice::create([
                    'client_id' => $quote->client_id,
                    'company_id' => $quote->company_id,
                    'status' => 'Draft',
                    'due_date' => now()->addDays(30),
                    'total' => $oneTimeItems->sum(fn($item) => $item->quantity * $item->unit_price),
                ]);

                foreach ($oneTimeItems as $item) {
                    InvoiceLineItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $item->product_id,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'standard_unit_price' => $item->standard_price ?? $item->unit_price,
                        'subtotal' => $item->quantity * $item->unit_price,
                        'tax_amount' => 0, // Simplified
                        'tax_credit_amount' => 0,
                        'is_fee' => false,
                    ]);
                }
            }

            // 2. Handle Recurring Items -> Subscription / Contract
            $recurringItems = $quote->lineItems()->where('is_recurring', true)->get();

            foreach ($recurringItems as $item) {
                // Find or Create Service Contract
                // We assume the description matches the contract name for simplicity in this context
                $contract = ServiceContract::firstOrCreate(
                    [
                        'client_id' => $quote->client_id, 
                        'name' => $item->description
                    ],
                    [
                        'status' => 'Active',
                        'standard_rate' => $item->unit_price // Default to this price
                    ]
                );

                $currentPrice = $contract->priceHistory()
                    ->whereNull('ended_at')
                    ->latest('started_at')
                    ->first();

                // If price differs or no price history exists
                if (!$currentPrice || $currentPrice->unit_price != $item->unit_price) {
                    if ($currentPrice) {
                        $currentPrice->update(['ended_at' => now()]);
                    }

                    ContractPriceHistory::create([
                        'contract_id' => $contract->id,
                        'unit_price' => $item->unit_price,
                        'started_at' => now(),
                    ]);
                }
            }
            
            $quote->update(['status' => 'Converted']);
            
            return true;
        });
    }
}

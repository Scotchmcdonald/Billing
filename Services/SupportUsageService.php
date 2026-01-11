<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Company;
use Modules\Billing\Models\BillableEntry;
use Modules\Inventory\Models\Product;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Log;
use Exception;

class SupportUsageService
{
    public function __construct(
        protected CreditLedgerService $credits,
        protected PricingEngineService $pricing
    ) {}

    /**
     * Process a resolved ticket.
     * 1. Calculate Cost.
     * 2. Burn Credits (if available).
     * 3. Or Create BillableEntry (if under Limit).
     * 4. Trigger Approval (if over Limit).
     */
    public function recordTicketResolution(Company $company, int $ticketId, string $tier, float $hours = 1.0): void
    {
        // 1. Determine Product/Rate for this Tier
        // Using a convention SKU: SUPPORT_{TIER}
        $sku = "SUPPORT_" . strtoupper($tier); 
        $product = Product::where('sku', $sku)->first();
        
        if (!$product) {
            // Fallback or Error
            throw new Exception("Billing Product not found for Tier: {$tier}");
        }

        // Calculate Cost using PricingEngine (handles Company discounts/overrides)
        // We treat it as 1 'Unit' of Ticket, or 'Hours' x Rate.
        // Let's assume Ticket-Based = 1 Unit. Hourly = Hours.
        // The prompt implies "Resolved tickets... qualify for cost".
        $quantity = $product->unit_of_measure === 'hour' ? $hours : 1;
        
        // This returns a PriceResult with Money
        $priceResult = $this->pricing->calculateEffectivePrice($company, \Modules\Inventory\DataTransferObjects\ProductSnapshot::fromModel($product));
        $costMoney = $priceResult->price->multiply($quantity);
        $costCents = $costMoney->amount;

        // 2. Try to Burn Credits first
        // Assuming Credits are 1:1 with Cents (Wallet) or 1:1 with Tickets? 
        // Logic: Checks if company calls burned "Points" or "Dollars".
        // Let's assume Credits are generic "Points" equal to Cents for simplicity, or 
        // we check if `credit_cost` is defined on product.
        
        if ($product->credit_cost > 0) {
             // Product has a specific Point cost (e.g. 1 Ticket = 10 Credits)
             $pointCost = $product->credit_cost * $quantity;
             if ($this->credits->burnCredits($company, (int)$pointCost, "Ticket Resolutiion: $ticketId", 'Ticket', $ticketId)) {
                 // Successfully paid via credits
                 return;
             }
        } else {
             // Fallback to Dollar-value wallet? 
             if ($this->credits->burnCredits($company, $costCents, "Ticket Resolution: $ticketId", 'Ticket', $ticketId)) {
                 return;
             }
        }

        // 3. If no credits, it goes to Monthly Invoice (Billable Entry)
        
        // CHECK MONTHLY LIMIT
        $currentUsage = $this->getCurrentMonthUnbilledUsage($company);
        $projectedTotal = $currentUsage + $costCents;
        
        $limit = $company->monthly_support_limit; // Stored in Cents
        
        if ($limit > 0) {
            // 1. Check for Hard Limit Violation
            if ($projectedTotal > $limit) {
                // OVERAGE -> PENDING APPROVAL
                $this->createBillableEntry(
                    company: $company, 
                    ticketId: $ticketId, 
                    product: $product, 
                    quantity: $quantity, 
                    cost: $costMoney,
                    status: 'pending_approval' // New status
                );
                
                Log::notice("Support Limit EXCEEDED for Company #{$company->id}. Ticket #{$ticketId} parked for approval.");
                // TODO: Dispatch new SupportLimitExceeded($company, $ticketId);
                return;
            }

            // 2. Check for 80% Threshold Alert
            // Only trigger if we are crossing the line right now to avoid spamming
            $threshold = (int)($limit * 0.80);
            if ($currentUsage < $threshold && $projectedTotal >= $threshold) {
                Log::info("Support Limit WARNING: Company #{$company->id} has reached 80% of monthly cap.");
                // TODO: Dispatch new SupportLimitWarning($company, $currentUsage, $limit);
            }
        }

        // STANDARD BILLABLE
        $this->createBillableEntry(
            company: $company, 
            ticketId: $ticketId, 
            product: $product, 
            quantity: $quantity, 
            cost: $costMoney,
            status: 'pending' // Standard billing pending status
        );
    }

    private function getCurrentMonthUnbilledUsage(Company $company): int
    {
        // Sum of all 'pending' billable entries for this month
        return (int) BillableEntry::where('company_id', $company->id)
            ->where('billing_status', 'pending')
            ->whereMonth('created_at', now()->month)
            ->sum('subtotal') * 100; // stored as decimal, convert to cents
    }

    private function createBillableEntry(Company $company, int $ticketId, Product $product, float $quantity, Money $cost, string $status): void
    {
        BillableEntry::create([
            'company_id' => $company->id,
            'ticket_id' => $ticketId, // Assuming ID is Int or string? Schema said foreignId, so int. 
                                      // If clean string passed, might fail. 
                                      // Casting to int for now/Assuming ID was resolved.
            'ticket_tier' => $product->ticket_tier,
            'description' => "Support: {$product->name} ({$quantity} {$product->unit_of_measure})",
            'quantity' => $quantity,
            'rate' => $cost->amount / $quantity / 100, // Back to float for DB
            'subtotal' => $cost->amount / 100,
            'is_billable' => true,
            'billing_status' => $status,
            'type' => 'labor',
            'date' => now(),
        ]);
    }
}

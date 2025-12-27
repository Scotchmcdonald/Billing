<?php

namespace Modules\Billing\Observers;

use Modules\Billing\Models\Subscription;
use Modules\Billing\Services\ProrationCalculator;
use Modules\Billing\Models\BillableEntry;
use Illuminate\Support\Facades\Log;

class SubscriptionObserver
{
    protected $prorationCalculator;

    public function __construct(ProrationCalculator $prorationCalculator)
    {
        $this->prorationCalculator = $prorationCalculator;
    }

    /**
     * Handle the Subscription "created" event.
     */
    public function created(Subscription $subscription): void
    {
        // New subscription mid-cycle
        // We need to check if it's a new subscription or a renewal (which shouldn't trigger this if it's just a new record for same sub? Cashier reuses records usually or creates new ones for swaps)
        
        // If it's a brand new subscription, we might want to prorate the first period if it's not starting on the billing cycle anchor.
        // However, Cashier usually handles the first invoice immediately.
        // But if we have custom proration logic, we might want to add a BillableEntry.
        
        // For now, let's just log it.
        Log::info("Subscription created: {$subscription->id}");
        
        // TODO: Implement specific logic if Cashier doesn't handle initial proration as desired.
    }

    /**
     * Handle the Subscription "updated" event.
     */
    public function updated(Subscription $subscription): void
    {
        // Check for cancellation
        if ($subscription->wasChanged('ends_at') && $subscription->ends_at) {
            // Subscription cancelled (scheduled for end of period or immediate)
            // If immediate, we might owe a credit.
            
            // If ends_at is in the future, it's "cancelled at period end", so no proration needed usually.
            // If ends_at is now (or close to now), it's "cancelled immediately".
            
            if ($subscription->ends_at->isPast() || $subscription->ends_at->isToday()) {
                 // Calculate credit for unused time
                 $prorationResult = $this->prorationCalculator->calculateProration($subscription, now(), 0);
                 
                 if ($prorationResult->credit_amount > 0) {
                     BillableEntry::create([
                        'company_id' => $subscription->company_id,
                        'subscription_id' => $subscription->id,
                        'type' => 'proration_adjustment',
                        'amount' => -$prorationResult->credit_amount,
                        'description' => "Credit for early cancellation",
                        'date' => now(),
                        'metadata' => $prorationResult->calculation_details
                     ]);
                 }
            }
        }
    }

    /**
     * Handle the Subscription "deleted" event.
     */
    public function deleted(Subscription $subscription): void
    {
        //
    }
}

<?php

namespace Modules\Billing\Services;

use Illuminate\Support\Carbon;
use Modules\Billing\DataTransferObjects\ProrationResult;
use Modules\Billing\Models\Subscription;

class ProrationCalculator
{
    public function calculateProration(Subscription $subscription, Carbon $changeDate, int $newQuantity): ProrationResult
    {
        // Assuming monthly billing for simplicity in this iteration, or using billing_frequency
        // Logic: Calculate remaining days in the current cycle vs total days.
        
        // Determine cycle start and end
        // If next_billing_date is set, that's the end of the current cycle.
        // Start would be based on frequency.
        
        if (!$subscription->next_billing_date) {
            // New subscription or issue, return 0
             return new ProrationResult(0, 0, 'none', []);
        }

        // Check Company Policy
        $company = $subscription->company;
        $policy = $company->settings['proration_policy'] ?? 'always_prorate';

        if ($policy === 'no_proration') {
             return new ProrationResult(0, 0, 'no_proration', []);
        }

        $cycleEnd = Carbon::parse($subscription->next_billing_date);
        $cycleStart = $cycleEnd->copy();
        
        if ($subscription->billing_frequency === 'monthly') {
            $cycleStart->subMonth();
        } elseif ($subscription->billing_frequency === 'quarterly') {
            $cycleStart->subQuarter();
        } elseif ($subscription->billing_frequency === 'annual') {
            $cycleStart->subYear();
        }

        $totalDays = $cycleStart->diffInDays($cycleEnd);
        $daysRemaining = $changeDate->diffInDays($cycleEnd, false);

        if ($daysRemaining <= 0) {
             return new ProrationResult(0, 0, 'none', ['reason' => 'Change date is after cycle end']);
        }

        $ratio = $daysRemaining / $totalDays;
        
        $currentTotal = $subscription->effective_price * $subscription->quantity;
        $newTotal = $subscription->effective_price * $newQuantity;
        
        $diff = $newTotal - $currentTotal;
        
        if ($policy === 'prorate_upgrades_only' && $diff < 0) {
             return new ProrationResult(0, 0, 'prorate_upgrades_only', []);
        }

        $proratedAmount = $diff * $ratio;

        $amount = max(0, $proratedAmount);
        $credit = max(0, -$proratedAmount);

        return new ProrationResult(
            amount: round($amount, 2),
            credit_amount: round($credit, 2),
            policy_used: 'daily_proration',
            calculation_details: [
                'total_days' => $totalDays,
                'days_remaining' => $daysRemaining,
                'ratio' => $ratio,
                'old_quantity' => $subscription->quantity,
                'new_quantity' => $newQuantity,
                'price' => $subscription->effective_price
            ]
        );
    }

    public function previewProration(Subscription $subscription, Carbon $changeDate, int $newQuantity): ProrationResult
    {
        return $this->calculateProration($subscription, $changeDate, $newQuantity);
    }
}

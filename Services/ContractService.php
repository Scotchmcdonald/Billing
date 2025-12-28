<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Subscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ContractService
{
    /**
     * Get subscriptions with contracts expiring soon.
     */
    public function getExpiringContracts(int $daysAhead = 60): Collection
    {
        return Subscription::whereNotNull('contract_end_date')
            ->where('contract_end_date', '<=', now()->addDays($daysAhead))
            ->where('contract_end_date', '>=', now())
            ->where('is_active', true)
            ->with('company')
            ->orderBy('contract_end_date', 'asc')
            ->get();
    }

    /**
     * Send renewal reminder for a subscription.
     * This would typically dispatch a notification or queue a job.
     */
    public function sendRenewalReminder(Subscription $subscription, int $daysRemaining): void
    {
        if (!$subscription->contract_end_date) {
            throw new \RuntimeException('Subscription does not have a contract end date');
        }

        // In a real implementation, this would dispatch a notification
        // notification(new ContractRenewalReminder($subscription, $daysRemaining));
        
        Log::info('Renewal reminder sent', [
            'subscription_id' => $subscription->id,
            'company_id' => $subscription->company_id,
            'days_remaining' => $daysRemaining,
            'contract_end_date' => $subscription->contract_end_date,
        ]);
    }

    /**
     * Mark a subscription as renewed with a new end date.
     */
    public function markAsRenewed(Subscription $subscription, ?Carbon $newEndDate = null): void
    {
        $oldEndDate = $subscription->contract_end_date;
        
        // If no new end date provided, extend by the original contract length
        if (!$newEndDate && $subscription->contract_start_date && $subscription->contract_end_date) {
            $contractLength = $subscription->contract_start_date->diffInDays($subscription->contract_end_date);
            $newEndDate = now()->addDays($contractLength);
        } elseif (!$newEndDate) {
            // Default to 1 year renewal
            $newEndDate = now()->addYear();
        }

        $subscription->update([
            'contract_start_date' => now(),
            'contract_end_date' => $newEndDate,
            'renewal_status' => 'active',
        ]);

        Log::info('Subscription marked as renewed', [
            'subscription_id' => $subscription->id,
            'old_end_date' => $oldEndDate,
            'new_end_date' => $newEndDate,
        ]);
    }

    /**
     * Mark a subscription as churned.
     */
    public function markAsChurned(Subscription $subscription, string $reason): void
    {
        $subscription->update([
            'renewal_status' => 'churned',
            'is_active' => false,
            'ends_at' => now(),
        ]);

        // Store churn reason in metadata
        $metadata = $subscription->metadata ?? [];
        $metadata['churn_reason'] = $reason;
        $metadata['churned_at'] = now()->toIso8601String();
        $subscription->update(['metadata' => $metadata]);

        Log::info('Subscription marked as churned', [
            'subscription_id' => $subscription->id,
            'company_id' => $subscription->company_id,
            'reason' => $reason,
        ]);
    }

    /**
     * Mark subscription as pending renewal.
     */
    public function markAsPendingRenewal(Subscription $subscription): void
    {
        $subscription->update([
            'renewal_status' => 'pending_renewal',
        ]);

        Log::info('Subscription marked as pending renewal', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Get all churned subscriptions with optional time filter.
     */
    public function getChurnedSubscriptions(?Carbon $since = null): Collection
    {
        $query = Subscription::where('renewal_status', 'churned')
            ->with('company')
            ->orderBy('updated_at', 'desc');

        if ($since) {
            $query->where('updated_at', '>=', $since);
        }

        return $query->get();
    }

    /**
     * Calculate churn rate for a given period.
     */
    public function calculateChurnRate(Carbon $startDate, Carbon $endDate): float
    {
        $startingSubscriptions = Subscription::where('is_active', true)
            ->where('created_at', '<', $startDate)
            ->count();

        if ($startingSubscriptions === 0) {
            return 0.0;
        }

        $churnedCount = Subscription::where('renewal_status', 'churned')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        return ($churnedCount / $startingSubscriptions) * 100;
    }
}

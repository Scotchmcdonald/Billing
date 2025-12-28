<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Quote;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class QuoteTrackingService
{
    /**
     * Record a quote view with IP and user agent.
     */
    public function recordView(Quote $quote, string $ipAddress, ?string $userAgent = null): void
    {
        if ($quote->viewed_at) {
            // Already viewed, don't update (preserve first view timestamp)
            Log::debug('Quote already viewed', [
                'quote_id' => $quote->id,
                'first_viewed_at' => $quote->viewed_at,
            ]);
            return;
        }

        $quote->update([
            'viewed_at' => now(),
            'viewed_ip' => $ipAddress,
        ]);

        Log::info('Quote viewed', [
            'quote_id' => $quote->id,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        // Notify owner of view
        $this->notifyOwnerOfView($quote);
    }

    /**
     * Record quote acceptance with signature data.
     */
    public function recordAcceptance(
        Quote $quote,
        string $signerName,
        string $signerEmail,
        ?string $signatureData = null
    ): void {
        if ($quote->accepted_at) {
            throw new \RuntimeException('Quote has already been accepted');
        }

        $quote->update([
            'accepted_at' => now(),
            'signer_name' => $signerName,
            'signer_email' => $signerEmail,
            'signature_data' => $signatureData,
            'status' => 'accepted', // Assuming quotes have a status field
        ]);

        Log::info('Quote accepted', [
            'quote_id' => $quote->id,
            'signer_name' => $signerName,
            'signer_email' => $signerEmail,
            'accepted_at' => $quote->accepted_at,
        ]);

        // Dispatch QuoteAccepted event
        // event(new QuoteAccepted($quote));
    }

    /**
     * Get view history for a quote.
     * Since we only track first view, this returns a simple collection.
     */
    public function getViewHistory(Quote $quote): Collection
    {
        if (!$quote->viewed_at) {
            return collect();
        }

        return collect([
            [
                'viewed_at' => $quote->viewed_at,
                'viewed_ip' => $quote->viewed_ip,
                'is_first_view' => true,
            ]
        ]);
    }

    /**
     * Notify the quote owner that it has been viewed.
     */
    public function notifyOwnerOfView(Quote $quote): void
    {
        // In real implementation, dispatch notification
        // Notification::send($quote->createdBy, new QuoteViewedNotification($quote));
        
        Log::info('Quote view notification queued', [
            'quote_id' => $quote->id,
        ]);
    }

    /**
     * Get quotes that were viewed but not accepted.
     */
    public function getViewedButNotAccepted(?int $daysOld = null): Collection
    {
        $query = Quote::whereNotNull('viewed_at')
            ->whereNull('accepted_at');

        if ($daysOld) {
            $query->where('viewed_at', '<=', now()->subDays($daysOld));
        }

        return $query->with('company')->get();
    }

    /**
     * Calculate average time to view for quotes.
     */
    public function getAverageTimeToView(): ?float
    {
        $quotes = Quote::whereNotNull('viewed_at')
            ->whereNotNull('created_at')
            ->get();

        if ($quotes->isEmpty()) {
            return null;
        }

        $totalDays = $quotes->sum(function ($quote) {
            return $quote->created_at->diffInDays($quote->viewed_at);
        });

        return $totalDays / $quotes->count();
    }

    /**
     * Calculate conversion rate (viewed to accepted).
     */
    public function getConversionRate(): float
    {
        $viewedCount = Quote::whereNotNull('viewed_at')->count();
        
        if ($viewedCount === 0) {
            return 0.0;
        }

        $acceptedCount = Quote::whereNotNull('accepted_at')->count();

        return ($acceptedCount / $viewedCount) * 100;
    }
}

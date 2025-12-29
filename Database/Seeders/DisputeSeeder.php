<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Billing\Models\Dispute;
use Modules\Billing\Models\Invoice;
use Carbon\Carbon;

class DisputeSeeder extends Seeder
{
    /**
     * Seed invoice disputes.
     */
    public function run(): void
    {
        // Get sent and overdue invoices (most likely to be disputed)
        $invoices = Invoice::whereIn('status', ['sent', 'overdue', 'paid'])
            ->inRandomOrder()
            ->limit(15)
            ->get();

        if ($invoices->isEmpty()) {
            $this->command->warn('⚠ No eligible invoices found. Please run InvoiceSeeder first.');
            return;
        }

        $disputeReasons = [
            'incorrect_quantity' => 'Billed quantity does not match actual usage',
            'service_not_received' => 'Services listed were not provided',
            'pricing_error' => 'Incorrect pricing applied - should match contract',
            'duplicate_charge' => 'Already paid this invoice / duplicate billing',
            'quality_issue' => 'Service quality did not meet expectations',
            'cancelled_service' => 'Service was cancelled but still billed',
            'contract_dispute' => 'Charges do not align with signed contract',
            'unauthorized_charge' => 'Did not authorize these charges',
        ];

        $disputeCount = 0;
        $resolvedCount = 0;

        foreach ($invoices->take(8) as $invoice) {
            $reason = array_rand($disputeReasons);
            $explanation = $disputeReasons[$reason];
            
            // Dispute 20-80% of invoice amount
            $disputedPercent = rand(20, 80);
            $disputedAmount = round($invoice->total * ($disputedPercent / 100), 2);
            
            // Determine status (60% pending, 30% resolved, 10% rejected)
            $statusRoll = rand(1, 100);
            if ($statusRoll <= 60) {
                $status = 'pending';
                $resolution = null;
                $resolvedBy = null;
                $resolvedAt = null;
            } elseif ($statusRoll <= 90) {
                $status = 'resolved';
                $resolution = $this->generateResolution($reason);
                $resolvedBy = 1;
                $resolvedAt = Carbon::now()->subDays(rand(1, 14));
                $resolvedCount++;
            } else {
                $status = 'rejected';
                $resolution = 'Dispute rejected - charges are accurate per contract terms';
                $resolvedBy = 1;
                $resolvedAt = Carbon::now()->subDays(rand(1, 7));
            }

            Dispute::create([
                'invoice_id' => $invoice->id,
                'company_id' => $invoice->company_id,
                'reason' => $reason,
                'disputed_amount' => $disputedAmount,
                'line_item_ids' => $this->selectDisputedLineItems($invoice, $disputedPercent),
                'explanation' => $explanation . '. ' . $this->generateDetailedExplanation($reason),
                'status' => $status,
                'resolution' => $resolution,
                'created_by' => rand(2, 5), // Customer contact
                'resolved_by' => $resolvedBy,
                'resolved_at' => $resolvedAt,
                'created_at' => Carbon::parse($invoice->issue_date)->addDays(rand(5, 20)),
            ]);

            // Mark invoice as disputed
            if ($status === 'pending') {
                $invoice->update(['is_disputed' => true]);
            }

            $disputeCount++;
        }

        $this->command->info("✓ Created {$disputeCount} disputes ({$resolvedCount} resolved)");
        $this->showDisputeStats();
    }

    /**
     * Select line items to dispute
     */
    private function selectDisputedLineItems(Invoice $invoice, int $percent): array
    {
        $lineItems = $invoice->lineItems()->get();
        if ($lineItems->isEmpty()) {
            return [];
        }

        // Dispute 1-3 line items
        $numToDispute = min(rand(1, 3), $lineItems->count());
        return $lineItems->random($numToDispute)->pluck('id')->toArray();
    }

    /**
     * Generate detailed explanation
     */
    private function generateDetailedExplanation(string $reason): string
    {
        $explanations = [
            'incorrect_quantity' => 'Our records show we only had 12 active users during this period, but we were billed for 18.',
            'service_not_received' => 'The premium support package was not activated for our account during this billing cycle.',
            'pricing_error' => 'Per our contract dated March 2024, our negotiated rate is $75/user, not $85/user.',
            'duplicate_charge' => 'This appears to be a duplicate of invoice INV-2024-1234 which was already paid on Nov 15.',
            'quality_issue' => 'Multiple outages occurred this month (Nov 3, Nov 12, Nov 21) affecting service availability.',
            'cancelled_service' => 'We submitted cancellation request on October 28th via support ticket #4521.',
            'contract_dispute' => 'Contract specifies 30-day billing cycle, but this invoice covers 35 days.',
            'unauthorized_charge' => 'The additional storage charges were never requested or approved by our team.',
        ];

        return $explanations[$reason] ?? 'Please review and provide clarification.';
    }

    /**
     * Generate resolution based on reason
     */
    private function generateResolution(string $reason): string
    {
        $resolutions = [
            'incorrect_quantity' => 'Verified usage records. Adjusted invoice to reflect 12 users. Credit note issued for difference.',
            'service_not_received' => 'Confirmed service was not activated. Removed charge from invoice. Credit applied.',
            'pricing_error' => 'Contract pricing verified. Adjusted rate to $75/user as per agreement. Updated billing system.',
            'duplicate_charge' => 'Confirmed duplicate billing. Original payment applied. This invoice voided.',
            'quality_issue' => 'Service level agreement breach acknowledged. Applied 25% credit for affected period.',
            'cancelled_service' => 'Cancellation confirmed. Pro-rated final invoice. Credit issued for unused period.',
            'contract_dispute' => 'Billing cycle corrected. Adjusted to 30-day period. Credit note issued.',
            'unauthorized_charge' => 'Charge removed. Additional approval process implemented for future add-ons.',
        ];

        return $resolutions[$reason] ?? 'Issue resolved with customer. Adjusted billing as agreed.';
    }

    /**
     * Show dispute statistics
     */
    private function showDisputeStats(): void
    {
        $breakdown = Dispute::selectRaw('status, COUNT(*) as count, SUM(disputed_amount) as total_amount')
            ->groupBy('status')
            ->get();

        $this->command->info("\nDispute Status Breakdown:");
        foreach ($breakdown as $row) {
            $this->command->info("  • {$row->status}: {$row->count} disputes (\${$row->total_amount})");
        }

        // Most common reasons
        $reasons = Dispute::selectRaw('reason, COUNT(*) as count')
            ->groupBy('reason')
            ->orderBy('count', 'desc')
            ->limit(3)
            ->get();

        $this->command->info("\nTop Dispute Reasons:");
        foreach ($reasons as $row) {
            $this->command->info("  • " . str_replace('_', ' ', ucwords($row->reason)) . ": {$row->count}");
        }
    }
}

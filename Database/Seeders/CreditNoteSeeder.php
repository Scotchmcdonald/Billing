<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Billing\Models\CreditNote;
use Modules\Billing\Models\Invoice;
use Carbon\Carbon;

class CreditNoteSeeder extends Seeder
{
    /**
     * Seed credit notes for invoices.
     */
    public function run(): void
    {
        $invoices = Invoice::whereIn('status', ['paid', 'sent', 'overdue'])
            ->inRandomOrder()
            ->limit(12)
            ->get();

        if ($invoices->isEmpty()) {
            $this->command->warn('⚠ No eligible invoices found. Please run InvoiceSeeder first.');
            return;
        }

        $creditReasons = [
            'service_credit' => 'Service credit for downtime/performance issues',
            'billing_error' => 'Billing error correction',
            'customer_refund' => 'Customer refund request - overpayment',
            'goodwill_gesture' => 'Goodwill credit for customer satisfaction',
            'proration_adjustment' => 'Pro-rated adjustment for mid-cycle changes',
            'dispute_resolution' => 'Credit issued to resolve billing dispute',
            'contract_adjustment' => 'Contract terms adjustment',
            'service_cancellation' => 'Partial refund for early cancellation',
        ];

        $creditCount = 0;
        $totalAmount = 0;

        foreach ($invoices->take(10) as $invoice) {
            // 40% chance an invoice gets a credit note
            if (rand(1, 100) > 40) {
                continue;
            }

            $reasonKey = array_rand($creditReasons);
            $reason = $creditReasons[$reasonKey];
            
            // Credit 5-50% of invoice amount
            $creditPercent = rand(5, 50);
            $creditAmount = round($invoice->total * ($creditPercent / 100), 2);
            
            // Determine if credit has been applied
            $isApplied = rand(1, 100) <= 70; // 70% applied
            $appliedAt = $isApplied 
                ? Carbon::parse($invoice->issue_date)->addDays(rand(3, 30))
                : null;

            CreditNote::create([
                'invoice_id' => $invoice->id,
                'company_id' => $invoice->company_id,
                'amount' => $creditAmount, // Already in decimal format
                'reason' => $reasonKey,
                'notes' => $this->generateCreditNotes($reasonKey, $creditPercent),
                'issued_by' => 1,
                'applied_at' => $appliedAt,
                'created_at' => Carbon::parse($invoice->issue_date)->addDays(rand(1, 25)),
            ]);

            $creditCount++;
            $totalAmount += $creditAmount;
        }

        $this->command->info("✓ Created {$creditCount} credit notes totaling \$" . number_format($totalAmount, 2));
        $this->showCreditNoteStats();
    }

    /**
     * Generate detailed credit note notes
     */
    private function generateCreditNotes(string $reasonKey, int $percent): string
    {
        $notes = [
            'service_credit' => "Applying {$percent}% credit due to service interruptions during billing period. SLA breach documented in support tickets.",
            'billing_error' => "Correcting billing calculation error. Customer was overcharged by {$percent}%. Error has been identified and resolved in billing system.",
            'customer_refund' => "Processing refund for overpayment. Customer paid more than invoiced amount. Returning {$percent}% as credit.",
            'goodwill_gesture' => "Issuing {$percent}% credit as goodwill gesture for onboarding issues and initial service challenges.",
            'proration_adjustment' => "Pro-rated credit for mid-cycle service changes. Customer modified subscription on day 15 of 30-day cycle.",
            'dispute_resolution' => "Credit issued to resolve billing dispute #" . rand(1000, 9999) . ". {$percent}% of disputed amount credited per agreement.",
            'contract_adjustment' => "Retroactive contract pricing adjustment. Updated terms effective from contract amendment date.",
            'service_cancellation' => "Partial refund for early cancellation. {$percent}% credit for unused service period.",
        ];

        return $notes[$reasonKey] ?? "Credit note issued - {$percent}% of invoice amount.";
    }

    /**
     * Show credit note statistics
     */
    private function showCreditNoteStats(): void
    {
        $total = CreditNote::count();
        $applied = CreditNote::whereNotNull('applied_at')->count();
        $pending = $total - $applied;
        
        $totalValue = CreditNote::sum('amount'); // Already in decimal format
        $appliedValue = CreditNote::whereNotNull('applied_at')->sum('amount');

        $this->command->info("\nCredit Note Statistics:");
        $this->command->info("  • Total: {$total} credit notes (\$" . number_format($totalValue, 2) . ")");
        $this->command->info("  • Applied: {$applied} (\$" . number_format($appliedValue, 2) . ")");
        $this->command->info("  • Pending: {$pending} (\$" . number_format($totalValue - $appliedValue, 2) . ")");

        // Average credit amount
        $avgCredit = $total > 0 ? $totalValue / $total : 0;
        $this->command->info("  • Average credit: \$" . number_format($avgCredit, 2));
    }
}

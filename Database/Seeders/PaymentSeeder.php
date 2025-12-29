<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\Invoice;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    /**
     * Seed payments for invoices.
     */
    public function run(): void
    {
        // Get paid invoices (they should have payments)
        $paidInvoices = Invoice::where('status', 'paid')->get();
        
        // Also get some sent/overdue invoices for partial payments (30% chance)
        $partialInvoices = Invoice::whereIn('status', ['sent', 'overdue'])
            ->get()
            ->filter(fn() => rand(1, 100) <= 30);
        
        $invoices = $paidInvoices->merge($partialInvoices);

        if ($invoices->isEmpty()) {
            $this->command->warn('⚠ No invoices found. Please run InvoiceSeeder first.');
            return;
        }

        $paymentMethods = ['stripe_card', 'stripe_ach', 'check', 'wire', 'cash', 'other'];
        $paymentCount = 0;
        $totalAmount = 0;

        foreach ($invoices as $invoice) {
            $invoiceTotal = (float) $invoice->total;
            
            // Determine payment amount based on status
            if ($invoice->status === 'paid') {
                // Fully paid - create 1 payment for full amount
                $amountToPay = $invoiceTotal;
                $numPayments = 1;
            } else {
                // Partial payment - pay 30-70% of total
                $amountToPay = round($invoiceTotal * (rand(30, 70) / 100), 2);
                $numPayments = rand(1, 2); // 1-2 partial payments
            }
            
            $remainingAmount = $amountToPay;

            for ($i = 0; $i < $numPayments; $i++) {
                // Last payment takes remaining amount
                $paymentAmount = ($i === $numPayments - 1) 
                    ? $remainingAmount 
                    : round($remainingAmount / ($numPayments - $i) * (rand(40, 100) / 100), 2);

                $remainingAmount -= $paymentAmount;

                // Payment date between issue and now
                $paymentDate = $this->generatePaymentDate($invoice);
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

                Payment::create([
                    'invoice_id' => $invoice->id,
                    'company_id' => $invoice->company_id,
                    'amount' => $paymentAmount,
                    'payment_date' => $paymentDate,
                    'payment_method' => $paymentMethod,
                    'payment_reference' => 'PAY-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                    'notes' => $this->generatePaymentNotes($paymentMethod, $numPayments, $i + 1),
                    'created_by' => rand(1, 2),
                    'created_at' => $paymentDate,
                    'updated_at' => $paymentDate,
                ]);

                $paymentCount++;
                $totalAmount += $paymentAmount;
            }
        }

        $this->command->info("✓ Created {$paymentCount} payments totaling \$" . number_format($totalAmount, 2));
        
        // Show payment method breakdown
        $this->showPaymentBreakdown();
    }

    /**
     * Generate payment date based on invoice
     */
    private function generatePaymentDate(Invoice $invoice): Carbon
    {
        $issueDate = Carbon::parse($invoice->issue_date);
        $now = Carbon::now();

        // Paid invoices: payment between issue date and due date (or shortly after)
        if ($invoice->status === 'paid') {
            $dueDate = Carbon::parse($invoice->due_date);
            $daysBetween = $issueDate->diffInDays(min($dueDate->addDays(10), $now));
            return $issueDate->copy()->addDays(rand(5, max(5, $daysBetween)));
        }

        // Partial payments: random date between issue and now
        $daysSinceIssue = $issueDate->diffInDays($now);
        return $issueDate->copy()->addDays(rand(1, max(1, $daysSinceIssue)));
    }

    /**
     * Generate transaction ID based on payment method
     */
    private function generateTransactionId(string $method): string
    {
        $prefixes = [
            'credit_card' => 'CC',
            'ach' => 'ACH',
            'wire_transfer' => 'WIRE',
            'check' => 'CHK',
            'cash' => 'CASH',
            'stripe' => 'ch',
            'paypal' => 'PP',
        ];

        $prefix = $prefixes[$method] ?? 'TXN';
        return $prefix . '-' . strtoupper(substr(md5(uniqid()), 0, 12));
    }

    /**
     * Generate gateway payment ID
     */
    private function generateGatewayId(string $gateway): string
    {
        if ($gateway === 'stripe') {
            return 'ch_' . substr(md5(uniqid()), 0, 24);
        } elseif ($gateway === 'paypal') {
            return 'PAYID-' . strtoupper(substr(md5(uniqid()), 0, 16));
        }
        return null;
    }

    /**
     * Generate payment notes
     */
    private function generatePaymentNotes(string $method, int $totalPayments, int $currentPayment): ?string
    {
        if ($totalPayments > 1) {
            return "Partial payment {$currentPayment} of {$totalPayments} via " . str_replace('_', ' ', ucwords($method));
        }

        $notes = [
            'credit_card' => 'Payment processed via credit card',
            'ach' => 'ACH transfer completed',
            'wire_transfer' => 'Wire transfer received',
            'check' => 'Check received and deposited',
            'cash' => 'Cash payment received',
            'stripe' => 'Online payment via Stripe',
            'paypal' => 'PayPal payment received',
        ];

        return $notes[$method] ?? 'Payment received';
    }

    /**
     * Show payment method breakdown
     */
    private function showPaymentBreakdown(): void
    {
        $breakdown = Payment::selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('payment_method')
            ->orderBy('total_amount', 'desc')
            ->get();

        $this->command->info("\nPayment Method Breakdown:");
        foreach ($breakdown as $row) {
            $method = str_replace('_', ' ', ucwords($row->payment_method));
            $this->command->info("  • {$method}: {$row->count} payments (\${$row->total_amount})");
        }
    }
}

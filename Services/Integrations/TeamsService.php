<?php

namespace Modules\Billing\Services\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TeamsService
{
    protected string $webhookUrl;

    public function __construct()
    {
        $this->webhookUrl = config('services.teams.webhook_url', '');
    }

    /**
     * Send notification to Microsoft Teams.
     */
    public function sendNotification(string $title, string $message, array $facts = [], string $color = '0078D4'): bool
    {
        if (empty($this->webhookUrl)) {
            Log::warning('Teams webhook URL not configured');
            return false;
        }

        try {
            $payload = [
                '@type' => 'MessageCard',
                '@context' => 'https://schema.org/extensions',
                'summary' => $title,
                'themeColor' => $color,
                'title' => $title,
                'sections' => [
                    [
                        'activityTitle' => $message,
                        'facts' => $facts,
                    ],
                ],
            ];

            $response = Http::timeout(10)
                ->retry(2, 100)
                ->post($this->webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Teams notification sent', ['title' => $title]);
                return true;
            }

            Log::error('Teams notification failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Teams notification exception', [
                'error' => $e->getMessage(),
                'title' => $title,
            ]);

            return false;
        }
    }

    /**
     * Send payment received alert.
     */
    public function sendPaymentReceivedAlert(\Modules\Billing\Models\Payment $payment): void
    {
        $invoice = $payment->invoice;
        $company = $payment->company;

        $facts = [
            ['name' => 'Company', 'value' => $company->name],
            ['name' => 'Invoice', 'value' => $invoice->invoice_number],
            ['name' => 'Amount', 'value' => '$' . number_format($payment->amount / 100, 2)],
            ['name' => 'Method', 'value' => ucfirst($payment->method)],
            ['name' => 'Date', 'value' => $payment->created_at->format('M d, Y H:i')],
        ];

        $this->sendNotification(
            '💰 Payment Received',
            "Payment of $" . number_format($payment->amount / 100, 2) . " received from {$company->name}",
            $facts,
            '28A745' // Green
        );
    }

    /**
     * Send quote accepted alert.
     */
    public function sendQuoteAcceptedAlert(\Modules\Billing\Models\Quote $quote): void
    {
        $company = $quote->company;

        $facts = [
            ['name' => 'Company', 'value' => $company->name],
            ['name' => 'Quote #', 'value' => $quote->id],
            ['name' => 'Amount', 'value' => '$' . number_format($quote->total / 100, 2)],
            ['name' => 'Accepted', 'value' => now()->format('M d, Y H:i')],
        ];

        $this->sendNotification(
            '✅ Quote Accepted',
            "Quote accepted by {$company->name}",
            $facts,
            '0078D4' // Blue
        );
    }

    /**
     * Send overdue invoice alert.
     */
    public function sendOverdueInvoiceAlert(\Modules\Billing\Models\Invoice $invoice): void
    {
        $company = $invoice->company;
        $daysOverdue = now()->diffInDays($invoice->due_date, false);

        $facts = [
            ['name' => 'Company', 'value' => $company->name],
            ['name' => 'Invoice', 'value' => $invoice->invoice_number],
            ['name' => 'Amount', 'value' => '$' . number_format($invoice->total / 100, 2)],
            ['name' => 'Due Date', 'value' => $invoice->due_date->format('M d, Y')],
            ['name' => 'Days Overdue', 'value' => abs($daysOverdue)],
        ];

        $this->sendNotification(
            '⚠️ Invoice Overdue',
            "Invoice {$invoice->invoice_number} is " . abs($daysOverdue) . " days overdue",
            $facts,
            'FFC107' // Amber
        );
    }

    /**
     * Send contract expiration reminder.
     */
    public function sendContractExpiringAlert(\Modules\Billing\Models\Subscription $subscription, int $daysRemaining): void
    {
        $company = $subscription->company;

        $facts = [
            ['name' => 'Company', 'value' => $company->name],
            ['name' => 'Plan', 'value' => $subscription->plan_name ?? 'N/A'],
            ['name' => 'MRR', 'value' => '$' . number_format($subscription->monthly_amount / 100, 2)],
            ['name' => 'Expires', 'value' => $subscription->contract_end_date->format('M d, Y')],
            ['name' => 'Days Remaining', 'value' => $daysRemaining],
        ];

        $color = $daysRemaining <= 30 ? 'DC3545' : 'FFC107'; // Red if < 30 days, amber otherwise

        $this->sendNotification(
            '📅 Contract Expiring',
            "Contract for {$company->name} expires in {$daysRemaining} days",
            $facts,
            $color
        );
    }
}

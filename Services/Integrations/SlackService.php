<?php

namespace Modules\Billing\Services\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\Quote;
use Modules\Billing\Models\Invoice;

class SlackService
{
    protected string $webhookUrl;
    protected string $channel;

    public function __construct()
    {
        /** @var string $webhookUrl */
        $webhookUrl = config('services.slack.webhook_url', '');
        $this->webhookUrl = $webhookUrl;

        /** @var string $channel */
        $channel = config('services.slack.channel', '#billing');
        $this->channel = $channel;
    }

    /**
     * Send a notification to Slack.
     * 
     * @param array<int, mixed> $blocks
     */
    public function sendNotification(string $channel, string $message, array $blocks = []): bool
    {
        if (empty($this->webhookUrl)) {
            Log::warning('Slack webhook URL not configured');
            return false;
        }

        try {
            $payload = [
                'channel' => $channel,
                'text' => $message,
            ];

            if (!empty($blocks)) {
                $payload['blocks'] = $blocks;
            }

            $response = Http::timeout(10)
                ->retry(2, 100)
                ->post($this->webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Slack notification sent', ['channel' => $channel]);
                return true;
            }

            Log::error('Slack notification failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Slack notification exception', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send payment received alert.
     */
    public function sendPaymentReceivedAlert(Payment $payment): void
    {
        $message = "💰 Payment Received: $" . number_format($payment->amount, 2);
        
        $blocks = [
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "*Payment Received*\n$" . number_format($payment->amount, 2),
                ],
            ],
            [
                'type' => 'section',
                'fields' => [
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Company:*\n" . ($payment->company->name ?? 'N/A'),
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Method:*\n" . ucfirst($payment->payment_method),
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Invoice:*\n" . ($payment->invoice->invoice_number ?? 'N/A'),
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Date:*\n" . $payment->payment_date->format('M d, Y'),
                    ],
                ],
            ],
        ];

        $this->sendNotification($this->channel, $message, $blocks);
    }

    /**
     * Send quote accepted alert.
     */
    public function sendQuoteAcceptedAlert(Quote $quote): void
    {
        $message = "✅ Quote Accepted: " . $quote->company->name;
        
        $blocks = [
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "*Quote Accepted!*",
                ],
            ],
            [
                'type' => 'section',
                'fields' => [
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Company:*\n" . $quote->company->name,
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Total:*\n$" . number_format($quote->total ?? 0, 2),
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Signer:*\n" . ($quote->signer_name ?? 'Unknown'),
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Date:*\n" . now()->format('M d, Y'),
                    ],
                ],
            ],
        ];

        $this->sendNotification($this->channel, $message, $blocks);
    }

    /**
     * Send anomaly alert for invoices with unusual amounts.
     */
    public function sendAnomalyAlert(Invoice $invoice, float $score): void
    {
        $message = "⚠️ Invoice Anomaly Detected: " . $invoice->invoice_number;
        
        $blocks = [
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "*Anomaly Detected*\nInvoice amount is unusual",
                ],
            ],
            [
                'type' => 'section',
                'fields' => [
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Invoice:*\n" . $invoice->invoice_number,
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Amount:*\n$" . number_format($invoice->total, 2),
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Company:*\n" . $invoice->company->name,
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Score:*\n" . round($score, 2),
                    ],
                ],
            ],
        ];

        $this->sendNotification($this->channel, $message, $blocks);
    }
}

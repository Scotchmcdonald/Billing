<?php

namespace Modules\Billing\Services\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Payment;

class PayPalService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $mode;
    protected string $baseUrl;

    public function __construct()
    {
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $this->mode = config('services.paypal.mode', 'sandbox');
        $this->baseUrl = $this->mode === 'live' 
            ? 'https://api-m.paypal.com' 
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Get OAuth access token
     */
    protected function getAccessToken(): ?string
    {
        try {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post("{$this->baseUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials'
                ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('PayPal OAuth failed', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('PayPal OAuth error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Create PayPal order for invoice
     */
    public function createOrder(Invoice $invoice): ?array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->post("{$this->baseUrl}/v2/checkout/orders", [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [[
                        'reference_id' => $invoice->invoice_number,
                        'description' => "Invoice #{$invoice->invoice_number}",
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => number_format($invoice->total_amount / 100, 2, '.', '')
                        ]
                    ]],
                    'application_context' => [
                        'return_url' => route('billing.paypal.return', ['invoice' => $invoice->id]),
                        'cancel_url' => route('billing.paypal.cancel', ['invoice' => $invoice->id]),
                        'brand_name' => config('app.name'),
                        'user_action' => 'PAY_NOW'
                    ]
                ]);

            if ($response->successful()) {
                Log::info('PayPal order created', [
                    'invoice_id' => $invoice->id,
                    'order_id' => $response->json('id')
                ]);
                return $response->json();
            }

            Log::error('PayPal order creation failed', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('PayPal order creation error', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Capture payment for approved order
     */
    public function captureOrder(string $orderId): ?array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

            if ($response->successful()) {
                Log::info('PayPal payment captured', ['order_id' => $orderId]);
                return $response->json();
            }

            Log::error('PayPal capture failed', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('PayPal capture error', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create subscription billing plan
     */
    public function createSubscriptionPlan(string $name, int $priceInCents, string $interval = 'MONTH'): ?array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->post("{$this->baseUrl}/v1/billing/plans", [
                    'product_id' => config('services.paypal.product_id'),
                    'name' => $name,
                    'billing_cycles' => [[
                        'frequency' => [
                            'interval_unit' => $interval,
                            'interval_count' => 1
                        ],
                        'tenure_type' => 'REGULAR',
                        'sequence' => 1,
                        'total_cycles' => 0,
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'value' => number_format($priceInCents / 100, 2, '.', ''),
                                'currency_code' => 'USD'
                            ]
                        ]
                    ]],
                    'payment_preferences' => [
                        'auto_bill_outstanding' => true,
                        'payment_failure_threshold' => 3
                    ]
                ]);

            if ($response->successful()) {
                Log::info('PayPal plan created', ['plan_id' => $response->json('id')]);
                return $response->json();
            }

            Log::error('PayPal plan creation failed', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('PayPal plan creation error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Process refund
     */
    public function refund(Payment $payment, int $amountInCents): ?array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        $captureId = $payment->gateway_payment_id;

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->post("{$this->baseUrl}/v2/payments/captures/{$captureId}/refund", [
                    'amount' => [
                        'value' => number_format($amountInCents / 100, 2, '.', ''),
                        'currency_code' => 'USD'
                    ],
                    'note_to_payer' => 'Refund for invoice'
                ]);

            if ($response->successful()) {
                Log::info('PayPal refund processed', [
                    'payment_id' => $payment->id,
                    'refund_id' => $response->json('id')
                ]);
                return $response->json();
            }

            Log::error('PayPal refund failed', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('PayPal refund error', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}

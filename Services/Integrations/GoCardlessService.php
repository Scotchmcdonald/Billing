<?php

namespace Modules\Billing\Services\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Payment;

class GoCardlessService
{
    protected string $accessToken;
    protected string $environment;
    protected string $baseUrl;

    public function __construct()
    {
        $this->accessToken = config('services.gocardless.access_token');
        $this->environment = config('services.gocardless.environment', 'sandbox');
        $this->baseUrl = $this->environment === 'live'
            ? 'https://api.gocardless.com'
            : 'https://api-sandbox.gocardless.com';
    }

    /**
     * Create customer in GoCardless
     */
    public function createCustomer(Company $company): ?array
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->withHeaders([
                    'GoCardless-Version' => '2015-07-06',
                    'Content-Type' => 'application/json'
                ])
                ->timeout(30)
                ->post("{$this->baseUrl}/customers", [
                    'customers' => [
                        'email' => $company->email,
                        'given_name' => $company->primary_contact_first_name ?? '',
                        'family_name' => $company->primary_contact_last_name ?? '',
                        'company_name' => $company->name,
                        'metadata' => [
                            'company_id' => (string) $company->id
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $customerId = $response->json('customers.id');
                
                Log::info('GoCardless customer created', [
                    'company_id' => $company->id,
                    'customer_id' => $customerId
                ]);

                return $response->json('customers');
            }

            Log::error('GoCardless customer creation failed', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('GoCardless customer creation error', [
                'company_id' => $company->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create mandate (authorization for direct debit)
     */
    public function createMandate(string $customerId): ?array
    {
        try {
            // First create bank account
            $bankAccountResponse = Http::withToken($this->accessToken)
                ->withHeaders([
                    'GoCardless-Version' => '2015-07-06',
                    'Content-Type' => 'application/json'
                ])
                ->timeout(30)
                ->post("{$this->baseUrl}/customer_bank_accounts", [
                    'customer_bank_accounts' => [
                        'account_holder_name' => 'Account Holder',
                        'account_number' => '55779911',
                        'branch_code' => '200000',
                        'country_code' => 'GB',
                        'currency' => 'GBP',
                        'links' => [
                            'customer' => $customerId
                        ]
                    ]
                ]);

            if (!$bankAccountResponse->successful()) {
                Log::error('GoCardless bank account creation failed', ['response' => $bankAccountResponse->body()]);
                return null;
            }

            $bankAccountId = $bankAccountResponse->json('customer_bank_accounts.id');

            // Create mandate
            $mandateResponse = Http::withToken($this->accessToken)
                ->withHeaders([
                    'GoCardless-Version' => '2015-07-06',
                    'Content-Type' => 'application/json'
                ])
                ->timeout(30)
                ->post("{$this->baseUrl}/mandates", [
                    'mandates' => [
                        'scheme' => 'bacs',
                        'links' => [
                            'customer_bank_account' => $bankAccountId
                        ]
                    ]
                ]);

            if ($mandateResponse->successful()) {
                Log::info('GoCardless mandate created', [
                    'customer_id' => $customerId,
                    'mandate_id' => $mandateResponse->json('mandates.id')
                ]);
                return $mandateResponse->json('mandates');
            }

            Log::error('GoCardless mandate creation failed', ['response' => $mandateResponse->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('GoCardless mandate creation error', [
                'customer_id' => $customerId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create payment
     */
    public function createPayment(Invoice $invoice, string $mandateId): ?array
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->withHeaders([
                    'GoCardless-Version' => '2015-07-06',
                    'Content-Type' => 'application/json'
                ])
                ->timeout(30)
                ->post("{$this->baseUrl}/payments", [
                    'payments' => [
                        'amount' => $invoice->total_amount - $invoice->paid_amount, // in pence
                        'currency' => 'GBP',
                        'description' => "Invoice #{$invoice->invoice_number}",
                        'metadata' => [
                            'invoice_id' => (string) $invoice->id,
                            'invoice_number' => $invoice->invoice_number
                        ],
                        'links' => [
                            'mandate' => $mandateId
                        ]
                    ]
                ]);

            if ($response->successful()) {
                Log::info('GoCardless payment created', [
                    'invoice_id' => $invoice->id,
                    'payment_id' => $response->json('payments.id')
                ]);
                return $response->json('payments');
            }

            Log::error('GoCardless payment creation failed', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('GoCardless payment creation error', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $paymentId): ?string
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->withHeaders([
                    'GoCardless-Version' => '2015-07-06'
                ])
                ->timeout(30)
                ->get("{$this->baseUrl}/payments/{$paymentId}");

            if ($response->successful()) {
                return $response->json('payments.status');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('GoCardless payment status check error', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Handle webhook event
     */
    public function handleWebhook(array $payload): void
    {
        try {
            foreach ($payload['events'] ?? [] as $event) {
                $action = $event['action'] ?? '';
                $resourceType = $event['resource_type'] ?? '';

                Log::info('GoCardless webhook received', [
                    'action' => $action,
                    'resource_type' => $resourceType
                ]);

                if ($resourceType === 'payments') {
                    $this->handlePaymentEvent($event);
                } elseif ($resourceType === 'mandates') {
                    $this->handleMandateEvent($event);
                }
            }
        } catch (\Exception $e) {
            Log::error('GoCardless webhook processing error', ['error' => $e->getMessage()]);
        }
    }

    protected function handlePaymentEvent(array $event): void
    {
        $paymentId = $event['links']['payment'] ?? null;
        $action = $event['action'] ?? '';

        if (!$paymentId) {
            return;
        }

        // Find associated payment in our system
        $payment = Payment::where('gateway_payment_id', $paymentId)->first();

        if ($payment) {
            switch ($action) {
                case 'confirmed':
                    $payment->update(['status' => 'completed']);
                    Log::info('GoCardless payment confirmed', ['payment_id' => $payment->id]);
                    break;
                case 'failed':
                    $payment->update(['status' => 'failed']);
                    Log::warning('GoCardless payment failed', ['payment_id' => $payment->id]);
                    break;
            }
        }
    }

    protected function handleMandateEvent(array $event): void
    {
        $mandateId = $event['links']['mandate'] ?? null;
        $action = $event['action'] ?? '';

        Log::info('GoCardless mandate event', [
            'mandate_id' => $mandateId,
            'action' => $action
        ]);
    }
}

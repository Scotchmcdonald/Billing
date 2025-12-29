<?php

namespace Modules\Billing\Services\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Payment;

class XeroService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $tenantId;
    protected string $accessToken;

    public function __construct()
    {
        /** @var string $clientId */
        $clientId = config('services.xero.client_id', '');
        $this->clientId = $clientId;

        /** @var string $clientSecret */
        $clientSecret = config('services.xero.client_secret', '');
        $this->clientSecret = $clientSecret;

        /** @var string $tenantId */
        $tenantId = config('services.xero.tenant_id', '');
        $this->tenantId = $tenantId;

        /** @var string $accessToken */
        $accessToken = config('services.xero.access_token', '');
        $this->accessToken = $accessToken;
    }

    /**
     * Sync invoice to Xero.
     */
    public function syncInvoice(Invoice $invoice): ?string
    {
        if (empty($this->accessToken)) {
            Log::warning('Xero access token not configured');
            return null;
        }

        try {
            $payload = [
                'Type' => 'ACCREC',
                'Contact' => [
                    'Name' => $invoice->company->name,
                ],
                'DateString' => $invoice->created_at?->format('Y-m-d'),
                'DueDateString' => $invoice->due_date->format('Y-m-d'),
                'LineAmountTypes' => 'Exclusive',
                'LineItems' => $this->buildLineItems($invoice),
                'Status' => $this->mapInvoiceStatus($invoice->status),
                'InvoiceNumber' => $invoice->invoice_number,
                'Reference' => "Billing Invoice #{$invoice->id}",
            ];

            $response = Http::withToken($this->accessToken)
                ->withHeaders([
                    'xero-tenant-id' => $this->tenantId,
                    'Accept' => 'application/json',
                ])
                ->timeout(15)
                ->retry(2, 100)
                ->post('https://api.xero.com/api.xro/2.0/Invoices', [
                    'Invoices' => [$payload],
                ]);

            if ($response->successful()) {
                /** @var string|int $id */
                $id = $response->json('Invoices.0.InvoiceID');
                $xeroInvoiceId = strval($id);
                
                Log::info('Invoice synced to Xero', [
                    'invoice_id' => $invoice->id,
                    'xero_id' => $xeroInvoiceId,
                ]);

                // Update invoice with Xero ID
                $invoice->update(['xero_invoice_id' => $xeroInvoiceId]);

                return $xeroInvoiceId;
            }

            Log::error('Xero invoice sync failed', [
                'status' => $response->status(),
                'error' => $response->json('Message'),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Xero invoice sync exception', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id,
            ]);

            return null;
        }
    }

    /**
     * Sync payment to Xero.
     */
    public function syncPayment(Payment $payment): ?string
    {
        if (empty($this->accessToken) || empty($payment->invoice->xero_invoice_id)) {
            Log::warning('Cannot sync payment: missing Xero configuration or invoice not synced');
            return null;
        }

        try {
            /** @var string $accountCode */
            $accountCode = config('services.xero.payment_account_code', '1200');

            $payload = [
                'Invoice' => [
                    'InvoiceID' => $payment->invoice->xero_invoice_id,
                ],
                'Account' => [
                    'Code' => $accountCode,
                ],
                'Date' => $payment->created_at?->format('Y-m-d'),
                'Amount' => $payment->amount / 100,
                'Reference' => "Payment #{$payment->id}",
            ];

            $response = Http::withToken($this->accessToken)
                ->withHeaders([
                    'xero-tenant-id' => $this->tenantId,
                    'Accept' => 'application/json',
                ])
                ->timeout(15)
                ->retry(2, 100)
                ->post('https://api.xero.com/api.xro/2.0/Payments', [
                    'Payments' => [$payload],
                ]);

            if ($response->successful()) {
                /** @var string|int $id */
                $id = $response->json('Payments.0.PaymentID');
                $xeroPaymentId = strval($id);
                
                Log::info('Payment synced to Xero', [
                    'payment_id' => $payment->id,
                    'xero_id' => $xeroPaymentId,
                ]);

                return $xeroPaymentId;
            }

            Log::error('Xero payment sync failed', [
                'status' => $response->status(),
                'error' => $response->json('Message'),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Xero payment sync exception', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
            ]);

            return null;
        }
    }

    /**
     * Build line items from invoice billable entries.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildLineItems(Invoice $invoice): array
    {
        $lineItems = [];

        foreach ($invoice->billableEntries as $entry) {
            $lineItems[] = [
                'Description' => $entry->description,
                'Quantity' => $entry->quantity,
                'UnitAmount' => $entry->rate, // Removed / 100 as rate is likely float
                'AccountCode' => $this->getAccountCode($entry->type),
            ];
        }

        return $lineItems;
    }

    /**
     * Map internal invoice status to Xero status.
     */
    protected function mapInvoiceStatus(string $status): string
    {
        return match ($status) {
            'draft' => 'DRAFT',
            'sent', 'pending' => 'SUBMITTED',
            'paid' => 'PAID',
            'void', 'cancelled' => 'VOIDED',
            default => 'DRAFT',
        };
    }

    /**
     * Get Xero account code for entry type.
     */
    protected function getAccountCode(string $type): string
    {
        $key = match ($type) {
            'service' => 'services.xero.service_account_code',
            'product' => 'services.xero.product_account_code',
            'labor' => 'services.xero.labor_account_code',
            default => 'services.xero.default_account_code',
        };

        $default = match ($type) {
            'service' => '4000',
            'product' => '4100',
            'labor' => '4200',
            default => '4000',
        };

        /** @var string $code */
        $code = config($key, $default);
        
        return $code;
    }
}

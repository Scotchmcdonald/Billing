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
        $this->clientId = config('services.xero.client_id', '');
        $this->clientSecret = config('services.xero.client_secret', '');
        $this->tenantId = config('services.xero.tenant_id', '');
        $this->accessToken = config('services.xero.access_token', '');
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
                'DateString' => $invoice->created_at->format('Y-m-d'),
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
                $xeroInvoiceId = $response->json('Invoices.0.InvoiceID');
                
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
            $payload = [
                'Invoice' => [
                    'InvoiceID' => $payment->invoice->xero_invoice_id,
                ],
                'Account' => [
                    'Code' => config('services.xero.payment_account_code', '1200'),
                ],
                'Date' => $payment->created_at->format('Y-m-d'),
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
                $xeroPaymentId = $response->json('Payments.0.PaymentID');
                
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
     */
    protected function buildLineItems(Invoice $invoice): array
    {
        $lineItems = [];

        foreach ($invoice->billableEntries as $entry) {
            $lineItems[] = [
                'Description' => $entry->description,
                'Quantity' => $entry->quantity,
                'UnitAmount' => $entry->price / 100,
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
        return match ($type) {
            'service' => config('services.xero.service_account_code', '4000'),
            'product' => config('services.xero.product_account_code', '4100'),
            'labor' => config('services.xero.labor_account_code', '4200'),
            default => config('services.xero.default_account_code', '4000'),
        };
    }
}

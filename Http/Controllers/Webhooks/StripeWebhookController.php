<?php

namespace Modules\Billing\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Payment;

class StripeWebhookController extends Controller
{
    /**
     * Handle Stripe Webhook events.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        $type = $payload['type'] ?? null;
        $data = $payload['data']['object'] ?? null;

        if (!$type || !$data) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        Log::info("Received Stripe Webhook: {$type}");

        try {
            switch ($type) {
                case 'invoice.payment_succeeded':
                    $this->handlePaymentSucceeded($data);
                    break;
                case 'invoice.payment_failed':
                    $this->handlePaymentFailed($data);
                    break;
                default:
                    Log::info("Unhandled Stripe event type: {$type}");
            }
        } catch (\Exception $e) {
            Log::error('Stripe Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook handling failed'], 500);
        }

        return response()->json(['status' => 'success']);
    }

    protected function handlePaymentSucceeded($data)
    {
        $stripeInvoiceId = $data['id'];
        $amountPaid = ($data['amount_paid'] ?? 0) / 100; // Stripe is in cents

        $invoice = Invoice::where('stripe_invoice_id', $stripeInvoiceId)->first();

        if ($invoice) {
            $invoice->update([
                'status' => 'paid',
                'paid_amount' => $invoice->paid_amount + $amountPaid,
            ]);

            // Record Payment
            Payment::create([
                'invoice_id' => $invoice->id,
                'company_id' => $invoice->company_id,
                'amount' => $amountPaid,
                'payment_method' => 'stripe_card', // Simplified, could be derived from charge
                'payment_reference' => $data['payment_intent'] ?? $stripeInvoiceId,
                'payment_date' => now(),
                'created_by' => 1, // System user ID
                'notes' => 'Auto-recorded via Stripe Webhook',
            ]);
            
            Log::info("Invoice {$invoice->invoice_number} marked as paid via Stripe.");
        } else {
            Log::warning("Stripe Invoice ID {$stripeInvoiceId} not found in system.");
        }
    }

    protected function handlePaymentFailed($data)
    {
        $stripeInvoiceId = $data['id'];
        $invoice = Invoice::where('stripe_invoice_id', $stripeInvoiceId)->first();

        if ($invoice) {
            Log::info("Payment failed for Invoice {$invoice->invoice_number}.");
            // Logic to notify user or retry could go here
        }
    }
}

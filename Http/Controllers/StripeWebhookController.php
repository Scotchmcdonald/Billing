<?php

namespace Modules\Billing\Http\Controllers;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use Illuminate\Http\Request;
use Modules\Billing\Models\BillingLog;
use Modules\Billing\Models\Company;

class StripeWebhookController extends CashierController
{
    /**
     * Handle invoice payment succeeded.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleInvoicePaymentSucceeded($payload)
    {
        $company = $this->getUserByStripeId($payload['data']['object']['customer']);

        if ($company) {
            BillingLog::create([
                'company_id' => $company->id,
                'action' => 'invoice.payment_succeeded',
                'description' => 'Invoice ' . $payload['data']['object']['id'] . ' paid.',
                'payload' => $payload,
            ]);
        }

        return parent::handleInvoicePaymentSucceeded($payload);
    }

    /**
     * Handle invoice payment failed.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleInvoicePaymentFailed($payload)
    {
        $company = $this->getUserByStripeId($payload['data']['object']['customer']);

        if ($company) {
            BillingLog::create([
                'company_id' => $company->id,
                'action' => 'invoice.payment_failed',
                'description' => 'Invoice ' . $payload['data']['object']['id'] . ' failed.',
                'payload' => $payload,
            ]);

            // Smart Dunning: Notify Billing Admin
            // In a real app, this would trigger a Notification class
            // Notification::send($company->billingAdmins, new PaymentFailedNotification($payload['data']['object']['id']));
            
            // For now, we log the intent to notify
            \Illuminate\Support\Facades\Log::info("Smart Dunning: Triggering 'One-Click Update' notification for Company ID: {$company->id}");
        }

        return parent::handleInvoicePaymentFailed($payload);
    }

    /**
     * Get the billable entity instance by Stripe ID.
     * Overriding to support Company model instead of User.
     *
     * @param  string  $stripeId
     * @return \Modules\Billing\Models\Company|null
     */
    protected function getUserByStripeId($stripeId)
    {
        return Company::where('stripe_id', $stripeId)->first();
    }
}

<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Company;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentGatewayService
{
    public function __construct()
    {
        Stripe::setApiKey(config('cashier.secret'));
    }

    public function createSetupIntent(Company $company)
    {
        return $company->createSetupIntent();
    }

    public function charge(Company $company, $amount, $paymentMethodId, $description = null)
    {
        try {
            $idempotencyKey = 'charge_' . $company->id . '_' . time() . '_' . uniqid();
            
            $payment = $company->charge($amount, $paymentMethodId, [
                'description' => $description,
                'off_session' => true, // For recurring or background charges
                'confirm' => true,
            ], [
                'idempotency_key' => $idempotencyKey,
            ]);
            return $payment;
        } catch (\Exception $e) {
            // Log error
            throw $e;
        }
    }

    public function createSubscription(Company $company, $priceId)
    {
        return $company->newSubscription('default', $priceId)->create();
    }
    
    public function getInvoices(Company $company)
    {
        return $company->invoices();
    }
}

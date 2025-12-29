<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Company;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentGatewayService
{
    public function __construct()
    {
        /** @var string $secret */
        $secret = config('cashier.secret');
        Stripe::setApiKey($secret);
    }

    /**
     * @return \Stripe\SetupIntent
     */
    public function createSetupIntent(Company $company)
    {
        return $company->createSetupIntent();
    }

    /**
     * @param int $amount
     * @param string $paymentMethodId
     * @param string|null $description
     * @return \Laravel\Cashier\Payment
     */
    public function charge(Company $company, int $amount, string $paymentMethodId, ?string $description = null)
    {
        try {
            $idempotencyKey = 'charge_' . $company->id . '_' . time() . '_' . uniqid();
            
            // Note: Removed idempotency_key as 4th argument because Company::charge() only accepts 3 arguments.
            // If idempotency is required, consider using the Stripe client directly.
            $payment = $company->charge($amount, $paymentMethodId, [
                'description' => $description,
                'off_session' => true, // For recurring or background charges
                'confirm' => true,
            ]);
            return $payment;
        } catch (\Exception $e) {
            // Log error
            throw $e;
        }
    }

    /**
     * @param string $priceId
     * @return \Laravel\Cashier\Subscription
     */
    public function createSubscription(Company $company, string $priceId)
    {
        return $company->newSubscription('default', $priceId)->create();
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\Billing\Models\Invoice, \Modules\Billing\Models\Company>
     */
    public function getInvoices(Company $company)
    {
        return $company->invoices();
    }
}

<?php

namespace Modules\Billing\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Billing\Models\BillingSettings;

class BillingConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load Stripe configuration from database
        try {
            $stripeKey = BillingSettings::where('key', 'stripe_key')->first()?->value;
            $stripeSecret = BillingSettings::where('key', 'stripe_secret')->first()?->value;
            $stripeWebhookSecret = BillingSettings::where('key', 'stripe_webhook_secret')->first()?->value;

            if ($stripeKey) {
                config(['cashier.key' => $stripeKey]);
            }

            if ($stripeSecret) {
                config(['cashier.secret' => $stripeSecret]);
            }

            if ($stripeWebhookSecret) {
                config(['cashier.webhook.secret' => $stripeWebhookSecret]);
            }
        } catch (\Exception $e) {
            // Silent fail during migrations or when table doesn't exist yet
        }
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }
}

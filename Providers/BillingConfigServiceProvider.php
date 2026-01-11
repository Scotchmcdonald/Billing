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
        // Integration configurations loaded from database
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }
}

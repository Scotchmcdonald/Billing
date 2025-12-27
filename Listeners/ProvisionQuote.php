<?php

namespace Modules\Billing\Listeners;

use Modules\Billing\Events\QuoteApproved;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Subscription;
use Illuminate\Support\Facades\Log;

class ProvisionQuote
{
    public function handle(QuoteApproved $event)
    {
        $quote = $event->quote;
        
        // 1. Create Company (if new prospect)
        if (!$quote->company_id) {
            $company = Company::create([
                'name' => $quote->prospect_name,
                'email' => $quote->prospect_email,
                'is_active' => true,
            ]);
            $quote->company_id = $company->id;
            $quote->save();
        } else {
            $company = $quote->company;
        }

        // 2. Create Subscriptions
        foreach ($quote->lineItems as $item) {
            if ($item->product_id) {
                // Assuming product has info about subscription type
                // For now, just create a subscription record
                Subscription::create([
                    'company_id' => $company->id,
                    'name' => $item->description,
                    'stripe_status' => 'active',
                    'quantity' => $item->quantity,
                ]);
            }
        }

        Log::info("Quote #{$quote->id} provisioned for Company #{$company->id}");
    }
}

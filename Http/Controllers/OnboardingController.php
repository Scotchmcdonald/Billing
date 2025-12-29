<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Subscription;
use Modules\Crm\Models\Contact;

class OnboardingController extends Controller
{
    /**
     * Show the onboarding wizard
     */
    public function index()
    {
        return view('billing::onboarding.wizard');
    }

    /**
     * Submit the onboarding form
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'industry' => 'nullable|string',
            'company_size' => 'nullable|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip' => 'required|string',
            'billing_first_name' => 'required|string',
            'billing_last_name' => 'required|string',
            'billing_email' => 'required|email',
            'billing_phone' => 'required|string',
            'billing_title' => 'nullable|string',
            'payment_method' => 'required|in:card,invoice',
            'subscription_tier' => 'required|in:basic,professional,enterprise',
        ]);

        $company = null;

        DB::transaction(function () use ($validated, &$company) {
            // Create company
            $company = Company::create([
                'name' => $validated['company_name'],
                'industry' => $validated['industry'],
                'company_size' => $validated['company_size'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'zip' => $validated['zip'],
                'status' => 'active',
                'onboarded_at' => now(),
            ]);

            // Create billing contact
            $contact = Contact::create([
                'company_id' => $company->id,
                'first_name' => $validated['billing_first_name'],
                'last_name' => $validated['billing_last_name'],
                'email' => $validated['billing_email'],
                'phone' => $validated['billing_phone'],
                'title' => $validated['billing_title'],
                'is_billing_contact' => true,
                'is_primary' => true,
            ]);

            // Create subscription
            $tierPricing = [
                'basic' => 99,
                'professional' => 299,
                'enterprise' => 799,
            ];

            $subscription = Subscription::create([
                'company_id' => $company->id,
                'tier' => $validated['subscription_tier'],
                'status' => $validated['payment_method'] === 'card' ? 'pending_payment' : 'active',
                'billing_cycle' => 'monthly',
                'base_price' => $tierPricing[$validated['subscription_tier']],
                'starts_at' => now(),
            ]);

            // Log onboarding activity
            activity()
                ->performedOn($company)
                ->causedBy(auth()->user())
                ->withProperties([
                    'tier' => $validated['subscription_tier'],
                    'payment_method' => $validated['payment_method'],
                ])
                ->log('Client onboarded');
        });

        // Return appropriate response
        if ($validated['payment_method'] === 'card') {
            // Create Stripe checkout session
            $stripeUrl = $this->createStripeCheckout($company, $validated['subscription_tier']);

            return response()->json([
                'success' => true,
                'stripe_url' => $stripeUrl,
            ]);
        }

        return response()->json([
            'success' => true,
            'redirect_url' => route('billing.portal.dashboard', $company->slug),
        ]);
    }

    /**
     * Create Stripe checkout session
     */
    private function createStripeCheckout(Company $company, string $tier): string
    {
        // TODO: Implement Stripe checkout session creation
        // This is a placeholder for the actual Stripe integration
        return route('billing.portal.dashboard', $company->slug);
    }
}

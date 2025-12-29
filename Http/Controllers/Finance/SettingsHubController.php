<?php

namespace Modules\Billing\Http\Controllers\Finance;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class SettingsHubController extends Controller
{
    /**
     * Display the consolidated settings hub with tabs
     */
    public function index(Request $request)
    {
        return view('billing::finance.settings-hub');
    }

    /**
     * Update Stripe settings
     */
    public function updateStripe(Request $request)
    {
        $validated = $request->validate([
            'stripe_secret_key' => 'nullable|string',
            'stripe_publishable_key' => 'nullable|string',
            'stripe_webhook_secret' => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            setting([$key => $value]);
        }

        return back()->with('success', 'Stripe settings updated successfully.');
    }

    /**
     * Update QuickBooks settings
     */
    public function updateQuickBooks(Request $request)
    {
        $validated = $request->validate([
            'qbo_client_id' => 'nullable|string',
            'qbo_client_secret' => 'nullable|string',
            'qbo_realm_id' => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            setting([$key => $value]);
        }

        return back()->with('success', 'QuickBooks settings updated successfully.');
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'invoice_prefix' => 'nullable|string|max:10',
            'invoice_start_number' => 'nullable|integer|min:1',
            'payment_terms' => 'nullable|integer|min:0',
            'late_fee_percentage' => 'nullable|numeric|min:0|max:100',
            'invitation_timeout' => 'nullable|integer|min:1',
        ]);

        foreach ($validated as $key => $value) {
            setting([$key => $value]);
        }

        return back()->with('success', 'General settings updated successfully.');
    }

    /**
     * Generic update for other integrations
     */
    public function update(Request $request)
    {
        $input = $request->except(['_token', 'integration']);
        
        foreach ($input as $key => $value) {
            setting([$key => $value]);
        }

        $integration = ucfirst($request->input('integration', 'Settings'));
        return back()->with('success', "{$integration} settings updated successfully.");
    }

    /**
     * Handle Xero OAuth Callback
     */
    public function handleXeroCallback(Request $request)
    {
        // Placeholder for Xero OAuth flow
        // In a real implementation, this would exchange the code for an access token
        
        return redirect()->route('billing.finance.settings-hub', ['tab' => 'integrations'])
            ->with('success', 'Xero connected successfully (Placeholder).');
    }
}

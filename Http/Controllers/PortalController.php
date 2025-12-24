<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Services\BillingAuthorizationService;
use Modules\Billing\Services\PaymentGatewayService;
use Modules\Billing\Models\Company;
use Illuminate\Support\Facades\Auth;

class PortalController extends Controller
{
    protected $authService;
    protected $paymentService;

    public function __construct(BillingAuthorizationService $authService, PaymentGatewayService $paymentService)
    {
        $this->authService = $authService;
        $this->paymentService = $paymentService;
    }

    public function entry()
    {
        $user = Auth::user();
        $companies = $this->authService->getAuthorizedCompanies($user);

        if ($companies->isEmpty()) {
            return view('billing::portal.no_company');
        }

        // If only one company, redirect to it
        if ($companies->count() === 1) {
            return redirect()->route('billing.portal.dashboard', ['company' => $companies->first()->id]);
        }

        // Otherwise show selector (TODO: Build selector view)
        // For now, just redirect to first
        return redirect()->route('billing.portal.dashboard', ['company' => $companies->first()->id]);
    }

    public function dashboard(Company $company)
    {
        // Ensure Stripe Customer exists
        if (!$company->stripe_id) {
            $company->createAsStripeCustomer();
        }

        return view('billing::portal.dashboard', [
            'company' => $company,
            'invoices' => $this->paymentService->getInvoices($company),
        ]);
    }

    public function paymentWizard(Company $company)
    {
        return view('billing::payment.wizard', [
            'company' => $company,
            'step' => 1,
            'paymentMethod' => 'cc',
            'fee' => 42.50
        ]);
    }

    public function paymentMethods(Company $company)
    {
        // Authorization check is handled by middleware for 'view', 
        // but we need to check 'manage' permission for this specific action
        if (!$this->authService->canManageBilling(Auth::user(), $company)) {
            abort(403, 'You do not have permission to manage payment methods.');
        }

        // Smart Payment Routing: Check balance
        // Note: balance() returns a formatted string usually, but rawBalance() or similar might be needed.
        // For this example, we assume we can get a raw float or we parse it.
        // Cashier's balance() returns a string. We'd need to check Stripe directly or use a cached value.
        // Let's assume we have a method or logic to get the raw amount.
        // For now, we'll pass a flag if we detect a high balance scenario (mocked).
        $highValueTransaction = false; 
        // if ($company->rawBalance() > 100000) { $highValueTransaction = true; }

        $intent = $this->paymentService->createSetupIntent($company);

        return view('billing::portal.payment_methods', [
            'company' => $company,
            'intent' => $intent,
            'highValueTransaction' => $highValueTransaction,
        ]);
    }

    public function invoices(Company $company)
    {
        return view('billing::portal.invoices', [
            'company' => $company,
            'invoices' => $this->paymentService->getInvoices($company),
        ]);
    }

    public function team(Company $company)
    {
        if (!$this->authService->canManageBilling(Auth::user(), $company)) {
            abort(403, 'You do not have permission to manage the team.');
        }

        return view('billing::portal.team', [
            'company' => $company,
            'users' => $company->users,
        ]);
    }
}

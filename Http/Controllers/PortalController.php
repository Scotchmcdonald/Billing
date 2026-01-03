<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Services\BillingAuthorizationService;
use Modules\Billing\Services\PaymentGatewayService;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

use Modules\Billing\Models\Quote;
use Modules\Billing\Events\QuoteApproved;
use Modules\Billing\Events\QuoteRejected;

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
        
        // If admin, redirect to portal access page
        if ($user->isAdmin() || $user->can('finance.admin')) {
            return redirect()->route('billing.finance.portal-access');
        }

        $companies = $this->authService->getAuthorizedCompanies($user);

        if ($companies->isEmpty()) {
            return view('billing::portal.no_company');
        }

        // If only one company, redirect to it
        if ($companies->count() === 1) {
            return redirect()->route('billing.portal.dashboard', ['company' => $companies->first()->id]);
        }

        // Otherwise show selector
        return view('billing::portal.company_selector', ['companies' => $companies]);
    }

    public function dashboard(Company $company)
    {
        // Ensure Stripe Customer exists
        try {
            if (! $company->stripe_id) {
                $company->createAsStripeCustomer();
            }
        } catch (\Exception $e) {
            report($e);
        }

        $user = Auth::user();
        $companies = $this->authService->getAuthorizedCompanies($user);
        $hasMultipleCompanies = $companies->count() > 1;

        $invoices = [];
        $payments = [];
        $quotes = [];
        $paymentMethods = collect();

        try {
            // Fetch local invoices
            $invoices = $company->invoices()->orderBy('issue_date', 'desc')->get();
        } catch (\Exception $e) {
            report($e);
        }

        try {
            $payments = $company->payments()->orderBy('payment_date', 'desc')->get();
        } catch (\Exception $e) {
            report($e);
        }

        try {
            $quotes = $company->quotes()->whereIn('status', ['sent', 'accepted', 'rejected'])->orderBy('created_at', 'desc')->get();
        } catch (\Exception $e) {
            report($e);
        }

        try {
            $paymentMethods = $company->paymentMethods();
        } catch (\Exception $e) {
            report($e);
        }

        $totalSubsidy = 0;
        if ($company->pricing_tier === 'non_profit') {
            foreach ($invoices as $invoice) {
                foreach ($invoice->lineItems as $item) {
                    $standard = $item->standard_unit_price ?? $item->unit_price;
                    if ($standard > $item->unit_price) {
                        $totalSubsidy += ($standard - $item->unit_price) * $item->quantity;
                    }
                }
            }
        }

        return view('billing::portal.dashboard', [
            'company' => $company,
            'invoices' => $invoices,
            'totalSubsidy' => $totalSubsidy,
            'payments' => $payments,
            'quotes' => $quotes,
            'subscriptions' => $company->subscriptions,
            'paymentMethods' => $paymentMethods,
            'hasMultipleCompanies' => $hasMultipleCompanies,
        ]);
    }

    public function showQuote(Company $company, int $id)
    {
        $quote = $company->quotes()->with('lineItems')->findOrFail($id);
        
        // Track view
        if (!$quote->viewed_at) {
            $quote->update(['viewed_at' => now()]);
            
            \Modules\Billing\Models\BillingLog::create([
                'company_id' => $company->id,
                'event' => 'quote.viewed',
                'description' => "Quote #{$quote->quote_number} viewed in portal by " . Auth::user()->name,
                'payload' => ['quote_id' => $quote->id, 'user_id' => Auth::id()],
                'level' => 'info'
            ]);
        }

        return view('billing::portal.quotes.show', compact('company', 'quote'));
    }

    public function acceptQuote(Request $request, Company $company, int $id)
    {
        $quote = $company->quotes()->with('lineItems')->findOrFail($id);
        
        $request->validate([
            'terms_accepted' => 'required|accepted',
            'notes' => 'nullable|string',
            'billing_frequency' => 'required|in:monthly,annually',
        ]);

        if ($quote->is_accepted) {
            return back()->with('error', 'This quote has already been accepted.');
        }

        if ($quote->valid_until < now()) {
            return back()->with('error', 'This quote has expired.');
        }

        // Update prices based on selected frequency
        $frequency = $request->billing_frequency;
        $total = 0;

        foreach ($quote->lineItems as $item) {
            $price = $frequency === 'monthly' 
                ? ($item->unit_price_monthly ?? $item->unit_price) 
                : ($item->unit_price_annually ?? ($item->unit_price * 12));
            
            $subtotal = $price * $item->quantity;
            
            $item->update([
                'unit_price' => $price,
                'subtotal' => $subtotal,
            ]);
            
            $total += $subtotal;
        }

        $quote->update([
            'accepted_at' => now(),
            'accepted_by_name' => Auth::user()->name,
            'accepted_by_email' => Auth::user()->email,
            'status' => 'accepted',
            'billing_frequency' => $frequency,
            'total' => $total,
            'notes' => $quote->notes . ($request->notes ? "\n\nAcceptance Note: " . $request->notes : ''),
        ]);

        \Modules\Billing\Models\BillingLog::create([
            'company_id' => $company->id,
            'event' => 'quote.accepted',
            'description' => "Quote #{$quote->quote_number} accepted in portal by " . Auth::user()->name . " ({$frequency})",
            'payload' => ['quote_id' => $quote->id, 'user_id' => Auth::id(), 'billing_frequency' => $frequency],
            'level' => 'info'
        ]);

        event(new QuoteApproved($quote));

        return back()->with('success', 'Quote accepted successfully.');
    }

    public function rejectQuote(Request $request, Company $company, int $id)
    {
        $quote = $company->quotes()->findOrFail($id);
        
        $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        if ($quote->status !== 'sent') {
            return back()->with('error', 'Cannot reject this quote.');
        }

        $quote->update([
            'status' => 'rejected',
            'notes' => $quote->notes . "\n\nRejection Reason: " . $request->rejection_reason . " (by " . Auth::user()->name . ")",
        ]);

        \Modules\Billing\Models\BillingLog::create([
            'company_id' => $company->id,
            'event' => 'quote.rejected',
            'description' => "Quote #{$quote->quote_number} rejected in portal by " . Auth::user()->name,
            'payload' => ['quote_id' => $quote->id, 'reason' => $request->rejection_reason, 'user_id' => Auth::id()],
            'level' => 'warning'
        ]);

        event(new QuoteRejected($quote, $request->rejection_reason));

        return back()->with('success', 'Quote rejected.');
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

        $user = Auth::user();
        $companies = $this->authService->getAuthorizedCompanies($user);
        $hasMultipleCompanies = $companies->count() > 1;

        return view('billing::portal.payment_methods', [
            'company' => $company,
            'intent' => $intent,
            'highValueTransaction' => $highValueTransaction,
            'hasMultipleCompanies' => $hasMultipleCompanies,
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

    public function downloadPdf(Company $company, Invoice $invoice)
    {
        // Ensure invoice belongs to company
        if ($invoice->company_id !== $company->id) {
            abort(403);
        }

        $pdf = Pdf::loadView('billing::pdf.invoice', ['invoice' => $invoice]);
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }
}

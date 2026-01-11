<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\PortalController;
use Modules\Billing\Http\Controllers\FinanceController;
use Modules\Billing\Http\Controllers\PriceOverrideController;
use Modules\Billing\Http\Controllers\ExecutiveDashboardController;
use Modules\Billing\Http\Controllers\ContractController;
use Modules\Billing\Http\Controllers\CreditNoteController;
use Modules\Billing\Http\Controllers\DisputeController;
use Modules\Billing\Http\Controllers\RetainerController;
use Modules\Billing\Http\Controllers\AuditLogController;
use Modules\Billing\Http\Controllers\Finance\PreFlightController;
use Modules\Billing\Http\Controllers\Finance\ReportsHubController;
use Modules\Billing\Http\Controllers\Finance\SettingsHubController;
use Modules\Billing\Http\Controllers\Finance\InvoiceController;

// Public Quote Builder
Route::prefix('quote-builder')->group(function () {
    Route::get('/', [\Modules\Billing\Http\Controllers\PublicQuoteController::class, 'index'])->name('billing.public.quote.index');
    Route::post('/calculate', [\Modules\Billing\Http\Controllers\PublicQuoteController::class, 'calculate'])->name('billing.public.quote.calculate');
    Route::post('/submit', [\Modules\Billing\Http\Controllers\PublicQuoteController::class, 'store'])->name('billing.public.quote.store');
    Route::get('/view/{token}', [\Modules\Billing\Http\Controllers\PublicQuoteController::class, 'show'])->name('billing.public.quote.show');
    Route::post('/view/{token}/accept', [\Modules\Billing\Http\Controllers\PublicQuoteController::class, 'accept'])->name('billing.public.quote.accept');
    Route::post('/view/{token}/reject', [\Modules\Billing\Http\Controllers\PublicQuoteController::class, 'reject'])->name('billing.public.quote.reject');
});

// Public Payment
Route::get('/pay/{invoice}', [\Modules\Billing\Http\Controllers\PublicPaymentController::class, 'show'])
    ->name('billing.pay.show')
    ->middleware('signed');

// Public Invitation Acceptance
Route::get('/invitation/{token}', [\Modules\Billing\Http\Controllers\Auth\InvitationAcceptanceController::class, 'show'])->name('billing.invitation.accept');
Route::post('/invitation/{token}', [\Modules\Billing\Http\Controllers\Auth\InvitationAcceptanceController::class, 'store'])->name('billing.invitation.accept.store');

Route::middleware(['auth'])->group(function () {
    // Portal Entry - Redirects to first company or shows selector
    Route::get('/', [PortalController::class, 'entry'])->name('billing.portal.entry');

    // Finance Admin
    Route::prefix('finance')->middleware(['can:finance.admin'])->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('billing.finance.index');
        Route::get('/portal-access', [\Modules\Billing\Http\Controllers\Finance\PortalAccessController::class, 'index'])->name('billing.finance.portal-access');
        Route::get('/dashboard', [FinanceController::class, 'dashboard'])->name('billing.finance.dashboard');
        Route::get('/pre-flight', [FinanceController::class, 'preFlight'])->name('billing.finance.pre-flight');
        
        // Pre-Flight Enhanced
        Route::get('/pre-flight-enhanced', [PreFlightController::class, 'index'])->name('billing.finance.pre-flight-enhanced');
        Route::post('/pre-flight/{invoice}/approve', [PreFlightController::class, 'approve'])->name('billing.finance.pre-flight.approve');
        Route::post('/pre-flight/{invoice}/approve-and-send', [PreFlightController::class, 'approveAndSend'])->name('billing.finance.pre-flight.approve-and-send');
        Route::post('/pre-flight/{invoice}/send', [PreFlightController::class, 'send'])->name('billing.finance.pre-flight.send');
        Route::post('/pre-flight/bulk-approve', [PreFlightController::class, 'bulkApprove'])->name('billing.finance.pre-flight.bulk-approve');
        Route::post('/pre-flight/bulk-approve-and-send', [PreFlightController::class, 'bulkApproveAndSend'])->name('billing.finance.pre-flight.bulk-approve-and-send');
        Route::post('/pre-flight/approve-all-clean', [PreFlightController::class, 'approveAllClean'])->name('billing.finance.pre-flight.approve-all-clean');
        
        Route::get('/usage-review', [FinanceController::class, 'usageReview'])->name('billing.finance.usage-review');
        Route::post('/usage-review/{id}/approve', [FinanceController::class, 'approveUsageChange'])->name('billing.finance.usage-review.approve');
        Route::post('/usage-review/{id}/reject', [FinanceController::class, 'rejectUsageChange'])->name('billing.finance.usage-review.reject');
        Route::get('/profitability', [FinanceController::class, 'profitability'])->name('billing.finance.profitability');
        Route::get('/revenue-recognition', function() { return redirect()->route('billing.finance.reports-hub', ['tab' => 'revenue-recognition']); })->name('billing.finance.revenue-recognition');
        
        // Quotes
        Route::get('/quotes', [\Modules\Billing\Http\Controllers\QuoteController::class, 'index'])->name('billing.finance.quotes.index');
        Route::get('/quotes/create', [\Modules\Billing\Http\Controllers\QuoteController::class, 'create'])->name('billing.finance.quotes.create');
        Route::post('/quotes', [\Modules\Billing\Http\Controllers\QuoteController::class, 'store'])->name('billing.finance.quotes.store');
        Route::get('/quotes/{id}', [\Modules\Billing\Http\Controllers\QuoteController::class, 'show'])->name('billing.finance.quotes.show');
        Route::get('/quotes/{id}/edit', [\Modules\Billing\Http\Controllers\QuoteController::class, 'edit'])->name('billing.finance.quotes.edit');
        Route::put('/quotes/{id}', [\Modules\Billing\Http\Controllers\QuoteController::class, 'update'])->name('billing.finance.quotes.update');
        Route::post('/quotes/{id}/send', [\Modules\Billing\Http\Controllers\QuoteController::class, 'send'])->name('billing.finance.quotes.send');
        Route::post('/quotes/{id}/convert', [\Modules\Billing\Http\Controllers\QuoteController::class, 'convertToInvoice'])->name('billing.finance.quotes.convert');

        Route::get('/overrides', function() { return redirect()->route('billing.finance.reports-hub', ['tab' => 'overrides']); })->name('billing.finance.overrides');
        
        // Invoices with Tabbed Interface
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('billing.finance.invoices');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('billing.finance.invoices.show');
        Route::get('/invoices/{invoice}/dispute', [DisputeController::class, 'showForm'])->name('billing.finance.invoices.dispute.form');
        Route::post('/invoices/{invoice}/dispute', [DisputeController::class, 'store'])->name('billing.finance.invoices.dispute');
        
        Route::get('/payments', [FinanceController::class, 'payments'])->name('billing.finance.payments');
        Route::post('/payments', [\Modules\Billing\Http\Controllers\PaymentController::class, 'store'])->name('billing.finance.payments.store');
        
        // Reports Hub (consolidates: Executive, Reports, AR Aging, Profitability)
        Route::get('/reports-hub', [ReportsHubController::class, 'index'])->name('billing.finance.reports-hub');
        Route::get('/reports-hub/export', [ReportsHubController::class, 'export'])->name('billing.finance.reports-hub.export');
        
        // Legacy routes - redirect to Reports Hub
        Route::get('/ar-aging', function() { return redirect()->route('billing.finance.reports-hub', ['tab' => 'ar-aging']); });
        Route::get('/reports', function() { return redirect()->route('billing.finance.reports-hub', ['tab' => 'reports']); });
        Route::get('/profitability', function() { return redirect()->route('billing.finance.reports-hub', ['tab' => 'profitability']); });
        Route::get('/executive', function() { return redirect()->route('billing.finance.reports-hub', ['tab' => 'executive']); });
        
        Route::get('/collections', [FinanceController::class, 'collections'])->name('billing.finance.collections');
        Route::get('/export', [FinanceController::class, 'export'])->name('billing.finance.export');
        Route::get('/executive/year-over-year', [ExecutiveDashboardController::class, 'yearOverYear'])->name('billing.finance.executive.yoy');
        Route::get('/executive/board-report', [ExecutiveDashboardController::class, 'boardReport'])->name('billing.finance.executive.board-report');
        
        // Contracts
        Route::get('/contracts', function() { return redirect()->route('billing.finance.reports-hub', ['tab' => 'contracts']); })->name('billing.finance.contracts');
        Route::get('/contracts/create', [ContractController::class, 'create'])->name('billing.finance.contracts.create');
        Route::post('/contracts', [ContractController::class, 'store'])->name('billing.finance.contracts.store');
        Route::get('/contracts/{id}', [ContractController::class, 'show'])->name('billing.finance.contracts.show');
        Route::put('/contracts/{id}', [ContractController::class, 'update'])->name('billing.finance.contracts.update');
        
        // Credit Notes
        Route::get('/credit-notes', function() { return redirect()->route('billing.finance.reports-hub', ['tab' => 'credit-notes']); })->name('billing.finance.credit-notes');
        Route::get('/credit-notes/create', [CreditNoteController::class, 'create'])->name('billing.finance.credit-notes.create');
        Route::post('/credit-notes', [CreditNoteController::class, 'store'])->name('billing.finance.credit-notes.store');
        Route::get('/credit-notes/{id}', [CreditNoteController::class, 'show'])->name('billing.finance.credit-notes.show');
        
        // Disputes
        Route::get('/disputes', function() { return redirect()->route('billing.finance.reports-hub', ['tab' => 'disputes']); })->name('billing.finance.disputes');
        Route::get('/disputes/{id}', [DisputeController::class, 'show'])->name('billing.finance.disputes.show');
        Route::post('/disputes/{id}/resolve', [DisputeController::class, 'resolve'])->name('billing.finance.disputes.resolve');
        
        // Retainers
        Route::get('/retainers', function() { return redirect()->route('billing.finance.reports-hub', ['tab' => 'retainers']); })->name('billing.finance.retainers');
        Route::get('/retainers/create', [RetainerController::class, 'create'])->name('billing.finance.retainers.create');
        Route::post('/retainers', [RetainerController::class, 'store'])->name('billing.finance.retainers.store');
        Route::get('/retainers/{id}', [RetainerController::class, 'show'])->name('billing.finance.retainers.show');
        Route::post('/retainers/{id}/draw', [RetainerController::class, 'draw'])->name('billing.finance.retainers.draw');
        
        // Audit Log
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('billing.finance.audit-log');
        
        // Settings Hub (consolidates: General, Integrations, Templates, Numbering, Notifications)
        Route::get('/settings-hub', [SettingsHubController::class, 'index'])->name('billing.finance.settings-hub');
        Route::post('/settings-hub/general', [SettingsHubController::class, 'updateGeneral'])->name('billing.finance.settings-hub.general');
        Route::post('/settings-hub/helcim', [SettingsHubController::class, 'updateHelcim'])->name('billing.finance.settings-hub.helcim');
        Route::post('/settings-hub/google-chat', [SettingsHubController::class, 'updateGoogleChat'])->name('billing.finance.settings-hub.google-chat');
        Route::post('/settings-hub/quickbooks', [SettingsHubController::class, 'updateQuickBooks'])->name('billing.finance.settings-hub.quickbooks');
        Route::post('/settings-hub/update', [SettingsHubController::class, 'update'])->name('billing.finance.settings-hub.update');
        
        // Integration Callbacks
        Route::get('/settings-hub/xero/callback', [SettingsHubController::class, 'handleXeroCallback'])->name('billing.finance.xero.callback');
        
        // Legacy settings route - redirect to Settings Hub
        Route::get('/settings', function() { return redirect()->route('billing.finance.settings-hub'); });
    });

    // Technician Time Entry Feedback
    Route::prefix('technician')->group(function () {
        Route::get('/feedback', [\Modules\Billing\Http\Controllers\TechnicianFeedbackController::class, 'index'])->name('billing.technician.feedback');
    });

    // Client Onboarding
    Route::prefix('onboarding')->middleware(['can:finance.admin'])->group(function () {
        Route::get('/', [\Modules\Billing\Http\Controllers\OnboardingController::class, 'index'])->name('billing.onboarding.index');
        Route::post('/submit', [\Modules\Billing\Http\Controllers\OnboardingController::class, 'submit'])->name('billing.onboarding.submit');
    });

    // Invitations
    Route::prefix('invitations')->middleware(['can:finance.admin'])->group(function () {
        Route::get('/create', [\Modules\Billing\Http\Controllers\InvitationController::class, 'create'])->name('billing.finance.invitations.create');
        Route::post('/', [\Modules\Billing\Http\Controllers\InvitationController::class, 'store'])->name('billing.finance.invitations.store');
    });

    // Company Specific Portal Routes
    Route::prefix('{company}')->middleware(['billing.auth'])->group(function () {
        Route::get('/', [PortalController::class, 'dashboard'])->name('billing.portal.dashboard');
        Route::get('/quotes/{id}', [PortalController::class, 'showQuote'])->name('billing.portal.quotes.show');
        Route::post('/quotes/{id}/accept', [PortalController::class, 'acceptQuote'])->name('billing.portal.quotes.accept');
        Route::post('/quotes/{id}/reject', [PortalController::class, 'rejectQuote'])->name('billing.portal.quotes.reject');
        Route::get('/collections', [FinanceController::class, 'collections'])->name('billing.portal.finance.collections');
        Route::get('/reports', [FinanceController::class, 'reports'])->name('billing.portal.finance.reports');
        Route::get('/export', [FinanceController::class, 'export'])->name('billing.portal.finance.export');
        Route::get('/invoices/{invoice}/pdf', [PortalController::class, 'downloadPdf'])->name('billing.portal.invoice.pdf');
    });
});






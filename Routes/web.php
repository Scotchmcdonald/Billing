<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\PortalController;
use Modules\Billing\Http\Controllers\FinanceController;
use Modules\Billing\Http\Controllers\PriceOverrideController;
use Modules\Billing\Http\Controllers\StripeWebhookController;
use Modules\Billing\Http\Controllers\ExecutiveDashboardController;
use Modules\Billing\Http\Controllers\ContractController;
use Modules\Billing\Http\Controllers\CreditNoteController;
use Modules\Billing\Http\Controllers\DisputeController;
use Modules\Billing\Http\Controllers\RetainerController;
use Modules\Billing\Http\Controllers\AuditLogController;

// Public Quote Builder
Route::prefix('quote-builder')->group(function () {
    Route::get('/', [\Modules\Billing\Http\Controllers\PublicQuoteController::class, 'index'])->name('billing.public.quote.index');
    Route::post('/calculate', [\Modules\Billing\Http\Controllers\PublicQuoteController::class, 'calculate'])->name('billing.public.quote.calculate');
    Route::post('/submit', [\Modules\Billing\Http\Controllers\PublicQuoteController::class, 'store'])->name('billing.public.quote.store');
    Route::get('/view/{token}', [\Modules\Billing\Http\Controllers\PublicQuoteController::class, 'show'])->name('billing.public.quote.show');
});

Route::middleware(['auth'])->group(function () {
    // Portal Entry - Redirects to first company or shows selector
    Route::get('/', [PortalController::class, 'entry'])->name('billing.portal.entry');

    // Finance Admin
    Route::prefix('finance')->middleware(['can:finance.admin'])->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('billing.finance.index');
        Route::get('/portal-access', [FinanceController::class, 'portalAccess'])->name('billing.finance.portal-access');
        Route::get('/dashboard', [FinanceController::class, 'dashboard'])->name('billing.finance.dashboard');
        Route::get('/pre-flight', [FinanceController::class, 'preFlight'])->name('billing.finance.pre-flight');
        Route::get('/usage-review', [FinanceController::class, 'usageReview'])->name('billing.finance.usage-review');
        Route::post('/usage-review/{id}/approve', [FinanceController::class, 'approveUsageChange'])->name('billing.finance.usage-review.approve');
        Route::post('/usage-review/{id}/reject', [FinanceController::class, 'rejectUsageChange'])->name('billing.finance.usage-review.reject');
        Route::get('/profitability', [FinanceController::class, 'profitability'])->name('billing.finance.profitability');
        Route::get('/revenue-recognition', [FinanceController::class, 'revenueRecognition'])->name('billing.finance.revenue-recognition');
        
        // Quotes
        Route::get('/quotes/create', [\Modules\Billing\Http\Controllers\QuoteController::class, 'create'])->name('billing.finance.quotes.create');
        Route::post('/quotes', [\Modules\Billing\Http\Controllers\QuoteController::class, 'store'])->name('billing.finance.quotes.store');
        Route::get('/quotes/{id}', [\Modules\Billing\Http\Controllers\QuoteController::class, 'show'])->name('billing.finance.quotes.show');

        Route::get('/overrides', [PriceOverrideController::class, 'index'])->name('billing.finance.overrides');
        Route::get('/invoices', [FinanceController::class, 'invoices'])->name('billing.finance.invoices');
        Route::get('/payments', [FinanceController::class, 'payments'])->name('billing.finance.payments');
        Route::post('/payments', [\Modules\Billing\Http\Controllers\PaymentController::class, 'store'])->name('billing.finance.payments.store');
        Route::get('/ar-aging', [FinanceController::class, 'arAging'])->name('billing.finance.ar-aging');
        
        Route::get('/collections', [FinanceController::class, 'collections'])->name('billing.finance.collections');
        Route::get('/reports', [FinanceController::class, 'reports'])->name('billing.finance.reports');
        Route::get('/export', [FinanceController::class, 'export'])->name('billing.finance.export');
        
        // Executive Dashboard
        Route::get('/executive', [ExecutiveDashboardController::class, 'index'])->name('billing.finance.executive');
        Route::get('/executive/year-over-year', [ExecutiveDashboardController::class, 'yearOverYear'])->name('billing.finance.executive.yoy');
        Route::get('/executive/board-report', [ExecutiveDashboardController::class, 'boardReport'])->name('billing.finance.executive.board-report');
        
        // Contracts
        Route::get('/contracts', [ContractController::class, 'index'])->name('billing.finance.contracts');
        Route::get('/contracts/create', [ContractController::class, 'create'])->name('billing.finance.contracts.create');
        Route::post('/contracts', [ContractController::class, 'store'])->name('billing.finance.contracts.store');
        Route::get('/contracts/{id}', [ContractController::class, 'show'])->name('billing.finance.contracts.show');
        Route::put('/contracts/{id}', [ContractController::class, 'update'])->name('billing.finance.contracts.update');
        
        // Credit Notes
        Route::get('/credit-notes', [CreditNoteController::class, 'index'])->name('billing.finance.credit-notes');
        Route::get('/credit-notes/create', [CreditNoteController::class, 'create'])->name('billing.finance.credit-notes.create');
        Route::post('/credit-notes', [CreditNoteController::class, 'store'])->name('billing.finance.credit-notes.store');
        Route::get('/credit-notes/{id}', [CreditNoteController::class, 'show'])->name('billing.finance.credit-notes.show');
        
        // Disputes
        Route::get('/disputes', [DisputeController::class, 'index'])->name('billing.finance.disputes');
        Route::get('/disputes/{id}', [DisputeController::class, 'show'])->name('billing.finance.disputes.show');
        Route::post('/disputes/{id}/resolve', [DisputeController::class, 'resolve'])->name('billing.finance.disputes.resolve');
        
        // Retainers
        Route::get('/retainers', [RetainerController::class, 'index'])->name('billing.finance.retainers');
        Route::get('/retainers/create', [RetainerController::class, 'create'])->name('billing.finance.retainers.create');
        Route::post('/retainers', [RetainerController::class, 'store'])->name('billing.finance.retainers.store');
        Route::get('/retainers/{id}', [RetainerController::class, 'show'])->name('billing.finance.retainers.show');
        Route::post('/retainers/{id}/draw', [RetainerController::class, 'draw'])->name('billing.finance.retainers.draw');
        
        // Audit Log
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('billing.finance.audit-log');
        
        // Settings
        Route::get('/settings', [FinanceController::class, 'settings'])->name('billing.finance.settings');
        Route::post('/settings/stripe', [FinanceController::class, 'updateStripeSettings'])->name('billing.finance.settings.stripe');
        Route::post('/settings/quickbooks', [FinanceController::class, 'updateQuickBooksSettings'])->name('billing.finance.settings.quickbooks');
    });

    // Company Specific Portal Routes
    Route::prefix('{company}')->middleware(['billing.auth'])->group(function () {
        Route::get('/', [PortalController::class, 'dashboard'])->name('billing.portal.dashboard');
        Route::get('/collections', [FinanceController::class, 'collections'])->name('billing.portal.finance.collections');
        Route::get('/reports', [FinanceController::class, 'reports'])->name('billing.portal.finance.reports');
        Route::get('/export', [FinanceController::class, 'export'])->name('billing.portal.finance.export');
    });
});

// Webhooks
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('billing.stripe.webhook');
Route::post('/webhooks/rmm/device-count', [\Modules\Billing\Http\Controllers\Webhooks\RmmWebhookController::class, 'deviceCount'])->name('billing.webhooks.rmm.device-count');



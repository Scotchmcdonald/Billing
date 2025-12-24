<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\PortalController;
use Modules\Billing\Http\Controllers\FinanceController;
use Modules\Billing\Http\Controllers\StripeWebhookController;

Route::middleware(['auth'])->group(function () {
    // Portal Entry - Redirects to first company or shows selector
    Route::get('/', [PortalController::class, 'entry'])->name('billing.portal.entry');

    // Company Specific Portal Routes
    Route::prefix('{company}')->middleware(['billing.auth'])->group(function () {
        Route::get('/', [PortalController::class, 'dashboard'])->name('billing.portal.dashboard');
        Route::get('/pay', [PortalController::class, 'paymentWizard'])->name('billing.portal.pay');
        Route::get('/payment-methods', [PortalController::class, 'paymentMethods'])->name('billing.portal.payment_methods');
        Route::get('/invoices', [PortalController::class, 'invoices'])->name('billing.portal.invoices');
        Route::get('/team', [PortalController::class, 'team'])->name('billing.portal.team');
    });
    
    // Finance Admin
    Route::prefix('finance')->middleware(['can:finance.admin'])->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('billing.finance.index');
        Route::get('/collections', [FinanceController::class, 'collections'])->name('billing.finance.collections');
        Route::get('/reports', [FinanceController::class, 'reports'])->name('billing.finance.reports');
        Route::get('/export', [FinanceController::class, 'export'])->name('billing.finance.export');
    });
});

// Webhooks
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('billing.stripe.webhook');

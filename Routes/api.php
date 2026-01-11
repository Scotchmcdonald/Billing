<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\Api\CatalogController;
use Modules\Billing\Http\Controllers\Api\InvoiceController;
use Modules\Billing\Http\Controllers\Api\PriceOverrideController;
use Modules\Billing\Http\Controllers\Api\BillableEntryController;
use Modules\Billing\Http\Controllers\Api\PaymentController;
use Modules\Billing\Http\Controllers\Webhooks\HelcimWebhookController;
use Modules\Billing\Http\Controllers\Webhooks\RmmWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Webhooks
Route::post('/webhooks/helcim', [HelcimWebhookController::class, 'handle'])->name('billing.webhooks.helcim');
Route::post('/webhooks/rmm/device-count', [RmmWebhookController::class, 'deviceCount'])->name('billing.webhooks.rmm.device-count');

Route::middleware(['auth:sanctum', 'can:finance.admin'])->group(function () {
    // Catalog
    Route::get('/catalog', [CatalogController::class, 'index']);
    Route::get('/products/{product}/pricing', [CatalogController::class, 'showProductPricing']);

    // Invoices
    Route::post('/invoices/generate', [InvoiceController::class, 'generate']);
    Route::get('/invoices/pending-review', [InvoiceController::class, 'pendingReview']);
    Route::post('/invoices/{invoice}/finalize', [InvoiceController::class, 'finalize']);
    Route::post('/invoices/{invoice}/void', [InvoiceController::class, 'void']);

    // Price Overrides
    Route::get('/overrides', [PriceOverrideController::class, 'index']);
    Route::post('/overrides', [PriceOverrideController::class, 'store']);
    Route::put('/overrides/{override}', [PriceOverrideController::class, 'update']);
    Route::delete('/overrides/{override}', [PriceOverrideController::class, 'destroy']);

    // Billable Entries (Admin/Tech side)
    Route::post('/entries', [BillableEntryController::class, 'store']);
    Route::patch('/entries/{entry}/toggle-billable', [BillableEntryController::class, 'toggleBillable']);

    // Payments
    Route::post('/payments', [PaymentController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'billing.auth'])->group(function () {
    // Catalog
    Route::get('/companies/{company}/catalog', [CatalogController::class, 'showForCompany']);

    // Invoices
    Route::get('/invoices/{invoice}/preview-pdf', [InvoiceController::class, 'previewPdf']);

    // Billable Entries
    Route::get('/companies/{company}/unbilled', [BillableEntryController::class, 'unbilled']);
});

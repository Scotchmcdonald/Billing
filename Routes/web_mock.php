<?php

use Modules\Billing\Http\Controllers\MockHelcimController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'billing/mock/helcim', 
    'middleware' => ['web', 'auth'] // Ensure appropriate middleware
], function () {
    Route::get('/console', [MockHelcimController::class, 'index'])->name('billing.mock.helcim.index');
    Route::post('/outcome', [MockHelcimController::class, 'setOutcome'])->name('billing.mock.helcim.outcome');
});

<?php

/**
 * Simulation Routes (Proof of Concept)
 * 
 * Routes for role simulation feature.
 * Only accessible to executives and admins.
 * 
 * Part of: BATCH_10_ROLE_SIMULATION
 * 
 * Add to: routes/web.php
 */

Route::middleware(['auth', 'role:executive,admin'])
    ->prefix('simulation')
    ->name('simulation.')
    ->group(function () {
        
        // Start simulation
        Route::post('start', [SimulationController::class, 'start'])
            ->name('start');
        
        // Switch simulated role
        Route::post('switch-role', [SimulationController::class, 'switchRole'])
            ->name('switch-role');
        
        // Terminate simulation
        Route::post('terminate', [SimulationController::class, 'terminate'])
            ->name('terminate');
        
        // Get simulation status (API)
        Route::get('status', [SimulationController::class, 'status'])
            ->name('status');
    });

<?php

/**
 * Gate Override for Simulation (Proof of Concept)
 * 
 * Add to: app/Providers/AuthServiceProvider.php
 * In the boot() method, BEFORE other gate definitions.
 * 
 * Part of: BATCH_10_ROLE_SIMULATION
 */

use Illuminate\Support\Facades\Gate;

// Simulation Gate Override
Gate::before(function ($user, $ability) {
    // Only override if simulation is active
    if (!session('simulating')) {
        return null; // Let normal gate logic proceed
    }

    $simulatedRole = session('simulated_role');
    
    // Define role-to-permissions mapping
    $rolePermissions = [
        'technician' => [
            'view_tickets',
            'create_tickets',
            'create_time_entries',
            'view_own_time_entries',
            'view_own_invoices',
            'upload_receipts',
        ],
        'client_admin' => [
            'view_company_tickets',
            'create_company_tickets',
            'view_company_invoices',
            'view_company_reports',
            'manage_payment_methods',
            'view_company_subscriptions',
            'manage_company_users',
        ],
        'client_user' => [
            'view_own_tickets',
            'create_own_tickets',
            'view_own_invoices',
            'update_profile',
        ],
    ];

    // Check if simulated role has the requested ability
    $hasPermission = in_array($ability, $rolePermissions[$simulatedRole] ?? []);

    // Developer overlay: Store debug info
    if (config('app.debug') && request()->header('X-Simulation-Debug')) {
        $debugLog = session('simulation_debug_log', []);
        $debugLog[] = [
            'ability' => $ability,
            'simulated_role' => $simulatedRole,
            'result' => $hasPermission ? 'ALLOWED' : 'DENIED',
            'reason' => $hasPermission 
                ? "Role '{$simulatedRole}' has permission '{$ability}'"
                : "Role '{$simulatedRole}' lacks permission '{$ability}'",
            'checked_at' => now()->toDateTimeString(),
        ];
        
        // Keep only last 10 checks
        if (count($debugLog) > 10) {
            array_shift($debugLog);
        }
        
        session(['simulation_debug_log' => $debugLog]);
        
        // Also flash the last check for banner display
        session()->flash('simulation_debug', end($debugLog));
    }

    // Return result or null (to allow other gates)
    return $hasPermission ?: null;
});

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Simulation Middleware
 * 
 * Enforces read-only mode during role simulation sessions.
 * Blocks POST/PUT/PATCH/DELETE requests unless explicit override flag present.
 * 
 * Part of: BATCH_10_ROLE_SIMULATION (Proof of Concept)
 */
class SimulationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if simulation mode is active
        if (!session('simulating')) {
            return $next($request);
        }

        // Allow safe methods (read-only)
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $next($request);
        }

        // Check for simulation override flag
        if ($request->input('_simulation_override') === true 
            || $request->header('X-Simulation-Override') === 'true') {
            
            // Log override usage for audit trail
            Log::warning('Simulation override used', [
                'user_id' => auth()->id(),
                'simulated_role' => session('simulated_role'),
                'original_user_id' => session('original_user_id'),
                'method' => $request->method(),
                'route' => $request->path(),
                'ip' => $request->ip(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            // Flash warning to user
            session()->flash('warning', 'Simulation Override: Action executed in simulation mode. Data may be affected.');

            return $next($request);
        }

        // Block mutating requests during simulation
        return response()->json([
            'error' => 'Simulation Mode: Read-Only',
            'message' => 'POST/PUT/PATCH/DELETE requests are blocked during simulation to prevent accidental data modification.',
            'hint' => 'To execute this action, terminate simulation first or add _simulation_override flag.',
            'simulated_role' => session('simulated_role'),
            'simulation_started_at' => session('simulation_started_at'),
        ], 403);
    }
}

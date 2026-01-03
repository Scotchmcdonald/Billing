<?php

namespace Modules\Billing\POC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Facades\Activity;

/**
 * Simulation Controller (Proof of Concept)
 * 
 * Manages role simulation sessions for executives/admins.
 * Allows viewing the application through different user role perspectives.
 * 
 * Part of: BATCH_10_ROLE_SIMULATION
 */
class SimulationController extends Controller
{
    /**
     * Start a role simulation session
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function start(Request $request)
    {
        // Authorization: Only executives and admins can simulate
        abort_unless(
            auth()->user()->hasRole(['executive', 'admin']) || 
            Gate::allows('simulate-roles'),
            403,
            'Unauthorized: Role simulation requires executive or admin privileges'
        );

        // Validation
        $validated = $request->validate([
            'role' => 'required|in:technician,client_admin,client_user',
            'user_id' => 'nullable|exists:users,id', // Optional: simulate specific user
        ]);

        // Store simulation state in session
        session([
            'simulating' => true,
            'simulated_role' => $validated['role'],
            'simulated_user_id' => $validated['user_id'] ?? null,
            'original_user_id' => auth()->id(),
            'simulation_started_at' => now(),
        ]);

        // Audit log
        activity()
            ->causedBy(auth()->user())
            ->event('simulation_started')
            ->withProperties([
                'simulated_role' => $validated['role'],
                'simulated_user_id' => $validated['user_id'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log('Started role simulation');

        return redirect()
            ->back()
            ->with('success', "Simulation started: Viewing as {$validated['role']}");
    }

    /**
     * Switch to a different simulated role
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function switchRole(Request $request)
    {
        // Must be in simulation mode
        abort_unless(session('simulating'), 403, 'Not in simulation mode');

        // Validation
        $validated = $request->validate([
            'role' => 'required|in:technician,client_admin,client_user',
        ]);

        $previousRole = session('simulated_role');

        // Update session
        session(['simulated_role' => $validated['role']]);

        // Audit log
        activity()
            ->causedBy(auth()->user())
            ->event('simulation_role_switched')
            ->withProperties([
                'from_role' => $previousRole,
                'to_role' => $validated['role'],
                'ip_address' => $request->ip(),
            ])
            ->log('Switched simulated role');

        return response()->json([
            'success' => true,
            'message' => "Switched to {$validated['role']}",
            'new_role' => $validated['role'],
        ]);
    }

    /**
     * Terminate the simulation session
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function terminate()
    {
        if (session('simulating')) {
            $duration = now()->diffInSeconds(session('simulation_started_at'));
            $simulatedRole = session('simulated_role');

            // Audit log
            activity()
                ->causedBy(auth()->user())
                ->event('simulation_ended')
                ->withProperties([
                    'simulated_role' => $simulatedRole,
                    'duration_seconds' => $duration,
                    'duration_formatted' => gmdate('H:i:s', $duration),
                ])
                ->log('Ended role simulation');

            // Clear simulation session data
            session()->forget([
                'simulating',
                'simulated_role',
                'simulated_user_id',
                'original_user_id',
                'simulation_started_at',
                'simulation_debug',
                'simulation_debug_log',
            ]);

            return redirect()
                ->route('dashboard')
                ->with('success', 'Simulation terminated successfully');
        }

        return redirect()
            ->route('dashboard')
            ->with('info', 'No active simulation session');
    }

    /**
     * Get current simulation status (API endpoint)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function status()
    {
        if (!session('simulating')) {
            return response()->json([
                'simulating' => false,
            ]);
        }

        return response()->json([
            'simulating' => true,
            'simulated_role' => session('simulated_role'),
            'simulated_user_id' => session('simulated_user_id'),
            'original_user_id' => session('original_user_id'),
            'started_at' => session('simulation_started_at'),
            'duration_seconds' => now()->diffInSeconds(session('simulation_started_at')),
        ]);
    }
}

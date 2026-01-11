<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class MockHelcimController extends Controller
{
    /**
     * Display the Mock Payment Overlay / Console.
     */
    public function index()
    {
        // Only allow in non-production environments
        if (app()->environment('production')) {
            abort(404);
        }

        $currentOutcome = Cache::get('helcim_test_mode_outcome', 'APPROVED');

        return view('billing::mock.helcim-console', compact('currentOutcome'));
    }

    /**
     * Set the desired outcome for the next transaction.
     */
    public function setOutcome(Request $request)
    {
        if (app()->environment('production')) {
            abort(404);
        }

        $request->validate([
            'outcome' => 'required|in:APPROVED,DECLINED,TIMEOUT',
        ]);

        Cache::put('helcim_test_mode_outcome', $request->outcome, 600); // 10 minutes

        return response()->json([
            'status' => 'success', 
            'message' => "Next payment will simulate: {$request->outcome}"
        ]);
    }
}

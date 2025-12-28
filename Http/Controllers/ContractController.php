<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Models\Company;
use Modules\Billing\Services\ContractService;

class ContractController extends Controller
{
    public function __construct(
        protected ContractService $contractService
    ) {}

    /**
     * Display contracts expiring soon.
     */
    public function index(Request $request)
    {
        $daysAhead = $request->input('days_ahead', 60);
        
        $expiringContracts = $this->contractService->getExpiringContracts($daysAhead);
        
        // Get churned contracts for reference
        $churnedContracts = $this->contractService->getChurnedSubscriptions(now()->subMonths(3));

        return view('billing::finance.contracts.index', compact(
            'expiringContracts',
            'churnedContracts',
            'daysAhead'
        ));
    }

    /**
     * Mark a subscription as renewed.
     */
    public function markRenewed(Request $request, Subscription $subscription)
    {
        $request->validate([
            'new_end_date' => 'nullable|date|after:today',
        ]);

        try {
            $newEndDate = $request->new_end_date ? \Carbon\Carbon::parse($request->new_end_date) : null;
            
            $this->contractService->markAsRenewed($subscription, $newEndDate);

            return back()->with('success', 'Subscription marked as renewed');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mark a subscription as churned.
     */
    public function markChurned(Request $request, Subscription $subscription)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->contractService->markAsChurned($subscription, $request->reason);

            return back()->with('success', 'Subscription marked as churned');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Send renewal reminder.
     */
    public function sendReminder(Subscription $subscription)
    {
        try {
            if (!$subscription->contract_end_date) {
                return back()->withErrors(['error' => 'Subscription has no contract end date']);
            }

            $daysRemaining = now()->diffInDays($subscription->contract_end_date, false);
            
            $this->contractService->sendRenewalReminder($subscription, (int) $daysRemaining);

            return back()->with('success', 'Renewal reminder sent');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}

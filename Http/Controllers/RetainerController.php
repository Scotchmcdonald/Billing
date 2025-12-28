<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Models\Retainer;
use Modules\Billing\Models\Company;
use Modules\Billing\Services\RetainerService;
use Illuminate\Support\Carbon;

class RetainerController extends Controller
{
    public function __construct(
        protected RetainerService $retainerService
    ) {}

    /**
     * Display a listing of retainers.
     */
    public function index(Request $request)
    {
        $query = Retainer::with('company');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by company
        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Low balance filter
        if ($request->has('low_balance') && $request->low_balance) {
            $query->where('status', 'active')
                ->where('hours_remaining', '>', 0)
                ->where('hours_remaining', '<=', 5);
        }

        $retainers = $query->orderBy('created_at', 'desc')->paginate(25);
        $companies = Company::orderBy('name')->get();

        // Get low balance count for badge
        $lowBalanceCount = $this->retainerService->getLowBalanceRetainers()->count();

        return view('billing::finance.retainers.index', compact(
            'retainers',
            'companies',
            'lowBalanceCount'
        ));
    }

    /**
     * Show the form for creating a new retainer.
     */
    public function create()
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        
        return view('billing::finance.retainers.create', compact('companies'));
    }

    /**
     * Store a newly created retainer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'hours' => 'required|numeric|min:1|max:1000',
            'price' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date|after:today',
        ]);

        try {
            $company = Company::findOrFail($request->company_id);
            $expiresAt = $request->expires_at ? Carbon::parse($request->expires_at) : null;

            $retainer = $this->retainerService->purchaseRetainer(
                $company,
                $request->hours,
                (int) ($request->price * 100), // Convert to cents
                $expiresAt
            );

            return redirect()
                ->route('billing.finance.retainers.show', $retainer)
                ->with('success', 'Retainer created successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified retainer.
     */
    public function show(Retainer $retainer)
    {
        $retainer->load(['company']);
        
        // Get usage history from billable entries
        $usageHistory = \Modules\Billing\Models\BillableEntry::where('company_id', $retainer->company_id)
            ->whereJsonContains('metadata->retainer_id', $retainer->id)
            ->with(['user'])
            ->orderBy('date', 'desc')
            ->get();

        return view('billing::finance.retainers.show', compact('retainer', 'usageHistory'));
    }

    /**
     * Show form to add hours to a retainer.
     */
    public function addHours(Retainer $retainer)
    {
        return view('billing::finance.retainers.add-hours', compact('retainer'));
    }

    /**
     * Add hours to an existing retainer.
     */
    public function storeHours(Request $request, Retainer $retainer)
    {
        $request->validate([
            'hours' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        try {
            // Add hours to existing retainer
            $newHours = $retainer->hours_purchased + $request->hours;
            $newRemaining = $retainer->hours_remaining + $request->hours;

            $retainer->update([
                'hours_purchased' => $newHours,
                'hours_remaining' => $newRemaining,
                'status' => $newRemaining > 0 ? 'active' : $retainer->status,
            ]);

            return redirect()
                ->route('billing.finance.retainers.show', $retainer)
                ->with('success', 'Hours added successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Expire overdue retainers (can be called manually or via scheduled job).
     */
    public function expireOverdue()
    {
        $count = $this->retainerService->expireOverdueRetainers();

        return back()->with('success', "{$count} retainer(s) expired");
    }

    /**
     * Get retainer data for a company (API endpoint for portal).
     */
    public function forCompany(Company $company)
    {
        $activeRetainer = $this->retainerService->getActiveRetainer($company);
        $allRetainers = $this->retainerService->getRetainers($company);

        return response()->json([
            'active' => $activeRetainer,
            'all' => $allRetainers,
        ]);
    }
}

<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Services\DisputeService;
use Modules\Billing\Services\AuditService;

class DisputeController extends Controller
{
    public function __construct(
        protected DisputeService $disputeService,
        protected AuditService $auditService
    ) {}

    /**
     * Flag an invoice as disputed.
     */
    public function flag(Request $request, Invoice $invoice)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->disputeService->flagAsDisputed(
                $invoice,
                $request->reason,
                auth()->user()
            );

            return back()->with('success', 'Invoice flagged as disputed');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Resolve a dispute.
     */
    public function resolve(Request $request, Invoice $invoice)
    {
        $request->validate([
            'resolution' => 'required|string|max:500',
        ]);

        try {
            $this->disputeService->resolveDispute(
                $invoice,
                $request->resolution,
                auth()->user()
            );

            return back()->with('success', 'Dispute resolved successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Pause dunning for an invoice.
     */
    public function pauseDunning(Request $request, Invoice $invoice)
    {
        try {
            $this->disputeService->pauseDunning(
                $invoice,
                $request->input('reason'),
                auth()->user()
            );

            return back()->with('success', 'Dunning paused');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Resume dunning for an invoice.
     */
    public function resumeDunning(Invoice $invoice)
    {
        try {
            $this->disputeService->resumeDunning($invoice, auth()->user());

            return back()->with('success', 'Dunning resumed');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get all disputed invoices.
     */
    public function index()
    {
        $disputedInvoices = $this->disputeService->getDisputedInvoices();

        return view('billing::finance.disputes.index', compact('disputedInvoices'));
    }
}

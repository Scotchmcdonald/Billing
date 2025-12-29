<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Dispute;
use Modules\Billing\Services\DisputeService;
use Modules\Billing\Services\AuditService;
use Modules\Billing\Events\InvoiceDisputed;
use Illuminate\Support\Facades\Storage;

class DisputeController extends Controller
{
    public function __construct(
        protected DisputeService $disputeService,
        protected AuditService $auditService
    ) {}

    /**
     * Show dispute form for an invoice
     */
    public function showForm(Invoice $invoice)
    {
        return view('billing::finance.invoices.dispute', compact('invoice'));
    }

    /**
     * Create a dispute for an invoice
     */
    public function store(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'disputed_amount' => 'required|numeric|min:0.01|max:' . $invoice->total,
            'line_items' => 'nullable|json',
            'explanation' => 'required|string|min:20',
            'pause_dunning' => 'boolean',
            'files.*' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        // Create dispute record
        $dispute = Dispute::create([
            'invoice_id' => $invoice->id,
            'company_id' => $invoice->company_id,
            'reason' => $validated['reason'],
            'disputed_amount' => $validated['disputed_amount'],
            'line_item_ids' => $validated['line_items'] ?? null,
            'explanation' => $validated['explanation'],
            'status' => 'open',
            'created_by' => auth()->id(),
        ]);

        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('disputes/' . $dispute->id, 'private');
                $dispute->attachments()->create([
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        // Update invoice status
        $invoice->update([
            'status' => 'disputed',
            'disputed_at' => now(),
        ]);

        // Pause dunning if requested
        if ($validated['pause_dunning'] ?? true) {
            $invoice->update([
                'dunning_paused' => true,
                'dunning_paused_at' => now(),
                'dunning_pause_reason' => 'Dispute filed: ' . $validated['reason'],
            ]);
        }

        // Log activity
        activity()
            ->performedOn($invoice)
            ->causedBy(auth()->user())
            ->withProperties([
                'dispute_id' => $dispute->id,
                'reason' => $validated['reason'],
                'amount' => $validated['disputed_amount'],
            ])
            ->log('Invoice disputed');

        // Fire event
        event(new InvoiceDisputed($invoice, $dispute));

        return response()->json(['success' => true]);
    }

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

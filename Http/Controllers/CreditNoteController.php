<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Models\CreditNote;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Company;
use Modules\Billing\Services\CreditNoteService;
use Modules\Billing\Services\AuditService;

class CreditNoteController extends Controller
{
    public function __construct(
        protected CreditNoteService $creditNoteService,
        protected AuditService $auditService
    ) {}

    /**
     * Display a listing of credit notes.
     */
    public function index(Request $request)
    {
        $query = CreditNote::with(['invoice', 'company', 'issuedBy']);

        // Filter by company
        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Filter by status (applied or not)
        if ($request->has('status')) {
            if ($request->status === 'applied') {
                $query->whereNotNull('applied_at');
            } elseif ($request->status === 'pending') {
                $query->whereNull('applied_at');
            }
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        $creditNotes = $query->orderBy('created_at', 'desc')->paginate(25);
        $companies = Company::orderBy('name')->get();

        return view('billing::finance.credit-notes.index', compact('creditNotes', 'companies'));
    }

    /**
     * Show the form for creating a new credit note.
     */
    public function create(Invoice $invoice)
    {
        return view('billing::finance.credit-notes.create', compact('invoice'));
    }

    /**
     * Store a newly created credit note.
     */
    public function store(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->total,
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            $creditNote = $this->creditNoteService->issueCreditNote(
                $invoice,
                (int) ($request->amount * 100), // Convert to cents
                $request->reason,
                auth()->user(),
                $request->notes
            );

            return redirect()
                ->route('billing.finance.invoices.show', $invoice)
                ->with('success', 'Credit note issued successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Apply a credit note to an invoice.
     */
    public function apply(CreditNote $creditNote)
    {
        try {
            $this->creditNoteService->applyCreditNote($creditNote);

            return back()->with('success', 'Credit note applied successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Void (soft delete) a credit note.
     */
    public function void(CreditNote $creditNote)
    {
        try {
            $this->creditNoteService->voidCreditNote($creditNote, auth()->user());

            return back()->with('success', 'Credit note voided successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display credit notes for a specific invoice.
     */
    public function forInvoice(Invoice $invoice)
    {
        $creditNotes = $this->creditNoteService->getCreditNotesForInvoice($invoice);
        $totalCredit = $this->creditNoteService->getTotalCreditForInvoice($invoice);
        $unappliedCredit = $this->creditNoteService->getUnappliedCreditForInvoice($invoice);

        return view('billing::finance.credit-notes.for-invoice', compact(
            'invoice',
            'creditNotes',
            'totalCredit',
            'unappliedCredit'
        ));
    }
}

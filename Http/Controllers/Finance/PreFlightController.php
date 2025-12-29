<?php

namespace Modules\Billing\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Events\InvoiceApproved;
use Modules\Billing\Events\InvoiceSent;

class PreFlightController extends Controller
{
    /**
     * Display the pre-flight review interface
     */
    public function index()
    {
        $invoices = Invoice::with(['company', 'lineItems'])
            ->where('status', 'draft')
            ->orWhere('status', 'approved')
            ->orderBy('anomaly_score', 'desc')
            ->get();

        return view('billing::finance.pre-flight-enhanced', compact('invoices'));
    }

    /**
     * Approve a single invoice (does not send)
     */
    public function approve(Request $request, Invoice $invoice)
    {
        $invoice->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        // Log activity
        activity()
            ->performedOn($invoice)
            ->causedBy(auth()->user())
            ->withProperties(['action' => 'approved'])
            ->log('Invoice approved');

        event(new InvoiceApproved($invoice));

        return response()->json(['success' => true]);
    }

    /**
     * Approve and immediately send an invoice
     */
    public function approveAndSend(Request $request, Invoice $invoice)
    {
        DB::transaction(function () use ($invoice) {
            // First approve
            $invoice->update([
                'status' => 'sent',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'sent_at' => now(),
            ]);

            // Log activities
            activity()
                ->performedOn($invoice)
                ->causedBy(auth()->user())
                ->withProperties(['action' => 'approved_and_sent'])
                ->log('Invoice approved and sent to client');

            // Fire events
            event(new InvoiceApproved($invoice));
            event(new InvoiceSent($invoice));
        });

        return response()->json(['success' => true]);
    }

    /**
     * Send an already-approved invoice
     */
    public function send(Request $request, Invoice $invoice)
    {
        if ($invoice->status !== 'approved') {
            return response()->json(['error' => 'Invoice must be approved first'], 400);
        }

        $invoice->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        activity()
            ->performedOn($invoice)
            ->causedBy(auth()->user())
            ->withProperties(['action' => 'sent'])
            ->log('Invoice sent to client');

        event(new InvoiceSent($invoice));

        return response()->json(['success' => true]);
    }

    /**
     * Bulk approve invoices (does not send)
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:invoices,id',
        ]);

        $count = 0;

        foreach ($request->ids as $id) {
            $invoice = Invoice::find($id);
            
            if ($invoice && $invoice->status === 'draft') {
                $invoice->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => auth()->id(),
                ]);

                activity()
                    ->performedOn($invoice)
                    ->causedBy(auth()->user())
                    ->withProperties(['action' => 'bulk_approved'])
                    ->log('Invoice bulk approved');

                event(new InvoiceApproved($invoice));
                $count++;
            }
        }

        return response()->json(['success' => true, 'count' => $count]);
    }

    /**
     * Bulk approve and send invoices
     */
    public function bulkApproveAndSend(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:invoices,id',
        ]);

        $count = 0;

        DB::transaction(function () use ($request, &$count) {
            foreach ($request->ids as $id) {
                $invoice = Invoice::find($id);
                
                if ($invoice && in_array($invoice->status, ['draft', 'approved'])) {
                    $invoice->update([
                        'status' => 'sent',
                        'approved_at' => now(),
                        'approved_by' => auth()->id(),
                        'sent_at' => now(),
                    ]);

                    activity()
                        ->performedOn($invoice)
                        ->causedBy(auth()->user())
                        ->withProperties(['action' => 'bulk_approved_and_sent'])
                        ->log('Invoice bulk approved and sent');

                    event(new InvoiceApproved($invoice));
                    event(new InvoiceSent($invoice));
                    $count++;
                }
            }
        });

        return response()->json(['success' => true, 'sent' => $count]);
    }

    /**
     * Approve all clean invoices (anomaly score < 30)
     */
    public function approveAllClean(Request $request)
    {
        $invoices = Invoice::where('status', 'draft')
            ->where('anomaly_score', '<', 30)
            ->get();

        $count = 0;

        foreach ($invoices as $invoice) {
            $invoice->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            activity()
                ->performedOn($invoice)
                ->causedBy(auth()->user())
                ->withProperties(['action' => 'auto_approved_clean'])
                ->log('Clean invoice auto-approved');

            event(new InvoiceApproved($invoice));
            $count++;
        }

        return response()->json(['success' => true, 'count' => $count]);
    }
}

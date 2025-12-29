<?php

namespace Modules\Billing\Http\Controllers\Finance;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Dispute;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        $invoices = Invoice::with('company')
            ->latest()
            ->paginate(20);

        return view('billing::finance.invoices', compact('invoices'));
    }

    /**
     * Display invoice detail with tabbed interface
     */
    public function show($id)
    {
        $invoice = Invoice::with(['company', 'lineItems', 'payments', 'disputes.attachments'])
            ->findOrFail($id);

        // Count items for badges
        $disputeCount = $invoice->disputes()->where('status', 'open')->count();
        $timelineCount = $invoice->activities()->count() ?? 0;

        return view('billing::finance.invoices.show-tabbed', compact(
            'invoice',
            'disputeCount',
            'timelineCount'
        ));
    }
}

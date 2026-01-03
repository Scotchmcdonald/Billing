<?php

namespace Modules\Billing\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Events\InvoiceApproved;
use Modules\Billing\Events\InvoiceSent;
use Modules\Billing\Models\Subscription;
use Modules\Inventory\Models\Asset;

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

        $deltas = $this->calculateDeltas();
        $taxCredits = $this->calculateTaxCredits($invoices);
        $burdens = $this->calculateBurdens();
        $variances = $this->calculateVariances($invoices);

        return view('billing::finance.pre-flight-enhanced', compact('invoices', 'deltas', 'taxCredits', 'burdens', 'variances'));
    }

    private function calculateDeltas()
    {
        $deltas = [];
        $newSubscriptions = Subscription::where('created_at', '>=', now()->subDays(30))->get();
        
        foreach ($newSubscriptions as $sub) {
            $clientName = $sub->company->name ?? 'Unknown Client';
            if (!isset($deltas[$clientName])) {
                $deltas[$clientName] = ['added' => [], 'removed' => []];
            }
            $deltas[$clientName]['added'][] = "{$sub->quantity} x {$sub->name}";
        }

        $formattedDeltas = [];
        foreach ($deltas as $client => $changes) {
            $formattedDeltas[$client] = [
                'added' => implode(', ', $changes['added']),
                'removed' => implode(', ', $changes['removed'])
            ];
        }

        return $formattedDeltas;
    }

    private function calculateTaxCredits($invoices)
    {
        $credits = [];
        
        foreach ($invoices as $invoice) {
            if ($invoice->company && $invoice->company->pricing_tier === 'non_profit') {
                $creditTotal = 0;
                foreach ($invoice->lineItems as $item) {
                    // Use standard_unit_price if available, otherwise assume no credit (0)
                    $standardPrice = $item->standard_unit_price ?? $item->unit_price;
                    $diff = max(0, $standardPrice - $item->unit_price);
                    $creditTotal += $diff * $item->quantity;
                }
                
                if ($creditTotal > 0) {
                    $credits[$invoice->company->name] = $creditTotal;
                }
            }
        }

        return $credits;
    }

    private function calculateVariances($invoices)
    {
        $variances = [];
        
        foreach ($invoices as $invoice) {
            // Find previous invoice for this client
            $previousInvoice = Invoice::where('client_id', $invoice->client_id)
                ->where('id', '<', $invoice->id)
                ->where('status', '!=', 'cancelled')
                ->orderBy('id', 'desc')
                ->first();
                
            if ($previousInvoice && $previousInvoice->total > 0) {
                $diff = $invoice->total - $previousInvoice->total;
                $percent = ($diff / $previousInvoice->total) * 100;
                
                if (abs($percent) >= 20) {
                    $variances[$invoice->id] = [
                        'percent' => round($percent, 1),
                        'diff' => $diff,
                        'previous_total' => $previousInvoice->total
                    ];
                }
            }
        }
        
        return $variances;
    }

    private function calculateBurdens()
    {
        $burdens = [];
        $companies = \Modules\Billing\Models\Company::all();

        foreach ($companies as $company) {
            $revenue = $company->subscriptions()->where('is_active', true)->sum(DB::raw('quantity * effective_price'));
            
            // Use linked client to find assets, fallback to empty if not linked
            $assets = collect();
            if ($company->client_id) {
                $assets = Asset::where('client_id', $company->client_id)->with('softwareProducts')->get();
            }
            
            $burdenTotal = 0;
            foreach ($assets as $asset) {
                $burdenTotal += $asset->softwareProducts->sum('monthly_cost');
            }
            
            // Try to get user count from Client, fallback to Company users or 1
            $userCount = 0;
            if ($company->client) {
                $userCount = $company->client->users()->count();
            }
            if ($userCount == 0) {
                $userCount = $company->users()->count();
            }
            if ($userCount == 0) $userCount = 1;

            $burdens[$company->name] = [
                'revenue' => $revenue / $userCount,
                'burden' => $burdenTotal / $userCount
            ];
        }

        return $burdens;
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

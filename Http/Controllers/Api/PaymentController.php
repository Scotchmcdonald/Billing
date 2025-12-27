<?php

namespace Modules\Billing\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Record a payment.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:stripe_card,stripe_ach,check,wire,cash,other',
            'payment_reference' => 'nullable|string',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        // Prevent overpayment logic could go here, but allowing for now (credit balance scenario)

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'company_id' => $invoice->company_id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'],
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'],
                'created_by' => Auth::id(),
            ]);

            // Update invoice paid amount and status
            $invoice->paid_amount += $validated['amount'];
            if ($invoice->paid_amount >= $invoice->total) {
                $invoice->status = 'paid';
            }
            $invoice->save();

            DB::commit();

            return response()->json([
                'message' => 'Payment recorded successfully.',
                'payment' => $payment,
                'invoice_status' => $invoice->status,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to record payment.'], 500);
        }
    }
}

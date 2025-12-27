<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\Invoice;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:stripe_card,stripe_ach,check,wire,cash,other',
            'payment_reference' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        // Ensure amount doesn't exceed balance? 
        // For now, we allow overpayment or partial payment.

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'company_id' => $invoice->company_id,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_reference' => $validated['payment_reference'] ?? null,
            'payment_date' => $validated['payment_date'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        // Update Invoice Status if paid in full
        $totalPaid = $invoice->payments()->sum('amount');
        if ($totalPaid >= $invoice->total) {
            $invoice->update(['status' => 'paid']);
        }

        return response()->json([
            'message' => 'Payment recorded successfully.',
            'payment' => $payment,
            'invoice_status' => $invoice->status,
        ], 201);
    }
}

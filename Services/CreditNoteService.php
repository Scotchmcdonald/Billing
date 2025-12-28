<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\CreditNote;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CreditNoteService
{
    /**
     * Issue a credit note against an invoice.
     */
    public function issueCreditNote(
        Invoice $invoice,
        int $amountCents,
        string $reason,
        User $issuedBy,
        ?string $notes = null
    ): CreditNote {
        if ($amountCents <= 0) {
            throw new \InvalidArgumentException('Credit note amount must be greater than zero');
        }

        if ($amountCents > ($invoice->total * 100)) { // Convert to cents for comparison
            throw new \RuntimeException(
                'Credit note amount cannot exceed invoice total'
            );
        }

        Log::info('Issuing credit note', [
            'invoice_id' => $invoice->id,
            'amount_cents' => $amountCents,
            'reason' => $reason,
            'issued_by' => $issuedBy->id,
        ]);

        $creditNote = CreditNote::create([
            'invoice_id' => $invoice->id,
            'company_id' => $invoice->company_id,
            'amount' => $amountCents,
            'reason' => $reason,
            'notes' => $notes,
            'issued_by' => $issuedBy->id,
        ]);

        // Optionally dispatch event here
        // event(new CreditNoteIssued($creditNote));

        Log::info('Credit note issued successfully', [
            'credit_note_id' => $creditNote->id,
        ]);

        return $creditNote;
    }

    /**
     * Apply a credit note to an invoice (mark as applied).
     */
    public function applyCreditNote(CreditNote $creditNote): void
    {
        if ($creditNote->applied_at) {
            throw new \RuntimeException('Credit note has already been applied');
        }

        DB::transaction(function () use ($creditNote) {
            $creditNote->update([
                'applied_at' => now(),
            ]);

            // Update invoice paid_amount or status as needed
            $invoice = $creditNote->invoice;
            $creditAmountDecimal = $creditNote->amount / 100; // Convert cents to decimal
            
            $newPaidAmount = $invoice->paid_amount + $creditAmountDecimal;
            $invoice->update([
                'paid_amount' => $newPaidAmount,
            ]);

            // If fully paid after credit, update status
            if ($newPaidAmount >= $invoice->total) {
                $invoice->update(['status' => 'paid']);
            }

            Log::info('Credit note applied', [
                'credit_note_id' => $creditNote->id,
                'invoice_id' => $invoice->id,
                'amount_cents' => $creditNote->amount,
            ]);
        });
    }

    /**
     * Get all credit notes for an invoice.
     */
    public function getCreditNotesForInvoice(Invoice $invoice): Collection
    {
        return $invoice->creditNotes()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all credit notes for a company.
     */
    public function getCreditNotesForCompany(Company $company, ?bool $appliedOnly = null): Collection
    {
        $query = $company->creditNotes()
            ->with(['invoice', 'issuedBy'])
            ->orderBy('created_at', 'desc');

        if ($appliedOnly !== null) {
            if ($appliedOnly) {
                $query->whereNotNull('applied_at');
            } else {
                $query->whereNull('applied_at');
            }
        }

        return $query->get();
    }

    /**
     * Get total credit amount for an invoice (applied and unapplied).
     */
    public function getTotalCreditForInvoice(Invoice $invoice): int
    {
        return $invoice->creditNotes()->sum('amount');
    }

    /**
     * Get unapplied credit balance for an invoice in cents.
     */
    public function getUnappliedCreditForInvoice(Invoice $invoice): int
    {
        return $invoice->creditNotes()
            ->whereNull('applied_at')
            ->sum('amount');
    }

    /**
     * Void/cancel a credit note (soft delete).
     */
    public function voidCreditNote(CreditNote $creditNote, User $voidedBy): void
    {
        if ($creditNote->applied_at) {
            throw new \RuntimeException('Cannot void a credit note that has already been applied');
        }

        Log::info('Voiding credit note', [
            'credit_note_id' => $creditNote->id,
            'voided_by' => $voidedBy->id,
        ]);

        $creditNote->delete(); // Soft delete

        Log::info('Credit note voided', [
            'credit_note_id' => $creditNote->id,
        ]);
    }
}

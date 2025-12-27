<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\Billing\Services\AuditService;

class DisputeService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Flag an invoice as disputed.
     */
    public function flagAsDisputed(Invoice $invoice, string $reason, User $flaggedBy): void
    {
        if ($invoice->is_disputed) {
            throw new \RuntimeException('Invoice is already flagged as disputed');
        }

        $oldValues = [
            'is_disputed' => false,
            'dunning_paused' => $invoice->dunning_paused,
        ];

        $invoice->update([
            'is_disputed' => true,
            'dunning_paused' => true, // Automatically pause dunning when disputed
            'internal_notes' => ($invoice->internal_notes ?? '') . "\n[" . now() . "] Disputed by {$flaggedBy->name}: {$reason}",
        ]);

        $this->auditService->log(
            $invoice,
            'flagged_as_disputed',
            $oldValues,
            [
                'is_disputed' => true,
                'dunning_paused' => true,
                'reason' => $reason,
            ],
            $flaggedBy
        );

        Log::info('Invoice flagged as disputed', [
            'invoice_id' => $invoice->id,
            'flagged_by' => $flaggedBy->id,
            'reason' => $reason,
        ]);
    }

    /**
     * Resolve a dispute on an invoice.
     */
    public function resolveDispute(Invoice $invoice, string $resolution, User $resolvedBy): void
    {
        if (!$invoice->is_disputed) {
            throw new \RuntimeException('Invoice is not flagged as disputed');
        }

        $oldValues = [
            'is_disputed' => true,
            'dunning_paused' => $invoice->dunning_paused,
        ];

        $invoice->update([
            'is_disputed' => false,
            'dunning_paused' => false, // Resume dunning when resolved
            'internal_notes' => ($invoice->internal_notes ?? '') . "\n[" . now() . "] Resolved by {$resolvedBy->name}: {$resolution}",
        ]);

        $this->auditService->log(
            $invoice,
            'dispute_resolved',
            $oldValues,
            [
                'is_disputed' => false,
                'dunning_paused' => false,
                'resolution' => $resolution,
            ],
            $resolvedBy
        );

        Log::info('Invoice dispute resolved', [
            'invoice_id' => $invoice->id,
            'resolved_by' => $resolvedBy->id,
            'resolution' => $resolution,
        ]);
    }

    /**
     * Pause dunning for an invoice without marking as disputed.
     */
    public function pauseDunning(Invoice $invoice, ?string $reason = null, ?User $pausedBy = null): void
    {
        if ($invoice->dunning_paused) {
            return; // Already paused
        }

        $invoice->update([
            'dunning_paused' => true,
            'internal_notes' => $reason 
                ? ($invoice->internal_notes ?? '') . "\n[" . now() . "] Dunning paused: {$reason}"
                : $invoice->internal_notes,
        ]);

        if ($pausedBy) {
            $this->auditService->log(
                $invoice,
                'dunning_paused',
                ['dunning_paused' => false],
                ['dunning_paused' => true, 'reason' => $reason],
                $pausedBy
            );
        }

        Log::info('Dunning paused for invoice', [
            'invoice_id' => $invoice->id,
            'reason' => $reason,
        ]);
    }

    /**
     * Resume dunning for an invoice.
     */
    public function resumeDunning(Invoice $invoice, ?User $resumedBy = null): void
    {
        if (!$invoice->dunning_paused) {
            return; // Already active
        }

        if ($invoice->is_disputed) {
            throw new \RuntimeException('Cannot resume dunning while invoice is disputed. Resolve dispute first.');
        }

        $invoice->update([
            'dunning_paused' => false,
        ]);

        if ($resumedBy) {
            $this->auditService->log(
                $invoice,
                'dunning_resumed',
                ['dunning_paused' => true],
                ['dunning_paused' => false],
                $resumedBy
            );
        }

        Log::info('Dunning resumed for invoice', [
            'invoice_id' => $invoice->id,
        ]);
    }

    /**
     * Get all disputed invoices.
     */
    public function getDisputedInvoices(): Collection
    {
        return Invoice::where('is_disputed', true)
            ->with('company')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get invoices with paused dunning.
     */
    public function getInvoicesWithPausedDunning(): Collection
    {
        return Invoice::where('dunning_paused', true)
            ->with('company')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}

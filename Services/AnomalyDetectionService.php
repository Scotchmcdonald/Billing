<?php

namespace Modules\Billing\Services;

use Modules\Billing\DataTransferObjects\AnomalyReport;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\BillingLog;

class AnomalyDetectionService
{
    public function analyzeInvoice(Invoice $invoice): AnomalyReport
    {
        $flags = [];
        $score = 0;
        $severity = 'info';

        // Load line items
        $invoice->load('lineItems');

        // 1. Compare to previous 3 months average
        $previousInvoices = Invoice::where('company_id', $invoice->company_id)
            ->where('id', '!=', $invoice->id)
            ->where('status', '!=', 'void')
            ->where('status', '!=', 'draft')
            ->orderBy('issue_date', 'desc')
            ->take(3)
            ->with('lineItems')
            ->get();

        if ($previousInvoices->count() > 0) {
            $avgTotal = $previousInvoices->avg('total');
            
            if ($avgTotal > 0) {
                $variance = abs($invoice->total - $avgTotal) / $avgTotal;
                
                if ($variance > 0.50) { // 50% variance
                    $flags[] = "Total variance > 50% (Avg: {$avgTotal}, Current: {$invoice->total})";
                    $score += 60;
                    $severity = 'critical';
                } elseif ($variance > 0.20) { // 20% variance
                    $flags[] = "Total variance > 20% (Avg: {$avgTotal}, Current: {$invoice->total})";
                    $score += 30;
                    $severity = ($severity === 'critical') ? 'critical' : 'warning';
                }
            }
        }

        // Collect previous line items stats
        $previousItems = []; // product_id => [quantities]
        foreach ($previousInvoices as $prevInv) {
            foreach ($prevInv->lineItems as $item) {
                if (!isset($previousItems[$item->product_id])) {
                    $previousItems[$item->product_id] = [];
                }
                $previousItems[$item->product_id][] = $item->quantity;
            }
        }

        // 2. Missing Items Check
        $currentProductIds = $invoice->lineItems->pluck('product_id')->toArray();
        foreach ($previousItems as $productId => $quantities) {
            // If it appeared in at least 2 of the last 3 invoices
            if (count($quantities) >= 2) {
                if (!in_array($productId, $currentProductIds)) {
                    $score += 40;
                    $flags[] = "Missing recurring item (Product ID: {$productId})";
                }
            }
        }

        // 3. Quantity Spike Check & 4. New Item Check
        foreach ($invoice->lineItems as $item) {
            if (!isset($previousItems[$item->product_id])) {
                // New Item
                $score += 10;
                $flags[] = "New Item: {$item->description}";
            } else {
                // Quantity Spike
                $avgQty = array_sum($previousItems[$item->product_id]) / count($previousItems[$item->product_id]);
                if ($avgQty > 0 && $item->quantity > 2 * $avgQty) {
                    $score += 20;
                    $flags[] = "Quantity Spike: {$item->description} ({$item->quantity} vs Avg {$avgQty})";
                }
            }
        }

        // Auto-Actions
        if ($score > 60) {
            $invoice->status = 'pending_review';
            $invoice->save();
            
            BillingLog::create([
                'company_id' => $invoice->company_id,
                'severity' => 'warning',
                'event' => 'anomaly_detected',
                'description' => "Invoice #{$invoice->id} flagged for review. Score: {$score}. Anomalies: " . implode(', ', $flags),
            ]);
            $severity = 'critical';
        }

        return new AnomalyReport($score, $flags, $severity);
    }

    public function flagForReview(Invoice $invoice, string $reason)
    {
        $metadata = $invoice->metadata ?? [];
        $metadata['flagged'] = true;
        $metadata['flag_reason'] = $reason;
        
        $invoice->update(['metadata' => $metadata]);

        // Log to BillingLog if it exists
        if (class_exists(BillingLog::class)) {
            BillingLog::create([
                'company_id' => $invoice->company_id,
                'event' => 'invoice_flagged',
                'description' => "Invoice {$invoice->invoice_number} flagged: {$reason}",
                'severity' => 'warning',
                'user_id' => auth()->id(), // If triggered by user, or null if system
            ]);
        }
    }
}

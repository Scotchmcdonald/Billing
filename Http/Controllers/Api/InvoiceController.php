<?php

namespace Modules\Billing\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Services\InvoiceGenerationService;
use Modules\Billing\Services\AnomalyDetectionService;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    protected $invoiceService;
    protected $anomalyService;

    public function __construct(InvoiceGenerationService $invoiceService, AnomalyDetectionService $anomalyService)
    {
        $this->invoiceService = $invoiceService;
        $this->anomalyService = $anomalyService;
    }

    /**
     * Trigger monthly invoice generation.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request)
    {
        $request->validate([
            'billing_date' => 'nullable|date',
        ]);

        $billingDate = $request->input('billing_date') ? Carbon::parse($request->input('billing_date')) : Carbon::now();

        try {
            $invoices = $this->invoiceService->generateMonthlyInvoices($billingDate);
            return response()->json([
                'message' => 'Invoices generated successfully.',
                'count' => $invoices->count(),
                'invoices' => $invoices->pluck('id'),
            ]);
        } catch (\Exception $e) {
            Log::error('Invoice generation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate invoices.'], 500);
        }
    }

    /**
     * Get invoices pending review.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function pendingReview()
    {
        $invoices = Invoice::where('status', 'pending_review')
            ->with('company')
            ->get()
            ->map(function ($invoice) {
                // Ensure anomaly score is calculated/available
                if (!isset($invoice->metadata['anomaly_score'])) {
                    $report = $this->anomalyService->analyzeInvoice($invoice);
                    $invoice->metadata = array_merge($invoice->metadata ?? [], ['anomaly_score' => $report->score]);
                    $invoice->save();
                }
                return $invoice;
            });

        return response()->json($invoices);
    }

    /**
     * Finalize and send an invoice.
     *
     * @param Invoice $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function finalize(Invoice $invoice)
    {
        if ($invoice->status !== 'pending_review' && $invoice->status !== 'draft') {
            return response()->json(['error' => 'Invoice must be in draft or pending review status to finalize.'], 400);
        }

        try {
            $finalizedInvoice = $this->invoiceService->finalizeDraftInvoice($invoice);
            return response()->json([
                'message' => 'Invoice finalized successfully.',
                'invoice' => $finalizedInvoice,
            ]);
        } catch (\Exception $e) {
            Log::error('Invoice finalization failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to finalize invoice.'], 500);
        }
    }

    /**
     * Void an invoice.
     *
     * @param Invoice $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function void(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return response()->json(['error' => 'Cannot void a paid invoice.'], 400);
        }

        $invoice->update(['status' => 'void']);

        return response()->json([
            'message' => 'Invoice voided successfully.',
            'invoice' => $invoice,
        ]);
    }

    /**
     * Preview invoice PDF.
     *
     * @param Invoice $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function previewPdf(Invoice $invoice)
    {
        // Placeholder for PDF generation logic
        // In a real implementation, this would generate a PDF using a library like dompdf or snappy
        
        return response()->json([
            'message' => 'PDF preview not implemented yet.',
            'invoice_id' => $invoice->id,
            'download_url' => url("/api/v1/finance/invoices/{$invoice->id}/pdf"), // Hypothetical URL
        ]);
    }
}

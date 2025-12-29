<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class ExportService
{
    /**
     * Export data to Excel/CSV based on report type.
     *
     * @param string $reportType
     * @param array<string, mixed> $filters
     * @return string
     */
    public function exportToExcel(string $reportType, array $filters = []): string
    {
        $filename = $this->generateFilename($reportType);
        $path = "exports/{$filename}";

        switch ($reportType) {
            case 'invoices':
                $content = $this->exportInvoices($filters);
                break;
            case 'payments':
                $content = $this->exportPayments($filters);
                break;
            case 'ar_aging':
                $content = $this->exportArAging($filters);
                break;
            case 'subscriptions':
                $content = $this->exportSubscriptions($filters);
                break;
            default:
                throw new \InvalidArgumentException("Unknown report type: {$reportType}");
        }

        Storage::put($path, $content);

        return Storage::path($path);
    }

    /**
     * Export multiple invoices as PDFs in a ZIP file.
     *
     * @param array<int> $invoiceIds
     * @return string
     */
    public function exportInvoicesToPdf(array $invoiceIds): string
    {
        $zipFilename = 'invoices_' . now()->format('Y-m-d_His') . '.zip';
        $zipPath = storage_path("app/exports/{$zipFilename}");

        $zip = new \ZipArchive();
        
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('Could not create ZIP file');
        }

        foreach ($invoiceIds as $invoiceId) {
            $invoice = Invoice::findOrFail($invoiceId);
            
            // Generate PDF (assuming a PDF generation method exists)
            // $pdf = $this->generateInvoicePdf($invoice);
            // $zip->addFromString("invoice_{$invoice->invoice_number}.pdf", $pdf);
            
            // For now, add a placeholder
            $zip->addFromString("invoice_{$invoice->invoice_number}.txt", "Invoice #{$invoice->invoice_number}");
        }

        $zip->close();

        return $zipPath;
    }

    /**
     * Export AR aging report.
     *
     * @param array<string, mixed> $filters
     * @return string
     */
    public function exportArAging(array $filters = []): string
    {
        $invoices = Invoice::whereIn('status', ['sent', 'overdue'])
            ->with('company')
            ->orderBy('due_date', 'asc')
            ->get();

        $csv = Writer::createFromString('');
        
        $csv->insertOne([
            'Invoice Number',
            'Company',
            'Issue Date',
            'Due Date',
            'Total',
            'Paid',
            'Balance',
            'Days Overdue',
            'Aging Bucket',
        ]);

        foreach ($invoices as $invoice) {
            $balance = $invoice->total - $invoice->paid_amount;
            $daysOverdue = $invoice->due_date->isPast() ? (int) now()->diffInDays($invoice->due_date) : 0;
            $agingBucket = $this->getAgingBucket($daysOverdue);

            $csv->insertOne([
                $invoice->invoice_number,
                $invoice->company->name ?? '',
                $invoice->issue_date->format('Y-m-d'),
                $invoice->due_date->format('Y-m-d'),
                $invoice->total,
                $invoice->paid_amount,
                $balance,
                $daysOverdue,
                $agingBucket,
            ]);
        }

        return (string) $csv;
    }

    /**
     * Export payments register.
     */
    public function exportPaymentsRegister(Carbon $startDate, Carbon $endDate): string
    {
        $payments = Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->with(['invoice', 'company'])
            ->orderBy('payment_date', 'desc')
            ->get();

        $csv = Writer::createFromString('');
        
        $csv->insertOne([
            'Payment Date',
            'Company',
            'Invoice Number',
            'Amount',
            'Payment Method',
            'Reference',
            'Created By',
        ]);

        foreach ($payments as $payment) {
            $csv->insertOne([
                $payment->payment_date->format('Y-m-d'),
                $payment->company->name ?? '',
                $payment->invoice->invoice_number ?? '',
                $payment->amount,
                $payment->payment_method,
                $payment->payment_reference ?? '',
                $payment->creator->name ?? '',
            ]);
        }

        return (string) $csv;
    }

    /**
     * Export invoices list.
     *
     * @param array<string, mixed> $filters
     * @return string
     */
    protected function exportInvoices(array $filters): string
    {
        $query = Invoice::with(['company', 'lineItems']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['start_date'])) {
            $query->where('issue_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('issue_date', '<=', $filters['end_date']);
        }

        $invoices = $query->get();

        $csv = Writer::createFromString('');
        
        $csv->insertOne([
            'Invoice Number',
            'Company',
            'Status',
            'Issue Date',
            'Due Date',
            'Subtotal',
            'Tax',
            'Total',
            'Paid Amount',
            'Balance',
            'Line Items Count',
        ]);

        foreach ($invoices as $invoice) {
            $csv->insertOne([
                $invoice->invoice_number,
                $invoice->company->name ?? '',
                $invoice->status,
                $invoice->issue_date->format('Y-m-d'),
                $invoice->due_date->format('Y-m-d'),
                $invoice->subtotal,
                $invoice->tax_total,
                $invoice->total,
                $invoice->paid_amount,
                $invoice->total - $invoice->paid_amount,
                $invoice->lineItems->count(),
            ]);
        }

        return (string) $csv;
    }

    /**
     * Export payments list.
     *
     * @param array<string, mixed> $filters
     * @return string
     */
    protected function exportPayments(array $filters): string
    {
        $query = Payment::with(['invoice', 'company']);

        if (isset($filters['start_date'])) {
            $query->where('payment_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('payment_date', '<=', $filters['end_date']);
        }

        return $this->exportPaymentsRegister(
            Carbon::parse($filters['start_date'] ?? now()->subMonth()),
            Carbon::parse($filters['end_date'] ?? now())
        );
    }

    /**
     * Export subscriptions list.
     *
     * @param array<string, mixed> $filters
     * @return string
     */
    protected function exportSubscriptions(array $filters): string
    {
        $query = \Modules\Billing\Models\Subscription::with('company');

        if (isset($filters['status'])) {
            $query->where('is_active', $filters['status'] === 'active');
        }

        $subscriptions = $query->get();

        $csv = Writer::createFromString('');
        
        $csv->insertOne([
            'Company',
            'Product',
            'Quantity',
            'Price',
            'Frequency',
            'Status',
            'Start Date',
            'Next Billing',
            'Contract End',
            'Renewal Status',
        ]);

        foreach ($subscriptions as $subscription) {
            $csv->insertOne([
                $subscription->company->name ?? '',
                $subscription->product_id ?? '',
                $subscription->quantity,
                $subscription->effective_price,
                $subscription->billing_frequency,
                $subscription->is_active ? 'Active' : 'Inactive',
                $subscription->starts_at ? $subscription->starts_at->format('Y-m-d') : '',
                $subscription->next_billing_date ? $subscription->next_billing_date->format('Y-m-d') : '',
                $subscription->contract_end_date ? $subscription->contract_end_date->format('Y-m-d') : '',
                $subscription->renewal_status ?? '',
            ]);
        }

        return (string) $csv;
    }

    /**
     * Generate filename for export.
     */
    protected function generateFilename(string $reportType): string
    {
        return "{$reportType}_" . now()->format('Y-m-d_His') . '.csv';
    }

    /**
     * Determine aging bucket based on days overdue.
     */
    protected function getAgingBucket(int $daysOverdue): string
    {
        if ($daysOverdue <= 0) return 'Current';
        if ($daysOverdue <= 30) return '1-30 days';
        if ($daysOverdue <= 60) return '31-60 days';
        if ($daysOverdue <= 90) return '61-90 days';
        return '90+ days';
    }
}

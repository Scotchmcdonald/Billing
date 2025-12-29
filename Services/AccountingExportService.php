<?php

declare(strict_types=1);

namespace Modules\Billing\Services;

use Modules\Billing\Models\Company;
use League\Csv\Writer;
use Illuminate\Support\Collection;

use Modules\Billing\Models\Invoice;

class AccountingExportService
{
    /**
     * Generate a CSV export for accounting purposes.
     *
     * @param Collection<int, Company> $companies
     * @return string
     */
    public function generateCsv(Collection $companies): string
    {
        $csv = Writer::createFromString('');
        
        // Header
        $csv->insertOne([
            'Company Name',
            'Stripe Customer ID',
            'Payment Method Type',
            'Last 4 Digits',
            'Current Balance',
            'Created At',
            'Subscription Status',
            'Churn Risk'
        ]);

        foreach ($companies as $company) {
            $churnRisk = $this->calculateChurnRisk($company);
            
            // Calculate balance from invoices instead of calling Stripe API
            $balance = $company->invoices()
                ->whereIn('status', ['sent', 'overdue'])
                ->sum('total');
            
            // Check subscription status from database
            $isSubscribed = $company->subscriptions()
                ->where('is_active', true)
                ->exists();
            
            $csv->insertOne([
                $company->name,
                $company->stripe_id,
                $company->pm_type,
                $company->pm_last_four,
                number_format((float) $balance, 2),
                $company->created_at ? $company->created_at->toIso8601String() : '',
                $isSubscribed ? 'Active' : 'Inactive',
                $churnRisk ? 'High' : 'Low'
            ]);
        }

        return (string) $csv;
    }

    /**
     * Calculate churn risk based on payment method expiration or failures.
     *
     * @param Company $company
     * @return bool
     */
    public function calculateChurnRisk(Company $company): bool
    {
        // Logic: If no payment method, or if we had failed attempts (mocked here as we don't have full history in this context yet)
        if (!$company->pm_type) {
            return true;
        }
        
        // In a real scenario, we would check $company->paymentMethods() and check expiration dates
        // or check recent failed invoices.
        // For now, let's assume if they have a balance > 0 and no recent successful payment, it's a risk.
        
        return false;
    }

    /**
     * Generate a Revenue Recognition Report broken down by Category.
     *
     * @param Collection<int, Invoice> $invoices
     * @return string
     */
    public function generateRevenueRecognitionReport(Collection $invoices): string
    {
        $csv = Writer::createFromString('');
        
        // Header
        $csv->insertOne([
            'Invoice ID',
            'Date',
            'Category', // Managed Service, Hardware, etc.
            'Product Name',
            'Revenue',
            'Cost',
            'Margin'
        ]);

        foreach ($invoices as $invoice) {
            foreach ($invoice->lineItems as $item) {
                $product = $item->product;
                $revenue = $item->subtotal;
                $cost = $product ? ($product->cost_price * $item->quantity) : 0;
                $margin = $revenue - $cost;

                $csv->insertOne([
                    $invoice->id,
                    $invoice->created_at ? $invoice->created_at->toDateString() : '',
                    $product ? $product->category : 'Uncategorized',
                    $item->description,
                    $revenue,
                    $cost,
                    $margin
                ]);
            }
        }

        return (string) $csv;
    }
}

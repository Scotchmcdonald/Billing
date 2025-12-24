<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\BillingLog;
use Modules\Billing\Services\AccountingExportService;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    protected $exportService;

    public function __construct(AccountingExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    public function index()
    {
        // Dashboard for Finance Admin
        $companies = Company::with('subscriptions')->paginate(20);
        
        // Calculate Global Metrics (Simplified for example)
        // In a real app, these would be cached or aggregated from Stripe API
        $totalMrr = $companies->sum(function ($company) {
            return $company->subscriptions->sum(function ($sub) {
                return $sub->active() ? $sub->items->first()->price->unit_amount / 100 : 0;
            });
        });

        return view('billing::admin.dashboard', [
            'companies' => $companies,
            'totalMrr' => $totalMrr,
        ]);
    }

    public function collections()
    {
        // Logic to find past due invoices
        // This would typically query Stripe or a local cache of invoices
        return view('billing::finance.collections');
    }

    public function reports()
    {
        return view('billing::finance.reports');
    }

    public function export()
    {
        // We use chunking in the service or here, but for simplicity let's grab all for the export service
        // In production with thousands of rows, we'd stream this.
        $companies = Company::all();
        
        $csvContent = $this->exportService->generateCsv($companies);

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="finance_export.csv"',
        ]);
    }
}

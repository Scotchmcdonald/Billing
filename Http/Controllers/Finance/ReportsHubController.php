<?php

namespace Modules\Billing\Http\Controllers\Finance;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Retainer;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Models\Quote;
use Modules\Billing\Models\Dispute;
use Modules\Billing\Models\CreditNote;
use Modules\Billing\Models\Contract;
use Modules\Billing\Models\PriceOverride;
use Modules\Billing\Models\Company;
use Modules\Billing\Services\ForecastingService;
use Modules\Billing\Services\AnalyticsService;

class ReportsHubController extends Controller
{
    protected $forecastingService;
    protected $analyticsService;

    public function __construct(
        ForecastingService $forecastingService,
        AnalyticsService $analyticsService
    ) {
        $this->forecastingService = $forecastingService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * Export the current report to CSV
     */
    public function export(Request $request)
    {
        $reportType = $request->input('report_type', 'invoice_report');
        $data = $this->getReportData($request, $reportType);
        
        $filename = $reportType . '_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($data, $reportType) {
            $file = fopen('php://output', 'w');
            
            // Add headers based on report type
            switch ($reportType) {
                case 'revenue_summary':
                    fputcsv($file, ['Month', 'Invoices Count', 'Revenue']);
                    foreach ($data as $row) {
                        fputcsv($file, [$row->month, $row->count, $row->revenue]);
                    }
                    break;
                case 'client_activity':
                    fputcsv($file, ['Client', 'Invoices', 'Total Invoiced', 'Total Paid', 'Balance']);
                    foreach ($data as $row) {
                        fputcsv($file, [
                            $row->company->name ?? 'Unknown',
                            $row->invoice_count,
                            $row->total_invoiced,
                            $row->total_paid,
                            $row->total_invoiced - $row->total_paid
                        ]);
                    }
                    break;
                case 'tax_summary':
                    fputcsv($file, ['Month', 'Taxable Amount', 'Tax Amount', 'Total Amount']);
                    foreach ($data as $row) {
                        fputcsv($file, [$row->month, $row->taxable_amount, $row->tax_amount, $row->total_amount]);
                    }
                    break;
                case 'retainer_report':
                    fputcsv($file, ['Client', 'Hours Purchased', 'Hours Remaining', 'Total Value']);
                    foreach ($data as $row) {
                        fputcsv($file, [
                            $row->company->name ?? 'Unknown',
                            $row->total_purchased,
                            $row->total_remaining,
                            $row->total_value
                        ]);
                    }
                    break;
                case 'churn_report':
                    fputcsv($file, ['Month', 'New Subs', 'New MRR', 'Churned Subs', 'Churned MRR', 'Net MRR Change']);
                    foreach ($data as $row) {
                        fputcsv($file, [
                            $row->month,
                            $row->new_count,
                            $row->new_mrr,
                            $row->churn_count,
                            $row->churn_mrr,
                            $row->new_mrr - $row->churn_mrr
                        ]);
                    }
                    break;
                case 'quote_conversion':
                    fputcsv($file, ['Month', 'Total Quotes', 'Converted', 'Conversion Rate', 'Total Value', 'Converted Value']);
                    foreach ($data as $row) {
                        fputcsv($file, [
                            $row->month,
                            $row->total_quotes,
                            $row->converted_count,
                            number_format($row->conversion_rate, 1) . '%',
                            $row->total_value,
                            $row->converted_value
                        ]);
                    }
                    break;
                default: // Invoice Report / Payment Report
                    fputcsv($file, ['Invoice #', 'Client', 'Date', 'Status', 'Total']);
                    foreach ($data as $row) {
                        fputcsv($file, [
                            $row->invoice_number,
                            $row->company->name ?? 'N/A',
                            $row->issue_date->format('Y-m-d'),
                            $row->status,
                            $row->total
                        ]);
                    }
                    break;
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display the consolidated reports hub with tabs
     */
    public function index(Request $request)
    {
        // Count overdue invoices for badge
        $overdueCount = Invoice::where('status', 'overdue')->count();

        // Get metrics for executive dashboard
        $metrics = $this->analyticsService->getMetrics();
        
        // Calculate MRR Growth
        $currentMRR = \Modules\Billing\Models\Subscription::where('is_active', true)->sum('effective_price');
        $lastMonth = now()->subMonth();
        $lastMonthMRR = \Modules\Billing\Models\Subscription::where('starts_at', '<=', $lastMonth->endOfMonth())
            ->where(function($q) use ($lastMonth) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>', $lastMonth->endOfMonth());
            })
            ->sum('effective_price');
            
        $mrrGrowth = $lastMonthMRR > 0 ? (($currentMRR - $lastMonthMRR) / $lastMonthMRR) * 100 : 0;
        $metrics['mrr'] = $currentMRR;
        $metrics['mrr_growth'] = $mrrGrowth;

        $forecastData = $this->forecastingService->forecastMRR(6);
        $churnRate = $this->forecastingService->forecastChurn();

        // AR Aging data
        $arAging = $this->getArAgingData();

        // Get overdue invoices for the table
        $overdueInvoices = Invoice::with('company')
            ->where('status', 'sent')
            ->where('due_date', '<', now())
            ->orderBy('due_date', 'asc')
            ->limit(50)
            ->get();

        // Profitability data
        $profitability = $this->getProfitabilityData();

        // Top Clients
        $topClients = $this->getTopClientsByRevenue();

        // Report Data based on selection
        $reportType = $request->input('report_type', 'invoice_report');
        $reportData = $this->getReportData($request, $reportType);

        // Profitability by Service
        $profitabilityByService = $this->getProfitabilityByServiceType();

        // Quotes
        $quotes = Quote::with('company')->orderBy('created_at', 'desc')->paginate(15, ['*'], 'quotes_page');

        // Credit Notes
        $creditNotes = CreditNote::with('company')->orderBy('issue_date', 'desc')->paginate(15, ['*'], 'credit_notes_page');

        // Revenue Recognition
        $subscriptions = Subscription::with('company')->active()->get();
        $revenueRecognitionReport = $subscriptions->map(function ($subscription) {
            $monthlyRevenue = $subscription->effective_price;
            $deferredRevenue = 0;
            if (($subscription->billing_frequency ?? 'monthly') === 'annual') {
                $deferredRevenue = $subscription->effective_price * 11;
            }
            return [
                'subscription' => $subscription,
                'monthly_revenue' => $monthlyRevenue,
                'deferred_revenue' => $deferredRevenue,
            ];
        });

        // Contracts
        $contracts = Contract::with('company')->where('renewal_status', 'active')->paginate(15, ['*'], 'contracts_page');
        $expiringContracts = Contract::with('company')->where('contract_end_date', '<=', now()->addDays(30))->where('renewal_status', 'active')->get();
        $churnedContracts = Contract::with('company')->where('contract_end_date', '>=', now()->subDays(90))->where('renewal_status', 'churned')->get();
        // $contracts = collect([]);
        // $expiringContracts = collect([]);
        // $churnedContracts = collect([]);
        $daysAhead = 30;

        // Retainers
        $retainers = Retainer::with('company')->orderBy('created_at', 'desc')->paginate(15, ['*'], 'retainers_page');
        $lowBalanceCount = Retainer::where('hours_remaining', '<=', 5)->where('status', 'active')->count();

        // Disputes
        $disputes = Dispute::with(['invoice.company'])->orderBy('created_at', 'desc')->paginate(15, ['*'], 'disputes_page');

        // Overrides
        $overrides = PriceOverride::with(['company', 'product'])->orderBy('created_at', 'desc')->paginate(15, ['*'], 'overrides_page');

        $companies = Company::orderBy('name')->get();

        return view('billing::finance.reports-hub', compact(
            'overdueCount',
            'metrics',
            'forecastData',
            'churnRate',
            'arAging',
            'overdueInvoices',
            'profitability',
            'topClients',
            'reportData',
            'reportType',
            'profitabilityByService',
            'quotes',
            'creditNotes',
            'revenueRecognitionReport',
            'contracts',
            'expiringContracts',
            'churnedContracts',
            'daysAhead',
            'retainers',
            'lowBalanceCount',
            'disputes',
            'overrides',
            'companies'
        ));
    }

    /**
     * Get Report Data based on type
     */
    protected function getReportData(Request $request, $type)
    {
        switch ($type) {
            case 'revenue_summary':
                return $this->getRevenueSummary($request);
            case 'client_activity':
                return $this->getClientActivity($request);
            case 'tax_summary':
                return $this->getTaxSummary($request);
            case 'retainer_report':
                return $this->getRetainerReport($request);
            case 'churn_report':
                return $this->getChurnReport($request);
            case 'quote_conversion':
                return $this->getQuoteConversionReport($request);
            case 'payment_report':
                return $this->getRecentInvoices($request);
            case 'invoice_report':
            default:
                return $this->getRecentInvoices($request);
        }
    }

    /**
     * Get Revenue Summary
     */
    protected function getRevenueSummary(Request $request)
    {
        $query = Invoice::where('status', 'paid');
        $this->applyDateFilter($query, $request);
        
        // Check driver for date format syntax
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $dateFormat = $driver === 'sqlite' ? 'strftime("%Y-%m", issue_date)' : 'DATE_FORMAT(issue_date, "%Y-%m")';

        // Group by month
        return $query->selectRaw("$dateFormat as month, sum(total) as revenue, count(*) as count")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();
    }

    /**
     * Get Client Activity
     */
    protected function getClientActivity(Request $request)
    {
        $query = Invoice::with('company');
        $this->applyDateFilter($query, $request);
        
        return $query->selectRaw('company_id, sum(total) as total_invoiced, sum(case when status="paid" then total else 0 end) as total_paid, count(*) as invoice_count')
            ->groupBy('company_id')
            ->orderByDesc('total_invoiced')
            ->get();
    }

    /**
     * Get Tax Summary
     */
    protected function getTaxSummary(Request $request)
    {
        $query = Invoice::where('status', 'paid');
        $this->applyDateFilter($query, $request);
        
        // Check driver for date format syntax
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $dateFormat = $driver === 'sqlite' ? 'strftime("%Y-%m", issue_date)' : 'DATE_FORMAT(issue_date, "%Y-%m")';

        return $query->selectRaw("$dateFormat as month, sum(subtotal) as taxable_amount, sum(tax_total) as tax_amount, sum(total) as total_amount")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();
    }

    /**
     * Get Retainer Report
     */
    protected function getRetainerReport(Request $request)
    {
        $query = Retainer::with('company');
        
        // Filter by purchased_at date if date range is provided
        if ($request->has('date_range')) {
            switch ($request->date_range) {
                case 'this_month':
                    $query->whereBetween('purchased_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'last_month':
                    $query->whereBetween('purchased_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
                    break;
                case 'this_quarter':
                    $query->whereBetween('purchased_at', [now()->startOfQuarter(), now()->endOfQuarter()]);
                    break;
                case 'this_year':
                    $query->whereBetween('purchased_at', [now()->startOfYear(), now()->endOfYear()]);
                    break;
                case 'custom':
                    if ($request->has('start_date') && $request->has('end_date')) {
                        $query->whereBetween('purchased_at', [$request->start_date, $request->end_date]);
                    }
                    break;
            }
        }

        return $query->selectRaw('company_id, sum(hours_purchased) as total_purchased, sum(hours_remaining) as total_remaining, sum(price_paid) as total_value')
            ->groupBy('company_id')
            ->orderByDesc('total_remaining')
            ->get();
    }

    /**
     * Get Churn & Retention Report
     */
    protected function getChurnReport(Request $request)
    {
        // This report is slightly different, we want to show trends over time (months)
        // For simplicity in this table view, we will show a summary per month in the selected range
        
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $dateFormat = $driver === 'sqlite' ? 'strftime("%Y-%m", created_at)' : 'DATE_FORMAT(created_at, "%Y-%m")';
        $endDateFormat = $driver === 'sqlite' ? 'strftime("%Y-%m", ends_at)' : 'DATE_FORMAT(ends_at, "%Y-%m")';

        // We need to construct a list of months and query for each
        // For now, let's just get new subscriptions per month
        $newSubs = Subscription::selectRaw("$dateFormat as month, count(*) as new_count, sum(effective_price) as new_mrr")
            ->groupBy('month')
            ->orderBy('month', 'desc');
            
        if ($request->has('date_range')) {
             switch ($request->date_range) {
                case 'this_month':
                    $newSubs->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'last_month':
                    $newSubs->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
                    break;
                case 'this_quarter':
                    $newSubs->whereBetween('created_at', [now()->startOfQuarter(), now()->endOfQuarter()]);
                    break;
                case 'this_year':
                    $newSubs->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
                    break;
                case 'custom':
                    if ($request->has('start_date') && $request->has('end_date')) {
                        $newSubs->whereBetween('created_at', [$request->start_date, $request->end_date]);
                    }
                    break;
            }
        }
        
        $newSubs = $newSubs->get()->keyBy('month');

        // And cancelled subscriptions (churn)
        $churnedSubs = Subscription::selectRaw("$endDateFormat as month, count(*) as churn_count, sum(effective_price) as churn_mrr")
            ->whereNotNull('ends_at')
            ->groupBy('month')
            ->orderBy('month', 'desc');

        if ($request->has('date_range')) {
             switch ($request->date_range) {
                case 'this_month':
                    $churnedSubs->whereBetween('ends_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'last_month':
                    $churnedSubs->whereBetween('ends_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
                    break;
                case 'this_quarter':
                    $churnedSubs->whereBetween('ends_at', [now()->startOfQuarter(), now()->endOfQuarter()]);
                    break;
                case 'this_year':
                    $churnedSubs->whereBetween('ends_at', [now()->startOfYear(), now()->endOfYear()]);
                    break;
                case 'custom':
                    if ($request->has('start_date') && $request->has('end_date')) {
                        $churnedSubs->whereBetween('ends_at', [$request->start_date, $request->end_date]);
                    }
                    break;
            }
        }

        $churnedSubs = $churnedSubs->get()->keyBy('month');

        // Merge data
        $months = $newSubs->keys()->merge($churnedSubs->keys())->unique()->sortDesc();
        $data = [];
        
        foreach ($months as $month) {
            $new = $newSubs->get($month);
            $churn = $churnedSubs->get($month);
            
            $data[] = (object) [
                'month' => $month,
                'new_count' => $new ? $new->new_count : 0,
                'new_mrr' => $new ? $new->new_mrr : 0,
                'churn_count' => $churn ? $churn->churn_count : 0,
                'churn_mrr' => $churn ? $churn->churn_mrr : 0,
            ];
        }
        
        return collect($data);
    }

    /**
     * Get Quote Conversion Report
     */
    protected function getQuoteConversionReport(Request $request)
    {
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $dateFormat = $driver === 'sqlite' ? 'strftime("%Y-%m", created_at)' : 'DATE_FORMAT(created_at, "%Y-%m")';

        $query = Quote::selectRaw("$dateFormat as month, 
            count(*) as total_quotes, 
            sum(case when status='accepted' then 1 else 0 end) as converted_count,
            sum(total) as total_value,
            sum(case when status='accepted' then total else 0 end) as converted_value
        ");

        if ($request->has('date_range')) {
            switch ($request->date_range) {
                case 'this_month':
                    $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'last_month':
                    $query->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
                    break;
                case 'this_quarter':
                    $query->whereBetween('created_at', [now()->startOfQuarter(), now()->endOfQuarter()]);
                    break;
                case 'this_year':
                    $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
                    break;
                case 'custom':
                    if ($request->has('start_date') && $request->has('end_date')) {
                        $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
                    }
                    break;
            }
        }

        return $query->groupBy('month')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function($item) {
                $item->conversion_rate = $item->total_quotes > 0 ? ($item->converted_count / $item->total_quotes) * 100 : 0;
                return $item;
            });
    }

    /**
     * Apply Date Filter helper
     */
    protected function applyDateFilter($query, $request)
    {
        if ($request->has('date_range')) {
            switch ($request->date_range) {
                case 'this_month':
                    $query->whereBetween('issue_date', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'last_month':
                    $query->whereBetween('issue_date', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
                    break;
                case 'this_quarter':
                    $query->whereBetween('issue_date', [now()->startOfQuarter(), now()->endOfQuarter()]);
                    break;
                case 'this_year':
                    $query->whereBetween('issue_date', [now()->startOfYear(), now()->endOfYear()]);
                    break;
                case 'custom':
                    if ($request->has('start_date') && $request->has('end_date')) {
                        $query->whereBetween('issue_date', [$request->start_date, $request->end_date]);
                    }
                    break;
            }
        }
    }

    /**
     * Get Top Clients by Revenue (All Time)
     */
    protected function getTopClientsByRevenue()
    {
        return Invoice::selectRaw('company_id, sum(total) as total_revenue')
            ->where('status', 'paid')
            ->with('company')
            ->groupBy('company_id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();
    }

    /**
     * Get Recent Invoices
     */
    protected function getRecentInvoices(Request $request = null)
    {
        $query = Invoice::with('company');

        if ($request) {
            $this->applyDateFilter($query, $request);

            // Report Type Filter
            if ($request->has('report_type')) {
                if ($request->report_type == 'payment_report') {
                    $query->where('status', 'paid');
                }
            }
            
            // Group By / Ordering
            if ($request->has('group_by')) {
                if ($request->group_by == 'client') {
                    $query->orderBy('company_id');
                } elseif ($request->group_by == 'date') {
                    $query->orderBy('issue_date', 'desc');
                } else {
                    $query->latest('issue_date');
                }
            } else {
                $query->latest('issue_date');
            }
        } else {
            $query->latest('issue_date');
        }

        return $query->limit(50)->get();
    }

    /**
     * Get Profitability by Service Type (based on line item descriptions)
     */
    protected function getProfitabilityByServiceType()
    {
        // Group by product description and calculate revenue and actual cost
        return \Modules\Billing\Models\InvoiceLineItem::selectRaw('
                invoice_line_items.description, 
                sum(invoice_line_items.subtotal) as revenue,
                sum(invoice_line_items.quantity * COALESCE(products.cost_price, 0)) as cost
            ')
            ->join('invoices', 'invoice_line_items.invoice_id', '=', 'invoices.id')
            ->leftJoin('products', 'invoice_line_items.product_id', '=', 'products.id')
            ->where('invoices.status', 'paid')
            ->groupBy('invoice_line_items.description')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                // If cost is 0 (e.g. service with no product link), we might still want to flag it or leave it as 0
                // For now, we use the actual calculated cost from the database
                $item->profit = $item->revenue - $item->cost;
                $item->margin = $item->revenue > 0 ? ($item->profit / $item->revenue) * 100 : 0;
                return $item;
            });
    }

    /**
     * Get AR Aging breakdown
     */
    protected function getArAgingData()
    {
        $buckets = [
            '0-30' => [now()->subDays(30), now()],
            '31-60' => [now()->subDays(60), now()->subDays(31)],
            '61-90' => [now()->subDays(90), now()->subDays(61)],
            '90+' => [null, now()->subDays(90)],
        ];

        $data = [];

        foreach ($buckets as $key => $range) {
            $query = Invoice::where('status', 'sent');
            
            if ($range[0] === null) {
                $query->where('due_date', '<', $range[1]);
            } else {
                $query->whereBetween('due_date', $range);
            }

            $data[$key] = [
                'amount' => $query->sum('total'),
                'count' => $query->count(),
            ];
        }

        return $data;
    }

    /**
     * Get profitability metrics
     */
    protected function getProfitabilityData()
    {
        $revenue = Invoice::where('status', 'paid')->sum('total');
        
        // Calculate actual expenses based on product cost price for paid invoices
        $expenses = \Modules\Billing\Models\InvoiceLineItem::join('invoices', 'invoice_line_items.invoice_id', '=', 'invoices.id')
            ->leftJoin('products', 'invoice_line_items.product_id', '=', 'products.id')
            ->where('invoices.status', 'paid')
            ->sum(\Illuminate\Support\Facades\DB::raw('invoice_line_items.quantity * COALESCE(products.cost_price, 0)'));

        $grossProfit = $revenue - $expenses;
        $margin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;

        // Calculate Revenue Growth (This Month vs Last Month)
        $thisMonthRevenue = Invoice::where('status', 'paid')
            ->whereBetween('issue_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total');
            
        $lastMonthRevenue = Invoice::where('status', 'paid')
            ->whereBetween('issue_date', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('total');
            
        $revenueGrowth = $lastMonthRevenue > 0 ? (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0;

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'gross_profit' => $grossProfit,
            'margin' => round($margin, 1),
            'revenue_growth' => round($revenueGrowth, 1),
        ];
    }
}

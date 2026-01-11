<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\BillingLog;
use Modules\Billing\Services\AccountingExportService;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Subscription;

use Modules\Billing\Services\ProrationCalculator;
use Modules\Billing\Services\RevenueRecognitionService;
use Modules\Billing\Services\ForecastingService;
use Modules\Billing\Services\AnalyticsService;

class FinanceController extends Controller
{
    protected $exportService;
    protected $prorationCalculator;
    protected $revenueService;
    protected $forecastingService;
    protected $analyticsService;

    public function __construct(
        AccountingExportService $exportService, 
        ProrationCalculator $prorationCalculator,
        RevenueRecognitionService $revenueService,
        ForecastingService $forecastingService,
        AnalyticsService $analyticsService
    )
    {
        $this->exportService = $exportService;
        $this->prorationCalculator = $prorationCalculator;
        $this->revenueService = $revenueService;
        $this->forecastingService = $forecastingService;
        $this->analyticsService = $analyticsService;
    }

    public function index()
    {
        return redirect()->route('billing.finance.dashboard');
    }

    public function portalAccess()
    {
        $companies = Company::orderBy('name')->get();
        return view('billing::finance.portal_access', ['companies' => $companies]);
    }

    public function dashboard()
    {
        // MRR Calculation: Sum of all active subscriptions
        try {
            $totalMrr = Subscription::where('is_active', true)->sum('effective_price');
        } catch (\Exception $e) {
            $totalMrr = 0;
        }

        // AR Aging: Placeholder
        $arAging = [
            '0-30' => 12500,
            '31-60' => 4500,
            '61-90' => 1200,
            '90+' => 500,
        ];

        // Gross Profit: Placeholder
        $grossProfit = 45000;

        // Pre-Flight Queue
        $pendingInvoicesCount = 12; 

        // Recent Activity
        $recentActivity = [
            ['action' => 'Invoice Generated', 'description' => 'Invoice #INV-2024-001 for Acme Corp', 'time' => '2 mins ago'],
            ['action' => 'Payment Received', 'description' => '$500.00 from Globex Inc', 'time' => '15 mins ago'],
            ['action' => 'Override Approved', 'description' => '10% discount for Stark Industries', 'time' => '1 hour ago'],
        ];

        // Forecasting
        $forecastData = $this->forecastingService->forecastMRR(6);
        \Illuminate\Support\Facades\Log::info('Dashboard Forecast Data:', $forecastData);
        $churnRate = $this->forecastingService->forecastChurn();

        // Advanced Analytics
        $metrics = $this->analyticsService->getMetrics();

        return view('billing::finance.dashboard', compact('totalMrr', 'arAging', 'grossProfit', 'pendingInvoicesCount', 'recentActivity', 'forecastData', 'churnRate', 'metrics'));
    }




    public function payments()
    {
        return view('billing::finance.payments');
    }

    public function collections()
    {
        return view('billing::finance.collections');
    }

    public function reports()
    {
        return view('billing::finance.reports');
    }

    public function export()
    {
        $companies = Company::all();
        $csvContent = $this->exportService->generateCsv($companies);

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="finance_export.csv"',
        ]);
    }

    public function usageReview()
    {
        $usageChanges = \Modules\Billing\Models\UsageChange::with(['company', 'subscription.product'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('billing::finance.usage-review', compact('usageChanges'));
    }

    public function approveUsageChange(Request $request, $id)
    {
        $change = \Modules\Billing\Models\UsageChange::findOrFail($id);
        
        // Update Subscription
        $subscription = $change->subscription;
        if ($subscription) {
            // Calculate Proration
            $prorationResult = $this->prorationCalculator->calculateProration(
                $subscription, 
                \Carbon\Carbon::now(), 
                $change->new_quantity
            );

            // Create Billable Entry if needed
            if ($prorationResult->amount != 0) {
                \Modules\Billing\Models\BillableEntry::create([
                    'company_id' => $subscription->company_id,
                    'user_id' => auth()->id() ?? 1,
                    'type' => 'product',
                    'description' => "Proration adjustment for usage change (from {$change->old_quantity} to {$change->new_quantity})",
                    'quantity' => 1,
                    'rate' => $prorationResult->amount,
                    'subtotal' => $prorationResult->amount,
                    'is_billable' => true,
                    'date' => now(),
                    'metadata' => [
                        'source' => 'usage_review',
                        'usage_change_id' => $change->id,
                        'proration_details' => $prorationResult->calculation_details,
                    ],
                ]);
            }

            $subscription->quantity = $change->new_quantity;
            $subscription->save();
        }

        $change->status = 'approved';
        $change->save();

        return redirect()->back()->with('success', 'Usage change approved.');
    }

    public function rejectUsageChange(Request $request, $id)
    {
        $change = \Modules\Billing\Models\UsageChange::findOrFail($id);
        $change->status = 'rejected';
        $change->save();

        return redirect()->back()->with('success', 'Usage change rejected.');
    }

    public function profitability()
    {
        $companies = Company::with(['invoices', 'billableEntries'])->get();
        
        $report = $companies->map(function ($company) {
            // Revenue (MTD)
            $revenue = $company->invoices()
                ->whereBetween('issue_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('total');
                
            // COGS
            $cogs = DB::table('invoice_line_items')
                ->join('invoices', 'invoice_line_items.invoice_id', '=', 'invoices.id')
                ->join('products', 'invoice_line_items.product_id', '=', 'products.id')
                ->where('invoices.company_id', $company->id)
                ->whereBetween('invoices.issue_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum(DB::raw('invoice_line_items.quantity * products.cost_price'));
                
            // Labor Cost (using default rate of $50/hour since internal_cost_rate doesn't exist)
            $laborHours = DB::table('billable_entries')
                ->where('billable_entries.company_id', $company->id)
                ->where('billable_entries.type', 'time')
                ->whereBetween('billable_entries.date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('quantity');
                
            $laborCost = $laborHours * 50; // Default internal cost rate
                
            $grossMargin = $revenue - $cogs - $laborCost;
            $grossMarginPercent = $revenue > 0 ? ($grossMargin / $revenue) * 100 : 0;
            
            // Effective Hourly Rate
            $totalHours = $company->billableEntries()
                ->where('type', 'time')
                ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('quantity');
                
            $ehr = $totalHours > 0 ? $revenue / $totalHours : 0;
            
            return [
                'company' => $company,
                'revenue' => $revenue,
                'cogs' => $cogs,
                'labor_cost' => $laborCost,
                'gross_margin' => $grossMargin,
                'gross_margin_percent' => $grossMarginPercent,
                'ehr' => $ehr,
            ];
        })->sortBy('gross_margin_percent');
        
        return view('billing::finance.profitability', compact('report'));
    }

    public function revenueRecognition()
    {
        // Get all active subscriptions
        $subscriptions = \Modules\Billing\Models\Subscription::with('company')->active()->get();
        
        $report = $subscriptions->map(function ($subscription) {
            // Calculate monthly recognized revenue based on subscription price
            $monthlyRevenue = $subscription->effective_price;
            
            // Calculate deferred revenue (prepaid amounts not yet recognized)
            // For subscriptions, this would be any prepaid periods
            $deferredRevenue = 0;
            
            // If annual subscription, calculate deferred revenue
            if (($subscription->billing_frequency ?? 'monthly') === 'annual') {
                // Assume annual payment upfront, recognize monthly
                $deferredRevenue = $subscription->effective_price * 11; // 11 months remaining (example)
            }
            
            return [
                'subscription' => $subscription,
                'monthly_revenue' => $monthlyRevenue,
                'deferred_revenue' => $deferredRevenue,
            ];
        });
        
        return view('billing::finance.revenue-recognition', compact('report'));
    }

    public function settings()
    {
        $settings = \Modules\Billing\Models\BillingSettings::all()->keyBy('key');
        return view('billing::finance.settings', compact('settings'));
    }

    public function updateQuickBooksSettings(Request $request)
    {
        $request->validate([
            'quickbooks_enabled' => 'nullable', // Checkbox sends 'on' or nothing
            'quickbooks_client_id' => 'nullable|string',
            'quickbooks_client_secret' => 'nullable|string',
            'quickbooks_realm_id' => 'nullable|string',
        ]);

        $this->updateSetting('quickbooks_enabled', $request->has('quickbooks_enabled'), 'quickbooks', 'boolean');
        $this->updateSetting('quickbooks_client_id', $request->quickbooks_client_id, 'quickbooks', 'string', true);
        $this->updateSetting('quickbooks_client_secret', $request->quickbooks_client_secret, 'quickbooks', 'string', true);
        $this->updateSetting('quickbooks_realm_id', $request->quickbooks_realm_id, 'quickbooks', 'string');

        return redirect()->back()->with('success', 'QuickBooks settings updated.');
    }

    protected function updateSetting($key, $value, $group, $type, $encrypted = false)
    {
        \Modules\Billing\Models\BillingSettings::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'type' => $type,
                'is_encrypted' => $encrypted
            ]
        );
    }
}

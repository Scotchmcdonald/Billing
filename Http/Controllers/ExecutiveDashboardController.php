<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Billing\Services\AnalyticsService;
use Modules\Billing\Services\ClientHealthService;
use Modules\Billing\Services\AlertService;
use Modules\Billing\Services\ContractService;

class ExecutiveDashboardController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService,
        protected ClientHealthService $healthService,
        protected AlertService $alertService,
        protected ContractService $contractService
    ) {}

    /**
     * Display executive dashboard.
     */
    public function index()
    {
        // Get key metrics
        $baseMetrics = $this->analyticsService->getMetricsWithComparison();
        
        // Calculate MRR
        $mrr = \Modules\Billing\Models\Subscription::where('is_active', true)->sum('effective_price');
        
        // Calculate total AR
        $totalAr = \Modules\Billing\Models\Invoice::whereIn('status', ['sent', 'overdue'])->sum('total');
        
        // Count overdue invoices
        $overdueCount = \Modules\Billing\Models\Invoice::where('status', 'overdue')->count();
        
        // Count active clients
        $activeClients = \Modules\Billing\Models\Company::where('is_active', true)->count();
        
        // Calculate churn rate
        $churnRate = $this->contractService->calculateChurnRate(now()->subDays(90), now());
        
        // Get at-risk clients
        $atRiskClients = $this->healthService->getAtRiskClients(50);
        
        // Get revenue trend (last 12 months)
        $revenueTrend = $this->getRevenueTrend();
        
        // Get top clients
        $topClients = $this->getTopClients(10);
        
        // Get alerts
        $alerts = $this->getAlerts();
        
        $metrics = [
            'mrr' => $mrr,
            'mrr_change' => 0, // TODO: Calculate MRR change month-over-month
            'total_ar' => $totalAr,
            'overdue_count' => $overdueCount,
            'active_clients' => $activeClients,
            'at_risk_clients' => $atRiskClients->count(),
            'churn_rate' => $churnRate,
            'revenue_trend' => $revenueTrend,
            'top_clients' => $topClients,
        ];
        
        return view('billing::finance.executive-dashboard', compact('metrics', 'alerts'));
    }

    /**
     * Get revenue trend for last 12 months.
     */
    protected function getRevenueTrend(): array
    {
        $trend = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthKey = $month->format('M');
            
            $revenue = \Modules\Billing\Models\Invoice::query()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('status', 'paid')
                ->sum('total');
            
            $trend[$monthKey] = $revenue;
        }
        
        return $trend;
    }

    /**
     * Get top clients by revenue.
     */
    protected function getTopClients(int $limit = 10): array
    {
        $clients = \Modules\Billing\Models\Company::query()
            ->withSum(['invoices as total_revenue' => function ($query) {
                $query->where('status', 'paid')
                      ->where('created_at', '>=', now()->subYear());
            }], 'total')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
        
        return $clients->map(function ($client) {
            return [
                'name' => $client->name,
                'revenue' => $client->total_revenue ?? 0,
            ];
        })->toArray();
    }

    /**
     * Get critical alerts.
     */
    protected function getAlerts(): array
    {
        $alerts = [];
        
        try {
            $thresholdAlerts = $this->alertService->checkThresholds();
            
            foreach ($thresholdAlerts as $alert) {
                $alerts[] = [
                    'severity' => $alert['level'] === 'critical' ? 'danger' : 'warning',
                    'title' => $alert['metric'],
                    'message' => $alert['message'],
                    'action_url' => $alert['action_url'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to get alerts', ['error' => $e->getMessage()]);
        }
        
        return $alerts;
    }
}

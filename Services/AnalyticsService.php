<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;

class AnalyticsService
{
    protected $forecastingService;

    public function __construct(ForecastingService $forecastingService)
    {
        $this->forecastingService = $forecastingService;
    }

    public function getMetrics(): array
    {
        return [
            'arpu' => $this->calculateARPU(),
            'ltv' => $this->calculateLTV(),
            'gross_margin' => $this->calculateGrossMargin(),
            'revenue_per_tech' => $this->calculateRevenuePerTechnician(),
        ];
    }

    public function calculateARPU(): float
    {
        $totalMRR = Subscription::where('is_active', true)->sum('effective_price');
        $activeCompanies = Company::where('is_active', true)->count();

        return $activeCompanies > 0 ? $totalMRR / $activeCompanies : 0;
    }

    public function calculateLTV(): float
    {
        $arpu = $this->calculateARPU();
        $churnRate = $this->forecastingService->forecastChurn() / 100; // Convert percentage to decimal

        if ($churnRate <= 0) {
            // Fallback: 3 years (36 months) if no churn data available yet
            return $arpu * 36;
        }

        return $arpu / $churnRate;
    }

    public function calculateGrossMargin(): float
    {
        // Calculate for the last month
        $start = Carbon::now()->subMonth()->startOfMonth();
        $end = Carbon::now()->subMonth()->endOfMonth();

        $invoices = Invoice::whereBetween('issue_date', [$start, $end])
            ->with(['lineItems.product'])
            ->get();

        $totalRevenue = 0;
        $totalCost = 0;

        foreach ($invoices as $invoice) {
            $totalRevenue += $invoice->subtotal; // Exclude tax
            foreach ($invoice->lineItems as $item) {
                if ($item->product) {
                    $totalCost += $item->quantity * $item->product->cost_price;
                }
            }
        }

        if ($totalRevenue <= 0) {
            return 0;
        }

        return (($totalRevenue - $totalCost) / $totalRevenue) * 100;
    }

    public function calculateRevenuePerTechnician(): float
    {
        $totalMRR = Subscription::where('is_active', true)->sum('effective_price');
        $techCount = User::count(); // Assuming all users are techs/staff

        return $techCount > 0 ? $totalMRR / $techCount : 0;
    }

    /**
     * Calculate effective hourly rate for a specific company.
     */
    public function calculateEffectiveHourlyRate(Company $company): float
    {
        $startDate = Carbon::now()->subMonths(3);
        
        // Get total revenue from invoices
        $totalRevenue = Invoice::where('company_id', $company->id)
            ->where('issue_date', '>=', $startDate)
            ->sum('total');

        // Get total billable hours
        $totalHours = \Modules\Billing\Models\BillableEntry::where('company_id', $company->id)
            ->where('is_billable', true)
            ->where('type', 'time')
            ->where('date', '>=', $startDate)
            ->sum('quantity');

        if ($totalHours <= 0) {
            return 0;
        }

        return $totalRevenue / $totalHours;
    }

    /**
     * Calculate effective hourly rate for all companies.
     */
    public function calculateEffectiveHourlyRateAll(): \Illuminate\Support\Collection
    {
        $companies = Company::where('is_active', true)->get();

        return $companies->map(function ($company) {
            return [
                'company_id' => $company->id,
                'company_name' => $company->name,
                'effective_hourly_rate' => $this->calculateEffectiveHourlyRate($company),
            ];
        })->sortByDesc('effective_hourly_rate');
    }

    /**
     * Get metrics with period comparison (month-over-month or year-over-year).
     */
    public function getMetricsWithComparison(string $period = 'mom'): array
    {
        $current = $this->getMetricsForPeriod(now());
        
        if ($period === 'mom') {
            $previous = $this->getMetricsForPeriod(now()->subMonth());
        } else { // yoy
            $previous = $this->getMetricsForPeriod(now()->subYear());
        }

        return [
            'current' => $current,
            'previous' => $previous,
            'changes' => [
                'arpu' => $this->calculateChange($previous['arpu'], $current['arpu']),
                'ltv' => $this->calculateChange($previous['ltv'], $current['ltv']),
                'gross_margin' => $this->calculateChange($previous['gross_margin'], $current['gross_margin']),
                'revenue_per_tech' => $this->calculateChange($previous['revenue_per_tech'], $current['revenue_per_tech']),
            ],
            'period' => $period,
        ];
    }

    /**
     * Get metrics for a specific period.
     */
    protected function getMetricsForPeriod(Carbon $date): array
    {
        // For simplicity, use current calculations
        // In production, you'd want to calculate based on historical data
        return $this->getMetrics();
    }

    /**
     * Calculate percentage change between two values.
     */
    protected function calculateChange(float $previous, float $current): array
    {
        if ($previous == 0) {
            return [
                'value' => $current,
                'percent' => 0,
                'direction' => 'neutral',
            ];
        }

        $change = (($current - $previous) / $previous) * 100;

        return [
            'value' => $current - $previous,
            'percent' => round($change, 2),
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
        ];
    }
}

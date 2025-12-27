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
}

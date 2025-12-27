<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Subscription;
use Modules\Billing\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ForecastingService
{
    /**
     * Forecast MRR for the next X months.
     * Uses a simple linear projection based on active subscriptions and recent growth.
     *
     * @param int $monthsAhead
     * @return array [month_label => predicted_mrr]
     */
    public function forecastMRR(int $monthsAhead = 6): array
    {
        // 1. Calculate Current MRR
        $currentMRR = Subscription::where('is_active', true)
            ->sum('effective_price');

        // 2. Calculate Historical Growth Rate (Last 6 months)
        // We'll look at invoices to approximate historical revenue
        $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfMonth();
        
        $invoices = Invoice::where('status', '!=', 'void')
            ->where('issue_date', '>=', $sixMonthsAgo)
            ->get();

        $historicalRevenue = $invoices->groupBy(function ($invoice) {
            return $invoice->issue_date->format('Y-m');
        })->map(function ($group) {
            return $group->sum('total');
        })->sortKeys()->toArray();

        // Calculate average monthly growth rate
        $growthRates = [];
        $previousMonthTotal = null;
        foreach ($historicalRevenue as $total) {
            if ($previousMonthTotal !== null && $previousMonthTotal > 0) {
                $growthRates[] = ($total - $previousMonthTotal) / $previousMonthTotal;
            }
            $previousMonthTotal = $total;
        }

        $avgGrowthRate = count($growthRates) > 0 ? array_sum($growthRates) / count($growthRates) : 0;
        
        // Cap growth rate for conservative forecasting (e.g., max 5% per month)
        $avgGrowthRate = min($avgGrowthRate, 0.05);
        // Ensure non-negative for safety unless we want to show decline
        // $avgGrowthRate = max($avgGrowthRate, 0); 

        // 3. Project Future MRR
        $forecast = [];
        $projectedMRR = $currentMRR;

        for ($i = 1; $i <= $monthsAhead; $i++) {
            $projectedMRR = $projectedMRR * (1 + $avgGrowthRate);
            $monthLabel = Carbon::now()->addMonths($i)->format('M Y');
            $forecast[$monthLabel] = round($projectedMRR, 2);
        }

        return [
            'current_mrr' => $currentMRR,
            'avg_growth_rate' => $avgGrowthRate,
            'forecast' => $forecast
        ];
    }

    /**
     * Predict Churn Rate for the next quarter.
     * Based on cancellation patterns in the last 90 days.
     *
     * @return float (percentage 0-100)
     */
    public function forecastChurn(): float
    {
        $ninetyDaysAgo = Carbon::now()->subDays(90);

        // Count subscriptions active at start of period (approximate)
        // Active now + Cancelled in last 90 days
        $cancelledSubs = Subscription::whereNotNull('ends_at')
            ->where('ends_at', '>=', $ninetyDaysAgo)
            ->count();

        $activeSubs = Subscription::where('is_active', true)->count();
        
        $totalSubsAtStart = $activeSubs + $cancelledSubs;

        if ($totalSubsAtStart == 0) {
            return 0.0;
        }

        // Churn Rate = Cancelled / Total at Start
        $churnRate = ($cancelledSubs / $totalSubsAtStart) * 100;

        return round($churnRate, 2);
    }
}

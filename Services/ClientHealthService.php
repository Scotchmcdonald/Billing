<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Company;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Subscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class ClientHealthService
{
    /**
     * Calculate a health score for a company (0-100).
     * Higher score = healthier client relationship.
     */
    public function calculateHealthScore(Company $company): array
    {
        $factors = $this->getHealthFactors($company);
        
        // Weighted scoring
        $weights = [
            'profitability' => 0.30,
            'payment_behavior' => 0.25,
            'contract_status' => 0.20,
            'engagement' => 0.15,
            'growth' => 0.10,
        ];

        $score = 0;
        foreach ($weights as $factor => $weight) {
            $score += ($factors[$factor]['score'] ?? 0) * $weight;
        }

        return [
            'score' => round($score, 2),
            'factors' => $factors,
            'risk_level' => $this->getRiskLevel($score),
            'recommendations' => $this->getRecommendations($score, $factors),
        ];
    }

    /**
     * Get companies at risk based on health score threshold.
     */
    public function getAtRiskClients(int $threshold = 40): Collection
    {
        $companies = Company::where('is_active', true)->get();
        
        return $companies->filter(function ($company) use ($threshold) {
            $health = $this->calculateHealthScore($company);
            return $health['score'] < $threshold;
        })->sortBy(function ($company) {
            return $this->calculateHealthScore($company)['score'];
        })->values();
    }

    /**
     * Get individual health factors for a company.
     */
    public function getHealthFactors(Company $company): array
    {
        return [
            'profitability' => $this->assessProfitability($company),
            'payment_behavior' => $this->assessPaymentBehavior($company),
            'contract_status' => $this->assessContractStatus($company),
            'engagement' => $this->assessEngagement($company),
            'growth' => $this->assessGrowth($company),
        ];
    }

    /**
     * Assess profitability factor.
     */
    protected function assessProfitability(Company $company): array
    {
        $invoices = Invoice::where('company_id', $company->id)
            ->where('issue_date', '>=', now()->subMonths(3))
            ->with('lineItems.product')
            ->get();

        if ($invoices->isEmpty()) {
            return ['score' => 50, 'status' => 'unknown', 'detail' => 'No recent invoices'];
        }

        $totalRevenue = $invoices->sum('subtotal');
        $totalCost = 0;

        foreach ($invoices as $invoice) {
            foreach ($invoice->lineItems as $item) {
                if ($item->product) {
                    $totalCost += $item->quantity * ($item->product->cost_price ?? 0);
                }
            }
        }

        $margin = $totalRevenue > 0 ? (($totalRevenue - $totalCost) / $totalRevenue) * 100 : 0;

        if ($margin >= 40) {
            return ['score' => 100, 'status' => 'excellent', 'detail' => "Margin: {$margin}%"];
        } elseif ($margin >= 25) {
            return ['score' => 70, 'status' => 'good', 'detail' => "Margin: {$margin}%"];
        } elseif ($margin >= 15) {
            return ['score' => 40, 'status' => 'acceptable', 'detail' => "Margin: {$margin}%"];
        } else {
            return ['score' => 10, 'status' => 'poor', 'detail' => "Margin: {$margin}%"];
        }
    }

    /**
     * Assess payment behavior.
     */
    protected function assessPaymentBehavior(Company $company): array
    {
        $invoices = Invoice::where('company_id', $company->id)
            ->whereIn('status', ['paid', 'overdue'])
            ->where('issue_date', '>=', now()->subMonths(6))
            ->get();

        if ($invoices->isEmpty()) {
            return ['score' => 50, 'status' => 'unknown', 'detail' => 'No payment history'];
        }

        $paidInvoices = $invoices->where('status', 'paid');
        $overdueCount = $invoices->where('status', 'overdue')->count();
        
        // Calculate average days to pay
        $totalDaysToPay = 0;
        $paidCount = 0;

        foreach ($paidInvoices as $invoice) {
            if ($invoice->payments->isNotEmpty()) {
                $firstPayment = $invoice->payments->first();
                $daysToPay = $invoice->due_date->diffInDays($firstPayment->payment_date, false);
                $totalDaysToPay += $daysToPay;
                $paidCount++;
            }
        }

        $avgDaysToPay = $paidCount > 0 ? $totalDaysToPay / $paidCount : 0;

        if ($overdueCount === 0 && $avgDaysToPay <= 0) {
            return ['score' => 100, 'status' => 'excellent', 'detail' => 'Pays early/on-time'];
        } elseif ($overdueCount === 0 && $avgDaysToPay <= 7) {
            return ['score' => 80, 'status' => 'good', 'detail' => 'Pays within 7 days'];
        } elseif ($overdueCount <= 1 && $avgDaysToPay <= 15) {
            return ['score' => 60, 'status' => 'acceptable', 'detail' => 'Occasional delays'];
        } else {
            return ['score' => 20, 'status' => 'poor', 'detail' => "Overdue: {$overdueCount}"];
        }
    }

    /**
     * Assess contract status.
     */
    protected function assessContractStatus(Company $company): array
    {
        $activeSubscriptions = Subscription::where('company_id', $company->id)
            ->where('is_active', true)
            ->get();

        if ($activeSubscriptions->isEmpty()) {
            return ['score' => 30, 'status' => 'none', 'detail' => 'No active contracts'];
        }

        $expiringSoon = $activeSubscriptions->filter(function ($sub) {
            return $sub->contract_end_date && 
                   $sub->contract_end_date->isBetween(now(), now()->addDays(60));
        })->count();

        $churned = $activeSubscriptions->where('renewal_status', 'churned')->count();

        if ($churned > 0) {
            return ['score' => 10, 'status' => 'churned', 'detail' => "{$churned} churned"];
        } elseif ($expiringSoon > 0) {
            return ['score' => 50, 'status' => 'expiring', 'detail' => "{$expiringSoon} expiring soon"];
        } else {
            return ['score' => 100, 'status' => 'active', 'detail' => "{$activeSubscriptions->count()} active"];
        }
    }

    /**
     * Assess engagement level.
     */
    protected function assessEngagement(Company $company): array
    {
        // This would typically check support tickets, portal logins, etc.
        // For now, use invoice frequency as a proxy
        $recentInvoices = Invoice::where('company_id', $company->id)
            ->where('created_at', '>=', now()->subMonths(3))
            ->count();

        if ($recentInvoices >= 3) {
            return ['score' => 100, 'status' => 'high', 'detail' => "{$recentInvoices} invoices in 3mo"];
        } elseif ($recentInvoices >= 1) {
            return ['score' => 70, 'status' => 'moderate', 'detail' => "{$recentInvoices} invoices in 3mo"];
        } else {
            return ['score' => 30, 'status' => 'low', 'detail' => 'No recent activity'];
        }
    }

    /**
     * Assess growth trend.
     */
    protected function assessGrowth(Company $company): array
    {
        $lastQuarter = Invoice::where('company_id', $company->id)
            ->where('issue_date', '>=', now()->subMonths(3))
            ->sum('total');

        $previousQuarter = Invoice::where('company_id', $company->id)
            ->where('issue_date', '>=', now()->subMonths(6))
            ->where('issue_date', '<', now()->subMonths(3))
            ->sum('total');

        if ($previousQuarter == 0) {
            return ['score' => 50, 'status' => 'new', 'detail' => 'New client'];
        }

        $growthRate = (($lastQuarter - $previousQuarter) / $previousQuarter) * 100;

        if ($growthRate > 20) {
            return ['score' => 100, 'status' => 'growing', 'detail' => "+{$growthRate}%"];
        } elseif ($growthRate > 0) {
            return ['score' => 70, 'status' => 'stable', 'detail' => "+{$growthRate}%"];
        } elseif ($growthRate > -10) {
            return ['score' => 40, 'status' => 'declining', 'detail' => "{$growthRate}%"];
        } else {
            return ['score' => 10, 'status' => 'shrinking', 'detail' => "{$growthRate}%"];
        }
    }

    /**
     * Get risk level based on score.
     */
    protected function getRiskLevel(float $score): string
    {
        if ($score >= 70) return 'low';
        if ($score >= 40) return 'medium';
        return 'high';
    }

    /**
     * Get recommendations based on health factors.
     */
    protected function getRecommendations(float $score, array $factors): array
    {
        $recommendations = [];

        if ($factors['profitability']['score'] < 50) {
            $recommendations[] = 'Review pricing and margins';
        }

        if ($factors['payment_behavior']['score'] < 50) {
            $recommendations[] = 'Implement stricter payment terms';
        }

        if ($factors['contract_status']['score'] < 50) {
            $recommendations[] = 'Schedule renewal discussion';
        }

        if ($factors['engagement']['score'] < 50) {
            $recommendations[] = 'Increase engagement touchpoints';
        }

        if ($factors['growth']['score'] < 50) {
            $recommendations[] = 'Identify upsell opportunities';
        }

        return $recommendations;
    }
}

<?php

namespace Modules\Billing\Services;

use Illuminate\Support\Carbon;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\InvoiceLineItem;

class RevenueRecognitionService
{
    public function calculateMonthlyRevenue(Carbon $month): float
    {
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        // Accrual Revenue
        $accrualRevenue = 0;
        
        // Find line items that overlap with this month
        $lineItems = InvoiceLineItem::whereNotNull('service_period_start')
            ->whereNotNull('service_period_end')
            ->where('service_period_start', '<=', $endOfMonth)
            ->where('service_period_end', '>=', $startOfMonth)
            ->get();

        foreach ($lineItems as $item) {
            $itemStart = Carbon::parse($item->service_period_start);
            $itemEnd = Carbon::parse($item->service_period_end);
            
            // Calculate overlap days
            $overlapStart = $itemStart->max($startOfMonth);
            $overlapEnd = $itemEnd->min($endOfMonth);
            
            $overlapDays = $overlapStart->diffInDays($overlapEnd) + 1;
            $totalDays = $itemStart->diffInDays($itemEnd) + 1;
            
            if ($totalDays > 0) {
                $dailyRate = $item->subtotal / $totalDays;
                $accrualRevenue += $dailyRate * $overlapDays;
            }
        }

        return $accrualRevenue;
    }

    public function getDeferredRevenue(): float
    {
        $deferredRevenue = 0;
        $now = now();

        $lineItems = InvoiceLineItem::whereNotNull('service_period_start')
            ->whereNotNull('service_period_end')
            ->where('service_period_end', '>', $now)
            ->get();

        foreach ($lineItems as $item) {
            $itemStart = Carbon::parse($item->service_period_start);
            $itemEnd = Carbon::parse($item->service_period_end);
            
            $remainingStart = $itemStart->max($now->copy()->addDay());
            
            if ($remainingStart <= $itemEnd) {
                $remainingDays = $remainingStart->diffInDays($itemEnd) + 1;
                $totalDays = $itemStart->diffInDays($itemEnd) + 1;
                
                if ($totalDays > 0) {
                    $dailyRate = $item->subtotal / $totalDays;
                    $deferredRevenue += $dailyRate * $remainingDays;
                }
            }
        }

        return $deferredRevenue;
    }
}

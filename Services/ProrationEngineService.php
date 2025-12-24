<?php

namespace Modules\Billing\Services;

use Carbon\Carbon;

class ProrationEngineService
{
    /**
     * Calculate the prorated amount for a subscription change.
     *
     * @param float $monthlyRate The full monthly rate per unit.
     * @param Carbon $periodStart The start of the billing period.
     * @param Carbon $periodEnd The end of the billing period.
     * @param Carbon $changeDate The date the change occurred.
     * @param int $oldQuantity The quantity before the change.
     * @param int $newQuantity The quantity after the change.
     * @return float The prorated amount to charge (positive) or credit (negative).
     */
    public function calculateProration(
        float $monthlyRate,
        Carbon $periodStart,
        Carbon $periodEnd,
        Carbon $changeDate,
        int $oldQuantity,
        int $newQuantity
    ): float {
        // Ensure dates are valid
        if ($changeDate->lt($periodStart) || $changeDate->gt($periodEnd)) {
            return 0.00;
        }

        $totalDaysInPeriod = $periodStart->diffInDays($periodEnd) + 1; // Inclusive
        $daysRemaining = $changeDate->diffInDays($periodEnd) + 1; // Inclusive of change date? Usually change applies from that day forward.

        if ($totalDaysInPeriod <= 0) {
            return 0.00;
        }

        // Use BCMath for precision
        $dailyRate = bcdiv((string)$monthlyRate, (string)$totalDaysInPeriod, 8);
        $quantityDiff = $newQuantity - $oldQuantity;
        
        $proratedAmount = bcmul(bcmul((string)$quantityDiff, $dailyRate, 8), (string)$daysRemaining, 8);

        return (float) round((float)$proratedAmount, 2);
    }
}

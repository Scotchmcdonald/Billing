<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Company;
use Modules\Billing\Models\Retainer;
use Modules\Billing\Models\BillableEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RetainerService
{
    /**
     * Purchase a new retainer for a company.
     */
    public function purchaseRetainer(
        Company $company,
        float $hours,
        int $priceCents,
        ?Carbon $expiresAt = null
    ): Retainer {
        Log::info('Purchasing retainer', [
            'company_id' => $company->id,
            'hours' => $hours,
            'price_cents' => $priceCents
        ]);

        $retainer = Retainer::create([
            'company_id' => $company->id,
            'hours_purchased' => $hours,
            'hours_remaining' => $hours,
            'price_paid' => $priceCents,
            'purchased_at' => now(),
            'expires_at' => $expiresAt,
            'status' => 'active',
        ]);

        Log::info('Retainer purchased successfully', [
            'retainer_id' => $retainer->id
        ]);

        return $retainer;
    }

    /**
     * Deduct hours from a retainer when work is completed.
     */
    public function deductHours(Retainer $retainer, float $hours, BillableEntry $entry): void
    {
        if ($retainer->status !== 'active') {
            throw new \RuntimeException("Cannot deduct hours from inactive retainer (status: {$retainer->status})");
        }

        if ($hours <= 0) {
            throw new \InvalidArgumentException('Hours to deduct must be greater than zero');
        }

        if ($hours > $retainer->hours_remaining) {
            throw new \RuntimeException(
                "Insufficient retainer hours. Requested: {$hours}, Available: {$retainer->hours_remaining}"
            );
        }

        DB::transaction(function () use ($retainer, $hours, $entry) {
            $newRemaining = $retainer->hours_remaining - $hours;
            
            $retainer->update([
                'hours_remaining' => $newRemaining,
                'status' => $newRemaining <= 0 ? 'depleted' : 'active',
            ]);

            // Optionally link the billable entry to the retainer
            $metadata = $entry->metadata ?? [];
            $metadata['retainer_id'] = $retainer->id;
            $metadata['retainer_hours_used'] = $hours;
            $entry->update(['metadata' => $metadata]);

            Log::info('Hours deducted from retainer', [
                'retainer_id' => $retainer->id,
                'hours_deducted' => $hours,
                'hours_remaining' => $newRemaining,
                'new_status' => $retainer->status,
            ]);
        });
    }

    /**
     * Get the active retainer for a company.
     */
    public function getActiveRetainer(Company $company): ?Retainer
    {
        return $company->retainers()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('expires_at', 'asc')
            ->first();
    }

    /**
     * Check if a retainer is below the low balance threshold.
     */
    public function checkLowBalanceThreshold(Retainer $retainer, float $threshold = 5.0): bool
    {
        return $retainer->hours_remaining <= $threshold && $retainer->hours_remaining > 0;
    }

    /**
     * Expire overdue retainers and return count of retainers expired.
     */
    public function expireOverdueRetainers(): int
    {
        $expiredCount = Retainer::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        if ($expiredCount > 0) {
            Log::info('Expired overdue retainers', ['count' => $expiredCount]);
        }

        return $expiredCount;
    }

    /**
     * Get all retainers for a company with optional status filter.
     */
    public function getRetainers(Company $company, ?string $status = null): Collection
    {
        $query = $company->retainers()->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    /**
     * Get retainers that are low on balance for notification purposes.
     */
    public function getLowBalanceRetainers(float $threshold = 5.0): Collection
    {
        return Retainer::where('status', 'active')
            ->where('hours_remaining', '>', 0)
            ->where('hours_remaining', '<=', $threshold)
            ->with('company')
            ->get();
    }
}

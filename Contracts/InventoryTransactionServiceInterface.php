<?php

namespace Modules\Billing\Contracts;

interface InventoryTransactionServiceInterface
{
    /**
     * Reserve stock for a specific duration.
     * Returns a reservation ID (idempotency key).
     */
    public function reserve(string $sku, int $quantity, string $referenceId, int $ttlMinutes = 10): string;

    /**
     * Commit a reservation, making the deduction permanent.
     */
    public function commit(string $reservationId, string $finalReferenceId): void;

    /**
     * Release/Cancel a reservation.
     */
    public function release(string $reservationId): void;

    /**
     * Direct allocation/decrement without prior reservation.
     * Useful for legacy flows or immediate admin actions.
     */
    public function decrementStock(string $sku, int $quantity, string $reason, string $referenceId): void;
}

<?php

namespace Modules\Billing\Contracts;

interface InventoryTransactionServiceInterface
{
    /**
     * Direct allocation/decrement without prior reservation.
     * Useful for legacy flows or immediate admin actions.
     */
    public function decrementStock(string $sku, int $quantity, string $reason, string $referenceId): void;

    /**
     * Get the current available stock for a product.
     */
    public function getAvailableStock(int $productId): int;
}

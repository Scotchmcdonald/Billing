<?php

namespace Modules\Billing\Contracts;

use Modules\Inventory\DataTransferObjects\ProductSnapshot;

interface ProductAvailabilityServiceInterface
{
    /**
     * Check if a product is available for purchase.
     * 
     * @param string $sku
     * @param int $quantity
     * @return bool
     */
    public function checkAvailability(string $sku, int $quantity = 1): bool;

    /**
     * Get an immutable snapshot of a product by SKU (or ID).
     * 
     * @param string $identifier SKU or ID
     * @return ProductSnapshot|null
     */
    public function getSnapshot(string $identifier): ?ProductSnapshot;
}

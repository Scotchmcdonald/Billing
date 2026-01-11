<?php

namespace Modules\Billing\Services;

use Modules\Billing\Contracts\ProductLookupInterface;

class PricingEngine
{
    protected $productLookup;

    public function __construct(ProductLookupInterface $productLookup)
    {
        $this->productLookup = $productLookup;
    }

    /**
     * Calculate cost for a ticket tier in Cents.
     * 
     * @param string $tier (e.g., 'tier_1', 'tier_2', 'tier_3')
     * @return int Cost in Cents
     */
    public function resolveTicketPrice(string $tier): int
    {
        // Find the product via Interface (Decoupled)
        $product = $this->productLookup->findByTicketTier($tier);

        if (!$product) {
            return 0;
        }

        // Return CASH price in Cents
        return (int) ($product->base_price * 100);
    }

    /**
     * Calculate Credit Point cost for a ticket tier.
     */
    public function resolveTicketDedcution(string $tier): int
    {
        $product = $this->productLookup->findByTicketTier($tier);

        if (!$product) {
            return 0;
        }

        return $product->credit_cost;
    }
}

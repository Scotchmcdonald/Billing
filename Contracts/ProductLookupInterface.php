<?php

namespace Modules\Billing\Contracts;

interface ProductLookupInterface
{
    /**
     * Find a product configuration by ticket tier.
     * Returns a simple object/DTO with pricing details.
     */
    public function findByTicketTier(string $tier): ?object;
}

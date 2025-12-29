<?php

namespace Modules\Billing\DataTransferObjects;

class PriceResult
{
    public function __construct(
        public float $price,
        public string $source, // 'override', 'tier', 'base'
        public float $margin_percent,
        public float $tax_credit = 0.0
    ) {}
}

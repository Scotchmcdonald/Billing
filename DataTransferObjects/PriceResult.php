<?php

namespace Modules\Billing\DataTransferObjects;

use App\ValueObjects\Money;

class PriceResult
{
    public function __construct(
        public Money $price,
        public string $source, // 'override', 'tier', 'base'
        public float $margin_percent,
        public Money $tax_credit
    ) {}
}

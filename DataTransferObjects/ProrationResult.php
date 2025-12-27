<?php

namespace Modules\Billing\DataTransferObjects;

class ProrationResult
{
    public function __construct(
        public float $amount,
        public float $credit_amount,
        public string $policy_used,
        public array $calculation_details
    ) {}
}

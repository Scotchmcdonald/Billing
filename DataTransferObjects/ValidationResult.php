<?php

namespace Modules\Billing\DataTransferObjects;

class ValidationResult
{
    public function __construct(
        public bool $is_safe,
        public float $margin_percent,
        public float $margin_floor_percent,
        public array $warnings = []
    ) {}
}

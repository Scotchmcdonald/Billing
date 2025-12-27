<?php

namespace Modules\Billing\DataTransferObjects;

class AnomalyReport
{
    public function __construct(
        public float $score, // 0-100
        public array $flags,
        public string $severity // 'info', 'warning', 'critical'
    ) {}
}

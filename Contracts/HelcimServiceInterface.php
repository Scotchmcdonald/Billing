<?php

namespace Modules\Billing\Contracts;

use Modules\Billing\DataTransferObjects\HelcimResponseDTO;
use Modules\Billing\Models\Company;

interface HelcimServiceInterface
{
    /**
     * Create a customer in Helcim.
     */
    public function createCustomer(Company $company): ?string;

    /**
     * Process a purchase transaction.
     */
    public function purchase(float $amount, string $ipAddress, ?string $customerCode = null, ?string $cardToken = null): ?HelcimResponseDTO;
}

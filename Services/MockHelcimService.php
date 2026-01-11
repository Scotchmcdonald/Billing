<?php

namespace Modules\Billing\Services;

use Modules\Billing\Contracts\HelcimServiceInterface;
use Modules\Billing\DataTransferObjects\HelcimResponseDTO;
use Modules\Billing\Models\Company;
use Illuminate\Support\Facades\Cache;

class MockHelcimService implements HelcimServiceInterface
{
    /**
     * Create a customer in Helcim (Mocked).
     */
    public function createCustomer(Company $company): ?string
    {
        return 'mock_cust_' . uniqid();
    }

    /**
     * Process a purchase transaction (Mocked).
     *
     * It checks a cache key `helcim_mock_outcome` set by the MockHelcimController
     * to determine what kind of response to return.
     */
    public function purchase(float $amount, string $ipAddress, ?string $customerCode = null, ?string $cardToken = null): ?HelcimResponseDTO
    {
        // 1. Get Simulated Outcome from Controller/UI
        // Default to SUCCESS if not set
        $outcome = Cache::get('helcim_test_mode_outcome', 'APPROVED'); 
        
        // 2. Simulate Delays
        // sleep(1); 

        // 3. Construct Response based on outcome
        $transactionId = 'mock_txn_' . uniqid();
        
        if ($outcome === 'TIMEOUT') {
             // Simulate a "null" return which effectively means "no response received" or an exception
             // Or return an ERROR status DTO depending on how consuming code handles it.
             // We'll return an ERROR DTO for gracefulness.
            return new HelcimResponseDTO(
                transactionId: $transactionId,
                status: 'ERROR',
                amount: $amount,
                currency: 'USD',
                rawResponse: ['error' => 'Simulated Timeout']
            );
        }

        if ($outcome === 'DECLINED') {
            return new HelcimResponseDTO(
                transactionId: $transactionId,
                status: 'DECLINED',
                amount: $amount,
                currency: 'USD',
                cardToken: $cardToken,
                cardNumber: '4242',
                rawResponse: ['responseMessage' => 'INSUFFICIENT FUNDS']
            );
        }

        // APPROVED
        return new HelcimResponseDTO(
            transactionId: $transactionId,
            status: 'APPROVED',
            amount: $amount,
            currency: 'USD',
            cardToken: $cardToken ?? 'mock_tok_' . uniqid(),
            cardNumber: '4242',
            approvalCode: 'TESTAP',
            invoiceNumber: 'INV-' . rand(1000, 9999),
            rawResponse: ['responseMessage' => 'APPROVED']
        );
    }
}

<?php

namespace Modules\Billing\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Billing\Contracts\HelcimServiceInterface;
use Modules\Billing\DataTransferObjects\HelcimResponseDTO;
use Modules\Billing\Models\Company;
use Illuminate\Support\Str;

class HelcimService implements HelcimServiceInterface
{
    protected string $apiKey;
    protected string $accountId;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = setting('helcim_api_key') ?? config('services.helcim.key');
        $this->accountId = setting('helcim_account_id') ?? config('services.helcim.account_id');
        $this->baseUrl = config('services.helcim.url', 'https://api.helcim.com/v2');
    }

    /**
     * Create a customer in Helcim.
     *
     * @param Company $company
     * @return string|null Helcim Customer Code/ID
     */
    public function createCustomer(Company $company): ?string
    {
        try {
            $response = Http::withHeaders([
                'api-token' => $this->apiKey,
                'account-id' => $this->accountId,
            ])->post("{$this->baseUrl}/customers", [
                'contactName' => $company->primaryContact ? $company->primaryContact->name : $company->name,
                'businessName' => $company->name,
                'email' => $company->email,
                'billingAddress' => [
                    'street1' => $company->address,
                    'city' => $company->city,
                    'province' => $company->state,
                    'postalCode' => $company->zip,
                    'country' => $company->country ?? 'USA', // Default to USA if null
                ]
            ]);

            if ($response->successful()) {
                return $response->json('customerCode');
            }

            Log::error('Helcim createCustomer failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Helcim createCustomer exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Process a purchase transaction.
     *
     * @param float $amount
     * @param string $ipAddress
     * @param string|null $customerCode
     * @param string|null $cardToken
     * @return HelcimResponseDTO|null Transaction response
     */
    public function purchase(float $amount, string $ipAddress, ?string $customerCode = null, ?string $cardToken = null): ?HelcimResponseDTO
    {
        try {
            $payload = [
                'currency' => 'USD',
                'amount' => $amount,
                'ipAddress' => $ipAddress,
                'ecommerce' => true,
            ];

            if ($customerCode) {
                $payload['customerCode'] = $customerCode;
            }
            
            if ($cardToken) {
                $payload['cardToken'] = $cardToken;
            }

            $response = Http::withHeaders([
                'api-token' => $this->apiKey,
                'account-id' => $this->accountId,
                'idempotency-key' => Str::uuid()->toString(),
            ])->post("{$this->baseUrl}/payment/purchase", $payload);

            if ($response->successful()) {
                return HelcimResponseDTO::fromArray($response->json());
            }

            Log::error('Helcim purchase failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Helcim purchase exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate a HelcimPay.js session token.
     *
     * @param float $amount
     * @param string $orderNumber
     * @return string|null
     */
    public function createHelcimPaySession(float $amount, string $orderNumber): ?string
    {
        try {
            $response = Http::withHeaders([
                'api-token' => $this->apiKey,
                'account-id' => $this->accountId,
            ])->post("{$this->baseUrl}/helcim-pay/initialize", [
                'paymentType' => 'purchase',
                'amount' => $amount,
                'currency' => 'USD',
                'orderNumber' => $orderNumber,
            ]);

            if ($response->successful()) {
                return $response->json('checkoutToken');
            }

            Log::error('Helcim createHelcimPaySession failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Helcim createHelcimPaySession exception: ' . $e->getMessage());
            return null;
        }
    }
}

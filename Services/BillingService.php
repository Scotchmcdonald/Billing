<?php

namespace Modules\Billing\Services;

use Modules\Crm\Models\Client;
use Modules\Billing\Models\ServiceContract;
use Modules\Billing\Models\Quote;
use Modules\Inventory\Models\Asset;

class BillingService
{
    public function calculateMonthlyMargin(Client $client): float
    {
        // 1. Calculate Revenue from Active Contracts
        $revenue = $client->serviceContracts()
            ->where('status', 'Active')
            ->with(['currentPrice'])
            ->get()
            ->sum(function (ServiceContract $contract) {
                return $contract->currentPrice ? $contract->currentPrice->unit_price : 0;
            });

        // 2. Calculate Software Burden (COGS)
        // Logic: Sum of software costs for all assets under that client
        // This can be refined by OS type if needed for the view
        $burden = 0;
        $assets = $client->assets()->with('softwareProducts')->get();

        foreach ($assets as $asset) {
            $burden += $asset->softwareProducts->sum('monthly_cost');
        }

        // 3. Calculate Margin
        return $revenue - $burden;
    }

    public function getPreFlightData(Client $client)
    {
        $margin = $this->calculateMonthlyMargin($client);
        
        // Calculate total tax credit for all active contracts
        $taxCredit = $client->serviceContracts()
            ->where('status', 'Active')
            ->get()
            ->sum(function ($contract) use ($client) {
                // Assuming quantity 1 for contract, or we need a quantity field on ServiceContract
                return $this->calculateTaxCredit($client, $contract, 1);
            });

        return [
            'client_name' => $client->name,
            'margin' => $margin,
            'tax_credit_preview' => $taxCredit,
            // 'delta' => ... // Logic to compare with previous month would go here
        ];
    }

    public function calculateTaxCredit(Client $client, ServiceContract $contract, int $quantity): float
    {
        if ($client->tier !== 'Non-Profit') {
            return 0.00;
        }

        $currentPrice = $contract->currentPrice ? $contract->currentPrice->unit_price : 0;
        $standardRate = $contract->standard_rate;

        // Credit = (Standard - Custom) * Quantity
        // Assuming Custom Rate is what they pay (unit_price)
        $creditPerUnit = max(0, $standardRate - $currentPrice);

        return $creditPerUnit * $quantity;
    }
    
    public function convertQuoteToContract(Quote $quote): ServiceContract
    {
        // Create Contract
        $contract = new ServiceContract();
        $contract->client_id = $quote->client_id;
        $contract->name = "Contract from Quote #" . $quote->id;
        $contract->status = 'Active';
        // Assuming standard rate comes from somewhere, or 0 for now
        $contract->standard_rate = 0.00; 
        $contract->save();

        // Add Price History
        // We use the quote total as the recurring price
        $contract->priceHistory()->create([
            'unit_price' => $quote->total,
            'started_at' => now(),
        ]);
        
        $quote->status = 'Converted';
        $quote->save();
        
        return $contract;
    }
}

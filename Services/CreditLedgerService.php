<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Company;
use Modules\Billing\Models\CreditTransaction;
use Illuminate\Support\Facades\DB;
use App\ValueObjects\Money;

class CreditLedgerService
{
    /**
     * Get current credit balance for a company.
     * Returns positive integer (available credits).
     */
    public function getBalance(Company $company): int
    {
        // Simple sum of all transactions.
        // Assuming IN (Purchase) is positive, OUT (Usage) is negative?
        // Wait, migration comment: "Positive for Debit (Usage), Negative for Credit (Purchase) or vice versa. Let's standard: +IN (Purchase), -OUT (Usage) like inventory."
        
        return (int) CreditTransaction::where('company_id', $company->id)
            ->where(function($query) {
                // Ignore expired credits? 
                // FIFO logic is complex with just a Sum. 
                // For a "Wallet" model where credits are currency-like and fungible, Sum is fine, 
                // but expiration requires filtering specific IN transactions.
                // For MVP, we sum everything. Expiration would require a separate cleanup job or detailed ledger queries.
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->sum('amount');
    }

    /**
     * Add credits to the wallet (Purchase).
     */
    public function addCredits(Company $company, int $amount, string $reason, ?string $refType = null, ?string $refId = null): CreditTransaction
    {
        return CreditTransaction::create([
            'company_id' => $company->id,
            'type' => 'PURCHASE',
            'amount' => abs($amount), // Ensure positive
            'description' => $reason,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'created_at' => now(),
        ]);
    }

    /**
     * Burn credits (Usage). 
     * Returns true if successful, false if insufficient funds.
     */
    public function burnCredits(Company $company, int $amount, string $reason, ?string $refType = null, ?string $refId = null): bool
    {
        return DB::transaction(function () use ($company, $amount, $reason, $refType, $refId) {
            $balance = $this->getBalance($company);
            $amountToBurn = abs($amount);

            if ($balance < $amountToBurn) {
                return false;
            }

            CreditTransaction::create([
                'company_id' => $company->id,
                'type' => 'USAGE',
                'amount' => -1 * $amountToBurn, // Negative
                'description' => $reason,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'created_at' => now(),
            ]);

            return true;
        });
    }
}

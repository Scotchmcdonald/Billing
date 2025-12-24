<?php

namespace Modules\Billing\Services;

class OffsetFeeService
{
    protected float $creditCardFeePercentage = 0.029; // 2.9%
    protected float $creditCardFeeFixed = 0.30; // $0.30

    /**
     * Calculate the processing fee based on the payment method type.
     *
     * @param float $amount
     * @param string $paymentMethodType
     * @return float
     */
    public function calculateFee(float $amount, string $paymentMethodType): float
    {
        if ($paymentMethodType === 'card') {
            // Use BCMath for precision: ($amount * 0.029) + 0.30
            $fee = bcadd(bcmul((string)$amount, (string)$this->creditCardFeePercentage, 4), (string)$this->creditCardFeeFixed, 4);
            return (float) round((float)$fee, 2);
        }

        // ACH is usually capped or flat fee, let's assume $0 for customer (absorbed by MSP) or small flat fee.
        // Requirement says "Save [Amount] by choosing ACH", implying ACH is cheaper/free for customer.
        return 0.00;
    }

    /**
     * Get the potential savings if switching from Card to ACH.
     *
     * @param float $amount
     * @return float
     */
    public function getPotentialSavings(float $amount): float
    {
        return $this->calculateFee($amount, 'card');
    }
}

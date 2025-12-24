<?php

namespace Modules\Billing\Services;

class PaymentFeeService
{
    /**
     * Calculate the processing fee based on the payment method type.
     *
     * @param float $amount
     * @param string $paymentMethod
     * @return float
     */
    public function calculateFee(float $amount, string $paymentMethod): float
    {
        if ($paymentMethod === 'us_bank_account') {
            return 0.00;
        }

        if ($paymentMethod === 'card') {
            // ($amount * 0.029) + 0.30
            return round(($amount * 0.029) + 0.30, 2);
        }

        // Default to 0 if unknown, or handle as needed.
        // Assuming other methods don't incur this specific surcharge.
        return 0.00;
    }
}

<?php

namespace Modules\Billing\Tests\Unit;

use Tests\TestCase;
use Modules\Billing\Services\PaymentFeeService;

class PaymentFeeServiceTest extends TestCase
{
    public function test_it_calculates_zero_fee_for_ach()
    {
        $service = new PaymentFeeService();
        $fee = $service->calculateFee(100.00, 'us_bank_account');

        $this->assertEquals(0.00, $fee);
    }

    public function test_it_calculates_correct_fee_for_card()
    {
        $service = new PaymentFeeService();
        // $100 * 0.029 + 0.30 = 2.90 + 0.30 = 3.20
        $fee = $service->calculateFee(100.00, 'card');

        $this->assertEquals(3.20, $fee);
    }

    public function test_it_handles_rounding_correctly()
    {
        $service = new PaymentFeeService();
        // $123.45 * 0.029 + 0.30 = 3.58005 + 0.30 = 3.88005 -> 3.88
        $fee = $service->calculateFee(123.45, 'card');

        $this->assertEquals(3.88, $fee);
    }
}

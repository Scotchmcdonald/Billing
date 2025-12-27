<?php

namespace Modules\Billing\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Billing\Services\ForecastingService;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Models\Company;
use Carbon\Carbon;

class ForecastingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $forecastingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->forecastingService = new ForecastingService();
    }

    public function test_forecast_mrr_calculates_current_mrr_correctly()
    {
        // Create companies
        Company::create(['id' => 1, 'name' => 'C1', 'is_active' => true]);
        Company::create(['id' => 2, 'name' => 'C2', 'is_active' => true]);
        Company::create(['id' => 3, 'name' => 'C3', 'is_active' => true]);

        // Create active subscriptions
        Subscription::create([
            'company_id' => 1,
            'name' => 'main',
            'stripe_id' => 'sub_1',
            'stripe_status' => 'active',
            'effective_price' => 100.00,
            'is_active' => true,
        ]);

        Subscription::create([
            'company_id' => 2,
            'name' => 'main',
            'stripe_id' => 'sub_2',
            'stripe_status' => 'active',
            'effective_price' => 200.00,
            'is_active' => true,
        ]);

        // Create inactive subscription
        Subscription::create([
            'company_id' => 3,
            'name' => 'main',
            'stripe_id' => 'sub_3',
            'stripe_status' => 'canceled',
            'effective_price' => 50.00,
            'is_active' => false,
        ]);

        $result = $this->forecastingService->forecastMRR(6);

        $this->assertEquals(300.00, $result['current_mrr']);
    }

    public function test_forecast_mrr_projects_growth()
    {
        // Mock historical invoices to simulate growth
        // Month 1: 1000
        // Month 2: 1100 (10% growth)
        // Month 3: 1210 (10% growth)
        
        $baseDate = Carbon::now()->subMonths(3)->startOfMonth();
        Company::create(['id' => 1, 'name' => 'C1', 'is_active' => true]);
        
        Invoice::create([
            'company_id' => 1,
            'issue_date' => $baseDate,
            'total' => 1000,
            'subtotal' => 1000,
            'tax_total' => 0,
            'status' => 'paid',
            'invoice_number' => 'INV-001',
            'due_date' => $baseDate->copy()->addDays(30),
        ]);

        Invoice::create([
            'company_id' => 1,
            'issue_date' => $baseDate->copy()->addMonth(),
            'total' => 1100,
            'subtotal' => 1100,
            'tax_total' => 0,
            'status' => 'paid',
            'invoice_number' => 'INV-002',
            'due_date' => $baseDate->copy()->addMonth()->addDays(30),
        ]);

        Invoice::create([
            'company_id' => 1,
            'issue_date' => $baseDate->copy()->addMonths(2),
            'total' => 1210,
            'subtotal' => 1210,
            'tax_total' => 0,
            'status' => 'paid',
            'invoice_number' => 'INV-003',
            'due_date' => $baseDate->copy()->addMonths(2)->addDays(30),
        ]);

        // Current MRR
        Subscription::create([
            'company_id' => 1,
            'name' => 'main',
            'stripe_id' => 'sub_1',
            'stripe_status' => 'active',
            'effective_price' => 1210.00,
            'is_active' => true,
        ]);

        $result = $this->forecastingService->forecastMRR(3);

        // Growth rate should be 0.1 (10%), but capped at 0.05 (5%) in service
        $this->assertEquals(0.05, $result['avg_growth_rate']);
        
        // Forecast: 1210 * 1.05 = 1270.5
        $nextMonth = Carbon::now()->addMonth()->format('M Y');
        $this->assertEquals(1270.5, $result['forecast'][$nextMonth]);
    }

    public function test_forecast_churn_calculates_rate()
    {
        // 10 Active Subscriptions
        for ($i = 0; $i < 10; $i++) {
            Company::create(['id' => $i + 1, 'name' => 'C' . ($i + 1), 'is_active' => true]);
            Subscription::create([
                'company_id' => $i + 1,
                'name' => 'main',
                'stripe_id' => 'sub_' . $i,
                'stripe_status' => 'active',
                'effective_price' => 100.00,
                'is_active' => true,
            ]);
        }

        // 2 Cancelled in last 90 days
        Company::create(['id' => 11, 'name' => 'C11', 'is_active' => true]);
        Subscription::create([
            'company_id' => 11,
            'name' => 'main',
            'stripe_id' => 'sub_cancelled_1',
            'stripe_status' => 'canceled',
            'effective_price' => 100.00,
            'is_active' => false,
            'ends_at' => Carbon::now()->subDays(10)
        ]);

        Company::create(['id' => 12, 'name' => 'C12', 'is_active' => true]);
        Subscription::create([
            'company_id' => 12,
            'name' => 'main',
            'stripe_id' => 'sub_cancelled_2',
            'stripe_status' => 'canceled',
            'effective_price' => 100.00,
            'is_active' => false,
            'ends_at' => Carbon::now()->subDays(45)
        ]);

        // Total at start = 10 active + 2 cancelled = 12
        // Churn = 2 / 12 = 16.66%

        $churnRate = $this->forecastingService->forecastChurn();

        $this->assertEquals(16.67, $churnRate);
    }
}

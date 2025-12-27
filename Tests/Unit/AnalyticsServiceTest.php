<?php

namespace Modules\Billing\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Billing\Services\AnalyticsService;
use Modules\Billing\Services\ForecastingService;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\InvoiceLineItem;
use Modules\Inventory\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Mockery;

class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $analyticsService;
    protected $forecastingServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->forecastingServiceMock = Mockery::mock(ForecastingService::class);
        $this->analyticsService = new AnalyticsService($this->forecastingServiceMock);
    }

    public function test_calculate_arpu()
    {
        // 2 Active Companies
        Company::create(['id' => 1, 'name' => 'C1', 'is_active' => true]);
        Company::create(['id' => 2, 'name' => 'C2', 'is_active' => true]);
        Company::create(['id' => 3, 'name' => 'C3', 'is_active' => false]); // Inactive

        // Subscriptions
        Subscription::create(['company_id' => 1, 'effective_price' => 100, 'is_active' => true, 'name' => 'main', 'stripe_id' => 'sub_1', 'stripe_status' => 'active']);
        Subscription::create(['company_id' => 2, 'effective_price' => 200, 'is_active' => true, 'name' => 'main', 'stripe_id' => 'sub_2', 'stripe_status' => 'active']);
        Subscription::create(['company_id' => 3, 'effective_price' => 50, 'is_active' => true, 'name' => 'main', 'stripe_id' => 'sub_3', 'stripe_status' => 'active']); 
        
        // Let's stick to consistent data.
        // C3 inactive, sub inactive.
        $sub3 = Subscription::find(3); // ID might not be 3 if auto-increment, but we set IDs for companies. Subscriptions auto-increment.
        // Actually, I didn't set ID for subscriptions.
        // Let's find by company_id
        $sub3 = Subscription::where('company_id', 3)->first();
        $sub3->is_active = false;
        $sub3->save();

        // Total MRR = 300. Active Companies = 2. ARPU = 150.
        $arpu = $this->analyticsService->calculateARPU();
        $this->assertEquals(150.0, $arpu);
    }

    public function test_calculate_ltv()
    {
        // Mock ARPU calculation by setting up data
        Company::create(['id' => 1, 'name' => 'C1', 'is_active' => true]);
        Subscription::create(['company_id' => 1, 'effective_price' => 100, 'is_active' => true, 'name' => 'main', 'stripe_id' => 'sub_1', 'stripe_status' => 'active']);
        // ARPU = 100

        // Mock Churn Rate from ForecastingService
        $this->forecastingServiceMock->shouldReceive('forecastChurn')
            ->once()
            ->andReturn(5.0); // 5% churn

        // LTV = ARPU / ChurnRate = 100 / 0.05 = 2000
        $ltv = $this->analyticsService->calculateLTV();
        $this->assertEquals(2000.0, $ltv);
    }

    public function test_calculate_gross_margin()
    {
        // Create Product with cost
        $product = Product::create([
            'name' => 'Service',
            'cost_price' => 50.00,
            'base_price' => 100.00,
            'sku' => 'SVC-001',
            'category' => 'Service',
            'type' => 'service',
            'tax_code' => 'TAX001',
            'gl_account_code' => '4000'
        ]);

        // Create Invoice for last month
        $lastMonth = Carbon::now()->subMonth();
        Company::create(['id' => 1, 'name' => 'C1', 'is_active' => true]);
        $invoice = Invoice::create([
            'company_id' => 1,
            'issue_date' => $lastMonth,
            'subtotal' => 200.00, // Revenue
            'total' => 200.00,
            'invoice_number' => 'INV-001',
            'due_date' => $lastMonth->copy()->addDays(30),
            'tax_total' => 0,
            'status' => 'paid'
        ]);

        // Line Item: 2 units. Revenue 200. Cost 2 * 50 = 100.
        InvoiceLineItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 100.00,
            'subtotal' => 200.00,
            'tax_amount' => 0,
            'is_fee' => false,
            'description' => 'Service'
        ]);

        // Margin = (200 - 100) / 200 = 0.5 = 50%
        $margin = $this->analyticsService->calculateGrossMargin();
        $this->assertEquals(50.0, $margin);
    }

    public function test_calculate_revenue_per_technician()
    {
        // 2 Techs
        User::factory()->count(2)->create();

        // MRR = 1000
        Company::create(['id' => 1, 'name' => 'C1', 'is_active' => true]);
        Subscription::create(['company_id' => 1, 'effective_price' => 1000, 'is_active' => true, 'name' => 'main', 'stripe_id' => 'sub_1', 'stripe_status' => 'active']);

        // Rev/Tech = 1000 / 2 = 500
        $rpt = $this->analyticsService->calculateRevenuePerTechnician();
        $this->assertEquals(500.0, $rpt);
    }
}

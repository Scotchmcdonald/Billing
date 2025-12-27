<?php

namespace Modules\Billing\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Models\Company;
use Illuminate\Support\Facades\Gate;

class FinanceDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock permission
        Gate::define('finance.admin', function ($user) {
            return true;
        });
    }

    public function test_dashboard_loads_with_forecasting_data()
    {
        $user = User::factory()->create();

        // Seed data for forecasting
        Company::create(['id' => 1, 'name' => 'C1', 'is_active' => true]);
        Subscription::create([
            'company_id' => 1,
            'name' => 'main',
            'stripe_id' => 'sub_1',
            'stripe_status' => 'active',
            'effective_price' => 100.00,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->get(route('billing.finance.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('billing::finance.dashboard');
        
        // Check if view has the data
        $response->assertViewHas('forecastData');
        $response->assertViewHas('churnRate');
        $response->assertViewHas('metrics');
        
        // Check for specific values in the view data
        $forecastData = $response->viewData('forecastData');
        $this->assertEquals(100.00, $forecastData['current_mrr']);
    }

    public function test_dashboard_displays_widgets()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->get(route('billing.finance.dashboard'));

        $response->assertSee('Revenue Forecast');
        $response->assertSee('Predicted Churn');
        $response->assertSee('ARPU');
        $response->assertSee('LTV');
    }
}

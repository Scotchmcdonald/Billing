<?php

namespace Modules\Billing\Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\BillingAuthorization;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BillingAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_unauthorized_company_billing()
    {
        $user = User::factory()->create();
        $company = Company::create(['name' => 'Test Company']);
        
        // User is NOT authorized

        $response = $this->actingAs($user)
                         ->get(route('billing.portal.dashboard', $company));

        $response->assertStatus(403);
    }

    public function test_authorized_user_can_view_billing()
    {
        $user = User::factory()->create();
        $company = Company::create(['name' => 'Test Company']);
        
        BillingAuthorization::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'role' => 'billing.payer'
        ]);

        $response = $this->actingAs($user)
                         ->get(route('billing.portal.dashboard', $company));

        $response->assertStatus(200);
    }

    public function test_finance_admin_can_export_data()
    {
        $admin = User::factory()->create();
        // Mock permission (assuming Spatie permission or similar gate logic is active and we can mock it, 
        // or we manually set the gate in the test setup if needed. 
        // For this example, we'll assume the gate checks a 'role' attribute or similar on User if not using a package,
        // or we mock the Gate facade).
        
        // Since we used $user->can('finance.admin') in the code, we need to ensure the Gate allows it.
        // A simple way in tests without full permission seeding is to define the gate.
        \Illuminate\Support\Facades\Gate::define('finance.admin', function ($user) {
            return true;
        });

        $response = $this->actingAs($admin)
                         ->get(route('billing.finance.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=utf-8');
    }
}

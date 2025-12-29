<?php

namespace Modules\Billing\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class PreFlightTest extends TestCase
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

    public function test_pre_flight_page_loads_with_invoices_data()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('billing.finance.pre-flight'));

        $response->assertStatus(200);
        $response->assertViewIs('billing::finance.pre-flight');
        
        $response->assertViewHas('invoices');
        
        $invoices = $response->viewData('invoices');
        $this->assertIsArray($invoices);
        $this->assertNotEmpty($invoices);
        
        // Check if the first invoice has line items
        $firstInvoice = $invoices[0];
        $this->assertArrayHasKey('line_items', $firstInvoice);
        $this->assertIsArray($firstInvoice['line_items']);
        $this->assertNotEmpty($firstInvoice['line_items']);
        $this->assertArrayHasKey('description', $firstInvoice['line_items'][0]);
        $this->assertArrayHasKey('amount', $firstInvoice['line_items'][0]);
    }
}

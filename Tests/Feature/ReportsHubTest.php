<?php

namespace Modules\Billing\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Modules\Billing\Models\CreditNote;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Invoice;
use Illuminate\Support\Facades\Gate;

class ReportsHubTest extends TestCase
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

    public function test_reports_hub_loads_with_credit_notes()
    {
        $user = User::factory()->create();
        $company = Company::create(['name' => 'Test Company', 'is_active' => true]);
        $invoice = Invoice::create([
            'company_id' => $company->id,
            'invoice_number' => 'INV-001',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 100,
            'tax_total' => 10,
            'total' => 110,
            'status' => 'sent',
            'currency' => 'USD',
        ]);

        CreditNote::create([
            'invoice_id' => $invoice->id,
            'company_id' => $company->id,
            'amount' => 5000, // cents
            'reason' => 'Overcharge',
            'issued_by' => $user->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('billing.finance.reports-hub'));

        $response->assertStatus(200);
        $response->assertViewIs('billing::finance.reports-hub');
        $response->assertViewHas('creditNotes');
    }
}

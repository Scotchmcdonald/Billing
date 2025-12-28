<?php

namespace Modules\Billing\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Invoice;
use App\Models\User;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_record_manual_payment()
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $this->actingAs($user);

        $company = Company::factory()->create();
        $invoice = Invoice::create([
            'company_id' => $company->id,
            'invoice_number' => 'INV-001',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 100.00,
            'total' => 100.00,
            'status' => 'sent',
        ]);

        $response = $this->postJson(route('billing.finance.payments.store'), [
            'invoice_id' => $invoice->id,
            'amount' => 100.00,
            'payment_method' => 'check',
            'payment_reference' => 'CHK-123',
            'payment_date' => now()->format('Y-m-d'),
            'notes' => 'Received via mail',
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 100.00,
            'payment_method' => 'check',
            'payment_reference' => 'CHK-123',
        ]);

        $this->assertEquals('paid', $invoice->fresh()->status);
    }

    public function test_partial_payment_does_not_mark_invoice_paid()
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $this->actingAs($user);

        $company = Company::factory()->create();
        $invoice = Invoice::create([
            'company_id' => $company->id,
            'invoice_number' => 'INV-002',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 100.00,
            'total' => 100.00,
            'status' => 'sent',
        ]);

        $response = $this->postJson(route('billing.finance.payments.store'), [
            'invoice_id' => $invoice->id,
            'amount' => 50.00,
            'payment_method' => 'check',
            'payment_date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(201);
        $this->assertEquals('sent', $invoice->fresh()->status);
    }
}

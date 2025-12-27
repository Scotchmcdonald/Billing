<?php

namespace Modules\Billing\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Billing\Services\InvoiceGenerationService;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Models\BillableEntry;
use Modules\Inventory\Models\Product;

class InvoiceGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected InvoiceGenerationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InvoiceGenerationService();
    }

    public function test_generates_invoice_for_subscription()
    {
        $company = Company::factory()->create();
        $product = Product::factory()->create(['name' => 'Managed Service']);
        
        Subscription::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'effective_price' => 150.00,
            'quantity' => 2,
            'billing_frequency' => 'monthly',
            'next_billing_date' => now(),
        ]);

        $invoices = $this->service->generateMonthlyInvoices(now());

        $this->assertCount(1, $invoices);
        $invoice = $invoices->first();
        
        $this->assertEquals($company->id, $invoice->company_id);
        $this->assertEquals(300.00, $invoice->subtotal); // 150 * 2
        
        $this->assertDatabaseHas('invoice_line_items', [
            'invoice_id' => $invoice->id,
            'description' => 'Managed Service (Monthly)',
            'quantity' => 2,
            'unit_price' => 150.00,
            'subtotal' => 300.00,
        ]);
    }

    public function test_generates_invoice_for_billable_entries()
    {
        $company = Company::factory()->create();
        
        BillableEntry::factory()->create([
            'company_id' => $company->id,
            'description' => 'On-site Support',
            'quantity' => 2.5,
            'rate' => 100.00,
            'subtotal' => 250.00,
            'invoice_line_item_id' => null,
        ]);

        $invoices = $this->service->generateMonthlyInvoices(now());

        $this->assertCount(1, $invoices);
        $invoice = $invoices->first();
        
        $this->assertEquals(250.00, $invoice->subtotal);
        
        $this->assertDatabaseHas('invoice_line_items', [
            'invoice_id' => $invoice->id,
            'description' => 'On-site Support',
            'quantity' => 2.5,
            'unit_price' => 100.00,
            'subtotal' => 250.00,
        ]);

        // Ensure entry is marked as billed
        $this->assertDatabaseMissing('billable_entries', [
            'company_id' => $company->id,
            'invoice_line_item_id' => null,
        ]);
    }

    public function test_combines_subscription_and_entries()
    {
        $company = Company::factory()->create();
        $product = Product::factory()->create();
        
        Subscription::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'effective_price' => 100.00,
            'quantity' => 1,
        ]);

        BillableEntry::factory()->create([
            'company_id' => $company->id,
            'subtotal' => 50.00,
        ]);

        $invoices = $this->service->generateMonthlyInvoices(now());
        $invoice = $invoices->first();

        $this->assertEquals(150.00, $invoice->subtotal);
        $this->assertCount(2, $invoice->lineItems);
    }

    public function test_skips_inactive_companies()
    {
        $company = Company::factory()->create(['is_active' => false]);
        
        Subscription::factory()->create([
            'company_id' => $company->id,
        ]);

        $invoices = $this->service->generateMonthlyInvoices(now());
        $this->assertCount(0, $invoices);
    }
}

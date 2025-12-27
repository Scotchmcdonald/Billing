<?php

namespace Modules\Billing\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Inventory\Models\Product;
use Modules\Billing\Models\Quote;

class PublicQuoteBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create some products
        Product::factory()->create([
            'name' => 'Basic Support',
            'base_price' => 100.00,
            'is_active' => true,
        ]);
        Product::factory()->create([
            'name' => 'Premium Support',
            'base_price' => 200.00,
            'is_active' => true,
        ]);
    }

    public function test_can_view_quote_builder_page()
    {
        $response = $this->get(route('billing.public.quote.index'));

        $response->assertStatus(200);
        $response->assertSee('Build Your MSP Plan');
        $response->assertSee('Basic Support');
        $response->assertSee('Premium Support');
    }

    public function test_can_calculate_quote_total()
    {
        $product1 = Product::where('name', 'Basic Support')->first();
        $product2 = Product::where('name', 'Premium Support')->first();

        $response = $this->postJson(route('billing.public.quote.calculate'), [
            'items' => [
                ['product_id' => $product1->id, 'quantity' => 2], // 200
                ['product_id' => $product2->id, 'quantity' => 1], // 200
            ]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'total' => 400.00,
                'breakdown' => [
                    [
                        'product' => 'Basic Support',
                        'quantity' => 2,
                        'total' => 200.00
                    ],
                    [
                        'product' => 'Premium Support',
                        'quantity' => 1,
                        'total' => 200.00
                    ]
                ]
            ]);
    }

    public function test_can_submit_quote()
    {
        $product = Product::where('name', 'Basic Support')->first();

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'company_name' => 'Acme Corp',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5] // 500
            ]
        ];

        $response = $this->postJson(route('billing.public.quote.store'), $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'quote_token', 'redirect_url']);

        $this->assertDatabaseHas('quotes', [
            'prospect_name' => 'John Doe',
            'prospect_email' => 'john@example.com',
            'total' => 500.00,
            'status' => 'draft'
        ]);

        $quote = Quote::where('prospect_email', 'john@example.com')->first();
        $this->assertDatabaseHas('quote_line_items', [
            'quote_id' => $quote->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'subtotal' => 500.00
        ]);
    }

    public function test_can_view_submitted_quote()
    {
        $quote = Quote::create([
            'prospect_name' => 'Jane Doe',
            'prospect_email' => 'jane@example.com',
            'total' => 100.00,
            'token' => 'test-token-123',
            'valid_until' => now()->addDays(30),
        ]);

        $product = Product::where('name', 'Basic Support')->first();
        
        $quote->lineItems()->create([
            'product_id' => $product->id,
            'description' => $product->name,
            'quantity' => 1,
            'unit_price' => 100.00,
            'subtotal' => 100.00,
        ]);

        $response = $this->get(route('billing.public.quote.show', 'test-token-123'));

        $response->assertStatus(200);
        $response->assertSee('Quote Request Received!');
        $response->assertSee('Jane Doe');
        $response->assertSee('Basic Support');
        $response->assertSee('$100.00');
    }
}

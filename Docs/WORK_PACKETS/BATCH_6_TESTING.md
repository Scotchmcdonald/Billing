# Batch 6: Testing & Quality Assurance

**Execution Order:** Final (Depends on all previous batches)
**Parallelization:** Test suites can be developed in parallel
**Estimated Effort:** 5-7 days
**Priority:** P1 (Critical for deployment confidence)

---

## Agent Prompt

```
You are a Senior QA Engineer specializing in Laravel testing with PHPUnit and Laravel Dusk.

Your task is to implement comprehensive test coverage for the FinOps billing module. Tests must cover unit, feature, integration, and browser-based scenarios.

## Primary Objectives
1. Achieve 80%+ code coverage on critical paths
2. Create browser tests for all user-facing flows
3. Test all API endpoints
4. Validate edge cases and error handling

## Technical Standards
- Unit tests in `Modules/Billing/Tests/Unit/`
- Feature tests in `Modules/Billing/Tests/Feature/`
- Browser tests in `tests/Browser/Billing/`
- Use factories for test data
- Mock external services
- Use database transactions for isolation

## Testing Principles
- Test behavior, not implementation
- Each test should test ONE thing
- Use descriptive test method names
- Arrange-Act-Assert pattern
- Clean up after browser tests

## Files to Reference
- Existing tests: `tests/` directory
- Factories: `database/factories/`
- Test helpers: `tests/TestCase.php`
- Browser test base: `tests/DuskTestCase.php`

## Validation Criteria
- All tests pass in CI
- No flaky tests
- Coverage report generated
- Critical paths have 100% coverage
```

---

## Context & Technical Details

### Test Directory Structure
```
Modules/Billing/Tests/
├── Unit/
│   ├── Services/
│   ├── Models/
│   └── Jobs/
├── Feature/
│   ├── Controllers/
│   ├── Api/
│   └── Livewire/
└── Integration/
    └── Workflows/

tests/Browser/Billing/
├── InvoiceFlowTest.php
├── PaymentFlowTest.php
├── PortalFlowTest.php
└── ...
```

### Test Traits Available
```php
use RefreshDatabase;           // Reset DB each test
use WithFaker;                 // Generate fake data
use CreatesApplication;        // Bootstrap app
use DatabaseTransactions;      // Wrap in transaction
```

### Mocking External Services
```php
// Mock Stripe
Http::fake([
    'api.stripe.com/*' => Http::response(['id' => 'ch_123'], 200),
]);

// Mock RMM webhook
$this->mock(RmmService::class, function ($mock) {
    $mock->shouldReceive('getDeviceCount')->andReturn(50);
});
```

---

## Task Checklist

### 6.1 Factories

#### Invoice Factory
- [ ] Create `Modules/Billing/Database/Factories/InvoiceFactory.php`
  ```php
  class InvoiceFactory extends Factory
  {
      public function definition(): array
      {
          return [
              'company_id' => Company::factory(),
              'invoice_number' => 'INV-' . $this->faker->unique()->numberBetween(1000, 9999),
              'status' => 'draft',
              'subtotal' => $this->faker->randomFloat(2, 100, 10000),
              'tax' => fn($attrs) => $attrs['subtotal'] * 0.1,
              'total' => fn($attrs) => $attrs['subtotal'] + $attrs['tax'],
              'due_date' => now()->addDays(30),
          ];
      }
      
      public function sent(): static
      public function paid(): static
      public function overdue(): static
  }
  ```

#### Additional Factories
- [ ] `PaymentFactory.php`
- [ ] `InvoiceLineItemFactory.php`
- [ ] `QuoteFactory.php`
- [ ] `RetainerFactory.php`
- [ ] `CreditNoteFactory.php`
- [ ] `ProductBundleFactory.php`
- [ ] `DisputeFactory.php`

### 6.2 Unit Tests - Services

#### InvoiceService Tests
- [ ] Create `Modules/Billing/Tests/Unit/Services/InvoiceServiceTest.php`
  ```php
  class InvoiceServiceTest extends TestCase
  {
      /** @test */
      public function it_calculates_invoice_totals_correctly()
      
      /** @test */
      public function it_applies_discounts_correctly()
      
      /** @test */
      public function it_generates_unique_invoice_numbers()
      
      /** @test */
      public function it_prevents_editing_finalized_invoices()
  }
  ```

#### RetainerService Tests
- [ ] Create `Modules/Billing/Tests/Unit/Services/RetainerServiceTest.php`
- [ ] Test: Hours deduction
- [ ] Test: Balance tracking
- [ ] Test: Overage calculation
- [ ] Test: Auto-renewal

#### CreditNoteService Tests
- [ ] Create `Modules/Billing/Tests/Unit/Services/CreditNoteServiceTest.php`
- [ ] Test: Creation from invoice
- [ ] Test: Partial credit
- [ ] Test: Application to future invoice

#### AnomalyDetectionService Tests
- [ ] Create `Modules/Billing/Tests/Unit/Services/AnomalyDetectionServiceTest.php`
- [ ] Test: Spike detection
- [ ] Test: Pattern analysis
- [ ] Test: Threshold configuration

### 6.3 Unit Tests - Models

#### Invoice Model Tests
- [ ] Create `Modules/Billing/Tests/Unit/Models/InvoiceTest.php`
- [ ] Test: Relationships (company, lineItems, payments)
- [ ] Test: Scopes (overdue, unpaid, byStatus)
- [ ] Test: Accessors (formatted amounts, status badge)
- [ ] Test: Status transitions

#### Payment Model Tests
- [ ] Create `Modules/Billing/Tests/Unit/Models/PaymentTest.php`
- [ ] Test: Payment allocation
- [ ] Test: Refund processing
- [ ] Test: Receipt generation

### 6.4 Unit Tests - Jobs

#### GenerateRecurringInvoicesJob Test
- [ ] Create `Modules/Billing/Tests/Unit/Jobs/GenerateRecurringInvoicesJobTest.php`
- [ ] Test: Correct invoices generated
- [ ] Test: Skips already-invoiced periods
- [ ] Test: Handles client opt-outs

#### ProcessAutoPaymentsJob Test
- [ ] Create `Modules/Billing/Tests/Unit/Jobs/ProcessAutoPaymentsJobTest.php`
- [ ] Test: Charges correct invoices
- [ ] Test: Handles payment failures
- [ ] Test: Respects auto-pay settings

### 6.5 Feature Tests - Controllers

#### InvoiceController Tests
- [ ] Create `Modules/Billing/Tests/Feature/Controllers/InvoiceControllerTest.php`
  ```php
  class InvoiceControllerTest extends TestCase
  {
      /** @test */
      public function finance_admin_can_view_invoice_index()
      
      /** @test */
      public function finance_admin_can_create_invoice()
      
      /** @test */
      public function finance_admin_can_send_invoice()
      
      /** @test */
      public function technician_cannot_delete_invoice()
      
      /** @test */
      public function it_validates_required_fields()
  }
  ```

#### PaymentController Tests
- [ ] Create `Modules/Billing/Tests/Feature/Controllers/PaymentControllerTest.php`
- [ ] Test: Record manual payment
- [ ] Test: Process refund
- [ ] Test: Allocate payment to invoices

#### QuoteController Tests
- [ ] Create `Modules/Billing/Tests/Feature/Controllers/QuoteControllerTest.php`
- [ ] Test: Create quote
- [ ] Test: Send quote
- [ ] Test: Convert to invoice

### 6.6 Feature Tests - API

#### Billing API Tests
- [ ] Create `Modules/Billing/Tests/Feature/Api/InvoiceApiTest.php`
  ```php
  class InvoiceApiTest extends TestCase
  {
      /** @test */
      public function api_returns_paginated_invoices()
      
      /** @test */
      public function api_filters_by_status()
      
      /** @test */
      public function api_requires_authentication()
      
      /** @test */
      public function api_respects_rate_limits()
  }
  ```

#### Webhook Tests
- [ ] Create `Modules/Billing/Tests/Feature/Api/StripeWebhookTest.php`
- [ ] Test: Payment intent succeeded
- [ ] Test: Payment failed
- [ ] Test: Subscription updated
- [ ] Test: Invalid signature rejected

### 6.7 Feature Tests - Livewire Components

#### Invoice Table Tests
- [ ] Create `Modules/Billing/Tests/Feature/Livewire/InvoiceTableTest.php`
  ```php
  class InvoiceTableTest extends TestCase
  {
      /** @test */
      public function it_renders_invoice_list()
      
      /** @test */
      public function it_filters_by_status()
      
      /** @test */
      public function it_searches_by_invoice_number()
      
      /** @test */
      public function it_sorts_by_columns()
      
      /** @test */
      public function it_paginates_results()
  }
  ```

#### Payment Modal Tests
- [ ] Create `Modules/Billing/Tests/Feature/Livewire/PaymentModalTest.php`
- [ ] Test: Modal opens with invoice data
- [ ] Test: Validates payment amount
- [ ] Test: Records payment on submit

### 6.8 Integration Tests - Workflows

#### Full Billing Cycle Test
- [ ] Create `Modules/Billing/Tests/Integration/FullBillingCycleTest.php`
  ```php
  class FullBillingCycleTest extends TestCase
  {
      /** @test */
      public function complete_billing_cycle_works_end_to_end()
      {
          // Create client
          // Add service agreement
          // Generate invoice
          // Send invoice
          // Receive payment (mocked)
          // Verify accounting sync
          // Check audit trail
      }
  }
  ```

#### Quote to Invoice Test
- [ ] Create `Modules/Billing/Tests/Integration/QuoteToInvoiceTest.php`
- [ ] Test: Full quote lifecycle

#### Dispute Resolution Test
- [ ] Create `Modules/Billing/Tests/Integration/DisputeResolutionTest.php`
- [ ] Test: Dispute workflow

### 6.9 Browser Tests (Dusk)

#### Invoice Management Flow
- [ ] Create `tests/Browser/Billing/InvoiceManagementTest.php`
  ```php
  class InvoiceManagementTest extends DuskTestCase
  {
      /** @test */
      public function finance_admin_can_create_and_send_invoice()
      {
          $this->browse(function (Browser $browser) {
              $browser->loginAs($this->financeAdmin)
                  ->visit('/billing/invoices')
                  ->click('@create-invoice-btn')
                  ->select('@company-select', $this->company->id)
                  ->click('@add-line-item')
                  ->type('@line-description', 'Monthly Support')
                  ->type('@line-amount', '500.00')
                  ->press('Save Draft')
                  ->assertSee('Invoice created')
                  ->press('Send Invoice')
                  ->assertSee('Invoice sent');
          });
      }
  }
  ```

#### Client Portal Payment Flow
- [ ] Create `tests/Browser/Billing/ClientPortalPaymentTest.php`
- [ ] Test: View invoice
- [ ] Test: Download PDF
- [ ] Test: Make payment (with Stripe test mode)
- [ ] Test: View receipt

#### Quote Acceptance Flow
- [ ] Create `tests/Browser/Billing/QuoteAcceptanceTest.php`
- [ ] Test: Client views quote
- [ ] Test: Client accepts quote
- [ ] Test: Signature capture

### 6.10 Edge Case Tests

#### Error Handling Tests
- [ ] Create `Modules/Billing/Tests/Feature/ErrorHandlingTest.php`
- [ ] Test: Payment gateway timeout
- [ ] Test: Invalid invoice state transition
- [ ] Test: Concurrent payment attempts
- [ ] Test: Database deadlock recovery

#### Boundary Tests
- [ ] Test: Zero-amount invoice
- [ ] Test: Negative credit balance
- [ ] Test: Maximum line items
- [ ] Test: Long descriptions
- [ ] Test: Special characters in input

#### Permission Tests
- [ ] Create `Modules/Billing/Tests/Feature/PermissionTest.php`
- [ ] Test: Each role's access to each endpoint
- [ ] Test: Cross-tenant data isolation

### 6.11 Performance Tests

#### Load Testing Setup
- [ ] Create `tests/Performance/BillingLoadTest.php`
- [ ] Test: Invoice list with 10,000 invoices
- [ ] Test: Search performance
- [ ] Test: Bulk invoice generation

#### Query Optimization Verification
- [ ] Test: N+1 queries detected
- [ ] Test: Index usage verified

---

## Test Configuration

### phpunit.xml Addition
```xml
<testsuites>
    <testsuite name="Billing">
        <directory>./Modules/Billing/Tests</directory>
    </testsuite>
</testsuites>
```

### Coverage Configuration
```xml
<coverage processUncoveredFiles="true">
    <include>
        <directory suffix=".php">./Modules/Billing/</directory>
    </include>
    <exclude>
        <directory>./Modules/Billing/Tests</directory>
        <directory>./Modules/Billing/Database</directory>
    </exclude>
</coverage>
```

---

## Completion Verification

```bash
# Run all billing tests
php artisan test --filter=Billing

# Run with coverage
php artisan test --filter=Billing --coverage --min=80

# Run browser tests
php artisan dusk --filter=Billing

# Generate coverage report
XDEBUG_MODE=coverage php artisan test --filter=Billing --coverage-html=reports/coverage

# Check for flaky tests (run 5 times)
for i in {1..5}; do php artisan test --filter=Billing || exit 1; done
```

---

## Coverage Targets

| Component | Target | Critical Paths |
|-----------|--------|----------------|
| InvoiceService | 90% | Create, Send, Calculate |
| PaymentService | 90% | Record, Refund, Allocate |
| RetainerService | 85% | Deduct, Balance, Renew |
| Controllers | 80% | CRUD, Validation |
| Jobs | 85% | Execute, Retry, Fail |
| Models | 75% | Relationships, Scopes |

---

## Downstream Dependencies
- None (final batch)
- Enables CI/CD deployment confidence
- Required before production release

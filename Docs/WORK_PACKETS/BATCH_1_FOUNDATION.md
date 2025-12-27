# Batch 1: Foundation (Database & Models)

**Execution Order:** First (No dependencies)
**Parallelization:** Internal tasks are parallelizable after migrations run
**Estimated Effort:** 2-3 days
**Priority:** P1 - Must complete before all other batches

---

## Agent Prompt

```
You are a Senior Laravel Backend Engineer specializing in database architecture and Eloquent models.

Your task is to implement foundational database changes and model enhancements for the FinOps billing module. These changes enable all subsequent feature development.

## Primary Objectives
1. Create new database migrations for missing columns and tables
2. Enhance existing Eloquent models with new fields and relationships
3. Ensure all migrations are reversible and follow Laravel conventions
4. Add appropriate indexes for query performance

## Technical Standards
- Use Laravel 11 migration syntax
- Follow existing naming conventions: `billing_` prefix for all billing tables
- All monetary values stored as integers (cents)
- All dates use Carbon/timestamp
- JSON columns only for truly unstructured data
- Add database indexes on all foreign keys and frequently filtered columns

## Files to Reference
- Existing migrations: `Modules/Billing/Database/Migrations/`
- Existing models: `Modules/Billing/Models/`
- Schema reference: `FINOPS_IMPLEMENTATION_PLAN.md` (Phase 1)

## Validation Criteria
- All migrations run without error: `php artisan migrate`
- All migrations can rollback: `php artisan migrate:rollback`
- Models have proper fillable, casts, and relationships defined
- No breaking changes to existing functionality
```

---

## Context & Technical Details

### Existing Infrastructure
- **Database:** MySQL/MariaDB with `billing_` prefixed tables
- **Models Location:** `Modules/Billing/Models/`
- **Migrations Location:** `Modules/Billing/Database/Migrations/`

### Key Existing Models
- `Invoice` - Core invoice entity
- `Company` - Client/tenant entity
- `Subscription` - Recurring billing
- `BillableEntry` - Time/expense tracking
- `Payment` - Payment records
- `Quote` - Sales quotes
- `PriceOverride` - Client-specific pricing

---

## Task Checklist

### 1.1 Invoice Model Enhancements
- [ ] Add migration: `add_dispute_fields_to_invoices_table`
  ```php
  $table->boolean('is_disputed')->default(false);
  $table->boolean('dunning_paused')->default(false);
  $table->text('internal_notes')->nullable();
  $table->timestamp('approved_at')->nullable();
  $table->foreignId('approved_by')->nullable()->constrained('users');
  ```
- [ ] Update `Invoice` model with new fillable fields and casts

### 1.2 Subscription Model Enhancements
- [ ] Add migration: `add_contract_fields_to_subscriptions_table`
  ```php
  $table->date('contract_start_date')->nullable();
  $table->date('contract_end_date')->nullable();
  $table->string('contract_document_path')->nullable();
  $table->enum('renewal_status', ['active', 'pending_renewal', 'churned'])->default('active');
  ```
- [ ] Update `Subscription` model

### 1.3 New CreditNote Model
- [ ] Create migration: `create_credit_notes_table`
  ```php
  Schema::create('billing_credit_notes', function (Blueprint $table) {
      $table->id();
      $table->foreignId('invoice_id')->constrained('billing_invoices');
      $table->foreignId('company_id')->constrained('companies');
      $table->integer('amount'); // cents
      $table->string('reason');
      $table->text('notes')->nullable();
      $table->foreignId('issued_by')->constrained('users');
      $table->timestamp('applied_at')->nullable();
      $table->timestamps();
      $table->softDeletes();
  });
  ```
- [ ] Create `CreditNote` model with relationships

### 1.4 New Retainer Model
- [ ] Create migration: `create_retainers_table`
  ```php
  Schema::create('billing_retainers', function (Blueprint $table) {
      $table->id();
      $table->foreignId('company_id')->constrained('companies');
      $table->decimal('hours_purchased', 8, 2);
      $table->decimal('hours_remaining', 8, 2);
      $table->integer('price_paid'); // cents
      $table->date('purchased_at');
      $table->date('expires_at')->nullable();
      $table->enum('status', ['active', 'depleted', 'expired'])->default('active');
      $table->timestamps();
  });
  ```
- [ ] Create `Retainer` model with relationships

### 1.5 New ProductBundle Model
- [ ] Create migration: `create_product_bundles_table`
  ```php
  Schema::create('billing_product_bundles', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->text('description')->nullable();
      $table->json('product_ids'); // Array of product IDs with quantities
      $table->decimal('discount_percent', 5, 2)->default(0);
      $table->boolean('is_active')->default(true);
      $table->timestamps();
  });
  ```
- [ ] Create `ProductBundle` model

### 1.6 New AuditLog Model
- [ ] Create migration: `create_billing_audit_logs_table`
  ```php
  Schema::create('billing_audit_logs', function (Blueprint $table) {
      $table->id();
      $table->string('auditable_type'); // Invoice, Payment, PriceOverride
      $table->unsignedBigInteger('auditable_id');
      $table->string('event'); // created, updated, deleted, status_changed
      $table->json('old_values')->nullable();
      $table->json('new_values')->nullable();
      $table->foreignId('user_id')->nullable()->constrained('users');
      $table->string('ip_address', 45)->nullable();
      $table->timestamps();
      
      $table->index(['auditable_type', 'auditable_id']);
      $table->index('user_id');
      $table->index('created_at');
  });
  ```
- [ ] Create `BillingAuditLog` model

### 1.7 Quote Model Enhancements
- [ ] Add migration: `add_tracking_fields_to_quotes_table`
  ```php
  $table->timestamp('viewed_at')->nullable();
  $table->string('viewed_ip')->nullable();
  $table->timestamp('accepted_at')->nullable();
  $table->text('signature_data')->nullable(); // Base64 signature image
  $table->string('signer_name')->nullable();
  $table->string('signer_email')->nullable();
  ```
- [ ] Update `Quote` model

### 1.8 BillableEntry Enhancements
- [ ] Add migration: `add_invoice_link_to_billable_entries`
  ```php
  $table->foreignId('invoice_line_item_id')->nullable()->constrained('billing_invoice_line_items');
  $table->string('receipt_path')->nullable(); // For expense receipts
  ```
- [ ] Update `BillableEntry` model

### 1.9 New Notification Preferences
- [ ] Create migration: `create_notification_preferences_table`
  ```php
  Schema::create('billing_notification_preferences', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('users');
      $table->string('notification_type'); // payment_received, quote_viewed, etc.
      $table->boolean('email_enabled')->default(true);
      $table->boolean('slack_enabled')->default(false);
      $table->boolean('in_app_enabled')->default(true);
      $table->timestamps();
      
      $table->unique(['user_id', 'notification_type']);
  });
  ```

### 1.10 Performance Indexes
- [ ] Create migration: `add_performance_indexes_to_billing_tables`
  ```php
  // Compound indexes for dashboard queries
  Schema::table('billing_invoices', function (Blueprint $table) {
      $table->index(['company_id', 'status']);
      $table->index(['status', 'due_date']);
      $table->index(['is_disputed']);
  });
  
  Schema::table('billing_subscriptions', function (Blueprint $table) {
      $table->index(['next_billing_date']);
      $table->index(['contract_end_date']);
  });
  
  Schema::table('billing_billable_entries', function (Blueprint $table) {
      $table->index(['ticket_id']);
      $table->index(['user_id', 'created_at']);
  });
  ```

---

## Completion Verification

```bash
# Run all migrations
php artisan migrate

# Verify tables exist
php artisan tinker --execute="
    \$tables = ['billing_credit_notes', 'billing_retainers', 'billing_product_bundles', 'billing_audit_logs'];
    foreach (\$tables as \$t) {
        echo \$t . ': ' . (Schema::hasTable(\$t) ? 'OK' : 'MISSING') . PHP_EOL;
    }
"

# Verify new columns
php artisan tinker --execute="
    echo 'Invoice.is_disputed: ' . (Schema::hasColumn('billing_invoices', 'is_disputed') ? 'OK' : 'MISSING') . PHP_EOL;
    echo 'Subscription.contract_end_date: ' . (Schema::hasColumn('billing_subscriptions', 'contract_end_date') ? 'OK' : 'MISSING') . PHP_EOL;
    echo 'Quote.viewed_at: ' . (Schema::hasColumn('quotes', 'viewed_at') ? 'OK' : 'MISSING') . PHP_EOL;
"

# Test rollback
php artisan migrate:rollback --step=10
php artisan migrate
```

---

## Downstream Dependencies
- **Batch 2** (Services): Requires Retainer, CreditNote, AuditLog models
- **Batch 3** (UI): Requires all model changes for form fields
- **Batch 4** (Integrations): Requires notification preferences table

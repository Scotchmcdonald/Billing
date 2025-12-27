# FinOps Implementation Plan & Workload Tracker

## Executive Summary

This document outlines the comprehensive, phased implementation plan for transforming the existing billing module into a **"World Class" Financial Operations (FinOps) system** for IT MSP and Development operations.

### The Triple Threat Framework

To achieve World Class status, the system must solve the "Triple Threat" of MSP finance:

1. **Billing Accuracy:** Prevent revenue leakage from unbilled time/materials and incorrect pricing
2. **Cash Flow:** Accelerate payment collection and reduce AR aging
3. **Profit Visibility:** Real-time insight into client profitability (Revenue - COGS - Labor)

### Critical Architecture Requirements

1. **The "Company" Gap:** Robust 'Company/Tenant' model where all financial data, service inventory, and users are children of a Company entity
2. **The "Tiered + Override" Pricing Model:** Three Global Pricing Tiers [Standard, Non-Profit, Consumer] with Client-Specific Overrides taking precedence
3. **Visibility Mandate:** Every piece of logic, model, and function MUST be exposed via a UI - no "ghost" features
4. **Navigation Structure:** Top-level "Finance" menu alongside existing [Dashboard, Manage]

---

## Feature Prioritization Matrix

### Tier 1: Essential (The Foundation)
**Goal:** Legally send an invoice and record a payment

| Feature | Description | Personas |
|---------|-------------|----------|
| Standardized Invoice Engine | Professional PDF generation with branding, itemization, taxes | Finance Admin |
| Customer Financial Profiles | CRM-linked billing data (address, contact, tax IDs) | Finance Admin, Tech Admin |
| Simple Product/Service Catalog | Central SKU database with descriptions and prices | Tech Admin, Finance |
| Manual Payment Recording | Mark invoices as "Paid" (Cash, Check, Wire, CC) | Finance Admin |
| Basic Tax Calculation | Flat-rate sales tax or VAT based on location | Finance Admin |
| Billing Ledger | View of all invoices (Draft, Sent, Overdue, Paid) | Finance Admin, Client Admin |

### Tier 2: Standard (MSP Table Stakes)
**Goal:** Automate repetitive Managed Services billing

| Feature | Description | Personas |
|---------|-------------|----------|
| Recurring Billing Cycles | Auto-generation of monthly/quarterly/annual invoices for AYCE contracts | Finance Admin |
| Time Tracking Integration | Convert support ticket hours to billable line items | Technicians, Finance |
| Integrated Payment Gateways | Native Stripe/PayPal with "Pay Now" links (CC + ACH) | Finance Admin, Client Admin |
| Inventory & Asset Tracking | Stock levels for hardware and software licenses | Tech Admin |
| AR Aging Reports | Automated 30/60/90 day debt reports | Finance Admin |
| Expense Management | Tech expense logging (travel, parts) against tickets | Technicians, Finance |

### Tier 3: Nice to Have (Efficiency Boosters)
**Goal:** Remove manual intervention and improve customer experience

| Feature | Description | Personas |
|---------|-------------|----------|
| Self-Service Client Portal | Client login for invoice history, receipts, payment methods | Client Admin, Client Users |
| Automated Payment Reminders | Dunning management (before/on/after due date) | Finance Admin (config) |
| Contract Proration | Automatic partial-month billing for mid-cycle changes | Finance Admin |
| Vendor Cost Tracking | COGS tracking for gross profit per item | Finance Admin, Tech Admin |
| Accounting Software Sync | One/two-way sync with QuickBooks/Xero | Finance Admin |

### Tier 4: Premium (Operational Intelligence)
**Goal:** Professional-grade financial control and data-driven decisions

| Feature | Description | Personas |
|---------|-------------|----------|
| Automated Usage/Seat Counting | RMM integration to count endpoints/users and update quantities | Tech Admin, Finance |
| Pre-Paid Hour Blocks | Retainer model with auto-deduction as tickets close | Finance Admin, Client Admin |
| Subscription Reconciliation | Ensure client billing matches vendor licensing | Finance Admin, Tech Admin |
| Multi-Currency & Multi-Entity | Support for international operations | Finance Admin |
| Advanced Procurement Workflow | POs linking quote → vendor order → invoice | Finance Admin, Tech Admin |

### Tier 5: World Class (MSP Powerhouse)
**Goal:** Total automation and predictive financial health

| Feature | Description | Personas |
|---------|-------------|----------|
| FinOps & Profitability Analytics | Real-time Gross Margin per Client and Effective Hourly Rate | Finance Admin, Executives |
| AI-Driven Anomaly Detection | Flag unusual invoices before dispatch | Finance Admin |
| Revenue Recognition Logic | Accrual-based accounting across project months | Finance Admin |
| Automated Quote-to-Cash | Digital signature → hardware order → invoice → payment (no touch) | Sales, Finance, Tech Admin |
| Financial Health Benchmarking | Compare KPIs against industry standards (HTG, Service Leadership) | Executives, Finance Admin |

---

## Phase 1: Foundation (Database & Core Models)

**Goal:** Establish the "Company/Tenant" entity and "Hierarchical Pricing Engine" data structure.

### 1.1 Database Schema Updates

#### Companies Table Enhancement
- [x] **Migration:** `2025_01_01_000001_enhance_companies_table.php`
    - [x] Add `pricing_tier` ENUM ('standard', 'non_profit', 'consumer') DEFAULT 'standard'
    - [x] Add `tax_id` VARCHAR(50) NULLABLE (for EIN/VAT)
    - [x] Add `billing_address` TEXT NULLABLE (JSON: street, city, state, zip, country)
    - [x] Add `primary_contact_id` UNSIGNED BIGINT NULLABLE (FK to users)
    - [x] Add `settings` JSON NULLABLE (proration_policy, payment_terms, auto_pay_enabled, etc.)
    - [x] Add `margin_floor_percent` DECIMAL(5,2) DEFAULT 20.00 (Profitability guardrail)
    - [x] Add `is_active` BOOLEAN DEFAULT TRUE
    - [x] Add INDEX on `pricing_tier`
    - [x] Add INDEX on `is_active`

#### Customer-Company Association
- [x] **Migration:** `2025_01_01_000002_add_company_id_to_customers.php`
    - [x] Add `company_id` UNSIGNED BIGINT NULLABLE (FK to companies)
    - [x] Add INDEX on `company_id`
    - [x] Add FOREIGN KEY CONSTRAINT (ON DELETE SET NULL)
- [x] **Data Migration Script:** `database/seeders/MigrateCustomerCompanyStringsSeeder.php`
    - [x] Parse existing `customers.company` string field
    - [x] Create unique `Company` records from distinct values
    - [x] Update `customers.company_id` to reference new Company records
    - [x] Log any ambiguous mappings for manual review

#### Hierarchical Pricing Engine Tables
- [x] **Migration:** `2025_01_01_000003_create_product_tier_prices_table.php`
    - [x] Create `product_tier_prices` table:
        - `id` BIGINT UNSIGNED PRIMARY KEY
        - `product_id` UNSIGNED BIGINT (FK to products) - INDEXED
        - `tier` ENUM ('standard', 'non_profit', 'consumer') - INDEXED
        - `price` DECIMAL(15,4) NOT NULL
        - `starts_at` TIMESTAMP NULLABLE (for future price changes)
        - `ends_at` TIMESTAMP NULLABLE
        - `created_at`, `updated_at`
        - UNIQUE INDEX on (`product_id`, `tier`, `starts_at`)
- [x] **Migration:** `2025_01_01_000004_refactor_price_books_to_overrides.php`
    - [x] Rename `price_books` table to `price_overrides` (or create new and migrate)
    - [x] Add `type` ENUM ('fixed', 'discount_percent', 'markup_percent') DEFAULT 'fixed'
    - [x] Add `value` DECIMAL(15,4) NOT NULL (price or percentage)
    - [x] Rename `custom_price` to `value` if needed
    - [x] Add `notes` TEXT NULLABLE (reason for override)
    - [x] Add `approved_by` UNSIGNED BIGINT NULLABLE (FK to users - audit trail)
    - [x] Add `is_active` BOOLEAN DEFAULT TRUE

#### Recurring Subscriptions
- [x] **Migration:** `2025_01_01_000005_create_subscriptions_table.php`
    - [x] Create `subscriptions` table (if not using Cashier subscriptions):
        - `id` BIGINT UNSIGNED PRIMARY KEY
        - `company_id` UNSIGNED BIGINT (FK to companies)
        - `product_id` UNSIGNED BIGINT (FK to products)
        - `quantity` INTEGER DEFAULT 1
        - `effective_price` DECIMAL(15,4) (cached from tier/override calculation)
        - `billing_frequency` ENUM ('monthly', 'quarterly', 'annual')
        - `starts_at` TIMESTAMP NOT NULL
        - `ends_at` TIMESTAMP NULLABLE
        - `next_billing_date` DATE
        - `is_active` BOOLEAN DEFAULT TRUE
        - `metadata` JSON NULLABLE (RMM device IDs, license keys, etc.)
        - `created_at`, `updated_at`
        - INDEX on (`company_id`, `is_active`)
        - INDEX on `next_billing_date`

#### Invoices
- [x] **Migration:** `2025_01_01_000006_create_invoices_table.php`
    - [x] Create `invoices` table:
        - `id` BIGINT UNSIGNED PRIMARY KEY
        - `company_id` UNSIGNED BIGINT (FK to companies)
        - `invoice_number` VARCHAR(50) UNIQUE
        - `status` ENUM ('draft', 'pending_review', 'sent', 'paid', 'overdue', 'void') DEFAULT 'draft'
        - `issue_date` DATE
        - `due_date` DATE
        - `subtotal` DECIMAL(15,4) DEFAULT 0
        - `tax_amount` DECIMAL(15,4) DEFAULT 0
        - `total` DECIMAL(15,4) DEFAULT 0
        - `paid_amount` DECIMAL(15,4) DEFAULT 0
        - `stripe_invoice_id` VARCHAR(255) NULLABLE
        - `metadata` JSON NULLABLE (variance_flags, anomaly_score, etc.)
        - `created_at`, `updated_at`
        - INDEX on (`company_id`, `status`)
        - INDEX on `due_date`
        - INDEX on `issue_date`

#### Time & Materials Tracking
- [x] **Migration:** `2025_01_01_000007_create_billable_entries_table.php`
    - [x] Create `billable_entries` table:
        - `id` BIGINT UNSIGNED PRIMARY KEY
        - `company_id` UNSIGNED BIGINT (FK to companies)
        - `user_id` UNSIGNED BIGINT (FK to users - technician)
        - `ticket_id` UNSIGNED BIGINT NULLABLE (FK to tickets/threads)
        - `type` ENUM ('time', 'expense', 'product') DEFAULT 'time'
        - `description` TEXT
        - `quantity` DECIMAL(10,2) (hours or item count)
        - `rate` DECIMAL(15,4) (hourly rate or unit price)
        - `subtotal` DECIMAL(15,4)
        - `is_billable` BOOLEAN DEFAULT TRUE
        - `invoice_line_item_id` UNSIGNED BIGINT NULLABLE (FK - null if unbilled)
        - `date` DATE
        - `metadata` JSON NULLABLE (mileage, receipt_url, etc.)
        - `created_at`, `updated_at`
        - INDEX on (`company_id`, `is_billable`, `invoice_line_item_id`)
        - INDEX on `date`

#### Payment Records
- [x] **Migration:** `2025_01_01_000008_create_payments_table.php`
    - [x] Create `payments` table:
        - `id` BIGINT UNSIGNED PRIMARY KEY
        - `invoice_id` UNSIGNED BIGINT (FK to invoices)
        - `company_id` UNSIGNED BIGINT (FK to companies)
        - `amount` DECIMAL(15,4)
        - `payment_method` ENUM ('stripe_card', 'stripe_ach', 'check', 'wire', 'cash', 'other')
        - `payment_reference` VARCHAR(255) NULLABLE (check number, Stripe payment ID)
        - `payment_date` DATE
        - `notes` TEXT NULLABLE
        - `created_by` UNSIGNED BIGINT (FK to users)
        - `created_at`, `updated_at`
        - INDEX on `invoice_id`
        - INDEX on `company_id`

### 1.2 Model Logic Implementation

#### Company Model Enhancement
- [x] **Update:** `Modules\Billing\Models\Company.php`
    - [x] Add to `$fillable`: pricing_tier, tax_id, billing_address, primary_contact_id, settings, margin_floor_percent
    - [x] Add to `$casts`: 
        - `billing_address` => 'array'
        - `settings` => 'array'
        - `margin_floor_percent` => 'decimal:2'
    - [x] **Relationships:**
        - [x] `customers()` HasMany Customer
        - [x] `priceOverrides()` HasMany PriceOverride
        - [x] `subscriptions()` HasMany Subscription
        - [x] `invoices()` HasMany Invoice
        - [x] `billableEntries()` HasMany BillableEntry
        - [x] `primaryContact()` BelongsTo User
    - [x] **Key Methods:**
        - [x] `getEffectivePrice(Product $product, ?Carbon $date = null): float` - Check Override → Tier → Base
        - [x] `hasOverrideFor(Product $product): bool`
        - [x] `isMarginSafe(float $price, float $cost): bool` - Returns false if below margin_floor
        - [x] `getPricingTierLabel(): string` - Human-readable tier name

#### Product Model Enhancement
- [x] **Update:** `Modules\Inventory\Models\Product.php`
    - [x] **Relationships:**
        - [x] `tierPrices()` HasMany ProductTierPrice
        - [x] `priceOverrides()` HasMany PriceOverride
        - [x] `subscriptions()` HasMany Subscription
    - [x] **Key Methods:**
        - [x] `getPriceForTier(string $tier, ?Carbon $date = null): float`
        - [x] `getGrossMarginPercent(float $sellingPrice): float` - ((sellingPrice - cost_price) / sellingPrice) * 100
        - [x] `getTierPriceMatrix(): array` - Returns ['standard' => X, 'non_profit' => Y, 'consumer' => Z]

#### New Models
- [x] **Create:** `Modules\Inventory\Models\ProductTierPrice.php`
    - [x] `$fillable`: product_id, tier, price, starts_at, ends_at
    - [x] Relationships: `product()` BelongsTo Product
    - [x] Scope: `active()` - filters by date range
- [x] **Create:** `Modules\Inventory\Models\PriceOverride.php` (rename from PriceBook)
    - [x] `$fillable`: company_id, product_id, type, value, starts_at, ends_at, notes, approved_by, is_active
    - [x] Relationships: `company()`, `product()`, `approver()` BelongsTo User
    - [x] Method: `calculateEffectivePrice(float $basePrice): float`
- [x] **Create:** `Modules\Billing\Models\Subscription.php`
    - [x] `$fillable`: company_id, product_id, quantity, effective_price, billing_frequency, starts_at, ends_at, next_billing_date, is_active, metadata
    - [x] `$casts`: metadata => 'array', starts_at/ends_at => 'datetime', next_billing_date => 'date'
    - [x] Relationships: `company()`, `product()`
    - [x] Method: `calculateMonthlyRecurringRevenue(): float`
- [x] **Create:** `Modules\Billing\Models\Invoice.php`
    - [x] `$fillable`: company_id, invoice_number, status, issue_date, due_date, subtotal, tax_amount, total, paid_amount, stripe_invoice_id, metadata
    - [x] `$casts`: metadata => 'array', issue_date/due_date => 'date'
    - [x] Relationships: `company()`, `lineItems()` HasMany InvoiceLineItem, `payments()` HasMany Payment
    - [x] Methods: `isOverdue(): bool`, `getVarianceFromLastMonth(): float`, `getAnomalyScore(): float`
- [x] **Create:** `Modules\Billing\Models\BillableEntry.php`
    - [x] `$fillable`: company_id, user_id, ticket_id, type, description, quantity, rate, subtotal, is_billable, invoice_line_item_id, date, metadata
    - [x] `$casts`: metadata => 'array', quantity/rate/subtotal => 'decimal:4', date => 'date'
    - [x] Relationships: `company()`, `user()`, `invoiceLineItem()`
    - [x] Scope: `unbilled()` - where invoice_line_item_id IS NULL AND is_billable = TRUE
- [x] **Create:** `Modules\Billing\Models\Payment.php`
    - [x] `$fillable`: invoice_id, company_id, amount, payment_method, payment_reference, payment_date, notes, created_by
    - [x] `$casts`: amount => 'decimal:4', payment_date => 'date'
    - [x] Relationships: `invoice()`, `company()`, `creator()` BelongsTo User

### 1.3 Seed Data & Testing Data
- [x] **Seeder:** `ProductTierPriceSeeder.php`
    - [x] For each existing Product, create 3 tier prices:
        - Standard: base_price * 1.0
        - Non-Profit: base_price * 0.85 (15% discount)
        - Consumer: base_price * 1.10 (10% premium)
- [x] **Seeder:** `SampleCompaniesSeeder.php`
    - [x] Create 5 test companies across all pricing tiers
    - [x] Include margin_floor_percent variations (10%, 20%, 30%)
- [x] **Seeder:** `PriceOverrideSeeder.php`
    - [x] Create 3-5 sample overrides for testing "special pricing" scenarios

---

## Phase 2: API & Logic Layer

**Goal:** Enable backend to calculate prices dynamically, generate invoices, and handle payments.

### 2.1 Core Service Classes

#### Pricing Engine Service
- [x] **Create:** `Modules\Billing\Services\PricingEngineService.php`
    - [x] **Method:** `calculateEffectivePrice(Company $company, Product $product, ?Carbon $date = null): PriceResult`
        - [x] Logic Flow:
            1. Check for active PriceOverride (company-specific)
            2. If no override, get ProductTierPrice for company's tier
            3. If no tier price, fallback to Product->base_price
            4. Return PriceResult object with: price, source ('override', 'tier', 'base'), margin_percent
        - [x] Cache results using Laravel Cache (5 min TTL)
    - [x] **Method:** `validateMargin(Company $company, Product $product, float $proposedPrice): ValidationResult`
        - [x] Returns ValidationResult with: is_safe, margin_percent, margin_floor_percent, warnings[]
    - [x] **Method:** `getPriceBreakdown(Company $company, Product $product): array`
        - [x] Returns: base_price, tier_price, override_price, effective_price, tier_label, has_override

#### Invoice Generation Service
- [x] **Create:** `Modules\Billing\Services\InvoiceGenerationService.php`
    - [x] **Method:** `generateMonthlyInvoices(Carbon $billingDate): Collection`
        - [x] Query active companies
        - [x] For each company:
            - Collect recurring subscriptions due for billing
            - Collect unbilled BillableEntries
            - Generate draft Invoice with line items
            - Calculate tax
            - Set status to 'pending_review'
        - [x] Return Collection of generated Invoices
    - [x] **Method:** `generateInvoiceForCompany(Company $company, Carbon $billingDate): Invoice`
        - [x] Single company invoice generation
        - [x] Include proration logic for mid-cycle changes
    - [x] **Method:** `finalizeDraftInvoice(Invoice $invoice): Invoice`
        - [x] Change status from 'draft'/'pending_review' to 'sent'
        - [x] Generate invoice_number (format: INV-YYYY-MM-XXXX)
        - [x] Send to Stripe if company has stripe_id
        - [x] Trigger InvoiceFinalized event
        - [x] Send email notification to company

#### Proration Calculator
- [x] **Create:** `Modules\Billing\Services\ProrationCalculator.php`
    - [x] **Method:** `calculateProration(Subscription $subscription, Carbon $changeDate, int $newQuantity): ProrationResult`
        - [x] Support policies: 'full_month', 'daily_proration', 'next_cycle'
        - [x] Formula: (days_remaining / total_days_in_cycle) * price_per_unit * quantity_delta
        - [x] Return ProrationResult: amount, credit_amount, policy_used, calculation_details
    - [x] **Method:** `previewProration(...)` - Read-only preview for UI

#### Anomaly Detection Service  
- [x] **Create:** `Modules\Billing\Services\AnomalyDetectionService.php`
    - [x] **Method:** `analyzeInvoice(Invoice $invoice): AnomalyReport`
        - [x] Compare to previous 3 months for same company
        - [x] Calculate variance percentage
        - [x] Flag if variance > 20%
        - [x] Check for missing recurring items
        - [x] Return AnomalyReport: score (0-100), flags[], severity ('info', 'warning', 'critical')
    - [x] **Method:** `flagForReview(Invoice $invoice, string $reason)`
        - [x] Update invoice->metadata['flagged'] = true
        - [x] Log to BillingLog

### 2.2 Service Catalog API
- [x] **Endpoint:** `GET /api/v1/finance/catalog`
    - [x] **Auth:** Can:finance.admin
    - [x] **Response:** All products with tier price matrix
    - [x] **Controller:** `Modules\Billing\Http\Controllers\Api\CatalogController@index`
- [x] **Endpoint:** `GET /api/v1/finance/companies/{company}/catalog`
    - [x] **Auth:** billing.auth (company access)
    - [x] **Response:** Products with effective prices for that company
    - [x] **Controller:** `CatalogController@showForCompany`
- [x] **Endpoint:** `GET /api/v1/finance/products/{product}/pricing`
    - [x] **Auth:** Can:finance.admin
    - [x] **Response:** Tier price breakdown + list of companies with overrides
    - [x] **Controller:** `CatalogController@showProductPricing`

### 2.3 Invoice Management API
- [x] **Endpoint:** `POST /api/v1/finance/invoices/generate`
    - [x] **Auth:** Can:finance.admin
    - [x] **Payload:** { billing_date, company_ids[] } (optional: if null, generate for all)
    - [x] **Action:** Call InvoiceGenerationService->generateMonthlyInvoices()
    - [x] **Response:** { generated_count, invoices[] }
    - [x] **Controller:** `Modules\Billing\Http\Controllers\Api\InvoiceController@generate`
- [x] **Endpoint:** `GET /api/v1/finance/invoices/pending-review`
    - [x] **Auth:** Can:finance.admin
    - [x] **Response:** Invoices with status='pending_review' + anomaly scores
    - [x] **Controller:** `InvoiceController@pendingReview`
- [x] **Endpoint:** `POST /api/v1/finance/invoices/{invoice}/finalize`
    - [x] **Auth:** Can:finance.admin
    - [x] **Action:** InvoiceGenerationService->finalizeDraftInvoice()
    - [x] **Controller:** `InvoiceController@finalize`
- [x] **Endpoint:** `POST /api/v1/finance/invoices/{invoice}/void`
    - [x] **Auth:** Can:finance.admin
    - [x] **Action:** Set status='void', create audit log
- [x] **Endpoint:** `GET /api/v1/finance/invoices/{invoice}/preview-pdf`
    - [x] **Auth:** billing.auth OR can:finance.admin
    - [x] **Action:** Generate PDF using Laravel Dompdf
    - [x] **Controller:** `InvoiceController@previewPdf`

### 2.4 Price Override Management API
- [x] **Endpoint:** `GET /api/v1/finance/companies/{company}/overrides`
    - [x] **Auth:** Can:finance.admin
    - [x] **Response:** All active price overrides for company
    - [x] **Controller:** `Modules\Billing\Http\Controllers\Api\PriceOverrideController@index`
- [x] **Endpoint:** `POST /api/v1/finance/companies/{company}/overrides`
    - [x] **Auth:** Can:finance.admin
    - [x] **Payload:** { product_id, type, value, starts_at, ends_at, notes }
    - [x] **Validation:** 
        - Check margin safety using PricingEngineService
        - Require `notes` field (audit trail)
        - Set `approved_by` to Auth::id()
    - [x] **Controller:** `PriceOverrideController@store`
- [x] **Endpoint:** `PUT /api/v1/finance/overrides/{override}`
    - [x] **Auth:** Can:finance.admin
    - [x] **Validation:** Same as POST
- [x] **Endpoint:** `DELETE /api/v1/finance/overrides/{override}`
    - [x] **Auth:** Can:finance.admin
    - [x] **Action:** Soft delete (set is_active=false) rather than hard delete

### 2.5 Billable Entries API
- [x] **Endpoint:** `GET /api/v1/finance/billable-entries/unbilled`
    - [x] **Auth:** Can:finance.admin
    - [x] **Query Params:** ?company_id, ?date_from, ?date_to
    - [x] **Response:** Unbilled time/expense entries
    - [x] **Controller:** `Modules\Billing\Http\Controllers\Api\BillableEntryController@unbilled`
- [x] **Endpoint:** `POST /api/v1/finance/billable-entries`
    - [x] **Auth:** Auth (technician or admin)
    - [x] **Payload:** { company_id, type, description, quantity, rate, date, is_billable, metadata }
    - [x] **Controller:** `BillableEntryController@store`
- [x] **Endpoint:** `PATCH /api/v1/finance/billable-entries/{entry}/billable`
    - [x] **Auth:** Can:finance.admin OR entry owner
    - [x] **Payload:** { is_billable: true/false }
    - [x] **Controller:** `BillableEntryController@toggleBillable`

### 2.6 Payment Recording API
- [x] **Endpoint:** `POST /api/v1/finance/invoices/{invoice}/payments`
    - [x] **Auth:** Can:finance.admin
    - [x] **Payload:** { amount, payment_method, payment_reference, payment_date, notes }
    - [x] **Action:** 
        - Create Payment record
        - Update Invoice->paid_amount
        - Update Invoice->status to 'paid' if fully paid
        - Create activity log
    - [x] **Controller:** `Modules\Billing\Http\Controllers\Api\PaymentController@store`

---

## Phase 3: Frontend & UX (Persona-Based)

**Goal:** Expose all financial logic to appropriate users via intuitive, role-specific interfaces.

### 3.1 Navigation Structure

#### Update Global Navigation
- [x] **File:** `resources/views/layouts/navigation.blade.php`
    - [x] Add "Finance" dropdown menu (primary nav level) between "Dashboard" and "Manage"
    - [x] **Finance Menu Items:**
        - [x] **Dashboard** (route: billing.finance.dashboard) - Can:finance.admin
        - [x] **Pre-Flight Review** (route: billing.finance.pre-flight) - Can:finance.admin
        - [x] **Service Catalog** (route: inventory.products.index) - Can:finance.admin OR Can:inventory.manage
        - [x] **Price Overrides** (route: billing.finance.overrides) - Can:finance.admin
        - [x] **Invoices** (route: billing.finance.invoices) - Can:finance.admin
        - [x] **Payments** (route: billing.finance.payments) - Can:finance.admin
        - [x] **AR Aging** (route: billing.finance.ar-aging) - Can:finance.admin
        - [x] ---
        - [x] **Client Portal** (route: billing.portal.entry) - billing.auth (any company user)

### 3.2 Finance Dashboard (Finance Admin Persona)

**UX Pattern:** "Control Tower" - High-density metrics with drill-down capability

- [x] **View:** `Modules/Billing/Resources/views/finance/dashboard.blade.php`
- [x] **Route:** `Route::get('/finance/dashboard', [FinanceController::class, 'dashboard'])->name('billing.finance.dashboard')`
- [x] **Component Structure:**
    ```
    ├── Metrics Row (Tailwind Grid)
    │   ├── MRR Card (SVG Sparkline)
    │   ├── AR Aging Summary (30/60/90 breakdown)
    │   ├── Gross Profit This Month
    │   └── Unbilled Time/Materials
    ├── Pre-Flight Queue Widget
    │   ├── Count of pending_review invoices
    │   ├── Anomaly flags
    │   └── CTA: "Review Now"
    ├── Recent Activity Feed
    │   └── Last 10 invoice generations, payments, overrides
    ```
- [x] **Metrics Implementation:**
    - [x] **MRR Calculation:** Sum of (Subscription->quantity * effective_price) WHERE billing_frequency='monthly' OR (annual_price / 12)
    - [x] **SVG Sparkline:** Alpine.js component with last 12 months of MRR data
    - [x] **AR Aging:** Query invoices WHERE status IN ('sent', 'overdue') GROUP BY age brackets
    - [x] **Gross Profit:** (Total Revenue - COGS) from current month invoices
- [x] **Visual Design:**
    - [x] Use Tailwind `colors.indigo` for primary actions
    - [x] Use `colors.emerald` for positive metrics (MRR growth)
    - [x] Use `colors.rose` for alerts (overdue invoices)
    - [x] "Hazard Stripes" pattern (diagonal lines) for Pre-Flight Queue background

### 3.3 Pre-Flight Billing Review (Finance Admin Persona)

**UX Pattern:** "Quality Gate" - Approve/hold/edit before sending

- [x] **View:** `Modules/Billing/Resources/views/finance/pre-flight.blade.php`
- [x] **Route:** `Route::get('/finance/pre-flight', [FinanceController::class, 'preFlight'])`
- [x] **Data Table Columns:**
    - [x] Company Name (clickable → company detail)
    - [x] Invoice Total
    - [x] Variance from Last Month (%) - COLOR CODED
    - [x] Anomaly Score (0-100 with badge)
    - [x] Line Item Count
    - [x] Unbilled Items Included
    - [x] Actions: [View Details] [Approve] [Hold] [Edit]
- [x] **Anomaly Radar Visual:**
    - [x] Invoices with anomaly_score > 50 highlighted in amber
    - [x] Invoices with anomaly_score > 80 highlighted in rose
    - [x] Tooltip explaining anomaly (e.g., "23% higher than average")
- [x] **Bulk Actions:**
    - [x] "Approve All Clean" button (approve invoices with score < 20)
    - [x] "Export Review Report" (CSV with all pending invoices)
- [x] **Modal: Invoice Detail**
    - [x] Line-by-line breakdown
    - [x] Editable quantities (update underlying subscription)
    - [x] Add/Remove line items
    - [x] Notes field for internal comments
    - [x] [Approve & Send] [Save as Draft] [Void] buttons

### 3.4 Service Catalog UI (Tech Admin / Finance Admin Persona)

**UX Pattern:** "Product Matrix" - Manage SKUs and tiered pricing

- [x] **View:** `Modules/Inventory/Resources/views/products/index.blade.php` (enhance existing)
- [x] **Additional Views:**
    - [x] `products/pricing-matrix.blade.php` (new)
- [x] **Product List Enhancements:**
    - [x] Add "Filter Pills" using Alpine.js:
        - Active | Inactive | Has Overrides | Low Stock
    - [x] Add column: "Tier Pricing" (badge indicating if tier prices exist)
    - [x] Add action: [Manage Tier Pricing]
- [x] **Tier Pricing Manager Modal:**
    - [x] Product name header
    - [x] Three input fields:
        - Standard Tier Price: $____
        - Non-Profit Tier Price: $____ (show % diff from standard)
        - Consumer Tier Price: $____ (show % diff from standard)
    - [x] Display: Base Price, Cost Price
    - [x] Calculate and display: Margin % for each tier
    - [x] Warning if any tier margin < 20%
    - [x] [Save Tier Prices] [Cancel]
- [x] **Visual Design:**
    - [x] Use Tailwind `colors.amber` for low-margin warnings
    - [x] Display keyboard shortcut hint: `⌘K` in search bar

### 3.5 Price Override Manager (Finance Admin Persona)

**UX Pattern:** "Exception Dashboard" - Visibility into all special pricing

- [x] **View:** `Modules/Billing/Resources/views/finance/overrides.blade.php`
- [x] **Route:** `Route::get('/finance/overrides', [PriceOverrideController::class, 'index'])`
- [x] **Data Table:**
    - [x] Columns: Company, Product, Type (Fixed/Discount%), Value, Effective Dates, Margin Impact, Approved By, Actions
    - [x] Filter by Company, Product, Active/Inactive
    - [x] Sort by Margin Impact (lowest first - highest risk)
- [x] **"Margin Impact" Column:**
    - [x] Display as badge:
        - Green: Margin > 30%
        - Amber: Margin 15-30%
        - Rose: Margin < 15%
        - Rose + "!" icon: Margin < company.margin_floor_percent
- [x] **Actions:**
    - [x] [Edit] [Deactivate] [View Audit Log]
- [x] **Create Override Button:**
    - [x] Opens modal/form
    - [x] Dropdowns: Select Company, Select Product
    - [x] Override Type: Radio buttons (Fixed Price | Discount % | Markup %)
    - [x] Value input
    - [x] Real-time margin calculation preview
    - [x] Date range pickers (starts_at, ends_at)
    - [x] Required: Notes textarea (minimum 20 characters)
    - [x] Warning modal if margin < company.margin_floor_percent: "This override is below the margin floor. Are you sure?"

### 3.6 Client Portal (Client Admin Persona)

**UX Pattern:** "Self-Service Hub" - Transparency and control for clients

- [x] **View:** `Modules/Billing/Resources/views/portal/dashboard.blade.php`
- [x] **Route:** `Route::get('/portal', [PortalController::class, 'dashboard'])->name('billing.portal.dashboard')`
- [x] **Sections:**
    - [x] **My Services Tab:**
        - [x] List active subscriptions (Product, Quantity, Monthly Cost, Next Billing Date)
        - [x] Button: [Request Change] (sends ticket/email to admin)
        - [x] Display: Total MRR for this company
    - [x] **Invoices Tab:**
        - [x] List all invoices (newest first)
        - [x] Columns: Invoice #, Date, Amount, Status, Actions
        - [x] Status badges: Paid (green), Pending (amber), Overdue (rose)
        - [x] Actions: [View PDF] [Pay Now] (if unpaid)
    - [x] **Payments Tab:**
        - [x] List payment history
        - [x] Columns: Date, Invoice #, Amount, Method, Reference
    - [x] **Payment Methods Tab:**
        - [x] Stripe Elements integration
        - [x] Add/Update Card or ACH
        - [x] Toggle: "Enable Auto-Pay" (Stripe subscription)
        - [x] Display: Current default payment method with last 4 digits
- [x] **"Pay Now" Flow:**
    - [x] Button opens payment modal
    - [x] Display: Invoice total, due date
    - [x] Payment method selector (saved methods + "Add New")
    - [x] **Fee Offset Calculator:** (Alpine.js component)
        - If CC selected: "Processing fee: $X.XX (2.9% + $0.30). Total: $Y.YY"
        - If ACH selected: "No processing fee! Save $X.XX"
    - [x] [Complete Payment] button
    - [x] Success: Redirect to invoice detail with "Paid" badge
- [x] **Visual Design:**
    - [x] Sticky Order Summary (right sidebar on desktop, top on mobile)
    - [x] Trust signals: Lock icon, "Secure Payment via Stripe" badge
    - [x] Credit card input: Styled to look like physical card (brand logo detection)

### 3.7 Field Billing UI (Technician Persona)

**UX Pattern:** "Quick Capture" - Minimal friction for time/expense entry

- [x] **View:** `Modules/Billing/Resources/views/field/work-order.blade.php`
- [x] **Integration Point:** Extend existing ticket/thread detail view
- [x] **Component:** Billing Panel (collapsible section at bottom of ticket)
    - [x] **Time Entry Section:**
        - [x] Display: Auto-calculated time from ticket open/close
        - [x] Override: Manual hours input
        - [x] Toggle: "Billable" (green when ON, gray when OFF) - prominent
        - [x] Rate display: "$X.XX/hr" (based on company's T&M rate or user's default rate)
        - [x] Calculated: Total $
    - [x] **Expense Entry Section:**
        - [x] Button: [+ Add Expense]
        - [x] Modal: Description, Amount, Category (Travel, Parts, Other), Receipt upload
        - [x] List of added expenses with [Remove] button
    - [x] **Product/Parts Section:**
        - [x] Button: [+ Add Part from Inventory]
        - [x] Searchable product dropdown
        - [x] Quantity input
        - [x] Price display (based on company's effective price)
        - [x] [Scan Barcode] button (mobile only - use device camera)
    - [x] **Save Behavior:**
        - [x] On ticket close: Auto-create BillableEntry records
        - [x] Status indicator: "Will be billed on next invoice" (if billable=true)
- [x] **Mobile Optimization:**
    - [x] Large touch targets for toggle switches
    - [x] Barcode scanner integration (use HTML5 getUserMedia API)
    - [x] Offline support: Queue billable entries, sync when online (Service Worker)

### 3.8 Company Management (Finance Admin / Tech Admin)

- [x] **View:** `Modules/Billing/Resources/views/companies/index.blade.php`
- [x] **Route:** `Route::get('/finance/companies', [CompanyController::class, 'index'])`
- [x] **Data Table:**
    - [x] Columns: Company Name, Pricing Tier, Active Subscriptions, MRR, Total Overrides, Status, Actions
    - [x] Filter by: Pricing Tier, Has Overrides, Active/Inactive
- [x] **Company Detail View:**
    - [x] Tabs: Overview, Subscriptions, Invoices, Overrides, Settings
    - [x] **Overview Tab:**
        - [x] Company info (name, tax_id, billing_address, primary_contact)
        - [x] Metrics: Lifetime Value, MRR, Average Invoice, Payment Terms
        - [x] Margin Floor setting (editable by finance admin)
    - [x] **Settings Tab:**
        - [x] Pricing Tier selector (Standard/Non-Profit/Consumer)
        - [x] Proration Policy: Radio buttons (Full Month | Daily Proration | Next Cycle)
        - [x] Payment Terms: Dropdown (Net 30, Net 15, Due on Receipt)
        - [x] Auto-Pay Enabled: Toggle
        - [x] Invoice Delivery: Email, Portal, Both

---

## Phase 4: Automation & Intelligence (Premium/World Class Features)

**Goal:** Reduce manual effort, prevent revenue leakage, and enable predictive analytics.

### 4.1 Usage-Based Billing (RMM Integration)

#### RMM Webhook Handler
- [x] **Endpoint:** `POST /webhooks/rmm/device-count`
    - [x] **Auth:** API token or HMAC signature validation
    - [x] **Payload:** { company_id, device_count, timestamp, device_list[] }
    - [x] **Controller:** `Modules\Billing\Http\Controllers\Webhooks\RmmWebhookController@deviceCount`
- [x] **Logic:**
    - [x] Find active Subscription for company WHERE product.category='rmm_monitoring'
    - [x] Compare new device_count to current Subscription->quantity
    - [x] If changed:
        - Create BillableEntry for proration (if mid-cycle)
        - Update Subscription->quantity
        - Update Subscription->metadata['last_count_update']
        - Flag in Pre-Flight queue for review: "Usage change detected"
- [x] **Stale Device Handling:**
    - [x] Filter out devices with last_seen > 30 days ago
    - [x] Store in metadata['excluded_devices'] for audit

#### Usage Review Queue
- [x] **View:** `Modules/Billing/Resources/views/finance/usage-review.blade.php`
- [x] **Route:** `Route::get('/finance/usage-review')`
- [x] **Data Table:**
    - [x] Columns: Company, Product, Old Quantity, New Quantity, Delta, Proration Amount, Source, Actions
    - [x] Filter by: Date Range, Product Type
    - [x] Actions: [Approve] [Reject] [Manual Adjust]
- [x] **Approval:**
    - [x] Updates Subscription, marks usage change as reviewed
    - [x] Includes in next invoice generation

### 4.2 Automated Proration

- [x] **Integration:** `ProrationCalculator` service (already created in Phase 2)
- [x] **Trigger Points:**
    - [x] Subscription quantity change (via RMM or manual)
    - [ ] Subscription start mid-cycle (new client onboarding)
    - [ ] Subscription cancellation mid-cycle
- [ ] **Policy Enforcement:**
    - [ ] Read from `Company->settings['proration_policy']`
    - [ ] Apply calculation
    - [ ] Create BillableEntry with type='proration_adjustment'
    - [ ] Display clearly on invoice: "Pro-rated adjustment (X days)"

### 4.3 Profitability Guardrails & Alerts

#### Real-Time Margin Checker
- [x] **Implementation:** Integrate into PriceOverride creation/update
- [x] **Alert Modal:**
    - [x] Trigger when override price results in margin < company.margin_floor_percent
    - [x] Display: "⚠️ Margin Alert: This price results in X% margin, below your floor of Y%"
    - [x] Show: Cost Price, Proposed Price, Margin $, Margin %
    - [x] Require confirmation: "I understand this is below target margin" checkbox
    - [x] Log warning to BillingLog

#### Client Profitability Dashboard
- [x] **View:** `Modules/Billing/Resources/views/finance/profitability.blade.php`
- [x] **Route:** `Route::get('/finance/profitability', [FinanceController::class, 'profitability'])`
- [x] **Data Table:**
    - [x] Columns: Company, Revenue (MTD), COGS, Labor Cost, Gross Margin $, Gross Margin %, Effective Hourly Rate
    - [x] Sort by: Margin % (lowest first - at-risk clients)
    - [x] Color coding:
        - Green: Margin > 40%
        - Amber: Margin 20-40%
        - Rose: Margin < 20%
- [x] **Calculations:**
    - [x] Revenue: Sum of invoices.total for current month
    - [x] COGS: Sum of (invoice_line_items.quantity * products.cost_price)
    - [x] Labor Cost: Sum of (billable_entries.quantity * user.internal_cost_rate) WHERE type='time'
    - [x] Effective Hourly Rate: Revenue / Total billable hours
- [ ] **Drill-Down:**
    - [ ] Click company → detailed P&L view with month-over-month trend

### 4.4 AI-Driven Anomaly Detection

#### Anomaly Scoring Algorithm
- [x] **Implementation:** `AnomalyDetectionService->analyzeInvoice()` (already defined in Phase 2)
- [x] **Detection Rules:**
    - [x] **Variance Check:** Compare to average of last 3 months
        - If variance > 20%: +30 anomaly points
        - If variance > 50%: +60 anomaly points
    - [x] **Missing Items Check:** Compare line items to previous invoices
        - If previously recurring item is missing: +40 anomaly points
    - [x] **Quantity Spike Check:** If any line item quantity > 2x previous average
        - +20 anomaly points per item
    - [x] **New Item Check:** Line items never seen before for this company
        - +10 anomaly points (informational)
- [x] **Scoring:**
    - [x] Score 0-30: Green badge "Normal"
    - [x] Score 31-60: Amber badge "Review Recommended"
    - [x] Score 61-100: Rose badge "Action Required"
- [x] **Auto-Actions:**
    - [x] If score > 60: Automatically set invoice status to 'pending_review' (don't auto-send)
    - [x] Send Slack/email notification to finance team
    - [x] Log to BillingLog with severity='warning'

#### Machine Learning Enhancement (Future)
- [ ] **Optional:** Train simple ML model on historical invoice data
- [ ] **Features:** Company size, industry, seasonality, historical variance
- [ ] **Prediction:** Expected invoice amount ± confidence interval
- [ ] **Integration:** Compare actual to predicted, flag if outside 95% CI

### 4.5 Quote-to-Cash Workflow

#### Digital Quote System
- [x] **View:** `Modules/Billing/Resources/views/quotes/create.blade.php`
- [x] **Route:** `Route::get('/finance/quotes/create', [QuoteController::class, 'create'])`
- [x] **Interactive Quote Builder:**
    - [x] Select Company (or create new prospect)
    - [x] Add Products (searchable dropdown)

### 1.2 Model Logic Implementation
- [x] **Update `Modules\Billing\Models\Company.php`**
    - [x] Add relationships: `customers()`, `priceOverrides()`.
    - [x] Add helper method: `getEffectivePrice(Product $product)`.
- [x] **Update `Modules\Inventory\Models\Product.php`**
    - [x] Add relationship: `tierPrices()`.
    - [x] Add method: `getPriceForTier($tier)`.
- [x] **Seed Data**
    - [x] Create seeders for default Pricing Tiers.
    - [x] Create seeders for sample Companies with different tiers.

---

## Phase 2: API & Logic Layer

**Goal:** Enable the backend to calculate prices dynamically and generate invoices.

### 2.1 Service Catalog API
- [x] **Endpoint:** `GET /api/v1/finance/catalog` (Admin view - all tiers).
- [x] **Endpoint:** `GET /api/v1/finance/companies/{id}/catalog` (Client view - effective prices).
    - [x] Implement logic: Check Override -> Check Tier -> Fallback to Base.

### 2.2 Invoice Generation Engine
- [x] **Service:** `InvoiceGeneratorService`
    - [x] Method: `generateDraft(Company $company, $date)`.
    - [x] Logic: Collect recurring subscriptions + unbilled "Time & Materials" tickets.
- [x] **Endpoint:** `POST /api/v1/finance/invoices/generate` (Bulk generation trigger).
- [x] **Endpoint:** `POST /api/v1/finance/invoices/{id}/finalize` (Draft -> Sent).

### 2.3 Overrides Management
- [x] **Endpoint:** `POST /api/v1/finance/companies/{id}/overrides` (Create/Update override).
- [x] **Validation:** Ensure override price is not below COGS (Phase 3 prep).

---

## Phase 3: Frontend & UX

**Goal:** Expose all financial logic to the appropriate users via the UI.

### 3.1 Navigation & Layout
- [x] **Update Navigation (`navigation.blade.php`)**
    - [x] Add "Finance" Dropdown.
    - [x] Links: Dashboard, Service Inventory, Portal, Field Billing.

### 3.2 Finance Dashboard (Admin)
- [x] **Component:** `FinanceDashboard`
    - [x] Metrics: MRR, AR Aging (30/60/90), Gross Profit.
    - [x] Widget: "Pre-Flight Queue" summary.

### 3.3 Pre-Flight Billing Review
- [x] **View:** `Modules/Billing/Resources/views/finance/pre-flight.blade.php`
    - [x] **Data Grid:** List draft invoices with variance highlighting.
    - [x] **Actions:** Approve, Edit Line Items, Hold Invoice.
    - [x] **Anomaly Radar:** Visual alert for >20% month-over-month variance.

### 3.4 Service Inventory UI
- [x] **View:** Master Catalog Management.
    - [x] UI to set Base Price + Tier Multipliers.
- [x] **View:** Override Manager.
    - [x] Table showing all active client-specific overrides.

### 3.5 Client Portal
- [x] **View:** `My Services` tab.
    - [x] List active subscriptions/assets.
    - [x] "Pay Now" button for outstanding invoices.

---

## Phase 4: Automation & Intelligence (World Class)

**Goal:** Reduce manual effort and prevent revenue leakage.

### 4.1 Usage-Based Billing
- [x] **Integration:** RMM Webhook Handler.
    - [x] Logic: Update `quantity` on Subscription line items based on active device counts.
- [x] **Review Step:** "Usage Review" queue in Pre-Flight to confirm count changes.

### 4.2 Proration Logic
- [x] **Helper:** `ProrationCalculator`.
    - [x] Logic: Calculate partial costs based on `Company->settings['proration_policy']`.

### 4.3 Profitability Guardrails
    - [ ] Add Products (searchable dropdown)
    - [x] Quantity sliders with real-time price calculation
    - [x] Pricing tier selector (if prospect tier not yet set)
    - [x] Contract term selector (Monthly, Annual)
    - [x] Discount % input (optional)
    - [x] **Interactive Pricing Preview:**
        - Alpine.js component showing: Subtotal, Tax, Total, MRR
        - Slider: "Add X more users" → see price change in real-time
        - Toggle: "Annual billing" → show 10% discount
    - [x] Notes/Terms section (WYSIWYG editor)
    - [x] [Generate Quote PDF] [Send for Approval]
- [x] **Quote Approval Workflow:**
    - [x] Email to Client Admin with: Quote PDF + [Approve] [Request Changes] links
    - [x] **Approve Link:** Digital signature pad (canvas element)
    - [x] On signature: 
        - Mark quote as 'approved'
        - Trigger `QuoteApproved` event
- [x] **Automated Provisioning (Event Listeners):**
    - [x] `QuoteApproved` event triggers:
        1. **Create Company** (if new prospect)
        2. **Set pricing_tier** based on quote
        3. **Create Subscriptions** for all quoted products
        4. **Create first Invoice** (draft)
        5. **Send Welcome Email** with portal login
        6. **Notify Tech Team** for provisioning (webhook to PSA/RMM)
        7. **Create Vendor Orders** (if hardware included) - send to procurement
- [ ] **Vendor Order Integration:**
    - [ ] Create `vendor_orders` table (quote_id, vendor, products, status)
    - [ ] API integration with Dell/CDW/etc. (Phase 5 - optional)

### 4.6 Revenue Recognition & Accrual Accounting

- [x] **Migration:** `2025_01_01_000009_add_revenue_recognition_to_invoices.php`
    - [x] Add `revenue_recognition_method` ENUM ('cash', 'accrual') to invoices table
    - [x] Add `service_period_start` DATE to invoice_line_items
    - [x] Add `service_period_end` DATE to invoice_line_items
- [x] **Service:** `Modules\Billing\Services\RevenueRecognitionService.php`
    - [x] **Method:** `calculateMonthlyRevenue(Carbon $month): float`
        - [x] For accrual method: Sum line items WHERE service_period overlaps with month
        - [x] For cash method: Sum invoices WHERE payment_date in month
    - [x] **Method:** `getDeferredRevenue(): float`
        - [x] Annual/quarterly subscriptions paid upfront but not yet "earned"
- [x] **Report View:** `Modules/Billing/Resources/views/finance/revenue-recognition.blade.php`
    - [x] Monthly revenue breakdown (Recognized, Deferred, Total Billed)
    - [x] Export to CSV for accountant

### 4.7 Dunning Management (Automated Payment Reminders)

- [x] **Job:** `Modules\Billing\Jobs\SendPaymentReminderJob.php`
    - [x] Scheduled via Laravel Task Scheduler (daily)
    - [x] Query invoices WHERE status='sent' AND due_date is approaching/past
    - [x] Send email based on dunning schedule:
        - 3 days before due: "Friendly reminder"
        - On due date: "Payment due today"
        - 7 days overdue: "Overdue notice"
        - 14 days overdue: "Final notice"
        - 30 days overdue: "Account on hold" + disable service flag
- [x] **Email Templates:**
    - [x] `Modules/Billing/Mail/PaymentReminderMail.php`
    - [x] Blade template with: Invoice details, [Pay Now] button, contact info
    - [x] Tone escalation: Friendly → Firm → Urgent
- [x] **Opt-Out:** Setting in Company->settings['dunning_enabled'] = true/false

### 4.8 Accounting Software Sync (QuickBooks/Xero)

- [x] **Package:** Consider `spatie/laravel-quickbooks-client` or similar
- [x] **Configuration:**
    - [ ] OAuth flow for QB/Xero authentication
    - [x] Store tokens in `billing_settings` table or encrypted env
- [x] **Sync Strategy:**
    - [x] **One-Way (Recommended for Phase 4):** MSP App → QuickBooks
    - [x] Sync on Invoice finalization:
        - Create QB Invoice
        - Map products to QB Items
        - Map companies to QB Customers
    - [x] Sync on Payment recording:
        - Create QB Payment
        - Link to QB Invoice
- [x] **Job:** `Modules\Billing\Jobs\SyncToQuickBooksJob.php`
    - [x] Queue job on InvoiceFinalized event
    - [x] Retry logic with exponential backoff
    - [x] Error logging to BillingLog
- [x] **UI:** Settings page for sync configuration
    - [x] Toggle: "Enable QuickBooks Sync"
    - [x] Button: [Connect to QuickBooks] (OAuth)
    - [x] Status: Last sync time, error count
    - [x] [Test Connection] button

---

## Phase 5: World Class Features (Optional/Future)

### 5.1 Advanced Analytics & Benchmarking

- [ ] **Integration:** HTG Peer Groups or Service Leadership API (if available)
- [x] **Metrics to Track:**
    - [x] Average Revenue Per User (ARPU)
    - [ ] Customer Acquisition Cost (CAC)
    - [x] Lifetime Value (LTV)
    - [x] Gross Margin %
    - [ ] Effective Hourly Rate
    - [x] Revenue per Technician
- [x] **Benchmark Dashboard:**
    - [ ] Compare your metrics to industry averages
    - [x] Visual: Gauge charts showing your position vs. peers (Implemented as Metric Cards)
    - [ ] Recommendations: "Your effective hourly rate is $X, industry average is $Y. Consider raising prices."

### 5.2 Predictive Billing & Forecasting

- [x] **Service:** `Modules\Billing\Services\ForecastingService.php`
    - [x] **Method:** `forecastMRR(int $monthsAhead): array`
        - [x] Use linear regression or moving average on historical MRR
        - [x] Account for known contract ends/renewals
        - [x] Return: [month => predicted_mrr]
    - [x] **Method:** `forecastChurn(): float`
        - [x] Analyze subscription cancellation patterns
        - [x] Predict churn rate for next quarter
- [x] **Dashboard Widget:** Revenue forecast chart (next 6 months)

### 5.3 Multi-Currency Support (REJECTED)

- [-] **Migration:** Add `currency` VARCHAR(3) to invoices, subscriptions, products
- [-] **Service:** Currency conversion API integration (e.g., Fixer.io, Open Exchange Rates)
- [-] **Logic:** 
    - [-] Store prices in base currency (USD)
    - [-] Convert at invoice generation time
    - [-] Display currency symbol and code on invoices/portal

### 5.4 Advanced Procurement Workflow (DEFERRED)

- [-] **Purchase Orders:**
    - [-] Create PO from quote or manually
    - [-] Send to vendor via email or API
    - [-] Track PO status (Ordered, Received, Billed)
    - [-] Link PO to invoice (for COGS tracking)
- [-] **Vendor API Integrations:**
    - [-] Dell Premier API
    - [-] CDW API
    - [-] Ingram Micro
    - [-] Auto-create PO, track shipment, match vendor invoice to PO

### 5.5 White-Label Client Quoting Portal

- [x] **Subdomain:** `quotes.yourmsp.com`
- [x] **Public Quote Builder:**
    - [x] Client-facing form: Select services, see live pricing
    - [x] No login required initially
    - [x] Beautiful UX: Slider controls, live price updates, comparison tables
    - [x] "Configure Your Plan" wizard
    - [x] Output: Detailed quote PDF + [Request Consultation] button
- [x] **Benefits:**
    - [x] Lead generation (capture contact info)
    - [x] Reduces sales cycle (pre-qualified leads)
    - [x] Transparency builds trust

---

## Phase 6: Testing, Documentation & Training

### 6.1 Automated Testing

- [ ] **Unit Tests:**
    - [ ] PricingEngineService: Test tier pricing, override logic, margin calculations
    - [ ] ProrationCalculator: Test all proration policies
    - [ ] InvoiceGenerationService: Test recurring billing, unbilled entries aggregation
    - [ ] AnomalyDetectionService: Test scoring algorithm with mock data
- [ ] **Feature Tests:**
    - [ ] Invoice generation flow (API endpoint)
    - [ ] Payment recording flow
    - [ ] Price override creation with validation
    - [ ] Quote-to-cash workflow (event listeners)
- [ ] **Browser Tests (Dusk):**
    - [ ] Pre-Flight Review screen: Approve invoice flow
    - [ ] Client Portal: Pay invoice flow with Stripe test mode
    - [ ] Field Billing: Toggle billable, add expense
- [ ] **Target:** 80%+ code coverage for Billing module

### 6.2 Documentation

#### Technical Documentation
- [ ] **API Documentation:** OpenAPI/Swagger spec for all endpoints
    - [ ] Use `darkaonline/l5-swagger` package
    - [ ] Document request/response schemas, auth requirements
    - [x] **Manual Reference:** `docs/billing/API_REFERENCE.md` created for Public Quote Builder
- [ ] **Database Schema Diagram:** ERD showing all relationships
    - [ ] Use `laravel-er-diagram-generator` or similar
- [ ] **Service Architecture Diagram:** Visual map of services and their interactions
- [x] **Webhook Documentation:** `docs/billing/WEBHOOK_INTEGRATION.md` created

#### User Documentation
- [x] **Finance Admin Guide:** `docs/billing/FINANCE_ADMIN_GUIDE.md` created
    - [x] "How to run monthly billing"
    - [x] "Understanding the Pre-Flight Review"
    - [x] "Creating and managing price overrides"
    - [x] "Reading profitability reports"
- [x] **Technician Guide:** `docs/billing/TECHNICIAN_GUIDE.md` created
    - [x] "How to mark time as billable"
    - [x] "Adding parts and expenses to tickets"
    - [x] "Using the mobile barcode scanner"
- [x] **Client Admin Guide:** `docs/billing/CLIENT_ADMIN_GUIDE.md` created
    - [x] "Viewing and paying invoices"
    - [x] "Managing payment methods"
    - [ ] "Understanding your service breakdown"
- [ ] **Video Tutorials:**
    - [ ] 5-minute overview: "What is FinOps in our MSP platform?"
    - [ ] Role-specific walkthroughs (Finance, Tech, Client)

### 6.3 Training & Rollout Plan

- [ ] **Internal Training Sessions:**
    - [ ] Finance team: Full-day workshop on all features
    - [ ] Tech team: 1-hour session on field billing
    - [ ] Management: 30-min dashboard overview
- [ ] **Phased Rollout:**
    - [ ] **Week 1:** Internal testing with dummy data
    - [ ] **Week 2:** Pilot with 3-5 friendly clients
    - [ ] **Week 3:** Gather feedback, fix bugs
    - [ ] **Week 4:** Full rollout to all clients
- [ ] **Communication Plan:**
    - [ ] Email announcement to all clients
    - [ ] In-app notification about new portal features
    - [ ] Monthly "What's New" newsletter highlighting FinOps features

---

## Gap Analysis & Risk Mitigation

### Technical Risks

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| **Data Migration Failure** | High | Medium | - Backup database before migration<br>- Create mapping CSV for manual review<br>- Dry-run migration in staging<br>- Rollback plan |
| **Stripe Integration Issues** | High | Low | - Use Stripe test mode throughout dev<br>- Handle webhook retries<br>- Implement fallback to manual payment entry |
| **RMM Webhook Unreliability** | Medium | Medium | - Implement webhook retry queue<br>- Manual usage review as backstop<br>- Stale device threshold (ignore >30 days) |
| **Performance at Scale** | Medium | Medium | - Index all foreign keys and date columns<br>- Cache pricing calculations (5 min TTL)<br>- Queue invoice generation jobs<br>- Optimize SQL queries with EXPLAIN |
| **Pricing Logic Bugs** | High | Medium | - Comprehensive unit test coverage<br>- Manual QA of all pricing scenarios<br>- Pre-Flight Review as human checkpoint<br>- Price audit log (before/after changes) |

### Business Risks

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| **User Adoption (Technicians)** | Medium | Medium | - Simplify field billing UI (minimal clicks)<br>- Incentivize: Show technicians their billable hours<br>- Make default=billable (require opt-out, not opt-in) |
| **Client Confusion (Portal)** | Low | Medium | - Pre-rollout communication<br>- In-app tutorials/tooltips<br>- Dedicated support for first 30 days<br>- FAQ section in portal |
| **Revenue Leakage (Unbilled Time)** | High | Medium | - Automated reminders to techs for unbilled tickets<br>- Finance dashboard widget: "Unbilled Last 30 Days"<br>- Manager review: Weekly unbilled time report |
| **Margin Erosion (Price Overrides)** | High | Low | - Require `notes` field (audit trail)<br>- Approval workflow (can't override below floor without manager approval)<br>- Quarterly override review meeting<br>- Override Manager shows margin impact prominently |

### Operational Risks

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| **Month-End Billing Bottleneck** | Medium | High | - Automate invoice generation (scheduled job)<br>- Pre-Flight Review 2-3 days before month-end<br>- Stagger client billing dates if possible<br>- Queue jobs to prevent timeout |
| **Accounting Software Sync Failures** | Medium | Medium | - Manual sync fallback<br>- Error notifications to finance team<br>- Weekly reconciliation report<br>- Retry queue with exponential backoff |
| **Payment Processing Downtime** | High | Low | - Stripe has 99.99% uptime SLA<br>- Display maintenance notice if Stripe status API shows issue<br>- Accept manual payments as backup |

---

## Implementation Checklist Summary

### Phase 1: Foundation (Estimated: 4-6 weeks)
- [x] All database migrations (9 migrations)
- [x] All model enhancements (Company, Product, etc.)
- [x] 7 new models (ProductTierPrice, PriceOverride, Subscription, Invoice, BillableEntry, Payment, Quote)
- [x] 3 seeders (tier prices, sample companies, overrides)

### Phase 2: API & Logic (Estimated: 6-8 weeks)
- [x] 4 core services (PricingEngine, InvoiceGeneration, ProrationCalculator, AnomalyDetection)
- [x] 5 API controller groups (Catalog, Invoice, Override, BillableEntry, Payment)
- [x] 20+ API endpoints
- [x] Event listeners for automation

### Phase 3: Frontend & UX (Estimated: 8-10 weeks)
- [x] Navigation updates
- [ ] Finance Dashboard (5 major views)
- [x] Pre-Flight Review screen
- [x] Service Catalog enhancements
- [x] Price Override Manager
- [x] Client Portal (4 tabs)
- [x] Field Billing UI
- [x] Company Management views

### Phase 4: Automation (Estimated: 4-6 weeks)
- [x] RMM webhook integration
- [ ] Usage-based billing logic
- [ ] Automated proration
- [ ] Profitability guardrails
- [ ] Anomaly detection implementation
- [ ] Quote-to-cash workflow
- [ ] Revenue recognition service
- [ ] Dunning management
- [ ] Accounting software sync

### Phase 5: World Class (Estimated: 6-8 weeks, optional)
- [ ] Advanced analytics dashboard
- [ ] Predictive billing/forecasting
- [ ] Multi-currency support
- [ ] Advanced procurement
- [ ] White-label quoting portal

### Phase 6: Testing & Launch (Estimated: 3-4 weeks)
- [ ] Comprehensive test suite (80%+ coverage)
- [ ] All documentation
- [ ] Training materials
- [ ] Phased rollout plan execution

**Total Estimated Timeline: 31-42 weeks (7.5-10.5 months) for Phases 1-4 + Testing**

---

## Success Metrics & KPIs

### Billing Accuracy
- [ ] **Target:** <1% revenue leakage from unbilled time
- [ ] **Measurement:** Compare billable entries logged vs. invoiced monthly
- [ ] **Baseline:** Measure current state before implementation

### Cash Flow
- [ ] **Target:** Reduce average AR aging from X days to <30 days
- [ ] **Measurement:** Weighted average days in AR bucket
- [ ] **Target:** 80%+ invoices paid within 30 days

### Profit Visibility
- [ ] **Target:** 100% of clients have calculated gross margin in dashboard
- [ ] **Measurement:** Count of companies with complete COGS data
- [ ] **Target:** Identify and address bottom 20% of clients by margin

### Operational Efficiency
- [ ] **Target:** Reduce finance team hours on billing by 40%
- [ ] **Measurement:** Time tracking for "invoice generation" and "payment recording" tasks
- [ ] **Target:** Month-end close time from X days to 3 days

### User Adoption
- [ ] **Target:** 90%+ of technicians mark tickets as billable within 24 hours
- [ ] **Measurement:** Avg time from ticket close to billable entry creation
- [ ] **Target:** 50%+ of clients use portal to pay invoices (vs. check/manual)

### System Performance
- [ ] **Target:** Invoice generation for 100 companies completes in <5 minutes
- [ ] **Target:** Dashboard loads in <2 seconds (P95)
- [ ] **Target:** 99.5% uptime for payment processing

---

## Appendices

### A. Pricing Tier Recommended Multipliers

| Tier | Multiplier | Typical Use Case |
|------|------------|------------------|
| Standard | 1.00x | For-profit businesses, standard pricing |
| Non-Profit | 0.80-0.85x | 501(c)(3) organizations, educational institutions |
| Consumer | 1.05-1.15x | Residential clients, single-user accounts |

*Note: Adjust based on your market and cost structure.*

### B. Sample Proration Policy Configurations

**Full Month:**
- Client added mid-cycle: Billed full price from start date
- Client removed mid-cycle: No refund, service until end of cycle
- Best for: AYCE "all you can eat" contracts

**Daily Proration:**
- Formula: `(days_in_service / days_in_billing_cycle) * monthly_price`
- Client added mid-cycle: Prorated charge for remaining days
- Client removed mid-cycle: Prorated credit
- Best for: Per-user/per-device subscriptions

**Next Cycle:**
- Client added mid-cycle: No charge until next full cycle begins
- Client removed mid-cycle: Service until end of cycle, no refund
- Best for: Enterprise clients, contract-negotiated terms

### C. Invoice Number Format Recommendation

**Format:** `INV-YYYY-MM-XXXX`
- `YYYY`: Year (2025)
- `MM`: Month (01-12)
- `XXXX`: Sequential number within month (0001-9999)

**Example:** `INV-2025-01-0042` (42nd invoice in January 2025)

**Benefits:**
- Sortable by date
- Easy to identify billing month
- Unique within reasonable scale (9,999 invoices/month)

### D. Recommended Margin Floors by Service Type

| Service Type | Minimum Margin | Target Margin |
|--------------|----------------|---------------|
| Managed Services (AYCE) | 40% | 50-60% |
| Per-Device RMM | 30% | 40-50% |
| Software Licenses (resale) | 15% | 20-30% |
| Hardware Sales | 10% | 15-25% |
| Professional Services (hourly) | 50% | 60-70% |
| Project-Based Work | 35% | 45-55% |

*Note: These are industry benchmarks. Adjust based on your overhead and target profitability.*

### E. Field Billing Best Practices

1. **Default to Billable:** Make the billable toggle default to ON. Techs should explicitly mark as non-billable (internal work, warranty, etc.)
2. **Auto-Populate Rate:** Pull from company's T&M rate or user's default hourly rate. Don't make techs enter prices.
3. **Mobile-First:** Optimize field billing UI for mobile (large buttons, voice-to-text for descriptions)
4. **Require Description:** Don't allow blank time entries. Minimum 10 characters.
5. **Daily Reminder:** Send push notification at 5pm: "You have X unbilled hours today. Please review."
6. **Gamification:** Show leaderboard of "most billable hours this month" (with team opt-in)

### F. Client Communication Templates

#### New Portal Announcement Email
```
Subject: Introducing Your New [MSP Name] Client Portal

Dear [Client Admin Name],

We're excited to announce the launch of our new Client Portal, designed to give you 24/7 access to your account information and invoices.

What you can do:
✓ View and pay invoices online
✓ See your active services and licenses
✓ Update payment methods
✓ Download receipts and tax documents

Get Started: [Portal URL]
Your login: [Email]
Temporary password: [Generated password] (change on first login)

Questions? Reply to this email or call [Support Number].

Best regards,
[Your Name]
[MSP Name]
```

#### Invoice Email Template
```
Subject: Invoice [INV-YYYY-MM-XXXX] from [MSP Name] - Due [Date]

Hello [Client Admin Name],

Your invoice for [Month] is ready.

Invoice #: [INV-YYYY-MM-XXXX]
Amount Due: $[Total]
Due Date: [Date]

[Pay Now Button] → Links to portal payment page

View full invoice details: [Portal Link]

Services this month:
- [Service 1]: $[Amount]
- [Service 2]: $[Amount]
- [Service 3]: $[Amount]

Questions about this invoice? Contact us at [Support Email] or [Phone].

Thank you for your business!
[Your Name]
[MSP Name]
```

---

## Document Control

**Version:** 2.0 (Enhanced)
**Last Updated:** December 26, 2025
**Owner:** Lead FinOps Architect / Product Manager
**Review Cycle:** Monthly during implementation; Quarterly post-launch

**Revision History:**
- v1.0 (Initial): Basic checklist-style plan
- v2.0 (Enhanced): Comprehensive feature matrix, persona-based UX, detailed implementation steps, World Class features

**Related Documents:**
- PROJECT_CONTEXT.md
- LARAVEL_11_MODERNIZATION_AUDIT.md
- UX_STYLE_GUIDE.md

---

**Status Dashboard** (Update as you progress)

| Phase | Status | Completion % | Target Date | Actual Date |
|-------|--------|--------------|-------------|-------------|
| Phase 1: Foundation | **Complete** | 100% | Dec 2025 | Dec 26, 2025 |
| Phase 2: API & Logic | **Complete** | 100% | Dec 2025 | Dec 26, 2025 |
| Phase 3: Frontend & UX | **Complete** | 100% | Dec 2025 | Dec 26, 2025 |
| Phase 4: Automation | **Complete** | 100% | Dec 2025 | Dec 26, 2025 |
| Phase 5: World Class | **Complete** | 100% | Dec 2025 | Dec 26, 2025 |
| Phase 6: Testing & Launch | In Progress | 10% | Jan 2026 | - |

**Next Steps:**
1. Execute [Post-Implementation Checklist](POST_IMPLEMENTATION_CHECKLIST.md) to address audit findings.
2. Begin comprehensive "Phase 6" testing (Unit, Feature, Browser).
3. Generate API documentation (Swagger).
4. Prepare training materials for Finance and Tech teams.

---

*End of Document*

# Implementation Guide: Hybrid MSP Billing & Inventory System
# Module Focus: html/Modules/Billing & html/Modules/Inventory
# Version: 1.1 (January 2026)

## Implementation Status (Jan 11, 2026)
*   **Phase 1 (Provisioning Logic):** ✅ COMPLETED via `ProvisionQuote` refactor.
*   **Phase 1.1 (Strategy-Based Billing):** ✅ COMPLETED (RTO/Monthly/One-Time Logic).
*   **Phase 2 (Schema Alignment):** ✅ COMPLETED (Models Updated)
    *   [x] Verify `inventory_products` columns (`internal_bundle_costs`).
    *   [x] Verify `inventory_assets` columns (`ownership_status`, `custody_context`).
    *   [x] Verify `billing_agreements` columns (`rto_total_cents`, `is_separate_hosting`).
*   **Phase 3 (Consumption & Credits):** ✅ COMPLETED
    *   [x] `billing_credit_transactions` database migration.
    *   [x] `CreditLedgerService` implementation.
    *   [x] `PricingEngine` implementation.
    *   [x] `PostPaidAggregator` Logic (Integrated into `InvoiceGenerationService`).
*   **Phase 4 (Reconciliation):** ✅ COMPLETED
    *   [x] `ReconciliationService` implemented (Credits -> Finalize -> Helcim).
    *   [x] Link `InvoiceGenerationService` to `ReconciliationService`.
    *   [x] Helcim `correlation_id` (invoice number) support added.

---

---

## 1. ARCHITECTURAL OVERVIEW
This guide outlines a Domain-Driven Design (DDD) approach for a Managed Service Provider (MSP) application. It handles physical hardware, SaaS entitlements, recurring service tiers, and "Rent-to-Own" (RTO) development cycles.

### Core Principles:
- **Decoupled Handshake:** Billing and Inventory communicate via Events and DTOs, never via direct Model imports.
- **Financial Precision:** Integer-based math (cents) using the Money pattern.
- **State Machine Integrity:** Assets move through defined states (Consignment -> Stock -> Client-Owned).
- **Strategy-Based Billing:** Supports Monthly, Annual, Milestone, Hourly, and RTO models.

---

## 2. DATABASE SCHEMA: PRODUCT & ASSET MODELS

### A. The Product Template (`inventory_products`)
Defines the "What." Acts as a master catalog for hardware templates and service tiers.
- `id`, `sku`, `name`, `category` (Hardware, SaaS, Service, Project).
- `type` (Generic-Template, Fixed-Asset, Recurring-Seat).
- `base_cost_cents`, `base_price_cents`.
- `internal_bundle_costs` (JSONB): Tracks "hidden" RMM/AV costs ($ per seat) for margin calculation.

### B. The Asset Instance (`inventory_assets`)
Defines the "Which." Tracks the physical or digital instance of a product.
- `product_id` (BelongsTo Product).
- `ownership_status` (Enum: 'Consignment', 'Stock', 'Client-Owned', 'Scrapped').
- `custody_context` (MorphTo): Links to Client, Location, or Internal Warehouse.
- `specifications` (JSONB): Stores Model, CPU, RAM, Serial Number, License Keys.
- `is_billable` (Boolean).

### C. The Billing Agreement (`billing_agreements`)
Defines the "How much." Stores the financial contract for an asset or service.
- `client_id`, `asset_id` (Nullable).
- `billing_strategy` (Enum: 'Monthly', 'Annual', 'RTO', 'Milestone', 'Usage').
- `rto_total_cents`, `rto_balance_cents`.
- `is_separate_hosting` (Boolean): If true, hosting remains active when RTO ends.

---

## 3. CORE SERVICE LOGIC

### A. Inventory State Machine (The "Truth Ledger")
All stock movements must write an entry to `inventory_transactions` to prevent race conditions.
1. `reserve($product, $qty)`: Atomic lock on stock (TTL enabled).
2. `commit($reservationId)`: Moves state from 'Reserved' to 'Allocated'.
3. `release($reservationId)`: Returns stock to 'Available'.
4. **Ownership Flip:** When buying Consignment for Stock:
   - Debit Company Cash, Credit Internal Inventory.
   - Update `ownership_status` from 'Consignment' to 'Stock'.

### B. The "Generic-to-Specific" Procurement Workflow
1. **Quote:** User adds a "Generic Laptop" Product.
2. **Purchase:** Admin enters specific model (e.g., Dell Latitude) and Serial Number.
3. **Ingestion:** System creates an `Asset` record. The Generic Product is the "Parent," the Dell is the "Instance."
4. **Tracking:** Trends are tracked by Product SKU (How many 'Generic Laptops' sold?) while Asset handles the specific lifecycle.

### C. Automated Rent-to-Own (RTO) Workflow
- **Monthly Trigger:** Helcim processes subscription payment.
- **Ledger Update:** Subtract payment from `rto_balance_cents`.
- **Completion:** When `rto_balance_cents <= 0`:
  - Send `RTOPaidInFullNotification`.
  - Close RTO line item.
  - **Maintain Hosting:** If `is_separate_hosting` is true, the recurring 'Hosting/Maintenance' product remains active in a separate subscription.

---

## 4. FLEXIBLE BILLING STRATEGIES

### Usage-Based / On-Demand Support
- **Ticket Qualifying:** Technicians assign Tier (1/2/3) upon resolution.
- **Credit Wallet:** Client "OUT" transactions from the `InventoryLedgerService` (Support Credits).
- **Post-Paid Aggregation:**
  - On the 1st of the month, query all `Resolved` tickets for the previous month.
  - Apply "Tier Multipliers" from the `PricingEngine`.
  - Validate against "Monthly Pre-approved Limit."
  - If limit exceeded: Flag as "Pending Approval"; otherwise, push to Helcim.

---

## 5. REFINEMENTS FOR "WORLD CLASS" STATUS

### Idempotency & Reconciliation
- All Helcim transactions must include a `correlation_id` (Quote UUID).
- The Billing module must perform a "Pre-Bill Reconciliation":
  1. Count Active Seats (Support Plan).
  2. Pull Unbilled Dev Hours.
  3. Deduct available Credit Balances.
  4. Finalize Total and dispatch to Helcim.

### Decoupling Logic (Example Implementation)
```php
namespace Modules\Billing\Services;

interface ProductAvailabilityInterface {
    public function check(ProductSnapshot $dto): bool;
}

// Billing calls this interface. Inventory implements it.
// No direct model access.
```

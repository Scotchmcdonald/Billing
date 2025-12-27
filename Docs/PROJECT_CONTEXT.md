# PROJECT_CONTEXT.md

## 1. Current State

### Framework & Environment
*   **Laravel Version:** 11.0 (via `laravel/framework` ^11.0)
*   **PHP Version:** ^8.2
*   **Key Service Providers:**
    *   `Modules\Billing\Providers\BillingServiceProvider` (Billing Module)
    *   `Modules\Inventory\Providers\InventoryServiceProvider` (Inventory Module)
    *   `Modules\Crm\Providers\CrmServiceProvider` (Existing CRM Module)
    *   `Modules\EmailMigration\Providers\EmailMigrationServiceProvider` (Email Migration Module)
    *   `Qirolab\Theme\ThemeServiceProvider` (Theming)
    *   `App\Providers\ModuleCompatibilityServiceProvider`
*   **Billing-Adjacent Packages:**
    *   `laravel/cashier` (Assumed to be installed or required for Billing)
    *   `nwidart/laravel-modules` (Modular architecture)
    *   `spatie/laravel-activitylog` (Audit logging)
    *   `mews/purifier` (HTML sanitization)
    *   `league/csv` (For Finance Exports)

### Module Management
*   **Catalog:** `config/modules_catalog.php` defines installable modules.
    *   **Billing:** `https://github.com/Scotchmcdonald/Billing`
    *   **MspInventory:** `https://github.com/Scotchmcdonald/MspInventory` (Installs as `Inventory`)
    *   **CRM:** `https://github.com/BorealTek/CRM-Module`
    *   **Email Migration:** `https://github.com/BorealTek/Email-Migration`
*   **Installation Logic:** `ModulesController` handles GitHub cloning and `module.json` name resolution to prevent "Module Not Found" errors when repo name != module name.

### UI/UX & Design Tokens
*   **CSS Framework:** Tailwind CSS (configured in `tailwind.config.js`)
*   **Design Tokens:**
    *   `success`: `colors.emerald`
    *   `warning`: `colors.amber`
    *   `danger`: `colors.rose`
    *   `primary`: `colors.indigo`
    *   Font: `Figtree`
*   **Key Layouts:**
    *   `resources/views/layouts/app.blade.php`: Main application layout with navigation and auth checks.
    *   `resources/views/layouts/navigation.blade.php`: Navigation component.
*   **Blade Components:**
    *   `x-layouts.navigation`
    *   `x-stripe-payment-element`: Custom component for Stripe Elements (ACH prioritized).
    *   `x-billing::troubleshooting-card`: Error handling component with actionable steps.
    *   `@action('layout.head')`, `@action('layout.body_start')` (Hooks)
*   **UX Philosophy:** "Pilot's Cockpit" - State-aware feedback (loading states), high-density data views, and actionable error recovery.
*   **Module-Specific UI Implementations (World Class Standards):**
    *   **FinOps Control Tower (Billing Dashboard):**
        *   **Data Visualization:** SVG Sparklines for MRR and AR trends (lightweight, no external charting lib).
        *   **Safety:** "Hazard Stripes" background patterns for critical/destructive zones.
        *   **Interactivity:** Alpine.js powered export dropdowns and real-time filter toggles.
    *   **Product Catalog (Inventory):**
        *   **Filtering:** Interactive "Filter Pills" (Active, Draft, Low Stock) powered by Alpine.js.
        *   **Power User Features:** Visual keyboard shortcut hints (e.g., `⌘K`) in search bars.
        *   **Layout:** High-density table layouts with inline status indicators and secondary actions.
    *   **Payment Wizard (Billing):**
        *   **Trust Signals:** Visual Credit Card Input simulation (brand detection, physical card look).
        *   **Context:** Sticky Order Summary that remains visible during multi-step flows.
        *   **Behavioral Nudges:** Dynamic Fee Offset calculator showing savings when switching from CC to ACH.
        *   **Simulation:** Plaid-style bank connection UI for ACH flows.

### Auth & Security
*   **Authentication Stack:** Laravel Breeze (implied by `laravel/breeze` in `require-dev` and structure of `routes/web.php` / `UserController`).
*   **Middleware:**
    *   `auth`: Standard Laravel authentication.
    *   `verified`: Email verification.
    *   `admin`: `App\Http\Middleware\EnsureUserIsAdmin`
    *   `theme`: `App\Http\Middleware\ApplyUserTheme`
    *   `billing.auth`: `Modules\Billing\Http\Middleware\EnsureUserCanAccessCompanyBilling` (Enforces tenant isolation).
    *   `can:finance.admin`: Custom gate/middleware for internal finance staff.

## 2. Model Definitions

### Company (Tenant)
*   **Model:** `Modules\Billing\Models\Company`
*   **Table:** `companies`
*   **Attributes:**
    *   `id`, `name`, `email`
    *   `stripe_id`, `pm_type`, `pm_last_four`, `trial_ends_at` (Cashier columns)
*   **Relationships:**
    *   `billingAuthorizations()`: HasMany `BillingAuthorization`
    *   `users()`: BelongsToMany `User` (via `billing_authorizations`)
    *   `subscriptions()`: HasMany (Cashier)
    *   `priceBooks()`: HasMany `PriceBook` (Inventory)
*   **Key Methods:**
    *   `supportedPaymentMethods()`: Returns `['card', 'us_bank_account']`.

### User
*   **Model:** `App\Models\User`
*   **Table:** `users`
*   **Attributes:**
    *   `id`, `first_name`, `last_name`, `email`, `role`, `status`
*   **Relationships:**
    *   `mailboxes`, `folders`, `conversations`, `threads`
    *   (Inferred) `billingAuthorizations()`: HasMany `BillingAuthorization`

### Billing Authorization (RBAC)
*   **Model:** `Modules\Billing\Models\BillingAuthorization`
*   **Table:** `billing_authorizations`
*   **Attributes:**
    *   `user_id`, `company_id`, `role` (`billing.admin`, `billing.payer`)

### Billing Log (Audit)
*   **Model:** `Modules\Billing\Models\BillingLog`
*   **Table:** `billing_logs`
*   **Attributes:**
    *   `user_id`, `company_id`, `action`, `description`, `payload`, `ip_address`

### Inventory Models
*   **Product:** `Modules\Inventory\Models\Product`
    *   `sku`, `name`, `base_price`, `cost_price`, `gl_account`
*   **PriceBook:** `Modules\Inventory\Models\PriceBook`
    *   `company_id`, `product_id`, `unit_price` (Overrides base price)
*   **InvoiceLineItem:** `Modules\Inventory\Models\InvoiceLineItem` (Pending/Bucket items)

## 3. Integration Hooks

### Existing Data Conflicts
*   **`customers` table:** Existing CRM table (`Modules/Crm`).
    *   Columns: `first_name`, `last_name`, `company` (string), `email` (via `emails` table).
    *   **Conflict:** The CRM `customers` table uses a string for `company`. The Billing module introduces a strict `companies` table.
    *   **Resolution Strategy:** `billing:sync-crm-companies` console command syncs unique CRM company strings to the `companies` table.

### Route Naming Conventions
*   **Portal:** `billing.portal.*`
    *   `billing.portal.entry`: Redirects to company dashboard.
    *   `billing.portal.dashboard`: Main view `{company}`.
    *   `billing.portal.payment_methods`: Manage payments `{company}`.
    *   `billing.portal.invoices`: View invoices `{company}`.
    *   `billing.portal.team`: Manage team `{company}`.
*   **Finance:** `billing.finance.*`
    *   `billing.finance.index`: Dashboard.
    *   `billing.finance.export`: CSV Export.
*   **Inventory API:** `api.inventory.*`
    *   `GET /api/inventory/{company}/catalog`: JSON catalog with company-specific pricing.
*   **Webhooks:** `billing.stripe.webhook`

### Service Layer
*   **`BillingAuthorizationService`:** Handles multi-tenant logic (`canViewBilling`, `getAuthorizedCompanies`, `scopeForUser`).
*   **`PaymentGatewayService`:** Wraps Stripe interactions (Setup Intents, Invoices).
*   **`AccountingExportService`:** Generates CSV reports for finance admins, including Churn Risk calculation.
*   **`CatalogService` (Inventory):** Resolves product pricing (PriceBook vs Base Price).
*   **`OffsetFeeService` (Billing):** Calculates credit card processing fees.
*   **`InvoiceGenerator` (Billing):** Generates line items from Inventory SKUs.

### Jobs & Queues
*   **`AccountingSyncJob`:** Syncs finalized invoices to external accounting systems (QuickBooks/Xero).

### Console Commands
*   `billing:sync-crm-companies`: Syncs CRM customer company strings to the `companies` table.

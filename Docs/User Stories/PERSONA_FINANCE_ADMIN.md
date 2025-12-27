# Persona: Finance Admin
**Role:** The financial controller or office manager responsible for the MSP's cash flow and billing accuracy.

## Primary UI Locations
- **FinOps Dashboard:** `/billing/dashboard` ✅
- **Invoice Management:** `/billing/invoices` ✅
- **Subscription Manager:** `/billing/subscriptions` ✅
- **Reports Center:** `/billing/reports` ✅
- **Pre-Flight Review:** `/billing/pre-flight` ✅
- **Price Overrides:** `/billing/overrides` ✅

## User Stories (Implemented)

### Billing Cycle Management
- ✅ **As a Finance Admin**, I want to **generate all recurring invoices in one click** so that I don't have to manually create 50+ invoices every month.
  - *UI: Pre-Flight Review | Logic: `GenerateMonthlyInvoices` command*
- ✅ **As a Finance Admin**, I want to **review "Draft" invoices before they are sent** so that I can catch errors (like $0 line items) before the client sees them.
  - *UI: Pre-Flight Review with Anomaly Score | Logic: `AnomalyDetectionService`*
- ✅ **As a Finance Admin**, I want to **create client-specific price overrides** so that I can offer custom pricing without changing the global catalog.
  - *UI: Overrides Manager | Logic: `PricingEngineService`*

### Accounts Receivable (AR)
- ✅ **As a Finance Admin**, I want to **see a list of all overdue invoices sorted by age** so that I know who to call first for collections.
  - *UI: Dashboard AR Aging Widget (30/60/90+)*
- ✅ **As a Finance Admin**, I want the system to **automatically send payment reminders** so that I don't have to write awkward emails manually.
  - *Logic: `SendPaymentReminderJob` (3-day, due-date, 7-day, 14-day, 30-day) | UI: ⚠️ No history visibility*
- ✅ **As a Finance Admin**, I want to **record a check payment** against an invoice so that the client's balance is updated immediately.
  - *Logic: `Payment` model | UI: Portal Pay Modal*

### Profitability & Reporting
- ✅ **As a Finance Admin**, I want to **see the Gross Margin per Client** so that I can identify which clients are unprofitable.
  - *UI: Profitability Dashboard | Logic: `AnalyticsService`*
- ✅ **As a Finance Admin**, I want to **see a revenue forecast** so that I can plan for the future.
  - *UI: Dashboard Forecast Chart | Logic: `ForecastingService`*

## Problems Solved
1.  **Revenue Leakage:** Prevents "forgotten" billable hours via Anomaly Detection.
2.  **High DSO:** Reduces time to get paid via automated dunning.
3.  **Manual Drudgery:** Eliminates hours of spreadsheet work with scheduled `GenerateMonthlyInvoices`.

---

## 🚧 Valuable User Stories (Not Yet Implemented)

### AR & Collections
- ❌ **As a Finance Admin**, I want to **see a timeline of when dunning emails were sent for a specific invoice** so that I have context before calling a client.
  - *Gap: Logic exists (`BillingLog`), but no UI to surface it.*
- ❌ **As a Finance Admin**, I want to **pause dunning for a single invoice** (e.g., client disputed it) so that they don't get automated reminders while we resolve the issue.
  - *Gap: No `dunning_paused` flag on Invoice model.*

### Bulk Operations
- ❌ **As a Finance Admin**, I want to **apply a global price increase** (e.g., +5%) to all clients at once so that I can maintain margins after a vendor price hike.
  - *Gap: Overrides UI is single-record only. No bulk update feature.*
- ❌ **As a Finance Admin**, I want to **export the Pre-Flight Review to Excel** so that I can share it with my accountant before approving.
  - *Gap: Button exists in UI but not wired to logic.*

### Advanced Reporting
- ❌ **As a Finance Admin**, I want to **see "Effective Hourly Rate" per client** so that I can compare AYCE contracts to actual labor consumed.
  - *Gap: Data exists in `AnalyticsService` but metric not exposed in Profitability UI.*
- ❌ **As a Finance Admin**, I want to **benchmark our KPIs against industry averages** so that I know if we're performing well.
  - *Gap: HTG/Service Leadership API integration deferred.*

### Pre-Paid Hour Blocks (Retainers)
- ❌ **As a Finance Admin**, I want to **sell pre-paid hour blocks** to clients so that they can budget predictably.
  - *Gap: Mentioned in Tier 4 of plan, not implemented.*
- ❌ **As a Finance Admin**, I want the system to **auto-deduct from the retainer** when technicians log time so that the balance is always accurate.
  - *Gap: No retainer balance model or deduction logic.*
- ❌ **As a Finance Admin**, I want to **see a "Retainer Balance" widget** on the client profile so that I know when to upsell more hours.
  - *Gap: No UI for retainer tracking.*

### Adjustments & Disputes
- ❌ **As a Finance Admin**, I want to **issue a Credit Note** against an invoice so that I can correct errors without voiding the entire invoice.
  - *Gap: No Credit Note model or workflow.*
- ❌ **As a Finance Admin**, I want to **mark an invoice as "Disputed"** so that dunning pauses and I have a visual indicator.
  - *Gap: No `is_disputed` flag on Invoice model.*
- ❌ **As a Finance Admin**, I want to **add internal notes to an invoice** so that my team has context on unusual situations.
  - *Gap: No `internal_notes` field or UI.*


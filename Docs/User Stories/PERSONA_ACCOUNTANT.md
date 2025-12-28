# Persona: External Accountant / Bookkeeper
**Role:** An external party (CPA firm, bookkeeper) who needs access to financial data for tax prep, audits, and reconciliation without full system access.

## Primary UI Locations
- **Accounting Export:** `/billing/export` ❌ Not Implemented
- **Client Portal (Read-Only):** `/portal` 🔶 Not designed for accountants

## User Stories (Implemented)

### Data Export
- 🔶 **As an Accountant**, I want to **see invoice data** so that I can reconcile with the general ledger.
  - *Partial: `AccountingExportService` exists, but no dedicated UI. QuickBooks sync available.*

## Problems Solved
1.  **QuickBooks Sync:** If enabled, accountant can work in their native tool.

---

---

## 📋 Phase 13: Accountant Role & Reconciliation Tools
**Priority:** MEDIUM | **Estimated Effort:** 24-32 hours | **Pattern:** Control Tower + Resilient Design

### Phase Overview
This phase introduces a dedicated "Accountant" role with read-only financial access and comprehensive reconciliation tools. Focused on external accountants and bookkeepers who need financial data without system modification capabilities.

### User Stories for Phase 13 Implementation

#### Story 13.1: Dedicated Accountant Role
**As an Accountant**, I want a **limited "Accountant" role** that can view financials but not modify invoices or payments so that I have read-only access.

**Implementation Details:**
*   **Permission:** `accountant.readonly`
*   **Access Scope:**
    *   ✅ View invoices, payments, subscriptions
    *   ✅ Generate and export reports
    *   ✅ View audit logs
    *   ✅ Access revenue recognition schedules
    *   ❌ Create/edit/delete financial records
    *   ❌ Approve invoices
    *   ❌ Process payments
    *   ❌ Modify client data
*   **UI Indicators:**
    *   "Read-Only Access" badge in header
    *   Action buttons disabled with tooltip explanation
    *   Clear visual distinction from Finance Admin views
*   **Implementation:**
    *   Add `accountant` role to `roles` enum
    *   Create middleware: `EnsureUserIsAccountant`
    *   Update authorization policies for read-only access
*   **Invitation Workflow:**
    *   Finance Admin can invite external accountants via email
    *   Limited user creation form (no company modification access)
    *   Automatic role assignment upon acceptance

#### Story 13.2: Payments Register View
**As an Accountant**, I want to **see a Payments register** (all payments received, by method, with invoice links) so that I can reconcile to bank statements.

**Implementation Details:**
*   **Route:** `/billing/accountant/payments-register`
*   **UX Pattern:** Control Tower (high-density table)
*   **Components:**
    *   `x-payments-register-table` with advanced filtering
    *   `x-payment-method-badge` for visual method identification
    *   `x-reconciliation-status` indicator
*   **Table Columns:**
    *   Date Received
    *   Payment Method (Card/ACH/Check)
    *   Amount
    *   Invoice Number (linked)
    *   Client Name
    *   Transaction ID
    *   Reconciliation Status
    *   Fee Amount
*   **Filtering:**
    *   Date range picker
    *   Payment method filter
    *   Reconciliation status (Matched/Unmatched/Pending)
    *   Client search
*   **Export Options:**
    *   Excel (.xlsx)
    *   CSV
    *   QuickBooks IIF
    *   Xero format
*   **Service:** `PaymentReconciliationService` (new)
    *   Methods: `getPaymentsRegister()`, `markReconciled()`, `exportForAccounting()`

#### Story 13.3: AR Aging Report Export
**As an Accountant**, I want to **download a monthly AR Aging report in Excel** so that I can include it in the client's financial package.

**Implementation Details:**
*   **Location:** AR Aging dashboard widget
*   **Button:** "Export Report" with format dropdown
*   **Export Formats:**
    *   Excel (.xlsx) - formatted with charts
    *   CSV - raw data
    *   PDF - print-ready
*   **Report Content:**
    *   Summary by aging bucket (Current, 1-30, 31-60, 61-90, 90+)
    *   Detail by client and invoice
    *   Historical trend chart (last 12 months)
    *   Industry benchmark comparison (if available)
*   **Service:** Existing `ArAgingExport` class
    *   Enhance with Excel styling and charts
*   **Scheduling:**
    *   Option to schedule automatic monthly delivery
    *   Email to accountant on 1st of each month

#### Story 13.4: Bulk Invoice PDF Export
**As an Accountant**, I want to **download all invoices for a date range as a ZIP of PDFs** so that I have backup documentation for an audit.

**Implementation Details:**
*   **Route:** `/billing/accountant/export/bulk-invoices`
*   **UX Pattern:** Guided Journey (3-step wizard)
*   **Wizard Steps:**
    1.  **Selection:** Date range, status filter, client filter
    2.  **Options:** File naming convention, include paid/unpaid, include credit notes
    3.  **Processing:** Progress bar with cancellation option
*   **Components:**
    *   `x-date-range-picker` for selection
    *   `x-export-progress` with live status updates
    *   `x-download-ready-notification`
*   **Processing:**
    *   Queued job for large exports (>50 invoices)
    *   Generates individual PDFs
    *   Packages into ZIP archive
    *   Stores temporarily (24-hour expiration)
    *   Email notification when ready
*   **Service:** Existing `ExportService`
    *   New method: `bulkInvoicePdfExport()`
*   **Performance:**
    *   Async processing with progress updates
    *   Estimated time displayed based on count
    *   Compression for faster download

#### Story 13.5: Revenue Recognition Schedule
**As an Accountant**, I want to **see a Revenue Recognition schedule** so that I can properly book deferred revenue.

**Implementation Details:**
*   **Route:** `/billing/accountant/revenue-recognition`
*   **Existing:** UI exists (`revenue-recognition.blade.php`)
*   **Enhancement Tasks:**
    *   Validate data accuracy against subscriptions
    *   Add month-by-month breakdown table
    *   Implement GAAP/IFRS 15 compliance indicators
    *   Add export to Excel with journal entries
*   **Display Components:**
    *   `x-revenue-schedule-table` with monthly recognition
    *   `x-deferred-revenue-summary` cards
    *   `x-compliance-indicator` showing standards met
*   **Calculation Logic:**
    *   MRR contracts: Recognize evenly over term
    *   Annual prepayments: Monthly recognition schedule
    *   One-time services: Recognize on completion/delivery
*   **Export Format:**
    *   Excel with separate sheets per month
    *   Includes journal entry format
    *   Debit/Credit columns for easy posting

#### Story 13.6: Sales Tax Summary Report
**As an Accountant**, I want to **see Sales Tax collected by jurisdiction** so that I can file state/local tax returns.

**Implementation Details:**
*   **Route:** `/billing/accountant/tax-summary`
*   **Data Source:** Existing `TaxReportService`
*   **Report Sections:**
    *   Summary by jurisdiction (state/county/city)
    *   Detail by invoice
    *   Tax-exempt transactions
    *   Nexus indicator (where we collect)
*   **Table Columns:**
    *   Jurisdiction Name
    *   Tax Rate
    *   Taxable Amount
    *   Tax Collected
    *   Transaction Count
    *   Filing Period
*   **Filtering:**
    *   Date range (quarterly typical)
    *   Jurisdiction selector
    *   Include/exclude tax-exempt
*   **Export Options:**
    *   Excel with pivot tables
    *   CSV for tax software import
    *   PDF for filing documentation
*   **Integration:**
    *   Avalara export format (if using tax automation)
    *   TaxJar format
    *   Manual filing format

---

### Phase 13 Implementation Checklist

#### Backend Tasks
- [ ] Add `accountant` role to user roles enum
- [ ] Create `EnsureUserIsAccountant` middleware
- [ ] Update authorization policies for accountant read-only access
- [ ] Create `PaymentReconciliationService`
- [ ] Enhance `ArAgingExport` with Excel styling and charts
- [ ] Add `bulkInvoicePdfExport()` to `ExportService`
- [ ] Create `BulkInvoiceExportJob` for async processing
- [ ] Validate and fix revenue recognition calculations
- [ ] Add tax jurisdiction tracking to invoice tax lines
- [ ] Create accountant invitation workflow

#### Frontend Tasks
- [ ] Create `/billing/accountant/dashboard.blade.php` overview
- [ ] Create `/billing/accountant/payments-register.blade.php`
- [ ] Create `x-payments-register-table` component
- [ ] Create `x-payment-method-badge` component
- [ ] Create `x-reconciliation-status` component
- [ ] Add export button to AR Aging widget
- [ ] Create bulk invoice export wizard
- [ ] Create `x-export-progress` component with live updates
- [ ] Enhance revenue recognition view with compliance indicators
- [ ] Create tax summary report view
- [ ] Add "Read-Only Access" badge to accountant views
- [ ] Apply semantic color classes throughout

#### Testing Tasks
- [ ] Test accountant role permissions (CRUD restrictions)
- [ ] Test payments register filtering and sorting
- [ ] Test bulk PDF export with 500+ invoices
- [ ] Verify revenue recognition accuracy against GAAP standards
- [ ] Test tax summary by jurisdiction
- [ ] Test export formats (Excel, CSV, PDF, QuickBooks, Xero)
- [ ] Accessibility audit for all accountant views
- [ ] Load test for concurrent exports

#### Documentation Tasks
- [ ] Document accountant role creation and invitation process
- [ ] Document payments register reconciliation workflow
- [ ] Document revenue recognition methodology
- [ ] Document tax jurisdiction mapping and filing process
- [ ] Create troubleshooting guide for export issues
- [ ] Document supported accounting software integrations

---

### Success Metrics for Phase 13
*   **Adoption:** 70%+ of accountants use payments register monthly
*   **Efficiency:** 60% reduction in time to reconcile payments
*   **Accuracy:** 100% match between payments register and bank statements
*   **Satisfaction:** Accountants rate export features 4.5/5 or higher

---

### Dependencies
*   **Services:** Existing `ExportService`, `TaxReportService`, `ArAgingExport`
*   **Models:** `Payment`, `Invoice`, `TaxLine`, `Subscription`
*   **Libraries:** `league/csv`, PDF generation library
*   **External:** QuickBooks API, Xero API (for export formats)

---

### Risk Mitigation
*   **Permission Errors:** Comprehensive testing of read-only restrictions
*   **Export Performance:** Queue large exports to avoid timeouts
*   **Data Accuracy:** Implement validation checks before generating reports
*   **Format Compatibility:** Test exports with actual accounting software
*   **Compliance:** Consult with CPA on revenue recognition methodology

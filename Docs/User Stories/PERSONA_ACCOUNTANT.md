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

## 🚧 Valuable User Stories (Not Yet Implemented)

### Export & Reporting
- ❌ **As an Accountant**, I want to **download a monthly AR Aging report in Excel** so that I can include it in the client's financial package.
  - *Gap: AR data visible in UI, no export button.*
- ❌ **As an Accountant**, I want to **download all invoices for a date range as a ZIP of PDFs** so that I have backup documentation for an audit.
  - *Gap: No bulk PDF export.*
- ❌ **As an Accountant**, I want to **see a Revenue Recognition schedule** so that I can properly book deferred revenue.
  - *Gap: `RevenueRecognitionService` exists, UI exists (`revenue-recognition.blade.php`) but may not be fully functional.*

### Access Control
- ❌ **As an Accountant**, I want a **limited "Accountant" role** that can view financials but not modify invoices or payments so that I have read-only access.
  - *Gap: No dedicated accountant role. Must share Finance Admin credentials or use QuickBooks.*
- ❌ **As an Accountant**, I want to **generate a 1099-MISC report** for contractors paid through the system so that I can file taxes.
  - *Gap: No contractor payment tracking or 1099 generation.*

### Reconciliation
- ❌ **As an Accountant**, I want to **see a Payments register** (all payments received, by method, with invoice links) so that I can reconcile to bank statements.
  - *Gap: Payments exist in model, but no dedicated "Payments Register" view.*
- ❌ **As an Accountant**, I want to **see Sales Tax collected by jurisdiction** so that I can file state/local tax returns.
  - *Gap: Tax calculated on invoices, but no summary report by jurisdiction.*

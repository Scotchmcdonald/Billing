# Batch 3A: Finance Admin & Internal UI

**Execution Order:** Third (Depends on Batch 1 & 2)
**Parallelization:** Can run parallel with Batch 3B (Portal UI)
**Estimated Effort:** 4-5 days
**Priority:** P1

---

## Agent Prompt

```
You are a Senior Full-Stack Laravel Engineer specializing in Blade/Livewire UI development.

Your task is to implement Finance Admin-facing UI components for the FinOps billing module. These interfaces are used by MSP internal staff to manage billing operations.

## Primary Objectives
1. Create new Blade views with Alpine.js interactivity
2. Implement controller methods for new routes
3. Follow existing UI patterns and component library
4. Ensure mobile responsiveness using Tailwind CSS

## Technical Standards
- Views in `Modules/Billing/Resources/views/`
- Controllers in `Modules/Billing/Http/Controllers/`
- Use Alpine.js for client-side interactivity (already in use)
- Follow Tailwind CSS utility classes (already configured)
- Use `<x-app-layout>` wrapper for authenticated pages
- Toast notifications via existing flash message system

## UI/UX Guidelines (from UX_STYLE_GUIDE.md)
- Primary actions: `bg-indigo-600 hover:bg-indigo-700`
- Destructive actions: `bg-rose-600`
- Success states: `text-emerald-600`
- Warning states: `text-amber-600`
- Tables: Use existing `divide-y divide-gray-200` pattern
- Modals: Use Alpine.js `x-show` with backdrop

## Files to Reference
- Existing views: `Modules/Billing/Resources/views/finance/`
- Components: `Modules/Billing/Resources/views/components/`
- Routes: `Modules/Billing/Routes/web.php`

## Validation Criteria
- All new routes are accessible and render without errors
- Forms submit correctly and display validation errors
- UI is responsive on mobile (test at 375px width)
- No console JavaScript errors
```

---

## Context & Technical Details

### Existing UI Architecture
```
Modules/Billing/Resources/views/
├── admin/
│   └── dashboard.blade.php
├── finance/
│   ├── dashboard.blade.php       # Main FinOps dashboard
│   ├── pre-flight.blade.php      # Invoice review
│   ├── overrides.blade.php       # Price overrides
│   ├── profitability.blade.php   # Client profitability
│   └── settings.blade.php
├── field/
│   └── work-order.blade.php      # Technician billing
├── quotes/
│   ├── create.blade.php
│   └── show.blade.php
└── components/
    └── (reusable blade components)
```

### Key Existing Patterns
- Dashboard widgets: Cards with `bg-white shadow-sm rounded-lg p-6`
- Data tables: Sortable with pagination
- Modals: Alpine.js controlled with `x-data`, `x-show`
- Forms: Use Laravel's `@csrf`, `@method`, `old()` helpers

---

## Task Checklist

### 3A.1 Invoice Dispute & Credit Note UI

#### Invoice Detail Enhancements
- [ ] Add "Disputed" badge to invoice list and detail views
- [ ] Add "Flag as Disputed" button with reason modal
- [ ] Add "Pause/Resume Dunning" toggle
- [ ] Add "Internal Notes" section (accordion, not visible to client)
- [ ] Add "Activity Timeline" component showing:
  - Status changes with timestamps
  - Dunning emails sent
  - Payments received
  - Credit notes issued

#### Credit Note Management
- [ ] Create view: `finance/credit-notes/index.blade.php`
  - List all credit notes with filters (date, company, status)
  - Columns: CN#, Invoice#, Company, Amount, Reason, Issued By, Date
- [ ] Create view: `finance/credit-notes/create.blade.php`
  - Modal triggered from Invoice detail
  - Fields: Amount, Reason (dropdown + custom), Notes
  - Show invoice balance and validate amount ≤ balance
- [ ] Add route: `GET /billing/finance/credit-notes`
- [ ] Add route: `POST /billing/finance/invoices/{invoice}/credit-note`

### 3A.2 Retainer Management UI

#### Retainer Dashboard
- [ ] Create view: `finance/retainers/index.blade.php`
  - List all active retainers
  - Columns: Company, Hours Purchased, Hours Remaining, Expires, Status
  - Filter: Active, Depleted, Expired, Low Balance
  - Action: "Sell New Retainer" button

#### Sell Retainer Flow
- [ ] Create view: `finance/retainers/create.blade.php`
  - Company selector
  - Hours input with preset buttons (10, 20, 40, 80)
  - Price calculator (based on configured hourly rate)
  - Expiration date (optional)
  
#### Retainer Detail
- [ ] Create view: `finance/retainers/show.blade.php`
  - Current balance with visual progress bar
  - Usage history table (which tickets consumed hours)
  - "Add Hours" button
  - "Adjust Balance" button (with audit note)

#### Company Profile Widget
- [ ] Add "Retainer Balance" card to Company profile view
  - Shows: Hours remaining, Last deduction, Expires date
  - Alert if < 5 hours remaining

### 3A.3 Quote Pipeline Dashboard

#### Kanban View
- [ ] Create view: `quotes/pipeline.blade.php`
  - Columns: Draft, Sent, Viewed, Accepted, Lost
  - Cards show: Company, Total, Days Open, Owner
  - Drag-and-drop to change status (Alpine.js + Sortable.js)
  - Click card to open detail modal

#### Quote Detail Enhancements
- [ ] Add "Viewed" indicator with timestamp
- [ ] Add "Convert to Invoice" button
- [ ] Add "Convert to Invoice + Subscription" button
- [ ] Add "Duplicate Quote" button
- [ ] Add margin display (show % margin next to total)
- [ ] Add margin warning if below floor

### 3A.4 Pre-Flight Review Enhancements

#### Clarity Improvements
- [ ] Split "Approve" into "Approve" and "Approve & Send"
- [ ] Add confirmation modal: "You are about to send X invoices to clients"
- [ ] Add success toast with count: "5 invoices sent successfully"

#### Export Functionality
- [ ] Wire "Export to Excel" button
- [ ] Export columns: Company, Total, Variance %, Anomaly Score, Status
- [ ] Add "Export Approved" vs "Export All" options

#### Audit Trail
- [ ] Add "Approved By" and "Approved At" to invoice detail
- [ ] Add hover tooltip showing approval history

### 3A.5 Executive Dashboard

#### New Dashboard View
- [ ] Create view: `finance/executive.blade.php`
  - Clean, minimal layout (5 KPI cards only)
  - No tables, no operational details

#### KPI Cards
- [ ] MRR Card: Current value, MoM change %, trend sparkline
- [ ] Churn Rate Card: Current %, MoM change, target line
- [ ] Gross Margin Card: Current %, MoM change
- [ ] LTV Card: Current value, trend
- [ ] AR Aging Card: Total > 30 days, breakdown by bucket

#### Comparison Toggle
- [ ] Add "Compare: MoM | YoY" toggle
- [ ] Update all cards when toggled

### 3A.6 Contract Management UI

#### Contracts Expiring Widget
- [ ] Add to main Finance Dashboard
- [ ] Show contracts expiring in 60, 30, 15 days
- [ ] Link to subscription detail

#### Subscription Detail Enhancements
- [ ] Add "Contract" section with:
  - Start date, End date
  - Days remaining badge
  - Upload contract document (PDF)
  - Renewal status selector

#### Contracts Report
- [ ] Create view: `finance/contracts/index.blade.php`
  - List all subscriptions with contract dates
  - Filter: Expiring Soon, Active, Churned
  - Bulk action: "Send Renewal Reminder"

### 3A.7 Audit Log Viewer

#### Audit Log View
- [ ] Create view: `finance/audit-log.blade.php`
  - Filterable log viewer
  - Filters: Entity Type, User, Date Range, Event Type
  - Columns: Timestamp, User, Entity, Event, Changes
  - "Changes" shows diff (old → new)

#### Access from Entity Views
- [ ] Add "View Audit Log" link on Invoice detail
- [ ] Add "View Audit Log" link on Override detail
- [ ] Add "View Audit Log" link on Payment detail

### 3A.8 Bulk Override Manager

#### Enhanced Override List
- [ ] Add checkbox column for bulk selection
- [ ] Add bulk action bar when items selected:
  - "Apply % Increase"
  - "Set Fixed Price"
  - "Delete Selected"

#### Bulk Update Modal
- [ ] Fields: Action type, Value, Effective Date
- [ ] Preview: Show affected overrides and new prices
- [ ] Confirm: "This will update X overrides"

### 3A.9 Client Onboarding Wizard

#### Wizard Component
- [ ] Create view: `finance/onboarding/wizard.blade.php`
- [ ] Step 1: Company Information
  - Company name, Pricing tier, Primary contact
- [ ] Step 2: Billing Configuration
  - Payment terms, Auto-pay preference, Tax settings
- [ ] Step 3: Initial Subscription (optional)
  - Select products, quantities, start date
- [ ] Step 4: First Invoice (optional)
  - Generate draft invoice, Preview
- [ ] Step 5: Summary & Confirm
  - Review all entries, "Complete Setup" button

#### Navigation
- [ ] Add "New Client Setup" button to Company list
- [ ] Progress indicator showing current step

---

## Completion Verification

```bash
# Verify routes exist
php artisan route:list --path=billing

# Test page loads (no 500 errors)
curl -s -o /dev/null -w "%{http_code}" http://localhost/billing/finance/credit-notes
curl -s -o /dev/null -w "%{http_code}" http://localhost/billing/finance/retainers
curl -s -o /dev/null -w "%{http_code}" http://localhost/billing/quotes/pipeline
curl -s -o /dev/null -w "%{http_code}" http://localhost/billing/finance/executive

# Check for JS errors (manual browser test)
# Open each new page and check browser console
```

---

## Downstream Dependencies
- **Batch 5** (Jobs): Notification preferences used by scheduled jobs
- **Batch 6** (Testing): Feature tests require these routes

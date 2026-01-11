# Persona: Technician
**Role:** The front-line IT support engineer who solves client problems. They view billing as a secondary (often annoying) task.

## Primary UI Locations
- **Work Order Panel:** `/billing/field/work-order` ✅
- **Ticket Sidebar:** (Integrated in Helpdesk) 🔶 Partial
- **My Stats Dashboard:** `/billing/technician/stats` ❌ Not Implemented

## User Stories (Implemented)

### Time Tracking
- ✅ **As a Technician**, I want to **toggle "Billable" vs "Non-Billable" on my time entries** so that the client isn't charged for my lunch break.
  - *UI: Work Order Panel toggle | Logic: `BillableEntry.is_billable`*
- ✅ **As a Technician**, I want to **log hours with a start/end time or manual entry** so that I have flexibility in how I track time.
  - *UI: Work Order Panel supports both modes*

### Expense & Material Logging
- ✅ **As a Technician**, I want to **add parts/materials to a ticket from a dropdown** so that they appear on the client's invoice.
  - *UI: Work Order Panel "Add Part" modal with available parts list*
- ✅ **As a Technician**, I want to **add expenses (travel, parking) to a ticket** so that I can get reimbursed and the client is billed.
  - *UI: Work Order Panel "Add Expense" modal*
- ✅ **As a Technician**, I want to **see a live total of my billable work** before submitting so that I can sanity-check it.
  - *UI: Work Order Panel running total*

## Workflows

### Ticket Resolution & Billing
```mermaid
graph LR
    A[Ticket Assigned] --> B[Start Work]
    B --> C{Billable?}
    C -- Yes --> D[Log Time & Parts]
    C -- No --> E[Log Internal Note]
    D --> F[Submit Resolution]
    E --> F
    F --> G[Client Rating (Optional)]
    G --> H[Billing Review / Invoice]
```

## Problems Solved
1.  **Lost Revenue:** Captures parts, expenses, and time directly on the ticket.
2.  **Billing Disputes:** Provides detailed logs that flow directly to the invoice.

---

---

## 📋 Phase 14: Technician Efficiency & Context Awareness
**Priority:** MEDIUM-LOW | **Estimated Effort:** 24-32 hours | **Pattern:** State-Aware + Contextual Indicators

### Phase Overview
This phase enhances the technician experience with contextual awareness features that help them make better decisions in the field. Implements real-time indicators, efficiency tracking, and workflow optimizations.

### User Stories for Phase 14 Implementation

#### Story 14.1: Client AR Status Indicator
**As a Technician**, I want to **see a warning if a client is "Past Due"** so that I don't perform non-critical work for a delinquent client.

**Implementation Details:**
*   **Location:** Work Order Panel header and Ticket view
*   **Component:** `x-ar-status-badge`
*   **Visual Treatment:**
    *   **Current:** Green badge "Paid Up"
    *   **1-30 Days:** Yellow badge "Invoice Due"
    *   **31-60 Days:** Orange badge "Past Due - Contact Finance"
    *   **60+ Days:** Red badge "PAYMENT REQUIRED - Billable Work Only"
*   **Data Source:**
    *   Real-time AR aging calculation
    *   Cached per company (5-minute TTL)
*   **Tooltip Content:**
    *   Days overdue
    *   Total amount outstanding
    *   Action guidance ("Continue with billable work" vs "Verify with Finance before proceeding")
*   **Permissions:**
    *   Visible to technicians with `billing.view_ar_status` permission
    *   Configurable per company (some may want to hide from techs)
*   **Mobile Optimization:**
    *   Prominent placement on mobile work order view
    *   Swipe-to-reveal additional AR details

#### Story 14.2: Contract Coverage Lookup
**As a Technician**, I want to **know if a specific service is covered by the client's contract** so that I don't accidentally bill them for something included in their AYCE.

**Implementation Details:**
*   **Location:** Work Order Panel → "Add Time" modal
*   **Component:** `x-contract-coverage-indicator`
*   **Logic:**
    *   Match service category against active subscriptions
    *   Check if service falls under AYCE/managed services
    *   Display coverage status before time entry
*   **Visual States:**
    *   ✅ **Covered:** Green checkmark "Included in [Subscription Name]"
    *   ⚠️ **Partially Covered:** Yellow warning "May be billable - verify contract"
    *   ❌ **Not Covered:** Red indicator "Billable Service - log as billable"
    *   ❓ **Unknown:** Gray indicator "No active contract"
*   **Service:** `ContractCoverageService` (new)
    *   Methods: `checkCoverage(client_id, service_type)`, `getActiveContracts(client_id)`
*   **Integration:**
    *   Links to subscription details
    *   Suggests correct billability setting
*   **Rules Engine:**
    *   Configurable coverage rules per subscription type
    *   Support for inclusion/exclusion lists
    *   Hour bucket tracking for limited-hour contracts

#### Story 14.3: My Utilization Dashboard
**As a Technician**, I want to **see how many billable hours I've logged today/this week** so that I know if I'm meeting my utilization targets.

**Implementation Details:**
*   **Route:** `/billing/technician/my-stats`
*   **UX Pattern:** Control Tower (personal metrics)
*   **Components:**
    *   `x-utilization-gauge` showing percentage of target
    *   `x-daily-hours-chart` bar chart for week view
    *   `x-efficiency-score` composite metric
*   **Key Metrics:**
    *   Billable Hours (today/this week/this month)
    *   Non-Billable Hours breakdown
    *   Utilization Rate (billable / total)
    *   Average ticket resolution time
    *   Revenue generated (if permission granted)
*   **Target Indicators:**
    *   Company-wide target (e.g., 70% billable)
    *   Personal target (if set)
    *   On-track/behind indicator
*   **Gamification Elements:**
    *   Streak counter (days hitting target)
    *   Personal best badges
    *   Anonymous peer comparison (quartile ranking)
*   **Mobile First:**
    *   Quick-glance widget for mobile
    *   Progressive disclosure of details
    *   Swipe between metrics

#### Story 14.4: Daily Timesheet View
**As a Technician**, I want a **"Daily Timesheet" view** where I can see all my tickets and log time inline, instead of opening each ticket.

**Implementation Details:**
*   **Route:** `/billing/technician/timesheet`
*   **UX Pattern:** High-density table with inline editing
*   **Components:**
    *   `x-inline-time-entry` editable table cells
    *   `x-quick-timer` start/stop controls per ticket
    *   `x-bulk-submit` button for end-of-day submission
*   **Table Columns:**
    *   Ticket Number (linked)
    *   Client Name
    *   Brief Description
    *   Time Logged Today
    *   Quick Timer (Start/Stop)
    *   Billable Toggle
    *   Notes (inline edit)
*   **Workflow:**
    *   Shows all tickets assigned to tech
    *   Filters: Active, Closed Today, All
    *   Inline time entry without modal
    *   Real-time save on blur
    *   Running total at bottom
*   **Keyboard Shortcuts:**
    *   Tab navigation between fields
    *   Enter to save and move to next row
    *   Space to toggle billable
*   **Mobile Adaptation:**
    *   Card-based layout on small screens
    *   Swipe actions for quick entry

#### Story 14.5: Barcode Scanning for Hardware
**As a Technician**, I want to **scan a barcode on a piece of hardware** to add it to the ticket so that I don't have to type serial numbers.

**Implementation Details:**
*   **Location:** Work Order Panel → "Add Part" modal
*   **Component:** `x-barcode-scanner`
*   **Technology:**
    *   HTML5 camera API for mobile scanning
    *   QuaggaJS or ZXing for barcode recognition
    *   Support for QR codes, Code128, EAN
*   **Workflow:**
    1.  Click "Scan Barcode" button
    2.  Camera viewfinder appears
    3.  Frame barcode in guide box
    4.  Auto-capture and decode
    5.  Match against inventory/product catalog
    6.  Pre-fill part details
*   **Fallback:**
    *   Manual entry field if camera unavailable
    *   Photo upload for non-standard barcodes
    *   Type-ahead search for product name
*   **Integration:**
    *   Link to inventory system (if available)
    *   Create inventory item if not found (with approval)
    *   Track serial numbers for warranty
*   **Permissions:**
    *   Requires `billing.manage_parts` permission
    *   Audit log of scanned items

#### Story 14.6: Real-Time Inventory Levels
**As a Technician**, I want to **see real-time stock levels** when adding a part so that I don't promise something we don't have.

**Implementation Details:**
*   **Location:** Work Order Panel → "Add Part" dropdown/search
*   **Display:**
    *   Stock quantity next to part name
    *   Color-coded indicators:
        *   Green: >10 in stock
        *   Yellow: 3-10 in stock
        *   Red: 1-2 in stock (last units)
        *   Gray: Out of stock
*   **Features:**
    *   Real-time updates (WebSocket or polling)
    *   "Reserved" quantity shown (in other open tickets)
    *   ETA for restock (if integration available)
    *   Alternative part suggestions if out of stock
*   **Integration:**
    *   Connect to `Inventory` module (if installed)
    *   Fall back to manual stock tracking
*   **Actions:**
    *   "Order More" link to procurement
    *   "Notify When Available" subscription
    *   Use alternate part suggestion

---

### Phase 14 Implementation Checklist

#### Backend Tasks
- [ ] Create `ContractCoverageService` with coverage rules engine
- [ ] Add AR status calculation to company model
- [ ] Create `TechnicianUtilizationService` for metrics aggregation
- [ ] Add barcode decoding library (QuaggaJS or equivalent)
- [ ] Create inventory level API endpoint with real-time updates
- [ ] Add `billing.view_ar_status` permission
- [ ] Add `billing.manage_parts` permission
- [ ] Cache AR status and utilization metrics (5-min TTL)
- [ ] Create WebSocket channel for inventory updates (optional)

#### Frontend Tasks
- [ ] Create `x-ar-status-badge` component
- [ ] Create `x-contract-coverage-indicator` component
- [ ] Create `/billing/technician/my-stats.blade.php` dashboard
- [ ] Create `x-utilization-gauge` component
- [ ] Create `x-daily-hours-chart` component
- [ ] Create `/billing/technician/timesheet.blade.php` view
- [ ] Create `x-inline-time-entry` component
- [ ] Create `x-quick-timer` component
- [ ] Create `x-barcode-scanner` component with camera integration
- [ ] Add inventory level indicators to part selection
- [ ] Implement keyboard shortcuts for timesheet navigation
- [ ] Apply semantic color classes throughout
- [ ] Optimize all views for mobile-first

#### Testing Tasks
- [ ] Test AR status accuracy across aging buckets
- [ ] Test contract coverage logic with various subscription types
- [ ] Test utilization calculations with edge cases
- [ ] Test timesheet inline editing and bulk submission
- [ ] Test barcode scanning with various barcode formats
- [ ] Test inventory level updates in real-time
- [ ] Test mobile camera integration across devices
- [ ] Accessibility audit for all technician views
- [ ] Performance test for timesheet with 100+ tickets

#### Documentation Tasks
- [ ] Document AR status indicator logic and thresholds
- [ ] Document contract coverage rules configuration
- [ ] Document utilization target setting and gamification
- [ ] Document timesheet workflow and keyboard shortcuts
- [ ] Document barcode scanning setup and supported formats
- [ ] Document inventory integration requirements
- [ ] Create troubleshooting guide for camera issues

---

### Success Metrics for Phase 14
*   **Adoption:** 85%+ of technicians use timesheet view daily
*   **Efficiency:** 40% reduction in time entry overhead
*   **Accuracy:** 30% reduction in incorrect billability classifications
*   **AR Awareness:** 90% of techs aware of client payment status before starting work

---

### Dependencies
*   **Services:** Existing `AnalyticsService`, `BillableEntry` model
*   **Modules:** Optional integration with `Inventory` module
*   **Libraries:** QuaggaJS or ZXing for barcode scanning
*   **APIs:** HTML5 Camera API, optional WebSocket for real-time updates

---

### Risk Mitigation
*   **Camera Permissions:** Clear instructions for enabling camera access
*   **Inventory Integration:** Graceful degradation if Inventory module not installed
*   **Performance:** Cache utilization metrics to avoid heavy calculations on each view
*   **Privacy:** Ensure AR status visibility aligns with company policy
*   **Mobile Support:** Test barcode scanning across wide range of devices

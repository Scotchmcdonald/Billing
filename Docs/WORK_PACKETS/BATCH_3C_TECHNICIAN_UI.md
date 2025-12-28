# Batch 3C: Technician & Field UI

**Execution Order:** Third (Depends on Batch 1 & 2)
**Parallelization:** Can run parallel with Batch 3A and 3B
**Estimated Effort:** 2-3 days
**Priority:** P2

---

## Agent Prompt

```
You are a Senior Full-Stack Laravel Engineer specializing in mobile-first field technician interfaces.

Your task is to implement Technician-facing UI enhancements for the FinOps billing module. These interfaces are used by field technicians to log time, expenses, and parts while on-site with clients.

## Primary Objectives
1. Create efficient, low-friction time entry interfaces
2. Optimize for mobile devices (technicians use phones/tablets)
3. Provide context awareness (client status, contract coverage)
4. Reduce clicks needed to complete common tasks

## Technical Standards
- Views in `Modules/Billing/Resources/views/field/`
- Mobile-first design (start with 375px width)
- Large touch targets (minimum 44x44px)
- Minimize typing (use dropdowns, toggles, presets)
- Fast load times (lazy load non-critical elements)

## UX Principles for Field Tech
- Speed over aesthetics
- One-handed operation where possible
- Clear visual feedback for actions
- Offline tolerance (queue actions if needed)

## Files to Reference
- Existing field UI: `Modules/Billing/Resources/views/field/work-order.blade.php`
- User stories: `Modules/Billing/Docs/User Stories/PERSONA_TECHNICIAN.md`

## Validation Criteria
- All interfaces work on mobile Safari and Chrome
- Time entry can be completed in < 30 seconds
- No horizontal scrolling on mobile
- Touch targets meet 44px minimum
```

---

## Context & Technical Details

### Existing Field UI
```
Modules/Billing/Resources/views/field/
└── work-order.blade.php   # Billing panel for tickets
```

### Work Order Panel Features (Existing)
- Toggle billable/non-billable
- Manual hours entry
- Add expenses
- Add parts from list
- Running total display

---

## Task Checklist

### 3C.1 Daily Timesheet View

#### New View
- [ ] Create view: `field/timesheet.blade.php`
- [ ] Route: `GET /billing/technician/timesheet`

#### Layout
- [ ] Date selector at top (default: today)
- [ ] List of tickets assigned to tech for selected date
- [ ] Each ticket row shows:
  - Ticket # and subject
  - Client name
  - Current logged hours
  - Billable toggle
- [ ] Inline time entry (tap to expand)

#### Time Entry Inline
- [ ] Quick presets: 0.25, 0.5, 1, 2, 4 hours
- [ ] Or manual entry with number pad
- [ ] "Start Timer" button per ticket
- [ ] Auto-save on blur/change

#### Daily Summary
- [ ] Sticky footer showing:
  - Total hours logged
  - Billable hours
  - "Submit Day" button

### 3C.2 My Stats Dashboard

#### New View
- [ ] Create view: `field/stats.blade.php`
- [ ] Route: `GET /billing/technician/stats`

#### Metrics Cards
- [ ] Billable Hours Today (vs target)
- [ ] Billable Hours This Week
- [ ] Billable Hours This Month
- [ ] Utilization % (billable / total)
- [ ] Revenue Generated (this month)

#### Visual Elements
- [ ] Progress rings for utilization
- [ ] Comparison to team average
- [ ] Trend sparklines (last 30 days)

### 3C.3 Context Awareness Badges

#### Client AR Warning
- [ ] Show "PAST DUE" badge if client AR > 60 days
- [ ] Display on:
  - Work Order panel
  - Timesheet ticket rows
  - Ticket detail header
- [ ] Badge color: `bg-rose-500 text-white`
- [ ] Tooltip: "Client has overdue invoices"

#### Contract Coverage Indicator
- [ ] Query client's active subscriptions
- [ ] Check if service type is covered
- [ ] Display:
  - "INCLUDED" badge (green) - covered by AYCE
  - "BILLABLE" badge (blue) - not covered
  - "RETAINER" badge (yellow) - has retainer balance
- [ ] Show on Work Order panel header

### 3C.4 Invoice Status Visibility

#### BillableEntry List
- [ ] Add "Invoice Status" column
- [ ] Statuses:
  - "Pending" - not yet invoiced
  - "Invoiced" - linked to invoice (show INV#)
  - "N/A" - non-billable entries
- [ ] Filter: "Show only unbilled"

#### My Unbilled Time View
- [ ] Create view: `field/unbilled.blade.php`
- [ ] Route: `GET /billing/technician/unbilled`
- [ ] List all billable entries not yet invoiced
- [ ] Group by client
- [ ] Total hours and estimated value

### 3C.5 Expense Receipt Upload

#### Expense Modal Enhancement
- [ ] Add "Upload Receipt" section
- [ ] Camera capture button (mobile)
- [ ] File upload fallback (desktop)
- [ ] Preview thumbnail after upload
- [ ] Max file size: 5MB
- [ ] Accepted types: jpg, png, pdf

#### Storage
- [ ] Store in: `storage/app/receipts/{billable_entry_id}/`
- [ ] Update `BillableEntry.receipt_path`
- [ ] Compression/resize for large images

### 3C.6 Parts Inventory Awareness

#### Add Part Modal Enhancement
- [ ] Show stock level next to each part
- [ ] Color coding:
  - Green: In stock (> 5)
  - Yellow: Low stock (1-5)
  - Red: Out of stock (0)
- [ ] Warning modal if selecting out-of-stock item
- [ ] Option to "Add anyway (backordered)"

#### Barcode Scanner
- [ ] Add camera icon button
- [ ] Use QuaggaJS or similar for barcode scanning
- [ ] Scan barcode → lookup part → auto-fill
- [ ] Fallback to manual entry

### 3C.7 Quick Actions Widget

#### Home Screen Widget Concept
- [ ] Create compact widget view
- [ ] Actions:
  - "Start Timer" (opens ticket selector)
  - "Log Quick Time" (preset client + time)
  - "My Tickets Today"
- [ ] Consider PWA Add to Home Screen

#### Recent Tickets
- [ ] Show last 5 tickets worked on
- [ ] One-tap to open Work Order panel
- [ ] Cache locally for fast access

### 3C.8 Mobile Responsiveness Audit

#### Work Order Panel
- [ ] Test at 375px width
- [ ] Ensure touch targets ≥ 44px
- [ ] Stack layout for narrow screens
- [ ] Collapsible sections

#### Timesheet
- [ ] Horizontal scroll for wide tables
- [ ] Sticky date selector
- [ ] Swipe gestures for date navigation

#### Stats Dashboard
- [ ] Single column layout on mobile
- [ ] Full-width cards
- [ ] Touch-friendly date range selector

---

## Completion Verification

```bash
# Test on mobile device or emulator
# 1. Open Chrome DevTools
# 2. Toggle device toolbar (Ctrl+Shift+M)
# 3. Select iPhone 12 Pro (390px)
# 4. Navigate through all technician views

# Test timesheet flow
# 1. Log time on 3 tickets
# 2. Toggle billable status
# 3. Submit day
# 4. Verify entries saved

# Test expense upload
# 1. Add expense
# 2. Upload photo
# 3. Verify file stored
# 4. Verify thumbnail displays
```

---

## Downstream Dependencies
- **Batch 5** (Jobs): Timer data feeds into billing run
- **Batch 6** (Testing): Mobile browser tests

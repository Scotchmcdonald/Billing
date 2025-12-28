# Batch 3B: Client Portal & Public UI

**Execution Order:** Third (Depends on Batch 1 & 2)
**Parallelization:** Can run parallel with Batch 3A (Finance UI)
**Estimated Effort:** 3-4 days
**Priority:** P1

---

## Agent Prompt

```
You are a Senior Full-Stack Laravel Engineer specializing in client-facing portal development.

Your task is to implement Client Portal UI enhancements for the FinOps billing module. These interfaces are used by MSP clients to view invoices, make payments, and manage their accounts.

## Primary Objectives
1. Enhance existing portal views with new features
2. Implement public quote acceptance flow
3. Ensure excellent UX for non-technical users
4. Maintain security (clients should only see their own data)

## Technical Standards
- Views in `Modules/Billing/Resources/views/portal/`
- Public views in `Modules/Billing/Resources/views/public/`
- Use `<x-app-layout>` for authenticated portal pages
- Use `<x-guest-layout>` for public pages (quote acceptance)
- Always scope queries to authenticated company
- Client-friendly language (not technical jargon)

## Security Requirements
- All portal routes must verify company ownership
- Use policy checks: `$this->authorize('view', $invoice)`
- Sanitize all user inputs
- No internal data exposed (margin, cost, internal notes)

## Files to Reference
- Existing portal: `Modules/Billing/Resources/views/portal/`
- Public views: `Modules/Billing/Resources/views/public/`
- Portal controller: `Modules/Billing/Http/Controllers/PortalController.php`
- User stories: `Modules/Billing/Docs/User Stories/PERSONA_CLIENT_ADMIN.md`

## Validation Criteria
- All portal features work when logged in as client user
- Public quote pages work without authentication
- No access to other companies' data
- Mobile responsive (test at 375px width)
```

---

## Context & Technical Details

### Existing Portal Architecture
```
Modules/Billing/Resources/views/portal/
├── dashboard.blade.php        # Main client dashboard with tabs
├── payment_methods.blade.php  # Saved cards/ACH
├── company_selector.blade.php # Multi-company selection
├── team.blade.php            # Team member management
└── no_company.blade.php      # Fallback when no company linked

Modules/Billing/Resources/views/public/
├── quote-builder.blade.php   # Public pricing calculator
└── quote-show.blade.php      # Public quote view
```

### Portal Dashboard Tabs (Existing)
- My Services (subscriptions)
- Invoices (history)
- Payments (history)
- Payment Methods (cards/ACH)

---

## Task Checklist

### 3B.1 Invoice PDF Download

#### Portal Invoice List
- [ ] Add "Download PDF" icon button on each invoice row
- [ ] Route: `GET /portal/{company}/invoices/{invoice}/pdf`
- [ ] Generate PDF using existing `InvoiceGenerator` service
- [ ] Stream PDF directly (no temp files)

#### Invoice Detail Modal
- [ ] Add "Download PDF" button in modal footer
- [ ] Same endpoint, different trigger location

### 3B.2 Auto-Pay Toggle

#### Payment Methods Tab Enhancement
- [ ] Add "Auto-Pay Settings" section above saved cards
- [ ] Toggle: "Enable automatic payments"
- [ ] Select default payment method for auto-pay
- [ ] Display next auto-pay date if enabled
- [ ] Route: `POST /portal/{company}/settings/auto-pay`

#### Confirmation Flow
- [ ] Show confirmation modal when enabling
- [ ] Explain: "Your default payment method will be charged automatically"
- [ ] Show success toast on save

### 3B.3 Quote Acceptance (Public)

#### Enhanced Quote View
- [ ] Update `public/quote-show.blade.php`
- [ ] Add "Accept This Quote" button (prominent, green)
- [ ] Show quote validity date with countdown if < 7 days

#### Acceptance Modal
- [ ] Fields: Signer Name, Signer Email, Signature Pad
- [ ] Use signature pad library (e.g., signature_pad.js)
- [ ] Terms checkbox: "I agree to the terms..."
- [ ] Submit: `POST /quotes/{uuid}/accept`

#### Post-Acceptance
- [ ] Show success page with:
  - "Thank you! Your quote has been accepted"
  - Summary of what happens next
  - Contact information
- [ ] Trigger `QuoteAccepted` event (for Sales notification)

### 3B.4 Retainer Balance Display

#### Portal Dashboard Widget
- [ ] Add "Pre-Paid Hours" card to dashboard (if retainer exists)
- [ ] Display:
  - Hours remaining (large number)
  - Hours purchased
  - Expiration date (if set)
  - Visual progress bar
- [ ] Link to usage detail

#### Hour Usage Report
- [ ] Create view: `portal/retainer-usage.blade.php`
- [ ] Table: Date, Ticket #, Description, Hours Used, Technician
- [ ] Filter by date range
- [ ] Route: `GET /portal/{company}/retainer/usage`

### 3B.5 Invoice Line Item Transparency

#### Enhanced Invoice Detail
- [ ] Expand line items to show source:
  - For time entries: Ticket #, Technician name
  - For subscriptions: Service name, period
  - For expenses: Category, description
- [ ] Group by type (Recurring, Time & Materials, Expenses)

#### Ticket Link (if accessible)
- [ ] If client has ticket portal access, link ticket # to ticket detail
- [ ] Otherwise show ticket # as plain text

### 3B.6 Dispute Submission

#### Invoice Detail Enhancement
- [ ] Add "Question This Charge?" link on invoice detail
- [ ] Opens modal with:
  - Line item selector (which charge?)
  - Reason dropdown: "Incorrect quantity", "Service not received", "Other"
  - Description textarea
  - Submit button

#### Dispute Submission
- [ ] Route: `POST /portal/{company}/invoices/{invoice}/dispute`
- [ ] Creates dispute record (internal)
- [ ] Sends notification to Finance Admin
- [ ] Shows confirmation: "We've received your inquiry and will respond within 2 business days"

### 3B.7 Company Profile Management

#### Profile Section
- [ ] Add "Company Profile" tab to portal dashboard
- [ ] Editable fields:
  - Company name (display only, contact us to change)
  - Billing address (editable)
  - Billing email (editable)
  - Phone number (editable)
- [ ] Route: `PUT /portal/{company}/profile`

#### Address Validation
- [ ] Basic validation (required fields)
- [ ] Optional: Address autocomplete (Google Places API)

### 3B.8 Order Status Tracking

#### Orders Section
- [ ] Add "Orders" tab to portal dashboard (if hardware orders exist)
- [ ] List: Order #, Date, Items, Status, Tracking
- [ ] Status badges: Ordered, Processing, Shipped, Delivered
- [ ] Tracking link (if available)

#### Order Detail
- [ ] Modal showing:
  - Items ordered (from quote)
  - Timeline of status changes
  - Tracking number with carrier link
  - Estimated delivery date

### 3B.9 Portal Team Management (Client-Side)

#### Team Tab Enhancement
- [ ] Allow client admin to add portal users from their company
- [ ] Fields: Name, Email, Role (Admin, Viewer)
- [ ] Send invitation email to new user
- [ ] Route: `POST /portal/{company}/team/invite`

#### User Roles
- [ ] Admin: Can manage payment methods, accept quotes
- [ ] Viewer: Can only view invoices and services

### 3B.10 Self-Service Actions

#### Request Service Change
- [ ] Add "Request Change" button on subscription card
- [ ] Form: Change type (Upgrade, Downgrade, Cancel), Details, Preferred date
- [ ] Submit creates internal task/notification
- [ ] Confirmation: "Your request has been submitted"

#### Schedule a Call
- [ ] Add "Schedule a Call" link in portal header
- [ ] Options:
  - Calendly embed (if configured)
  - Simple form: Preferred date/time, Reason
- [ ] Route: `POST /portal/{company}/schedule-call`

---

## Completion Verification

```bash
# Test as client user
# 1. Log in as a user linked to a company
# 2. Navigate through all portal tabs
# 3. Test PDF download
# 4. Test auto-pay toggle
# 5. Test dispute submission

# Test public quote acceptance
# 1. Get a quote UUID from database
# 2. Visit /quotes/{uuid}
# 3. Complete acceptance flow
# 4. Verify quote status changed

# Security check
# 1. Try accessing another company's invoice
# 2. Should get 403 Forbidden
```

---

## Downstream Dependencies
- **Batch 5** (Jobs): Quote acceptance triggers notification jobs
- **Batch 6** (Testing): Browser tests for portal flows

# Persona: Client Admin
**Role:** The point of contact at the client company (e.g., Office Manager, CEO) who approves purchases and pays the bills.

## Primary UI Locations
- **Client Portal Dashboard:** `/portal/{company_id}/dashboard` ✅
- **Invoice History:** (Tab in Dashboard) ✅
- **Payment Methods:** `/portal/{company_id}/payment-methods` ✅
- **Quote Approval:** `/portal/quotes/{id}` ✅

## User Stories (Implemented)

### Invoice Management
- ✅ **As a Client Admin**, I want to **see all my invoices in one place** (Paid, Open, Overdue) so that I have a clear financial history.
  - *UI: Portal Dashboard "Invoices" Tab*
- ✅ **As a Client Admin**, I want to **see a breakdown of line items on an invoice** so that I can verify the charges.
  - *UI: Portal Invoice Detail Modal*

### Payments
- ✅ **As a Client Admin**, I want to **pay an invoice securely using a Credit Card or ACH** so that I can settle the debt instantly.
  - *UI: Portal "Pay Now" Modal with Stripe integration*
- ✅ **As a Client Admin**, I want to **see the processing fee upfront** before I pay so that there are no surprises.
  - *UI: Portal Pay Modal shows calculated fee*
- ✅ **As a Client Admin**, I want to **manage my saved payment methods** so that I can update an expired card.
  - *UI: Portal "Payment Methods" Tab*

### Services & Subscriptions
- ✅ **As a Client Admin**, I want to **see a list of my active services** so that I know what I'm paying for.
  - *UI: Portal Dashboard "My Services" Tab*

### Purchasing
- ✅ **As a Client Admin**, I want to **view a quote** sent by my MSP so that I can see the proposed pricing.
  - *UI: Public Quote View (`/quotes/{uuid}`)*

## Problems Solved
1.  **Payment Friction:** One-click pay with saved card.
2.  **Trust & Transparency:** Full visibility into charges and payment history.
3.  **Self-Service:** Reduces "Can you send me that invoice?" emails.

---

---

## 📋 Phase 15: Client Portal Self-Service Enhancements
**Priority:** HIGH | **Estimated Effort:** 28-36 hours | **Pattern:** Guided Journey + Resilient Design

### Phase Overview
This phase transforms the client portal into a comprehensive self-service platform, reducing support burden and improving client satisfaction. Implements intuitive wizards, transparency features, and procurement tracking.

### User Stories for Phase 15 Implementation

#### Story 15.1: Invoice PDF Download
**As a Client Admin**, I want to **download a PDF copy of an invoice** so that I can file it with my accountant.

**Implementation Details:**
*   **Location:** Portal Invoice Detail modal and list view
*   **Button:** "Download PDF" with loading state
*   **PDF Template:**
    *   Professional branded design
    *   Company logo and branding
    *   Complete line item breakdown
    *   Payment instructions
    *   Tax details
*   **Data Source:** Existing PDF generation service (Phase 9)
*   **File Naming:** `Invoice_[Number]_[ClientName]_[Date].pdf`
*   **Features:**
    *   Instant generation (< 2 seconds)
    *   Open in new tab or download
    *   Watermark for unpaid invoices ("COPY FOR YOUR RECORDS")
*   **Audit:**
    *   Log download action in `BillingAuditLog`
    *   Track which invoices are most frequently downloaded

#### Story 15.2: Auto-Pay Configuration
**As a Client Admin**, I want to **set up Auto-Pay** so that I don't have to remember to log in every month.

**Implementation Details:**
*   **Route:** `/portal/{company_id}/settings/auto-pay`
*   **UX Pattern:** Guided Journey (3-step wizard)
*   **Wizard Steps:**
    1.  **Payment Method:** Select saved payment method or add new
    2.  **Schedule:** Choose billing cycle, grace period, retry logic
    3.  **Confirmation:** Review settings, enable with confirmation
*   **Components:**
    *   `x-auto-pay-wizard` multi-step form
    *   `x-payment-method-selector` with card/ACH options
    *   `x-auto-pay-schedule` calendar picker
    *   `x-safety-net-options` for failure handling
*   **Database:**
    *   Add `auto_pay_enabled` boolean to `companies` table
    *   Add `auto_pay_method_id` foreign key
    *   Add `auto_pay_retry_attempts` integer
    *   Add `auto_pay_grace_days` integer
*   **Logic:**
    *   Job: `ProcessAutopayInvoicesJob` (runs daily)
    *   Retry failed payments (3 attempts, 3 days apart)
    *   Email notification before charging
    *   Email receipt after successful payment
    *   Email alert if payment fails
*   **Safety Features:**
    *   Require email confirmation to enable
    *   Ability to pause temporarily
    *   Ability to disable anytime
    *   Preview of next charge date and amount

#### Story 15.3: Self-Service Profile Editing
**As a Client Admin**, I want to **update my company's billing address** so that invoices are sent to the correct location.

**Implementation Details:**
*   **Route:** `/portal/{company_id}/settings/profile`
*   **Editable Fields:**
    *   Company Name
    *   Billing Address (street, city, state, zip)
    *   Shipping Address (if different)
    *   Billing Contact Name
    *   Billing Contact Email
    *   Billing Contact Phone
    *   Tax ID / EIN (optional)
    *   Industry/Sector (for benchmarking)
*   **Validation:**
    *   Address verification via API (SmartyStreets or similar)
    *   Email format validation
    *   Phone format validation
    *   Tax ID format validation
*   **Approval Workflow:**
    *   Some fields update immediately (phone, email)
    *   Critical fields require Finance Admin approval (company name, tax ID)
    *   Pending changes indicator
*   **Audit Trail:**
    *   Log all profile changes
    *   Track who made change and when
    *   Finance Admin notification for pending approvals
*   **UX:**
    *   Inline editing with save/cancel buttons
    *   Real-time validation feedback
    *   Success confirmation toast

#### Story 15.4: Team Member Management
**As a Client Admin**, I want to **add additional users from my company** to the portal so that my accountant can also view invoices.

**Implementation Details:**
*   **Route:** `/portal/{company_id}/settings/team`
*   **UX Pattern:** List view with add/edit/remove actions
*   **Components:**
    *   `x-team-member-list` table
    *   `x-invite-user-modal` form
    *   `x-user-role-selector` dropdown
*   **Portal Roles:**
    *   **Admin:** Full access (payments, settings, team management)
    *   **Billing:** View invoices, make payments (no settings)
    *   **Viewer:** Read-only access to invoices and services
*   **Invitation Flow:**
    1.  Client Admin enters email and role
    2.  System sends invitation email with magic link
    3.  Invitee creates password (if new user)
    4.  Access granted immediately
*   **Management:**
    *   Edit user role
    *   Disable user access (soft delete)
    *   Resend invitation
    *   View last login date
*   **Limits:**
    *   Max users per company (configurable, default: 10)
    *   Warning when approaching limit
    *   Upgrade prompt if limit reached
*   **Security:**
    *   Email domain verification (must match company domain)
    *   Admin approval for external domains
    *   Audit log of all team changes

#### Story 15.5: Procurement Tracking
**As a Client Admin**, I want to **see the status of my hardware order** (Ordered, Shipped, Delivered) so that I know when to expect delivery.

**Implementation Details:**
*   **Route:** `/portal/{company_id}/orders`
*   **UX Pattern:** Control Tower (order tracking dashboard)
*   **Components:**
    *   `x-order-timeline` stepper showing progress
    *   `x-order-card` summary cards
    *   `x-tracking-link` external carrier links
*   **Order Statuses:**
    *   Quote Accepted
    *   Order Placed
    *   Processing
    *   Shipped (with tracking)
    *   Delivered
    *   Installed (if applicable)
*   **Timeline Events:**
    *   Status changes with timestamps
    *   Notes from MSP
    *   Tracking numbers
    *   Expected delivery date
    *   Proof of delivery (signature)
*   **Notifications:**
    *   Email when order ships
    *   Email with tracking number
    *   SMS option for delivery alerts
*   **Database:**
    *   Create `procurement_orders` table
    *   Fields: id, company_id, quote_id, status, tracking_number, carrier, ship_date, delivery_date
    *   Relationship: One order, many line items
*   **Integration:**
    *   Link to quote/invoice
    *   Carrier tracking API (FedEx, UPS, USPS)
    *   Automated status updates via webhooks

#### Story 15.6: Invoice Line Item Transparency
**As a Client Admin**, I want to **see "Who called support?" on the invoice** (breakdown by user/ticket) so that I can verify charges are legitimate.

**Implementation Details:**
*   **Location:** Portal Invoice Detail modal
*   **Enhancement:** Expand line item details
*   **Display Format:**
    *   Time Entry Line:
        *   Ticket #12345 - "Network troubleshooting"
        *   Technician: John Smith
        *   Date: 2024-01-15
        *   Requested by: jane.doe@client.com
        *   Hours: 2.5 @ $150/hr
        *   Total: $375.00
*   **Components:**
    *   `x-line-item-detail-expander` accordion
    *   `x-ticket-attribution` linking to ticket details
    *   `x-user-attribution` showing requester
*   **Data Requirements:**
    *   Link `invoice_line_items` to `billable_entries`
    *   Link `billable_entries` to `tickets`
    *   Track ticket requester (user who opened ticket)
*   **Privacy:**
    *   Configurable: Show/hide user names
    *   Show initials only option
    *   Aggregate by department option
*   **Actions:**
    *   Expand/collapse details
    *   "Question this charge" link (opens dispute)
    *   Export line items to CSV

#### Story 15.7: Dispute Submission Enhancement
**As a Client Admin**, I want to **dispute a line item** directly from the portal so that I don't have to send an email.

**Implementation Details:**
*   **Location:** Portal Invoice Detail → Line item actions
*   **Button:** "Question This Charge" per line item
*   **UX Pattern:** Guided Journey (modal wizard)
*   **Wizard Steps:**
    1.  **Issue:** Select dispute reason (dropdown)
    2.  **Details:** Describe the issue (textarea)
    3.  **Evidence:** Upload supporting documents (multi-file)
    4.  **Confirmation:** Review and submit
*   **Components:**
    *   `x-dispute-wizard` modal
    *   `x-file-uploader` with drag-and-drop
    *   `x-dispute-timeline` showing status updates
*   **Dispute Reasons:**
    *   Service not provided
    *   Duplicate charge
    *   Incorrect pricing
    *   Already covered by contract
    *   Poor quality/incomplete work
    *   Other (requires explanation)
*   **Workflow:**
    *   Submission creates `Dispute` record
    *   Pauses dunning for disputed line item (or full invoice)
    *   Notifies Finance Admin immediately
    *   Finance Admin investigates and responds
    *   Client receives response in portal
    *   Dispute resolved or escalated
*   **Status Tracking:**
    *   Submitted
    *   Under Review
    *   Information Requested
    *   Resolved - Credit Issued
    *   Resolved - Charge Upheld
    *   Escalated to Management
*   **Existing:** Phase 3 implemented core dispute functionality
*   **Enhancement:** Improve UX, add per-line-item disputes

---

### Phase 15 Implementation Checklist

#### Backend Tasks
- [ ] Add auto-pay fields to `companies` table
- [ ] Create `ProcessAutopayInvoicesJob` for scheduled payments
- [ ] Create profile approval workflow for critical field changes
- [ ] Create `procurement_orders` table and model
- [ ] Add team member invitation and role management logic
- [ ] Add email domain verification for team invites
- [ ] Enhance invoice line items with ticket/user attribution
- [ ] Add carrier tracking API integration (FedEx, UPS, USPS)
- [ ] Create procurement status update webhook endpoints
- [ ] Add dispute reason enumeration

#### Frontend Tasks
- [ ] Add "Download PDF" button to invoice views
- [ ] Create `/portal/settings/auto-pay.blade.php` wizard
- [ ] Create `x-auto-pay-wizard` component
- [ ] Create `/portal/settings/profile.blade.php` view
- [ ] Create `/portal/settings/team.blade.php` view
- [ ] Create `x-invite-user-modal` component
- [ ] Create `/portal/orders.blade.php` procurement tracking view
- [ ] Create `x-order-timeline` stepper component
- [ ] Enhance invoice line item display with attribution
- [ ] Create `x-line-item-detail-expander` component
- [ ] Enhance dispute submission wizard
- [ ] Add per-line-item dispute capability
- [ ] Apply semantic color classes throughout
- [ ] Optimize all views for mobile

#### Testing Tasks
- [ ] Test PDF generation across various invoice types
- [ ] Test auto-pay scheduling and retry logic
- [ ] Test profile approval workflow
- [ ] Test team invitation flow (new and existing users)
- [ ] Test procurement tracking status updates
- [ ] Test carrier tracking API integration
- [ ] Test line item attribution display
- [ ] Test dispute submission and resolution workflow
- [ ] Test email domain verification logic
- [ ] Accessibility audit for all portal views
- [ ] Load test auto-pay job with 1000+ invoices

#### Documentation Tasks
- [ ] Document auto-pay setup guide for clients
- [ ] Document team management workflow
- [ ] Document procurement tracking integration
- [ ] Document dispute resolution process
- [ ] Create client onboarding guide for self-service features
- [ ] Document privacy settings for line item attribution
- [ ] Create troubleshooting guide for auto-pay failures

---

### Success Metrics for Phase 15
*   **Adoption:** 60%+ of clients enable auto-pay within 90 days
*   **Support Reduction:** 50% fewer "how do I pay" support tickets
*   **Dispute Resolution:** 80% of disputes resolved within 48 hours
*   **Profile Accuracy:** 90%+ of client profiles complete and current
*   **Satisfaction:** Client portal NPS > 60

---

### Dependencies
*   **Services:** Existing PDF generation, `DisputeService`, `PaymentService`
*   **Models:** `Company`, `Invoice`, `Payment`, `BillingAuthorization`, `Dispute`
*   **External APIs:** Address verification, carrier tracking (FedEx, UPS, USPS)
*   **Jobs:** `ProcessAutopayInvoicesJob` (new)

---

### Risk Mitigation
*   **Auto-Pay Failures:** Implement robust retry logic and notification system
*   **Payment Security:** PCI compliance review for stored payment methods
*   **Profile Changes:** Require approval for critical fields to prevent fraud
*   **Team Management:** Implement rate limiting on invitations to prevent abuse
*   **Procurement Tracking:** Graceful degradation if carrier APIs unavailable

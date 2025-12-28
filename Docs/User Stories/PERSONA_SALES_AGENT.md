# Persona: Sales Agent
**Role:** The account manager or sales representative responsible for bringing in new business and upselling existing clients.

## Primary UI Locations
- **Quote Builder (Internal):** `/billing/quotes/create` ✅
- **Quote Builder (Public):** `/quotes/build` ✅
- **Pipeline Dashboard:** `/billing/quotes/pipeline` ❌ Not Implemented
- **Product Catalog:** `/billing/products` 🔶 Admin Only

## User Stories (Implemented)

### Quoting & Proposals
- ✅ **As a Sales Agent**, I want to **quickly build a quote by selecting products from the catalog** so that I don't have to remember prices.
  - *UI: Quote Builder with product dropdown*
- ✅ **As a Sales Agent**, I want to **create a quote for a new prospect** (not yet a client) so that I can capture leads.
  - *UI: Quote Builder allows "New Prospect" with name/email*
- ✅ **As a Sales Agent**, I want to **add custom line items** to a quote so that I can include one-off services not in the catalog.
  - *UI: Quote Builder allows free-text description and price*

### Lead Generation
- ✅ **As a Sales Agent**, I want to **share a public "Pricing Calculator" link** with prospects so that they can self-serve and submit their contact info.
  - *UI: Public Quote Builder (`/quotes/build`) with lead capture form*

## Problems Solved
1.  **Slow Sales Cycle:** Quote builder reduces time-to-quote from hours to minutes.
2.  **Lead Capture:** Public builder brings in pre-qualified leads with contact info.

---

---

## 📋 Phase 16: Sales Pipeline & Quote-to-Cash
**Priority:** HIGH | **Estimated Effort:** 32-40 hours | **Pattern:** Control Tower + Guided Journey

### Phase Overview
This phase completes the sales cycle from initial quote to revenue capture. Implements pipeline management, margin controls, product bundles, and seamless conversion workflows that eliminate manual handoffs between sales and finance.

### User Stories for Phase 16 Implementation

#### Story 16.1: Pipeline Kanban Dashboard
**As a Sales Agent**, I want to **see a "Pipeline Dashboard"** of all open quotes (Draft, Sent, Viewed, Accepted, Lost) so that I can prioritize follow-ups.

**Implementation Details:**
*   **Route:** `/billing/sales/pipeline`
*   **UX Pattern:** Control Tower (Kanban board)
*   **Components:**
    *   `x-pipeline-kanban` drag-and-drop board
    *   `x-quote-card` summary cards for each quote
    *   `x-pipeline-filters` for date range, value, client
    *   `x-pipeline-metrics` summary statistics
*   **Columns/Stages:**
    1.  **Draft** (in progress, not sent)
    2.  **Sent** (delivered to client, awaiting view)
    3.  **Viewed** (client opened, no response)
    4.  **Negotiating** (client requested changes)
    5.  **Accepted** (ready for conversion)
    6.  **Lost** (declined or expired)
*   **Card Information:**
    *   Quote number and client name
    *   Total value (with margin %)
    *   Days in current stage
    *   Action indicators (needs follow-up, expiring soon)
    *   Quick actions (view, edit, convert, mark lost)
*   **Drag-and-Drop:**
    *   Move quotes between stages
    *   Auto-update status and timestamps
    *   Confirmation modal for "Lost" moves
    *   Audit log of stage changes
*   **Metrics Panel:**
    *   Total pipeline value
    *   Conversion rate by stage
    *   Average time in each stage
    *   Win rate percentage
*   **Filters:**
    *   Date range
    *   Value range
    *   Assigned sales agent
    *   Client type (new vs. existing)

#### Story 16.2: Quote View Tracking & Notifications
**As a Sales Agent**, I want to **receive a notification when a client views a quote** so that I can follow up at the perfect moment.

**Implementation Details:**
*   **Tracking Service:** Existing `QuoteTrackingService` (Phase 2)
*   **Enhancement:** Add notification layer
*   **Tracking Events:**
    *   Quote viewed (first time)
    *   Quote re-viewed (subsequent views)
    *   Quote downloaded (if PDF feature exists)
    *   Time spent viewing (duration tracking)
*   **Notification Channels:**
    *   In-app notification badge
    *   Email notification (immediate)
    *   Slack webhook (if configured)
    *   SMS (optional, for high-value quotes)
*   **Notification Content:**
    *   "John Doe from Acme Corp viewed Quote #Q-2024-001"
    *   Link to quote details
    *   Suggested next action ("Follow up within 24 hours")
    *   View timestamp and duration
*   **Rules Engine:**
    *   Notify only on first view (avoid spam)
    *   Notify on re-views after 7+ days (renewed interest)
    *   Notify on long view duration (5+ minutes = high interest)
*   **Database:**
    *   Add `quote_views` table with timestamps
    *   Fields: id, quote_id, viewer_ip, viewer_user_agent, viewed_at, duration_seconds
*   **Privacy:**
    *   Respect Do Not Track headers
    *   Anonymize IP addresses after 30 days
    *   Client-configurable tracking opt-out

#### Story 16.3: Quote Cloning
**As a Sales Agent**, I want to **clone an existing quote** to create a similar one for another client so that I don't have to start from scratch.

**Implementation Details:**
*   **Location:** Quote detail view → "Actions" dropdown
*   **Button:** "Clone Quote"
*   **UX Pattern:** Modal wizard
*   **Wizard Steps:**
    1.  **Source Confirmation:** Preview source quote
    2.  **New Client:** Select existing client or create new prospect
    3.  **Adjustments:** Modify pricing, terms, expiration
    4.  **Save:** Create draft quote
*   **Clone Behavior:**
    *   Copy all line items (products, quantities, prices)
    *   Copy payment terms and delivery details
    *   Copy notes and custom text
    *   Reset quote number, status, dates
    *   Link to original quote (audit trail)
*   **Smart Adjustments:**
    *   Update client-specific pricing (if overrides exist)
    *   Update expiration date (default: 30 days from clone)
    *   Clear signatures and approvals
    *   Clear tracking history
*   **Service:** `QuoteService::cloneQuote()`
*   **Audit:**
    *   Log clone action with source quote reference
    *   Track conversion rates of cloned quotes

#### Story 16.4: Margin Display & Validation
**As a Sales Agent**, I want to **see the calculated margin on a quote** before I send it so that I don't accidentally sell below cost.

**Implementation Details:**
*   **Location:** Quote builder and quote detail views
*   **Component:** `x-margin-indicator`
*   **Display:**
    *   Gross Margin % prominently shown
    *   Cost vs. Price breakdown
    *   Color-coded status:
        *   Green: Margin > company target
        *   Yellow: Margin within 5% of floor
        *   Red: Margin below floor (requires approval)
*   **Calculation:**
    *   Per Line Item: (Price - Cost) / Price × 100
    *   Overall Quote: Total Margin / Total Price × 100
    *   Weighted average for bundles
*   **Real-Time Updates:**
    *   Recalculate on quantity change
    *   Recalculate on discount application
    *   Recalculate on product substitution
*   **Data Source:**
    *   Product costs from `products.cost` field
    *   Pricing from `PricingEngineService`
*   **Visibility:**
    *   Always visible to sales agents
    *   Optionally hidden from clients (configurable)
*   **Integration:**
    *   Link to profitability analytics
    *   Track margin trends over time

#### Story 16.5: Margin Floor Enforcement
**As a Sales Agent**, I want to **see a warning if I apply a discount that goes below the "Margin Floor"** so that I don't need manager approval.

**Implementation Details:**
*   **Configuration:** Company setting `margin_floor_percent`
*   **Validation:** Real-time check in quote builder
*   **Warning Modal:**
    *   Title: "Margin Below Company Floor"
    *   Content: "This quote has a margin of X%, below the company floor of Y%"
    *   Options:
        1.  "Adjust Pricing" (go back to edit)
        2.  "Request Manager Approval" (send notification)
        3.  "Override" (if user has permission)
*   **Approval Workflow:**
    *   Quote status: "Pending Approval"
    *   Notification to manager
    *   Manager can approve/reject with notes
    *   Sales agent notified of decision
*   **Override Permission:**
    *   `sales.override_margin_floor` permission
    *   Logs all overrides in audit trail
    *   Monthly report to management
*   **Visual Indicators:**
    *   Red border on quote total
    *   Warning badge on quote card
    *   Tooltip explaining risk
*   **Business Logic:**
    *   Cannot send quote to client until approved or adjusted
    *   Expired approvals (7 days) require reapproval
    *   Bulk approval for trusted agents (if multiple pending)

#### Story 16.6: Product Bundles
**As a Sales Agent**, I want to **use pre-built "Bundles"** (e.g., "New Employee Setup") so that I don't have to add 10 line items every time.

**Implementation Details:**
*   **Model:** Existing `ProductBundle` (Phase 1)
*   **Route:** `/billing/bundles` for bundle management
*   **Bundle Structure:**
    *   Name (e.g., "New Employee Onboarding")
    *   Description
    *   Category
    *   List of products with default quantities
    *   Optional discount (bundle pricing)
    *   Default margin target
*   **Quote Builder Integration:**
    *   "Add Bundle" button in quote builder
    *   Search/filter bundles by category
    *   Preview bundle contents before adding
    *   Add all items at once or customize
*   **Customization:**
    *   Adjust quantities per line item
    *   Remove items from bundle
    *   Override pricing
    *   Add notes per item
*   **Pricing Logic:**
    *   Calculate bundle price as sum of items
    *   Apply bundle discount if configured
    *   Respect client-specific pricing overrides
    *   Show savings vs. individual items
*   **Management UI:**
    *   CRUD for bundles (admin only)
    *   Active/inactive status
    *   Usage analytics (most popular bundles)
    *   Version control for bundle changes
*   **Templates:**
    *   Common bundles pre-seeded:
        *   New Employee Setup
        *   Office Move Package
        *   Server Upgrade Bundle
        *   Backup & Security Suite

#### Story 16.7: One-Click Quote Conversion
**As a Sales Agent**, I want to **convert an approved quote into an Invoice and Subscription** with one click so that the handover to Finance is seamless.

**Implementation Details:**
*   **Location:** Quote detail view (Accepted quotes only)
*   **Button:** "Convert to Order" (prominent, primary action)
*   **Service:** Existing `QuoteConversionService` (Phase 2)
*   **UX Pattern:** Confirmation modal with preview
*   **Conversion Options:**
    1.  **One-Time Invoice** (T&M or project work)
    2.  **Recurring Subscription** (MRR/ARR)
    3.  **Hybrid** (setup invoice + ongoing subscription)
*   **Modal Content:**
    *   Preview of what will be created
    *   Invoice due date
    *   Subscription start date and billing cycle
    *   Payment terms
    *   Option to send invoice immediately
*   **Conversion Logic:**
    *   Create invoice from quote line items
    *   Create subscription(s) for recurring items
    *   Link invoice to quote (audit trail)
    *   Update quote status to "Converted"
    *   Trigger welcome email to client
    *   Add to Finance Admin review queue
*   **Validation:**
    *   Ensure client has billing contact
    *   Ensure payment method on file (or include setup link)
    *   Check for duplicate conversions
*   **Post-Conversion:**
    *   Redirect to invoice/subscription view
    *   Show success message with next steps
    *   Offer to notify Finance Admin
    *   Update pipeline metrics
*   **Permissions:**
    *   Sales agents can convert their own quotes
    *   Managers can convert any quote
    *   Audit log of all conversions

#### Story 16.8: Quote Expiration Management
**As a Sales Agent**, I want to **automatically mark quotes as expired** and receive reminders to follow up before expiration.

**Implementation Details:**
*   **Database:** Add `expires_at` timestamp to `quotes` table
*   **Default Expiration:** 30 days from sent date (configurable)
*   **Job:** `ExpireQuotesJob` (runs daily)
    *   Find quotes past expiration with status "Sent" or "Viewed"
    *   Update status to "Expired"
    *   Log expiration event
    *   Notify sales agent
*   **Pre-Expiration Reminders:**
    *   Email 7 days before expiration
    *   Email 1 day before expiration
    *   In-app notification
*   **Client Experience:**
    *   Expired quote shows "Expired" badge
    *   Option to request extension
    *   Sales agent can extend expiration date
*   **Renewal:**
    *   "Renew Quote" button (updates dates, creates new quote)
    *   Optionally update pricing to current rates
    *   Send renewed quote to client

---

### Phase 16 Implementation Checklist

#### Backend Tasks
- [ ] Create `PipelineController` with kanban data endpoints
- [ ] Add `quote_views` table for view tracking
- [ ] Implement quote view notification system
- [ ] Create `QuoteService::cloneQuote()` method
- [ ] Add real-time margin calculation to quote builder
- [ ] Implement margin floor validation and approval workflow
- [ ] Create bundle CRUD controller
- [ ] Enhance `QuoteConversionService` with one-click conversion
- [ ] Create `ExpireQuotesJob` for automatic expiration
- [ ] Add quote expiration reminder notifications
- [ ] Add `expires_at` field to `quotes` table
- [ ] Add `margin_floor_percent` to company settings

#### Frontend Tasks
- [ ] Create `/billing/sales/pipeline.blade.php` kanban view
- [ ] Create `x-pipeline-kanban` drag-and-drop component
- [ ] Create `x-quote-card` component
- [ ] Create `x-pipeline-filters` component
- [ ] Add view tracking JavaScript to public quote page
- [ ] Create quote clone modal wizard
- [ ] Create `x-margin-indicator` component
- [ ] Create margin floor warning modal
- [ ] Create bundle management UI
- [ ] Integrate bundle selector into quote builder
- [ ] Create one-click conversion modal
- [ ] Add "Convert to Order" button to accepted quotes
- [ ] Create quote expiration indicators
- [ ] Add quote renewal workflow UI
- [ ] Apply semantic color classes throughout
- [ ] Optimize all views for mobile

#### Testing Tasks
- [ ] Test kanban drag-and-drop across browsers
- [ ] Test quote view tracking accuracy
- [ ] Test notification delivery (email, in-app, Slack)
- [ ] Test quote cloning with various configurations
- [ ] Test margin calculations with discounts and overrides
- [ ] Test margin floor enforcement and approval workflow
- [ ] Test bundle creation and customization
- [ ] Test quote conversion (invoice, subscription, hybrid)
- [ ] Test quote expiration job and reminders
- [ ] Performance test pipeline with 1000+ quotes
- [ ] Accessibility audit for all sales views

#### Documentation Tasks
- [ ] Document pipeline management workflow
- [ ] Document quote view tracking and privacy considerations
- [ ] Document quote cloning best practices
- [ ] Document margin calculation methodology
- [ ] Document approval workflow for below-floor quotes
- [ ] Document bundle creation and management
- [ ] Document quote-to-cash conversion process
- [ ] Document expiration and renewal workflows
- [ ] Create sales agent training materials

---

### Success Metrics for Phase 16
*   **Pipeline Visibility:** 100% of quotes tracked in pipeline within 1 week
*   **Conversion Rate:** 15% improvement in quote-to-cash conversion
*   **Follow-Up Speed:** 50% reduction in average time to follow up after view
*   **Margin Protection:** 95% of quotes meet or exceed margin floor
*   **Bundle Usage:** 40% of quotes include at least one bundle
*   **Efficiency:** 70% reduction in time to convert quote to invoice

---

### Dependencies
*   **Services:** Existing `QuoteConversionService`, `QuoteTrackingService`, `PricingEngineService`
*   **Models:** `Quote`, `ProductBundle`, `Invoice`, `Subscription`, `Product`
*   **Libraries:** Drag-and-drop library (e.g., SortableJS)
*   **Jobs:** `ExpireQuotesJob` (new)

---

### Risk Mitigation
*   **Margin Accuracy:** Validate cost data completeness before launch
*   **Approval Bottleneck:** Implement bulk approval and delegation features
*   **Conversion Errors:** Extensive testing of conversion logic with edge cases
*   **View Tracking Privacy:** Ensure compliance with GDPR/privacy regulations
*   **Pipeline Performance:** Optimize queries for large datasets, implement caching

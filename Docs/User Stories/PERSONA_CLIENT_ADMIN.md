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

## 🚧 Valuable User Stories (Not Yet Implemented)

### Payments
- ❌ **As a Client Admin**, I want to **set up Auto-Pay** so that I don't have to remember to log in every month.
  - *Gap: `auto_pay_enabled` flag exists on Company settings, but no UI toggle in Portal.*
- ❌ **As a Client Admin**, I want to **download a PDF copy of an invoice** so that I can file it with my accountant.
  - *Gap: Invoice PDF generation service exists, but no "Download PDF" button in Portal.*

### Purchasing
- ❌ **As a Client Admin**, I want to **digitally sign a quote** to approve it so that the order can be processed.
  - *Gap: Quote view exists, but no e-signature or "Approve" button wired up.*
- ❌ **As a Client Admin**, I want to **see the status of my hardware order** (Ordered, Shipped, Delivered) so that I know when to expect delivery.
  - *Gap: Procurement workflow (Phase 5.4) was deferred. NOW APPROVED FOR IMPLEMENTATION*

### Account Management
- ❌ **As a Client Admin**, I want to **update my company's billing address** so that invoices are sent to the correct location.
  - *Gap: No "Company Profile" edit section in Portal.*
- ❌ **As a Client Admin**, I want to **add additional users from my company** to the portal so that my accountant can also view invoices.
  - *Gap: Portal Team view exists but may not allow client-side user management.*

### Retainer / Pre-Paid Hours
- ❌ **As a Client Admin**, I want to **see my remaining pre-paid hours balance** so that I know when to purchase more.
  - *Gap: No retainer balance display in Portal.*
- ❌ **As a Client Admin**, I want to **see a breakdown of how my pre-paid hours were used** (which tickets, which technicians) so that I can validate the deductions.
  - *Gap: No retainer usage report.*

### Transparency
- ❌ **As a Client Admin**, I want to **see "Who called support?" on the invoice** (breakdown by user/ticket) so that I can verify charges are legitimate.
  - *Gap: Invoice shows line items but not linked ticket/user details.*
- ❌ **As a Client Admin**, I want to **dispute a line item** directly from the portal so that I don't have to send an email.
  - *Gap: No dispute button or workflow in Portal.*

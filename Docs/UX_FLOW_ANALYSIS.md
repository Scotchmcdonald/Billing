# UX Flow Analysis: Sub-Optimal Journeys

This document identifies user journeys that are **functional but friction-heavy** in the current FinOps implementation. These are candidates for UX refinement to achieve "World Class" status.

---

## 1. Quote-to-Cash Flow (Sales → Finance)

### Current State
```
[Sales Agent]           [System]              [Finance Admin]           [Client]
     |                      |                        |                      |
     |-- Create Quote ----->|                        |                      |
     |                      |-- Store as "Draft" --->|                      |
     |                      |                        |                      |
     |-- "Send to Client" ->|                        |                      |
     |                      |-- Email Link -------------------------------->|
     |                      |                        |                      |
     |                      |                        |                 (Views Quote)
     |                      |                        |                      |
     |    ❓ GAP: No visibility that client viewed   |                      |
     |                      |                        |                      |
     |                      |                   (Client says "Yes" via email/phone)
     |                      |                        |                      |
     |    ❓ GAP: Manual status change required      |                      |
     |                      |                        |                      |
     |                      |    ❓ GAP: No "Convert to Invoice" button     |
     |                      |                        |                      |
```

### Problems
1. **No Quote View Tracking:** Sales doesn't know when/if client opened the quote.
2. **No Digital Acceptance:** Client can't click "Accept" on the quote; requires offline confirmation.
3. **No One-Click Conversion:** Approved quote must be manually recreated as an invoice.
4. **No Pipeline View:** Sales can't see all quotes in a Kanban/funnel view.

### Target State
- Quote sent → Client views (notification to Sales) → Client clicks "Accept & Sign" → System auto-creates Invoice + Subscription → Payment link sent → Done.

---

## 2. Pre-Flight Review → Invoice Dispatch

### Current State
```
[System]                    [Finance Admin]              [Client]
    |                              |                         |
    |-- Generate Draft Invoices -->|                         |
    |                              |                         |
    |                         (Opens Pre-Flight)             |
    |                              |                         |
    |                         (Reviews Anomaly Scores)       |
    |                              |                         |
    |                         (Clicks "Approve")             |
    |                              |                         |
    |    ❓ GAP: What happens next? Is invoice auto-sent?    |
    |                              |                         |
```

### Problems
1. **Unclear "Approve" Action:** Does clicking "Approve" in the modal actually dispatch the invoice? Or just change status?
2. **No Batch Dispatch:** "Approve All Clean" button exists, but does it also *send* them?
3. **No Confirmation:** No toast/message confirming "5 invoices sent to clients."
4. **No Audit Trail UI:** No way to see "Invoice #123 was approved by Jane at 2:15pm and sent at 2:16pm."

### Target State
- "Approve" = Change status to `approved`. "Send" = Dispatch email. These should be explicit separate actions OR a clear "Approve & Send" button.

---

## 3. Technician Time Entry → Invoice Line Item

### Current State
```
[Technician]             [System]              [Finance Admin]
     |                       |                        |
     |-- Log Time on Ticket ->|                       |
     |                       |-- Store BillableEntry ->|
     |                       |                        |
     |                       |     (Days/Weeks pass)  |
     |                       |                        |
     |                       |<-- Monthly Billing Run |
     |                       |                        |
     |                       |-- Aggregate Entries -->|
     |                       |                        |
     |   ❓ GAP: Tech has no visibility into whether their entry was invoiced
     |                       |                        |
```

### Problems
1. **No Feedback Loop:** Technician logs time but never knows if it made it to an invoice.
2. **No "My Unbilled Time" View:** Tech can't see a list of their entries awaiting billing.
3. **Batch Dependency:** Time only becomes an invoice during the monthly run. No ad-hoc invoicing for T&M work.

### Target State
- Tech logs time → Sees status badge: "Pending Invoice" → After billing run, status changes to "Invoiced (INV-2024-001)"

---

## 4. Client Onboarding (New Company Setup)

### Current State
```
[Finance Admin]
     |
     |-- Create Company in CRM (?)
     |
     |-- Go to Billing Module
     |
     |-- Set Pricing Tier (where?)
     |
     |-- Create Subscription (separate screen)
     |
     |-- Create First Invoice (another screen)
     |
     |-- Send Invoice (another action)
     |
```

### Problems
1. **No Onboarding Wizard:** Multiple screens, unclear order of operations.
2. **Company/CRM Disconnect:** Is the Company created in CRM first, then linked? Or created in Billing?
3. **No Checklist:** Finance Admin doesn't know if they've completed all setup steps.

### Target State
- "New Client Wizard": Company Info → Pricing Tier → Initial Subscription → First Invoice (optional) → Done.

---

## 5. Dispute Handling (Client Questions Invoice)

### Current State
```
[Client]                    [Finance Admin]              [System]
    |                              |                         |
    |-- "This charge is wrong!" -->|                         |
    |                              |                         |
    |                         (Checks invoice manually)      |
    |                              |                         |
    |   ❓ GAP: No way to "pause" dunning on this invoice    |
    |                              |                         |
    |   ❓ GAP: No way to add internal note "disputed"       |
    |                              |                         |
    |                         (Resolves issue, adjusts)      |
    |                              |                         |
    |   ❓ GAP: No credit note / adjustment workflow         |
    |                              |                         |
```

### Problems
1. **No Dispute Flag:** Can't mark an invoice as "Disputed" to pause automation.
2. **No Internal Notes:** Can't add context ("Client claims they didn't order this").
3. **No Credit Notes:** If adjustment needed, must void invoice and recreate. No partial credit.

### Target State
- "Flag as Disputed" → Pauses dunning → Add notes → Issue Credit Note OR Adjust Line Item → Resume billing.

---

## 6. Dunning Visibility (Finance Admin)

### Current State
```
[System]                    [Finance Admin]
    |                              |
    |-- Send Reminder (auto) ----->|  (No notification)
    |                              |
    |                         (Wants to see reminder history)
    |                              |
    |   ❓ GAP: Must check BillingLog table directly        |
    |                              |
```

### Problems
1. **Ghost Feature:** Dunning emails are sent, but Finance Admin has no UI to see when they were sent.
2. **No Per-Invoice Timeline:** Can't see "Reminder sent Day -3, Day 0, Day +7" on the invoice.

### Target State
- Invoice Detail View → "Activity Timeline" tab showing all automated communications + status changes.

---

## Priority Recommendations

| Flow | Severity | Effort | Recommendation |
|------|----------|--------|----------------|
| Quote-to-Cash | High | Medium | Add "Accept" button on public quote, "Convert to Invoice" on internal view |
| Pre-Flight Dispatch | Medium | Low | Clarify "Approve" vs "Send", add confirmation toast |
| Tech → Invoice Feedback | Medium | Low | Add "Invoice Status" column to BillableEntry list |
| Client Onboarding | Medium | Medium | Create "New Client Wizard" component |
| Dispute Handling | High | Medium | Add `is_disputed` flag, Credit Note model |
| Dunning Visibility | Low | Low | Add Activity Timeline to Invoice Detail modal |

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

## Problems Solved
1.  **Lost Revenue:** Captures parts, expenses, and time directly on the ticket.
2.  **Billing Disputes:** Provides detailed logs that flow directly to the invoice.

---

## 🚧 Valuable User Stories (Not Yet Implemented)

### Time Tracking Efficiency
- ❌ **As a Technician**, I want to **see how many billable hours I've logged today/this week** so that I know if I'm meeting my utilization targets.
  - *Gap: No "My Stats" dashboard. Data exists in `BillableEntry` but not aggregated.*
- ❌ **As a Technician**, I want a **"Daily Timesheet" view** where I can see all my tickets and log time inline, instead of opening each ticket.
  - *Gap: Identified in Post-Implementation Checklist, not built.*

### Parts & Inventory
- ❌ **As a Technician**, I want to **scan a barcode on a piece of hardware** to add it to the ticket so that I don't have to type serial numbers.
  - *Gap: No barcode scanning integration.*
- ❌ **As a Technician**, I want to **see real-time stock levels** when adding a part so that I don't promise something we don't have.
  - *Gap: No inventory integration. Parts list is static.*

### Context Awareness
- ❌ **As a Technician**, I want to **see a warning if a client is "Past Due"** so that I don't perform non-critical work for a delinquent client.
  - *Gap: No AR status surfaced in Work Order or Ticket view.*
- ❌ **As a Technician**, I want to **know if a specific service is covered by the client's contract** so that I don't accidentally bill them for something included in their AYCE.
  - *Gap: No contract coverage indicator in Work Order. Requires linking to Subscription.*

### Expense Management
- ❌ **As a Technician**, I want to **upload a photo of a receipt** to an expense so that Finance has proof.
  - *Gap: Expense modal has no file upload. Requires Media library integration.*

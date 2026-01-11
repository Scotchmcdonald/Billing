# Persona: End-User (Client Employee)
**Role:** An employee at the client company who requests support and consumes services (e.g., Office Staff, Remote Worker). They want quick resolutions without administrative friction.

## Primary UI Locations
- **Client Portal (Support):** `/portal/support` ❌ Not Implemented
- **Ticket View:** `/portal/tickets/{id}` ✅
- **My Assets:** `/portal/my-assets` ❌ Not Implemented

## User Stories (Planned / To Be Migrated)

### Support Interaction
- **As an employee**, I want to **see how many "On-Demand" credits my company has left** before I open a ticket, so I can decide if my issue is urgent enough to use one (Transparency).
- **As an employee**, I want to **provide a "Technician Tier" rating** after my issue is resolved, ensuring the billing accurately reflects the complexity of the help I received (Feedback).

### Asset Management
- **As a power user**, I want to **request a "Temporary Limit Increase"** for a specific high-priority project, so my tickets don't get stuck in "Pending Approval" status (Flexibility).
- **As an employee**, I want to **see which devices are assigned to me** so I can report issues against the correct asset (Accuracy).

## Problems Solved
1.  **Ticket Triage:** Reduces low-priority tickets when credits are low.
2.  **Asset Accuracy:** Links tickets to specific hardware for better tracking.
3.  **Process Efficiency:** Reduces friction for power users needing more resources.

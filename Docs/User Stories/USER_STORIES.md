# User Stories & Architecture Overview

## 1. Persona Documentation
Detailed user stories and workflows are maintained in specific persona documents:

*   **[Client Admin](PERSONA_CLIENT_ADMIN.md)**: Approvals, Payments, Invoices, Asset Management.
*   **[Finance Admin](PERSONA_FINANCE_ADMIN.md)**: Reconciliation, Invoicing, Reporting, Profitability.
*   **[End-User / Employee](PERSONA_END_USER.md)**: Ticket interaction, Credits, Technician Ratings.
*   **[Technician](PERSONA_TECHNICIAN.md)**: Time Tracking, Work Orders, Billable Materials.
*   **[Sales Agent](PERSONA_SALES_AGENT.md)**: Quotes, Pipeline, Lead Gen.
*   **[Accountant](PERSONA_ACCOUNTANT.md)**: External Audits, Tax Prep, Read-Only Access.
*   **[Executive](PERSONA_EXECUTIVE.md)**: High-level KPIs, Trends, Strategy.

## 2. Technical Contexts

### A. Pro-Rata & Co-Terminus Logic
*   **Context**: Adding employees mid-month.
*   **Requirement**: Billing engine must calculate pro-rata charges for partial months to align new seats with the main billing cycle.

### B. SLA-to-Billing Bridge
*   **Context**: SLA breaches.
*   **Requirement**: "Service Credits" or adjustments applied to invoices automatically based on performance metrics.

### C. Asset Lifecycle (ITAD)
*   **Context**: EOL Hardware.
*   **Requirement**: Inventory tracks depreciation/warranty and prompts for Refresh Quotes.

### D. Multi-Currency & Tax
*   **Context**: Jurisdictional differences.
*   **Requirement**: Distinct tax rules for Hardware (Inventory) vs Labor/Software (Service).

## 3. The Unified Ledger Concept
Transactions across all domains (Inventory, Credits, Labor) should be treated as "Units of Value" with consistent transaction logging (IN/OUT).

| Type | Unit | Reference |
| --- | --- | --- |
| Hardware | Chromebook | Quote |
| Credit | Support Unit | Payment |
| Service | Ticket Resolution | Ticket |
| Labor | Dev Hour | Project |

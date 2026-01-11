# User Stories & Architecture Overview

## 1. Persona Documentation
Detailed user stories and workflows are maintained in specific persona documents:

*   **[Accountant](01_Persona_Accountant.md)**: External Audits, Tax Prep, Read-Only Access.
*   **[Client Admin](02_Persona_Client_Admin.md)**: Approvals, Payments, Invoices, Asset Management.
*   **[End-User / Employee](03_Persona_End_User.md)**: Ticket interaction, Credits, Technician Ratings.
*   **[Executive](04_Persona_Executive.md)**: High-level KPIs, Trends, Strategy.
*   **[Finance Admin](05_Persona_Finance_Admin.md)**: Reconciliation, Invoicing, Reporting, Profitability.
*   **[Sales Agent](06_Persona_Sales_Agent.md)**: Quotes, Pipeline, Lead Gen.
*   **[Technician](07_Persona_Technician.md)**: Time Tracking, Work Orders, Billable Materials.

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
*   **[Plan Documentation](../PLAN_HYBRID_MSP_LIFECYCLE.md)**

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

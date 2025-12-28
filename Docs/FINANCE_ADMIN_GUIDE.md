# Finance Admin Guide

## Overview
The Billing Module is designed to automate the "Quote-to-Cash" lifecycle for Managed Service Providers (MSPs). This guide covers the daily and monthly operations required to manage finances, billing, and profitability.

## 1. Monthly Billing Run

The system is designed to automatically generate invoices on the 1st of every month. However, a manual review process ("Pre-Flight") is recommended before sending them to clients.

### Automatic Generation
*   **Schedule:** Runs automatically at 00:00 on the 1st of the month.
*   **Scope:** Generates invoices for all active subscriptions and unbilled billable time/expenses from the previous month.
*   **Status:** Invoices are created in `DRAFT` status.

### Manual Trigger
If you need to run billing manually (e.g., for testing or mid-month corrections):
1.  Navigate to **Finance > Invoices**.
2.  Click **Run Monthly Billing**.
3.  Select the target month and confirm.

## 2. The Pre-Flight Review

Before invoices are emailed to clients, they must pass the Pre-Flight Review. This ensures accuracy and prevents embarrassing billing errors.

### Accessing Pre-Flight
Navigate to **Finance > Pre-Flight Review**.

### The Review Process
The dashboard displays a list of all `DRAFT` invoices. The system automatically flags potential issues:
*   **Variance Alert:** If an invoice total varies by more than 15% compared to the previous month.
*   **Missing Data:** Clients missing tax IDs or billing addresses.
*   **Low Margin:** Invoices where the estimated gross margin is below the company floor (default 20%).

### Actions
*   **Approve:** Validates the invoice and queues it for sending.
*   **Hold:** Keeps the invoice in draft for further investigation.
*   **Reject/Delete:** Removes the draft invoice (allows regeneration).

## 3. Managing Price Overrides

The system uses a 3-tier pricing model (Standard, Non-Profit, Consumer). However, specific clients often negotiate custom rates.

### Creating an Override
1.  Navigate to **Finance > Overrides**.
2.  Click **New Override**.
3.  **Select Context:** Choose the specific **Company** and **Product**.
4.  **Set Price:** Enter the custom `Unit Price`.
5.  **Validation:** The system will check if the new price maintains the minimum profit margin. If it falls below the threshold, you may need executive approval (if configured).
6.  **Save:** The override takes effect immediately for all future invoices.

### Viewing Active Overrides
The Overrides Dashboard shows all active custom pricing, including:
*   Client Name
*   Product
*   Custom Price vs. List Price
*   Effective Date

## 4. Profitability Reports

Real-time insight into client and service profitability is a core feature of the module.

### Dashboard
Navigate to **Finance > Profitability**.

### Key Metrics
*   **Gross Margin %:** `(Revenue - COGS) / Revenue`.
*   **Effective Hourly Rate:** `Revenue / Total Hours Worked`. This tells you how much you are actually earning per hour of technician time, including flat-rate contracts.
*   **Revenue per Technician:** Helps identify high-performing staff or overloaded resources.

### Reports
*   **Client Profitability:** detailed breakdown of revenue and costs per client.
*   **Service Line Profitability:** Which services (e.g., "Microsoft 365", "Managed Workstation") are most profitable?

## 5. Collections & AR Aging

Navigate to **Finance > Collections** to view the Accounts Receivable aging report.
*   **Buckets:** Current, 1-30 Days, 31-60 Days, 61-90 Days, 90+ Days.
*   **Actions:** Send reminder emails directly from this view.

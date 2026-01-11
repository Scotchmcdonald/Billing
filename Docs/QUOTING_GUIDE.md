# Quoting & Pricing Guide

## Overview
This guide covers the quoting system in the Billing module, specifically the **Pricing Tier** logic, **Variance Tracking**, and **Approval Workflows**.

## 1. Pricing Tiers

The system uses three core pricing tiers to automate consistency:

| Tier | Typical Use | Variance |
|------|------------|---------|
| **Standard** | Regular B2B Clients | Benchmark (0%) |
| **Non-Profit** | Registered Charities | Discounted (e.g. -20%) |
| **Consumer** | Walk-in / Residential | Premium (e.g. +10%) |

### Auto-Population
*   **Company Context:** When you select a known Client (Company), the quote automatically switches to their assigned Tier.
*   **Product Lookup:** Adding a line item pulls the price for the specific tier from the `product_tier_prices` table.

## 2. Dynamic Variance Tracking

To ensure margin protection, the system calculates variance against the **Standard** price in real-time.

### Visual Indicators
*   🟢 **Green:** Price is lower than standard (Discount).
*   🟠 **Orange:** Price is higher than standard (Premium).
*   🔴 **Red:** Variance exceeds the **Approval Threshold** (default 15%).

### The Logic
> If Standard Price is \$100 and Threshold is 15%:
> *   Safe Range: \$85.00 - \$115.00
> *   Pricing outside this range triggers "Requires Approval".

## 3. Creating a Quote (Step-by-Step)

### Step 1: Client Selection
Navigate to **Billing > Finance > Quotes > Create**.
Select an existing company to auto-load their Tier and Address.

### Step 2: Line Items
*   Select Product from dropdown.
*   Price populates automatically.
*   **Manual Override:** If you type a new price, the system immediately calculates the variance.

### Step 3: Threshold & Approval
If your overrides exceed the threshold (e.g., giving a friend a 90% discount):
*   The Quote is flagged `requires_approval = true`.
*   You cannot "Send" the quote until a user with `approve_quotes` permission clears it.

## 4. Configuration & Schema

### Config
Defaults are located in `/config/quotes.php` or `.env`:
```ini
QUOTE_APPROVAL_THRESHOLD=15.00
```

### Database
*   **Quotes Table:** Stores `pricing_tier` and `approval_threshold_percent`.
*   **Quote Line Items:** Stores `standard_price`, `variance_amount`, and `variance_percent`.

### Troubleshooting
*   **Prices not populating?** Ensure `inventory_products` have entries in `product_tier_prices`.
*   **Variance always 0?** Check if the Product has a Base Price defined.


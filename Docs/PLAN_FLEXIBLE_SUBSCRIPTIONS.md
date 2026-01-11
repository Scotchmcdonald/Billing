# Implementation Plan: Flexible Subscription Billing

## Overview
This plan addresses the requirement to allow Clients to select their preferred billing frequency (Monthly vs Annual) during the Quote acceptance process, and for subscriptions to be generated based on these choices.

## Core User Story
> "A client company subscribes for a service which has an agreed cost of $600/year/employee. They have 10 employees. The default quote is annual. The client prefers monthly cash flow, so they toggle the quote to Monthly, updating the rate to $55/month/employee. They approve. The system creates a Monthly subscription. The Client Portal shows this active subscription and headcount."

## Architectural Decision: Pricing Modifiers vs. Explicit Rates

### Question
*Is it preferable to use credit/discount add-ons for price adjustments instead of changing unit prices?*

### Analysis
1.  **Frequency-Based Pricing (The "Standard" Rates)**
    *   **Recommendation**: Store explicit `price_monthly` and `price_annually` on the Product model.
    *   **Reasoning**: Frequency differences often aren't linear. A "10% discount for Annual" is a business rule, but specific numbers ($50/mo vs $500/yr) are cleaner to manage catalog-wide. It keeps Invoices clean: "10 x Licenses @ $500" is better than "10 x Licenses @ $600" + "1 x Annual Discount (-$1000)".

2.  **Ad-Hoc Modifiers (Discounts/Credits)**
    *   **Visible Line Items**: Use a Product of type `service` (or new type `adjustment`) with a negative price.
        *   *Pros*: clear visibility to client ("Loyalty Discount"), traceable in GL.
    *   **Hidden Adjustments**: Use the `PriceOverride` model to modify the specific `SubscriptionItem` unit price.
        *   *Pros*: Clean invoice presentation, effective for "Grandfathered" rates.

---

## Implementation Phases

### Phase 1: Product Catalog Enhancements
**Goal**: Allow products to define standard rates for specific frequencies.

1.  **Schema Update (`products` table)**
    *   Add `price_monthly` (decimal, nullable).
    *   Add `price_annually` (decimal, nullable).
2.  **Model Update (`Product`)**
    *   Update `fillable`.
    *   Add helper `getPriceForFrequency(string $frequency): ?float`.

### Phase 2: Quote Mechanics (The Toggle)
**Goal**: Allow frequency selection in the Quote Builder.

1.  **Schema Update (`quote_line_items`)**
    *   Add `billing_frequency` (enum/string) to the line item.
    *   Add `frequency_locked` (boolean) to prevent client changes if necessary.
2.  **Backend Logic**
    *   Endpoint: `POST /quote/{token}/update-line-frequency`.
    *   Logic: When toggled, look up the Product's corresponding `price_{freq}`. Update `unit_price` and `subtotal`. Recalculate Quote Total.
3.  **Frontend (Public Quote View)**
    *   Upgrade the implementation of `QuoteLineItem` display.
    *   If a product has *both* Monthly and Annual prices, render a toggle switch.
    *   AJAX call to update totals on toggle.

### Phase 3: Subscription Conversion
**Goal**: Ensure the Quote's chosen frequency dictates the Subscription terms.

1.  **Quote Acceptance Logic (`QuoteService` / `ContractService`)**
    *   When converting `QuoteLineItem` to `Subscription`:
        *   Map `quote_item.billing_frequency` -> `subscription.billing_frequency`.
        *   Map `quote_item.unit_price` -> `subscription.effective_price`.
        *   Calculate `next_billing_date` based on the frequency.

### Phase 4: Client Portal Visibility
**Goal**: Show clients what they are paying for.

1.  **New View**: `Modules/Billing/Resources/views/portal/subscriptions/index.blade.php`.
2.  **Data Table**:
    *   Product Name / Description.
    *   Quantity (Employees/Seats).
    *   Frequency (Monthly/Annual).
    *   Unit Price & Total Amount.
    *   Next Billing Date.
3.  **Routes**: `Route::get('/portal/subscriptions', ...)`

### Phase 5: Automated Invoicing
**Goal**: Bill based on the Subscription data.

1.  **Service**: `InvoiceGenerationService`.
2.  **Job**: `GenerateRecurringInvoices`.
    *   Query: Active Subscriptions where `next_billing_date <= NOW()`.
    *   Action: Create Invoice > Invoice Lines.
    *   Update: `subscription.next_billing_date` += `frequency`.

## Data Structure

### Product Table (Additions)
| Column | Type | Description |
| :--- | :--- | :--- |
| `price_monthly` | decimal(15, 4) | Default price if frequency is Monthly. |
| `price_annually` | decimal(15, 4) | Default price if frequency is Annual. |

### QuoteLineItem Table (Additions)
| Column | Type | Description |
| :--- | :--- | :--- |
| `billing_frequency` | string | The selected frequency for this specific line. |
| `locked` | boolean | If true, client cannot toggle frequency. |


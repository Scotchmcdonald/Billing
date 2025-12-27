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

## 🚧 Valuable User Stories (Not Yet Implemented)

### Quote Management
- ❌ **As a Sales Agent**, I want to **see a "Pipeline Dashboard"** of all open quotes (Draft, Sent, Viewed, Accepted, Lost) so that I can prioritize follow-ups.
  - *Gap: No pipeline/kanban view for quotes. Only creation flow exists.*
- ❌ **As a Sales Agent**, I want to **receive a notification when a client views a quote** so that I can follow up at the perfect moment.
  - *Gap: No quote view tracking or webhook.*
- ❌ **As a Sales Agent**, I want to **clone an existing quote** to create a similar one for another client so that I don't have to start from scratch.
  - *Gap: No "Duplicate" action on Quote model.*

### Pricing & Margin
- ❌ **As a Sales Agent**, I want to **see the calculated margin on a quote** before I send it so that I don't accidentally sell below cost.
  - *Gap: Quote Builder UI calculates total but not margin. Logic exists in `PricingEngineService`.*
- ❌ **As a Sales Agent**, I want to **see a warning if I apply a discount that goes below the "Margin Floor"** so that I don't need manager approval.
  - *Gap: `margin_floor_percent` exists on Company, but not enforced in Quote UI.*

### Bundles & Efficiency
- ❌ **As a Sales Agent**, I want to **use pre-built "Bundles"** (e.g., "New Employee Setup") so that I don't have to add 10 line items every time.
  - *Gap: No Product Bundles feature. Only individual products in catalog.*

### Catalog Awareness
- ❌ **As a Sales Agent**, I want to **see real-time stock levels** for hardware products so that I don't sell something we can't deliver.
  - *Gap: No inventory integration. Products have no stock tracking.*

### Quote-to-Cash
- ❌ **As a Sales Agent**, I want to **convert an approved quote into an Invoice and Subscription** with one click so that the handover to Finance is seamless.
  - *Gap: Quote-to-Cash events exist, but no single "Convert" button in Quote UI. Logic triggered externally.*

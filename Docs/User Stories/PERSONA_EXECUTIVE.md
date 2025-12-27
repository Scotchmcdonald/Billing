# Persona: Executive / Owner
**Role:** The MSP owner, CEO, or VP of Operations who needs strategic visibility into financial health without getting into the weeds.

## Primary UI Locations
- **Executive Dashboard:** `/billing/executive` ❌ Not Implemented
- **FinOps Dashboard:** `/billing/dashboard` ✅ (Partial - shares with Finance Admin)
- **Profitability Report:** `/billing/profitability` ✅

## User Stories (Implemented)

### Financial Health
- ✅ **As an Executive**, I want to **see MRR at a glance** so that I know if the business is growing.
  - *UI: FinOps Dashboard MRR card*
- ✅ **As an Executive**, I want to **see a 6-month revenue forecast** so that I can plan hiring and investments.
  - *UI: FinOps Dashboard Forecast Chart | Logic: `ForecastingService`*
- ✅ **As an Executive**, I want to **see which clients are unprofitable** so that I can make strategic decisions about pricing or termination.
  - *UI: Profitability Dashboard*

### Risk Awareness
- ✅ **As an Executive**, I want to **see total AR Aging** so that I know our cash flow risk.
  - *UI: FinOps Dashboard AR Widget*

## Problems Solved
1.  **Strategic Visibility:** High-level KPIs without drowning in detail.
2.  **Forecast Accuracy:** Data-driven planning instead of gut feelings.

---

## 🚧 Valuable User Stories (Not Yet Implemented)

### Executive Summary
- ❌ **As an Executive**, I want a **dedicated "Executive Dashboard"** with only the 5 KPIs I care about (MRR, Churn, Gross Margin, LTV, AR Aging) so that I don't have to parse Finance Admin details.
  - *Gap: Executives share the FinOps Dashboard which has operational details they don't need.*
- ❌ **As an Executive**, I want to **see month-over-month and year-over-year comparisons** so that I can understand trends, not just snapshots.
  - *Gap: Dashboard shows current values, no historical comparison.*

### Benchmarking
- ❌ **As an Executive**, I want to **compare our metrics against industry benchmarks** (HTG, Service Leadership, ConnectWise) so that I know how we stack up.
  - *Gap: Benchmarking API integration deferred (Phase 5.1).*
- ❌ **As an Executive**, I want to **see our "Effective Hourly Rate" vs. target** so that I know if our pricing strategy is working.
  - *Gap: Metric exists in `AnalyticsService` but not surfaced in any UI.*

### Alerts & Notifications
- ❌ **As an Executive**, I want to **receive a weekly email digest** of key financial metrics so that I don't have to log in.
  - *Gap: No scheduled report delivery system.*
- ❌ **As an Executive**, I want to **be alerted if churn rate spikes** or AR Aging exceeds a threshold so that I can intervene early.
  - *Gap: No threshold-based alerting system.*

### Client Health
- ❌ **As an Executive**, I want to **see a "Client Health Score"** combining profitability, payment history, and support ticket volume so that I can identify at-risk accounts.
  - *Gap: No composite health score. Data exists in silos.*

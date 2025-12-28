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

---

## 📋 Phase 11: Executive Dashboard & KPI Enhancements
**Priority:** HIGH | **Estimated Effort:** 16-24 hours | **Pattern:** Control Tower Dashboard

### Phase Overview
This phase delivers a dedicated Executive Dashboard designed specifically for strategic decision-makers. Following the "Pilot's Cockpit" philosophy, it provides high-level visibility without operational noise.

### User Stories for Phase 11 Implementation

#### Story 11.1: Dedicated Executive Dashboard
**As an Executive**, I want a **dedicated "Executive Dashboard"** with only the 5 KPIs I care about (MRR, Churn, Gross Margin, LTV, AR Aging) so that I don't have to parse Finance Admin details.

**Implementation Details:**
*   **Route:** `/billing/executive/dashboard`
*   **UX Pattern:** Control Tower Dashboard
*   **Components:**
    *   `x-kpi-card` for each metric (large numbers, trend sparklines)
    *   `x-trend-indicator` showing MoM/YoY changes
    *   Clean, high-density layout with `text-xs`/`text-sm` typography
*   **Key Features:**
    *   5 primary KPI cards prominently displayed
    *   Color-coded status indicators (success/warning/danger)
    *   Real-time updates every 30 seconds
    *   Export to PDF functionality
*   **Design Standards:**
    *   Semantic color usage (`bg-primary-600`, `text-success-700`)
    *   High information density without clutter
    *   Mobile-responsive grid layout

#### Story 11.2: Historical Trend Analysis
**As an Executive**, I want to **see month-over-month and year-over-year comparisons** so that I can understand trends, not just snapshots.

**Implementation Details:**
*   **Component:** `x-trend-comparison`
*   **Data Points:**
    *   MoM % change with up/down indicators
    *   YoY % change with historical context
    *   12-month rolling average
*   **Visualization:**
    *   Inline sparkline charts for each KPI
    *   Color-coded trend arrows (green up, red down)
    *   Percentage changes prominently displayed
*   **Service:** `TrendAnalyticsService` (new)
    *   Methods: `calculateMoM()`, `calculateYoY()`, `get12MonthAverage()`

#### Story 11.3: Alert Configuration UI
**As an Executive**, I want to **configure threshold-based alerts** (churn spike, AR aging excess) and receive notifications so that I can intervene early.

**Implementation Details:**
*   **Route:** `/billing/executive/alerts/configure`
*   **UX Pattern:** Guided Journey (Modal Wizard)
*   **Components:**
    *   `x-alert-configuration-modal` with multi-step form
    *   `x-threshold-input` for setting numeric thresholds
    *   `x-notification-preferences` for delivery channels
*   **Alert Types:**
    *   Churn Rate Spike (threshold: > X%)
    *   AR Aging Excess (threshold: > $X in 90+ day bucket)
    *   Margin Below Target (threshold: < X%)
    *   LTV:CAC Ratio Drop (threshold: < X:1)
*   **Notification Channels:**
    *   Email (immediate + daily digest)
    *   In-app notification badge
    *   Slack webhook (optional integration)
*   **Service:** Extends existing `AlertService`
    *   New method: `configureExecutiveAlerts()`

#### Story 11.4: Effective Hourly Rate Display
**As an Executive**, I want to **see our "Effective Hourly Rate" vs. target** so that I know if our pricing strategy is working.

**Implementation Details:**
*   **Location:** Executive Dashboard KPI card
*   **Data Source:** Existing `AnalyticsService::calculateEffectiveHourlyRate()`
*   **Display:**
    *   Current EHR vs. Target EHR side-by-side
    *   Variance percentage with color coding
    *   Trend sparkline showing last 6 months
*   **Calculation:**
    *   Total Revenue ÷ Total Billable Hours
    *   Compare against company-wide target rate
*   **Visual Treatment:**
    *   Large primary number (`text-4xl`)
    *   "vs Target" comparison in smaller text
    *   Status badge (on-target/below-target)

#### Story 11.5: Weekly Email Digest
**As an Executive**, I want to **receive a weekly email digest** of key financial metrics so that I don't have to log in.

**Implementation Details:**
*   **Job:** `SendExecutiveDigestJob` (scheduled weekly)
*   **Mailable:** `ExecutiveDigestMail`
*   **Content:**
    *   Week-over-week changes for 5 key KPIs
    *   Top 3 at-risk clients (by health score)
    *   Notable alerts triggered this week
    *   Quick action links (click to drill down)
*   **Template:** Responsive HTML email
    *   Clean, branded design
    *   Mobile-optimized layout
    *   CTA buttons using semantic colors
*   **Configuration:**
    *   User preference: Enable/disable digest
    *   Day of week preference
    *   Time of day preference

#### Story 11.6: Industry Benchmark Comparison
**As an Executive**, I want to **compare our metrics against industry benchmarks** (HTG, Service Leadership) so that I know how we stack up.

**Implementation Details:**
*   **Component:** `x-benchmark-comparison-card`
*   **Data Source:** Industry benchmark APIs (integration required)
*   **Metrics to Compare:**
    *   Gross Margin % (Benchmark: 45-55%)
    *   EBITDA % (Benchmark: 15-25%)
    *   Revenue per Employee (Benchmark: $150K-$200K)
    *   Client Acquisition Cost
*   **Visualization:**
    *   Gauge charts showing position within range
    *   Percentile ranking (e.g., "Top 25% in Gross Margin")
    *   Actionable insights for improvement
*   **Service:** `BenchmarkingService` (new)
    *   Methods: `fetchBenchmarks()`, `compareToIndustry()`
    *   API integrations: HTG, Service Leadership, ConnectWise

---

### Phase 11 Implementation Checklist

#### Backend Tasks
- [ ] Create `ExecutiveDashboardController` with KPI aggregation methods
- [ ] Implement `TrendAnalyticsService` for MoM/YoY calculations
- [ ] Extend `AlertService` with executive alert configuration
- [ ] Create `BenchmarkingService` for industry comparison (API integrations)
- [ ] Create `SendExecutiveDigestJob` for weekly email delivery
- [ ] Create `ExecutiveDigestMail` mailable with responsive template
- [ ] Add database migrations for alert configuration storage
- [ ] Add executive alert thresholds to `company_settings` table

#### Frontend Tasks
- [ ] Create `/billing/executive/dashboard.blade.php` view
- [ ] Create `x-kpi-card` Blade component
- [ ] Create `x-trend-indicator` Blade component
- [ ] Create `x-trend-comparison` Blade component
- [ ] Create `x-alert-configuration-modal` Blade component
- [ ] Create `x-benchmark-comparison-card` Blade component
- [ ] Implement real-time KPI updates (Alpine.js polling)
- [ ] Add sparkline chart generation (SVG-based, no external libs)
- [ ] Implement PDF export functionality for dashboard
- [ ] Apply semantic color classes throughout (no hardcoded colors)

#### Testing Tasks
- [ ] Unit tests for `TrendAnalyticsService` calculations
- [ ] Unit tests for executive alert threshold logic
- [ ] Feature tests for Executive Dashboard routes
- [ ] Test weekly digest job scheduling
- [ ] Test email template rendering across clients
- [ ] Test mobile responsiveness of dashboard
- [ ] Accessibility audit (WCAG AA compliance)
- [ ] Load test for real-time polling (30s intervals)

#### Documentation Tasks
- [ ] Document Executive Dashboard user guide
- [ ] Document alert configuration workflow
- [ ] Document benchmark data sources and update frequency
- [ ] Create troubleshooting guide for digest emails
- [ ] Document API integration requirements for benchmarking

---

### Success Metrics for Phase 11
*   **Adoption:** 80%+ of executive users access dashboard weekly
*   **Time-to-Insight:** < 10 seconds to understand business health
*   **Alert Effectiveness:** 90%+ of threshold breaches result in timely action
*   **User Satisfaction:** Net Promoter Score > 50 from executive users

---

### Dependencies
*   **Services:** Existing `AnalyticsService`, `ForecastingService`, `AlertService`
*   **Models:** `Company`, `Invoice`, `Subscription`, `BillableEntry`
*   **External APIs:** HTG Peer Groups, Service Leadership Index (Phase 11.6 only)

---

### Risk Mitigation
*   **API Downtime:** Benchmark comparisons gracefully degrade if external APIs unavailable
*   **Data Accuracy:** Implement data validation and audit logging for KPI calculations
*   **Email Deliverability:** Use transactional email service (SendGrid/Postmark) for digests
*   **Performance:** Cache KPI calculations (5-minute TTL) to reduce database load

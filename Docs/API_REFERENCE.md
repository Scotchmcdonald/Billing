# API Reference
**FinOps Billing Module - Complete Endpoint Documentation**  
**Last Updated:** 2025-12-28

---

## Table of Contents

1. [Public Quote Builder API](#public-quote-builder-api)
2. [Phase 11-17 View Endpoints](#phase-11-17-view-endpoints)
3. [Component API](#component-api)
4. [Integration Patterns](#integration-patterns)

---

## Public Quote Builder API

These endpoints allow external applications or the public-facing website to interact with the quoting engine.

### Base URL
`/billing/quote-builder`

### 1. Calculate Quote
Calculates the total cost based on selected items.

*   **Endpoint:** `POST /calculate`
*   **Auth:** None (Public)

#### Request Body
```json
{
    "items": [
        {
            "product_id": 1,
            "quantity": 5
        },
        {
            "product_id": 2,
            "quantity": 1
        }
    ]
}
```

#### Response
```json
{
    "total": 700.00,
    "breakdown": [
        {
            "product": "Basic Support",
            "quantity": 5,
            "unit_price": 100.00,
            "total": 500.00
        },
        {
            "product": "Premium Support",
            "quantity": 1,
            "unit_price": 200.00,
            "total": 200.00
        }
    ]
}
```

### 2. Submit Quote
Generates a draft quote and prospect record.

*   **Endpoint:** `POST /submit`
*   **Auth:** None (Public)

#### Request Body
```json
{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "company_name": "Acme Corp",
    "phone": "555-0123",
    "items": [
        {
            "product_id": 1,
            "quantity": 5
        }
    ]
}
```

#### Response
```json
{
    "message": "Quote generated successfully!",
    "quote_token": "abc123xyz...",
    "redirect_url": "https://your-msp.com/billing/quote-builder/view/abc123xyz..."
}
```

### 3. View Quote
Retrieves details of a submitted quote.

*   **Endpoint:** `GET /view/{token}`
*   **Auth:** None (Public - Token based)

#### Response
Returns HTML view of the quote.

---

## Phase 11-17 View Endpoints

### Phase 11: Executive Dashboard & KPI Enhancements

#### GET /finance/executive-dashboard-enhanced
Enhanced executive dashboard with real-time KPIs and trend analysis.

**Auth:** `can:finance.admin`  
**Services:** `TrendAnalyticsService`, `BenchmarkingService`  
**Components:** `x-kpi-card`, `x-sparkline`, `x-trend-indicator`

**Data Requirements:**
```php
[
    'metrics' => [
        'mrr' => ['current' => 45230, 'previous' => 40100, 'trend' => [...]],
        'churn_rate' => ['current' => 3.2, 'previous' => 4.1],
        'gross_margin' => ['current' => 62.5, 'target' => 60.0],
        'ltv' => ['current' => 12450, 'previous' => 11800],
        'ar_aging' => ['current' => 15230, '30_day' => 8900, '60_day' => 4200, '90_day' => 2130],
    ],
    'at_risk_clients' => [...],
    'active_alerts' => [...],
    'benchmarks' => [...]
]
```

#### GET /executive/yoy-growth-dashboard
Year-over-year growth comparisons with AI insights.

**Auth:** `can:finance.admin`  
**Services:** `TrendAnalyticsService`  
**Components:** `x-trend-indicator`

**Data Requirements:**
```php
[
    'growth_metrics' => [
        'revenue_growth' => 23.5,
        'client_growth' => 15.2,
        'mrr_growth' => 18.7,
        'ticket_growth' => 12.3,
    ],
    'monthly_comparison' => [...],
    'quarterly_performance' => [...],
    'insights' => [...]
]
```

#### GET /executive/board-report-generator
Configurable one-page board reports with traffic lights.

**Auth:** `can:finance.admin`  
**Services:** `BoardReportService`

**POST /executive/board-report/generate**
```json
{
    "period": "current_quarter",
    "sections": ["revenue", "clients", "profitability", "cash_flow", "pipeline", "risks"],
    "comparison": "prior_year"
}
```

---

### Phase 12: Bulk Operations & Finance Admin Tools

#### GET /finance/bulk-overrides-wizard-enhanced
3-step wizard for mass price adjustments.

**Auth:** `can:finance.admin`  
**Services:** `BulkOverrideService`, `PricingEngineService`

**POST /finance/bulk-overrides/preview**
```json
{
    "target": "tier",
    "tier": "non_profit",
    "adjustment_type": "percentage",
    "value": 5.0,
    "effective_date": "2025-02-01"
}
```

**POST /finance/bulk-overrides/apply**
```json
{
    "operation_id": "uuid",
    "confirmation": "APPLY CHANGES"
}
```

#### GET /finance/invoice-batch-actions
Multi-select batch operations on invoices.

**Auth:** `can:finance.admin`

**POST /finance/invoices/batch-action**
```json
{
    "action": "mark_paid",
    "invoice_ids": [123, 124, 125],
    "payment_details": {
        "payment_method": "check",
        "payment_date": "2025-01-15",
        "reference": "CHK-001234"
    }
}
```

#### GET /finance/invoice-numbering-config
Custom invoice number format configuration.

**Auth:** `can:finance.admin`

**PUT /finance/settings/invoice-numbering**
```json
{
    "prefix": "INV",
    "separator": "-",
    "year_format": "YYYY",
    "padding": 4,
    "reset_period": "yearly"
}
```

#### GET /finance/invoice-template-customizer
WYSIWYG invoice template editor.

**Auth:** `can:finance.admin`

**POST /finance/settings/invoice-template**
```json
{
    "logo_url": "...",
    "primary_color": "#4F46E5",
    "secondary_color": "#818CF8",
    "style": "modern",
    "footer_text": "..."
}
```

---

### Phase 13: Accountant Role & Reconciliation Tools

#### GET /finance/payments-register
High-density payments register with multi-format export.

**Auth:** `can:finance.admin OR role:accountant`  
**Services:** `PaymentReconciliationService`  
**Components:** `x-ar-status-badge`

**GET /finance/payments-register/export**
Query params: `?format=excel&from=2025-01-01&to=2025-01-31`

---

### Phase 14: Technician Efficiency & Context Awareness

#### GET /field/my-performance
Technician performance dashboard with gamification.

**Auth:** `auth` (user-specific)  
**Services:** `TechnicianUtilizationService`  
**Components:** `x-contract-coverage-indicator`

**Data Requirements:**
```php
[
    'utilization' => ['current' => 78, 'target' => 80, 'trend' => [...]],
    'hours' => ['billable' => 120, 'non_billable' => 35],
    'metrics' => [
        'avg_resolution_time' => 2.5,
        'first_time_fix_rate' => 82,
        'streak_days' => 14,
    ],
    'recent_tickets' => [...]
]
```

#### GET /field/mileage-tracker
Mileage logging with GPS integration.

**Auth:** `auth`  
**Services:** `MileageCalculationService`

**POST /field/mileage/calculate**
```json
{
    "origin": "123 Main St, City, ST",
    "destination": "456 Oak Ave, Town, ST",
    "date": "2025-01-15"
}
```

**Response:**
```json
{
    "distance_miles": 9.47,
    "reimbursement": 6.20,
    "irs_rate": 0.655
}
```

---

### Phase 15: Client Portal Self-Service

#### GET /portal/auto-pay-wizard
3-step auto-pay enrollment wizard.

**Auth:** `billing.auth:{company}`

**POST /portal/{company}/auto-pay/enable**
```json
{
    "payment_method_id": "pm_...",
    "grace_period_days": 3,
    "retry_attempts": 2,
    "email_notifications": ["before_charge", "success", "failure"],
    "verification_code": "123456"
}
```

#### GET /portal/dispute-workflow
Invoice dispute tracking with SLA monitoring.

**Auth:** `billing.auth:{company}`

**POST /portal/{company}/disputes/{dispute}/update**
```json
{
    "message": "Additional documentation attached",
    "attachments": [...]
}
```

#### GET /portal/payment-history-download
Custom payment history export builder.

**Auth:** `billing.auth:{company}`  
**Services:** `PaymentHistoryExportService`

**POST /portal/{company}/payment-history/export**
```json
{
    "format": "excel",
    "date_range": "ytd",
    "fields": ["transaction_id", "invoice_number", "amount", "fee", "net_amount"],
    "group_by_month": true,
    "include_refunds": true
}
```

#### GET /portal/scheduled-payments
View and manage upcoming auto-pay charges.

**Auth:** `billing.auth:{company}`

**POST /portal/{company}/scheduled-payments/{payment}/skip**
```json
{
    "reason": "Insufficient funds expected"
}
```

---

### Phase 16: Sales Pipeline & Quote-to-Cash

#### GET /quotes/pipeline-kanban
Drag-and-drop sales pipeline board.

**Auth:** `can:sales.view`

**PUT /quotes/{quote}/stage**
```json
{
    "stage": "negotiating",
    "notes": "Discussed pricing with client"
}
```

**POST /quotes/{quote}/convert**
Converts quote to invoice and creates company/subscriptions.

---

## Component API

### Blade Component Props

#### x-kpi-card
```blade
<x-kpi-card
    :title="string"
    :value="string|float"
    :change="string"
    :trend="'up'|'down'|'flat'"
    :icon="string"
    :sparkline="array<float>"
    :href="string|null"
/>
```

#### x-sparkline
```blade
<x-sparkline
    :data="array<float>"
    :width="int"
    :height="int"
    :color="'primary'|'success'|'warning'|'danger'"
/>
```

#### x-trend-indicator
```blade
<x-trend-indicator
    :current="float"
    :previous="float"
    :format="'currency'|'percent'|'number'"
    :period="'mom'|'yoy'"
    :show-arrow="bool"
/>
```

#### x-ar-status-badge
```blade
<x-ar-status-badge
    :status="'current'|'30_days'|'60_days'|'90_plus'"
    :amount="float"
    :tooltip="bool"
/>
```

#### x-margin-indicator
```blade
<x-margin-indicator
    :margin="float"
    :floor="float"
    :size="'sm'|'md'|'lg'"
/>
```

---

## Integration Patterns

### Real-Time Updates

**Pattern:** Alpine.js polling with optimistic UI updates

```javascript
Alpine.data('dashboard', () => ({
    metrics: @json($metrics),
    
    init() {
        this.poll();
        setInterval(() => this.poll(), 30000); // 30s
    },
    
    async poll() {
        const response = await fetch('/api/finance/dashboard/metrics');
        this.metrics = await response.json();
    }
}));
```

### Form Validation

**Pattern:** Server-side validation with client feedback

```php
// Controller
$validated = $request->validate([
    'amount' => 'required|numeric|min:0|max:999999.99',
    'company_id' => 'required|exists:companies,id',
]);

// Blade
@error('amount')
    <p class="text-danger-600 text-sm mt-1">{{ $message }}</p>
@enderror
```

### Async Actions

**Pattern:** Queue jobs for long-running operations

```php
// Controller
BulkInvoiceExportJob::dispatch($filters);

return response()->json([
    'message' => 'Export queued. You will receive an email when ready.',
]);

// Job
class BulkInvoiceExportJob implements ShouldQueue
{
    public function handle()
    {
        $file = $this->exportService->generate($this->filters);
        Mail::to($this->user)->send(new ExportReady($file));
    }
}
```

---

## Authentication Patterns

### Finance Admin Routes
```php
Route::middleware(['auth', 'verified', 'can:finance.admin'])->group(function() {
    Route::get('/finance/executive-dashboard-enhanced', ...);
    Route::get('/finance/bulk-overrides-wizard-enhanced', ...);
    // ...
});
```

### Client Portal Routes
```php
Route::middleware(['auth', 'verified', 'billing.auth:{company}'])->group(function() {
    Route::get('/portal/{company}/auto-pay-wizard', ...);
    Route::get('/portal/{company}/dispute-workflow', ...);
    // ...
});
```

### Field Technician Routes
```php
Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/field/my-performance', ...);
    Route::get('/field/mileage-tracker', ...);
});
```

---

## Error Handling

### Standard Error Response
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "amount": ["The amount must be at least 0."],
        "company_id": ["The selected company is invalid."]
    }
}
```

### Success Response
```json
{
    "message": "Operation completed successfully",
    "data": {...}
}
```

---

## Rate Limiting

**Public Endpoints:** 60 requests/minute  
**Authenticated Endpoints:** 120 requests/minute  
**Payment Endpoints:** 10 requests/minute  

---

**Document Version:** 2.0  
**Last Updated:** 2025-12-28  
**Related:** ARCHITECTURE.md, APPLICATION_UX_UI_STANDARDS.md

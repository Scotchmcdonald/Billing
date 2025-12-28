# System Architecture Documentation
**FinOps Billing Module - Complete Implementation**  
**Last Updated:** 2025-12-28  
**Status:** Production-Ready (Phases 1-17 Complete)

---

## Table of Contents

1. [System Overview](#system-overview)
2. [Component Architecture](#component-architecture)
3. [Service Layer](#service-layer)
4. [Data Flow Patterns](#data-flow-patterns)
5. [Integration Points](#integration-points)
6. [Security Architecture](#security-architecture)
7. [Performance & Scalability](#performance--scalability)

---

## System Overview

### Architecture Philosophy: "Pilot's Cockpit"

The FinOps Billing Module follows a **mission-critical control tower** design philosophy where:
- **Clinical Precision**: Every UI element serves a specific purpose
- **State-Aware**: Real-time feedback without page refreshes
- **Dense yet Scannable**: High information density with clear visual hierarchy
- **Resilient**: Comprehensive error handling with actionable recovery paths

### Technology Stack

**Backend:**
- Laravel 11.0 (PHP 8.2+)
- MySQL/PostgreSQL (relational data)
- Redis (caching & queuing)
- Laravel Queue (async jobs)

**Frontend:**
- Blade Templates (server-rendered)
- Alpine.js (reactive components)
- Tailwind CSS (semantic theming)
- SortableJS (drag-and-drop)

**External Services:**
- Stripe (payment processing)
- Google Maps API (mileage tracking)
- QuickBooks/Xero (accounting sync)

---

## Component Architecture

### 1. View Layer (21 Production Views)

#### Executive Views (3)
```
/finance/executive-dashboard-enhanced.blade.php
├── Real-time KPI Cards (MRR, Churn, Margin, LTV, AR)
├── Sparkline Trend Charts (SVG, no dependencies)
├── At-Risk Client Identification
└── Alert System with Severity Levels

/executive/yoy-growth-dashboard.blade.php
├── 4 Primary Growth Metrics with YoY Comparison
├── Monthly Revenue Chart (Dual-Bar Visualization)
├── Quarterly Performance Breakdown
└── AI-Generated Insights Panel

/executive/board-report-generator.blade.php
├── Configurable One-Page PDF Reports
├── Traffic Light Indicators (Green/Yellow/Red)
├── 6 Customizable Sections
└── Period Selection & Comparison Options
```

#### Finance Admin Views (8)
```
/finance/bulk-overrides-wizard-enhanced.blade.php
├── 3-Step Guided Journey (Selection → Configuration → Preview)
├── Tier-Based / Client-Specific / All Clients Targeting
├── Before/After Comparison Table
├── Typed Confirmation ("APPLY CHANGES")
└── 24-Hour Rollback Capability

/finance/payments-register.blade.php
├── High-Density Transaction Table
├── Advanced Filtering (Date, Method, Status, Client)
├── Multi-Format Export (Excel, CSV, QuickBooks, Xero)
├── Reconciliation Status Indicators
└── Read-Only Badge for Accountant Role

/finance/invoice-batch-actions.blade.php
├── Multi-Select Interface with Select All
├── Batch Actions (Mark Paid, Send Reminder, Void, Export)
├── Selection Summary Bar
├── Action-Specific Confirmation Modals
└── Typed Confirmations for Safety

/finance/invoice-numbering-config.blade.php
├── Format Builder (Prefix + Separator + Padding)
├── Reset Period Options (Never, Yearly, Monthly)
├── Live Preview of Next 5 Invoice Numbers
└── Example Templates Library

/finance/invoice-template-customizer.blade.php
├── Logo Upload with Preview
├── Brand Color Picker (Primary + Secondary)
├── Template Style Selection (Modern/Classic/Bold)
├── Live WYSIWYG Preview Panel
└── PDF Export for Testing

/finance/audit-log.blade.php
/finance/pre-flight.blade.php
/finance/profitability.blade.php
```

#### Client Portal Views (5)
```
/portal/auto-pay-wizard.blade.php
├── 3-Step Configuration (Payment Method → Schedule → Confirm)
├── Visual Progress Stepper
├── State Preservation Between Steps
├── Grace Period & Retry Configuration
└── Email Verification & Safety Confirmations

/portal/dispute-workflow.blade.php
├── Summary Stats Dashboard
├── Progress Timeline (3 Stages: Submitted → Review → Resolved)
├── SLA Breach Indicators
├── Days-in-Stage Tracking
└── Advanced Filtering

/portal/payment-history-download.blade.php
├── Quick Export Options (Excel/CSV/PDF)
├── Custom Export Builder (Date Range, Fields, Grouping)
├── Real-Time Record Count Estimation
└── Recent Payments Summary Table

/portal/scheduled-payments.blade.php
├── Next Payment Summary with Countdown
├── Balance Warning Notifications
├── Dual View Modes (List/Calendar)
├── Urgency Indicators (Green/Yellow/Red)
└── Skip/Reschedule Actions

/portal/dashboard.blade.php
```

#### Field/Technician Views (2)
```
/field/my-performance.blade.php
├── Circular Utilization Gauge with Target Tracking
├── Billable vs Non-Billable Hours Visualization
├── Weekly Activity Bar Chart
├── First-Time Fix Rate vs Industry Benchmark
├── Streak Counter for Gamification
└── Recent Tickets with AR Status Badges

/field/mileage-tracker.blade.php
├── Summary Dashboard (Month Miles, Pending Reimbursement)
├── Google Maps Distance Calculation
├── Receipt Upload (Image/PDF)
├── IRS Rate Calculation ($0.655/mile)
├── Status Workflow (Pending → Approved → Paid)
└── Export to Excel
```

#### Sales/Quote Views (1)
```
/quotes/pipeline-kanban.blade.php
├── 6-Stage Drag-and-Drop Workflow
├── Real-Time Pipeline Metrics
├── Quote Cards with Margin Indicators
├── Days-in-Stage Tracking
├── Advanced Filtering System
├── Confirmation Modals for Stage Changes
└── SortableJS Integration
```

### 2. Component Library (9 Reusable Components)

#### Data Visualization Components
```blade
<x-kpi-card
    :title="'Monthly Recurring Revenue'"
    :value="'$45,230'"
    :change="'+12.3%'"
    :trend="'up'"
    :icon="'currency-dollar'"
    :sparkline="[100, 120, 115, 145, 130, 160]"
/>

<x-sparkline
    :data="[10, 15, 13, 17, 14, 19, 18, 22]"
    :width="100"
    :height="30"
    :color="'primary'"
/>

<x-trend-indicator
    :current="45230"
    :previous="40100"
    :format="'currency'"
    :period="'mom'"
/>
```

#### Status & State Components
```blade
<x-ar-status-badge
    :status="'current'"
    :amount="350.00"
    :tooltip="true"
/>

<x-contract-coverage-indicator
    :has_contract="true"
    :coverage_percent="85"
    :renewal_date="'2025-06-30'"
/>

<x-margin-indicator
    :margin="23.5"
    :floor="20.0"
    :size="'md'"
/>
```

#### Utility Components
```blade
<x-money-display
    :amount="1250.50"
    :currency="'USD'"
    :size="'lg'"
    :show-currency="true"
/>

<x-status-badge
    :status="'sent'"
    :label="'Sent'"
    :color="'warning'"
/>

<x-stripe-payment-element
    :client-secret="$clientSecret"
    :company="$company"
/>
```

---

## Service Layer

### Core Services (15+)

#### 1. Pricing & Calculation Services

**PricingEngineService**
```php
// Hierarchical price calculation: Override → Tier → Base
public function calculateEffectivePrice(Company $company, Product $product): PriceResult
public function validateMargin(Company $company, float price, float cost): ValidationResult
public function getPriceBreakdown(Company $company, Product $product): array
```

**ProrationCalculator**
```php
// Mid-cycle subscription changes
public function calculateProration(Subscription $sub, Carbon $date, int $newQty): ProrationResult
public function previewProration(...): ProrationResult // Read-only preview
```

#### 2. Invoice & Billing Services

**InvoiceGenerationService**
```php
// Monthly invoice automation
public function generateMonthlyInvoices(Carbon $billingDate): Collection<Invoice>
public function generateInvoiceForCompany(Company $company, Carbon $date): Invoice
public function finalizeDraftInvoice(Invoice $invoice): Invoice
```

**AnomalyDetectionService**
```php
// AI-powered invoice variance detection
public function analyzeInvoice(Invoice $invoice): AnomalyReport
public function flagForReview(Invoice $invoice, string $reason): void
```

**BulkOverrideService** (Phase 12)
```php
// Mass price changes with audit trail
public function applyBulkOverride(array $companyIds, array $config): BulkOperation
public function previewImpact(array $companyIds, array $config): array
public function rollback(BulkOperation $operation): bool
```

#### 3. Analytics & Reporting Services

**TrendAnalyticsService** (Phase 11)
```php
// KPI trend calculation
public function getMRRTrend(int $months = 12): array
public function getChurnRate(string $period): float
public function getGrossMarginTrend(int $months = 12): array
public function getARAgingTrend(int $months = 6): array
```

**BenchmarkingService** (Phase 11)
```php
// Industry comparison
public function compareToIndustry(Company $company): BenchmarkReport
public function getIndustryMetrics(string $vertical): array
public function calculateZScore(float $value, string $metric): float
```

**ForecastingService**
```php
// Predictive analytics
public function forecastMRR(int $monthsAhead): array
public function forecastChurn(): float
public function predictRevenue(Carbon $month): float
```

#### 4. Payment & Reconciliation Services

**PaymentReconciliationService** (Phase 13)
```php
// Accountant workflows
public function generatePaymentsRegister(Carbon $from, Carbon $to): Collection
public function exportToQuickBooks(Collection $payments): string
public function exportToXero(Collection $payments): string
public function markAsReconciled(Payment $payment): void
```

**PaymentHistoryExportService** (Phase 17)
```php
// Client portal exports
public function exportToExcel(Company $company, array $filters): string
public function exportToCSV(Company $company, array $filters): string
public function exportToPDF(Company $company, array $filters): string
```

#### 5. Field Operations Services

**TechnicianUtilizationService** (Phase 14)
```php
// Productivity tracking
public function calculateUtilization(User $tech, Carbon $period): float
public function getBillableHours(User $tech, Carbon $period): float
public function getFirstTimeFixRate(User $tech): float
public function getAverageResolutionTime(User $tech): float
```

**MileageCalculationService** (Phase 17)
```php
// Mileage tracking & reimbursement
public function calculateDistance(string $from, string $to): float
public function calculateReimbursement(float $miles, Carbon $date): float
public function getIRSRate(int $year): float
public function validateTrip(MileageEntry $entry): ValidationResult
```

#### 6. Report Generation Services

**BoardReportService** (Phase 17)
```php
// Executive reporting
public function generateReport(Company $company, array $config): Report
public function getTrafficLightStatus(float $value, float $target): string
public function exportToPDF(Report $report): string
```

**RevenueRecognitionService**
```php
// Accrual accounting
public function calculateMonthlyRevenue(Carbon $month): float
public function getDeferredRevenue(): float
public function getRecognitionSchedule(Invoice $invoice): array
```

---

## Data Flow Patterns

### Pattern 1: Invoice Generation Flow

```
1. Scheduler (Laravel Cron)
   ↓
2. GenerateMonthlyInvoicesJob
   ↓
3. InvoiceGenerationService->generateMonthlyInvoices()
   ├── Query active Subscriptions (due for billing)
   ├── Query unbilled BillableEntries
   └── For each Company:
       ├── PricingEngineService->calculateEffectivePrice()
       ├── Create draft Invoice with line items
       ├── AnomalyDetectionService->analyzeInvoice()
       └── If anomaly_score < 60:
           ├── Status = 'pending_review'
           └── Notify finance team
       └── Else:
           └── Status = 'draft'
   ↓
4. Pre-Flight Review UI (/finance/pre-flight)
   ├── Display invoices with anomaly badges
   ├── Finance Admin reviews & edits
   └── Click [Approve & Send]
   ↓
5. InvoiceGenerationService->finalizeDraftInvoice()
   ├── Generate invoice_number
   ├── Status = 'sent'
   ├── Send to Stripe (if company has stripe_id)
   ├── Fire InvoiceFinalized event
   └── SendInvoiceEmail job queued
   ↓
6. Client receives email with [Pay Now] link
```

### Pattern 2: Real-Time Dashboard Updates

```
1. User loads /finance/executive-dashboard-enhanced
   ↓
2. Controller fetches metrics:
   ├── TrendAnalyticsService->getMRRTrend()
   ├── TrendAnalyticsService->getChurnRate()
   ├── BenchmarkingService->compareToIndustry()
   └── Query at-risk clients (AR > 60 days)
   ↓
3. Blade renders with Alpine.js data
   ↓
4. Alpine.js setInterval (30s):
   ├── Fetch /api/finance/dashboard/metrics
   ├── Update reactive data properties
   └── Sparklines re-render
```

### Pattern 3: Bulk Price Override Workflow

```
1. Finance Admin opens /finance/bulk-overrides-wizard-enhanced
   ↓
2. Step 1: Target Selection
   ├── Select: All Clients / Tier-Based / Specific Clients
   └── [Next]
   ↓
3. Step 2: Configuration
   ├── Adjustment Type: Percentage / Flat Amount
   ├── Value: +5%
   ├── Effective Date: Immediate / Scheduled
   └── [Preview]
   ↓
4. Step 3: Preview & Confirm
   ├── BulkOverrideService->previewImpact()
   ├── Display Before/After table
   ├── Calculate total impact
   ├── Type confirmation: "APPLY CHANGES"
   └── [Apply]
   ↓
5. BulkOverrideService->applyBulkOverride()
   ├── Create BulkOperation record
   ├── For each company:
   │   ├── Create PriceOverride record
   │   ├── Set approved_by = Auth::id()
   │   └── Log to BillingLog
   ├── Mark operation as complete
   └── Notify finance team
```

### Pattern 4: Auto-Pay Enrollment (Client Portal)

```
1. Client opens /portal/auto-pay-wizard
   ↓
2. Step 1: Payment Method
   ├── Select existing method OR
   ├── Add new card/ACH via Stripe Elements
   └── [Next]
   ↓
3. Step 2: Schedule Configuration
   ├── Grace Period: 3 days
   ├── Retry Attempts: 2
   ├── Email Notifications: ☑ Before charge, ☑ Success, ☑ Failure
   └── [Next]
   ↓
4. Step 3: Confirmation
   ├── Display: Summary of settings
   ├── Warning: "Your card will be charged automatically"
   ├── Email verification code sent
   ├── Enter verification code
   └── [Enable Auto-Pay]
   ↓
5. Backend:
   ├── Update Company->settings['auto_pay_enabled'] = true
   ├## Update Company->settings['auto_pay_config'] = [...]
   ├── Set default Stripe payment method
   ├── Create audit log entry
   └── Queue confirmation email
```

---

## Integration Points

### 1. Stripe Integration

**Payment Processing:**
```php
// Setup Intent Creation (Portal)
POST /portal/{company}/payment-methods/setup-intent
→ Stripe::setupIntent()
→ Return client_secret

// Payment Method Attachment
POST /portal/{company}/payment-methods/attach
→ Stripe::attachPaymentMethod($pmId, $stripeCustomerId)
→ Update Company->pm_type, pm_last_four

// Invoice Finalization
InvoiceFinalized Event
→ Stripe::invoice()->create([...])
→ Update Invoice->stripe_invoice_id
```

**Webhooks:**
```php
POST /webhooks/stripe
→ Signature verification (HMAC)
→ Events handled:
   - invoice.payment_succeeded → Update Invoice status
   - invoice.payment_failed → Trigger dunning
   - customer.subscription.deleted → Mark subscription inactive
```

### 2. Google Maps API (Mileage Tracking)

**Distance Calculation:**
```php
// Frontend: User enters addresses
{origin: "123 Main St, City, ST", destination: "456 Oak Ave, Town, ST"}

// Backend: MileageCalculationService
→ Google Maps Distance Matrix API
→ Response: {distance: {value: 15234}} // meters
→ Convert to miles: 15234 / 1609.34 = 9.47 miles
→ Calculate reimbursement: 9.47 * $0.655 = $6.20
```

### 3. QuickBooks/Xero Sync

**One-Way Sync (MSP → Accounting):**
```php
InvoiceFinalized Event
→ Queue: SyncToQuickBooksJob

Job execution:
1. Map Invoice → QB Invoice structure
2. Map InvoiceLineItems → QB Lines
3. Map Company → QB Customer (create if not exists)
4. API: POST /v3/company/{realmId}/invoice
5. Store QB invoice ID in metadata
6. Log sync result to BillingLog
```

### 4. RMM Webhooks (Usage-Based Billing)

**Device Count Updates:**
```php
POST /webhooks/rmm/device-count
{
    "company_id": 123,
    "device_count": 47,
    "timestamp": "2025-01-15T10:30:00Z",
    "device_list": [...]
}

→ RmmWebhookController
→ Find Subscription (product.category = 'rmm_monitoring')
→ Compare new count to current quantity
→ If changed:
    - Calculate proration
    - Update Subscription->quantity
    - Flag for Pre-Flight Review
    - Notify finance team
```

---

## Security Architecture

### Authentication & Authorization

**Middleware Stack:**
```php
// Finance Admin Routes
Route::middleware(['auth', 'verified', 'can:finance.admin'])

// Client Portal Routes
Route::middleware(['auth', 'verified', 'billing.auth:{company}'])

// Technician Routes
Route::middleware(['auth', 'verified'])

// Public Routes (Quote Builder)
Route::middleware(['throttle:60,1']) // Rate limiting
```

**Role-Based Access Control:**
```
Roles:
- super_admin: Full system access
- finance.admin: Finance operations, reporting
- billing.admin: Company-specific billing management
- billing.payer: Payment-only access (Client Admin)
- tech: Field operations, time entry
- accountant: Read-only financial access

Permissions enforced via:
- Gates: Gate::allows('finance.admin')
- Policies: InvoicePolicy->update($user, $invoice)
- Middleware: can:finance.admin
```

### Data Protection

**Encryption:**
```php
// Sensitive fields encrypted at rest
Company->billing_address (JSON, encrypted)
Payment->payment_reference (e.g., check numbers)
User->stripe_pm_id

// Encryption handled by Laravel's encryption
Crypt::encryptString($data)
Crypt::decryptString($encrypted)
```

**Audit Logging:**
```php
// All financial actions logged
BillingLog::create([
    'user_id' => Auth::id(),
    'company_id' => $company->id,
    'action' => 'price_override_created',
    'description' => 'Created override for Product X',
    'payload' => json_encode($override),
    'ip_address' => request()->ip(),
]);
```

**Input Validation:**
```php
// All user input validated
$validated = $request->validate([
    'amount' => 'required|numeric|min:0|max:999999.99',
    'company_id' => 'required|exists:companies,id',
    'payment_method' => 'required|in:card,ach,check,wire',
]);
```

### Payment Security

**PCI Compliance:**
- No card data stored on server
- Stripe Elements for card input
- Tokenization before server submission
- Stripe PCI-DSS Level 1 compliance

**Fraud Prevention:**
- Rate limiting on payment endpoints
- Stripe Radar for fraud detection
- IP address logging
- Failed payment attempt tracking

---

## Performance & Scalability

### Caching Strategy

**Query Caching:**
```php
// Pricing calculations (5 min TTL)
Cache::remember("price_{$company->id}_{$product->id}", 300, function() {
    return $this->pricingEngine->calculateEffectivePrice(...);
});

// Dashboard metrics (1 min TTL)
Cache::remember("dashboard_metrics_{$company->id}", 60, function() {
    return $this->trendAnalytics->getAllMetrics(...);
});

// Industry benchmarks (1 hour TTL)
Cache::remember("benchmarks_{$vertical}", 3600, function() {
    return $this->benchmarking->getIndustryMetrics(...);
});
```

**View Caching:**
```blade
@cache('invoice_pdf_' . $invoice->id, 3600)
    {{-- Render complex PDF layout --}}
@endcache
```

### Queue Management

**Job Prioritization:**
```php
// High priority: Payment processing
ProcessPaymentJob::dispatch($invoice)->onQueue('payments');

// Medium priority: Invoice generation
GenerateMonthlyInvoicesJob::dispatch()->onQueue('billing');

// Low priority: Analytics
CalculateBenchmarksJob::dispatch()->onQueue('analytics');
```

**Queue Configuration:**
```php
// config/queue.php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
],
```

### Database Optimization

**Indexes:**
```sql
-- Critical indexes for performance
CREATE INDEX idx_invoices_company_status ON invoices(company_id, status);
CREATE INDEX idx_invoices_due_date ON invoices(due_date);
CREATE INDEX idx_billable_entries_unbilled ON billable_entries(company_id, is_billable, invoice_line_item_id);
CREATE INDEX idx_subscriptions_next_billing ON subscriptions(next_billing_date, is_active);
```

**Query Optimization:**
```php
// Eager loading to prevent N+1
$invoices = Invoice::with([
    'company',
    'lineItems.product',
    'payments',
])->where('status', 'sent')->get();

// Chunking for large datasets
Invoice::where('status', 'draft')
    ->chunk(100, function($invoices) {
        foreach ($invoices as $invoice) {
            $this->processInvoice($invoice);
        }
    });
```

### Scalability Considerations

**Horizontal Scaling:**
- Stateless application design (sessions in Redis)
- Queue workers can scale independently
- Database read replicas for reporting queries
- CDN for static assets

**Vertical Optimization:**
- Optimized SQL queries (EXPLAIN analysis)
- Redis caching for hot data
- Lazy loading for components
- Pagination for large datasets

---

## Monitoring & Observability

### Logging

**Application Logs:**
```php
// Financial operations
Log::channel('billing')->info('Invoice finalized', [
    'invoice_id' => $invoice->id,
    'company_id' => $invoice->company_id,
    'total' => $invoice->total,
]);

// Payment processing
Log::channel('payments')->info('Payment recorded', [
    'payment_id' => $payment->id,
    'amount' => $payment->amount,
    'method' => $payment->payment_method,
]);
```

### Metrics

**Key Performance Indicators:**
```
- Invoice generation time (avg, p95, p99)
- Payment processing success rate
- API response times
- Cache hit ratio
- Queue processing rate
- Failed job count
```

### Alerting

**Critical Alerts:**
- Payment gateway downtime
- Invoice generation failures
- Queue backlog > 1000 jobs
- Disk space < 20%
- Memory usage > 90%

---

## Deployment Architecture

### Environment Configuration

**Production:**
```env
APP_ENV=production
APP_DEBUG=false
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
```

**Staging:**
```env
APP_ENV=staging
APP_DEBUG=true
QUEUE_CONNECTION=redis
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

### CI/CD Pipeline

```
1. Push to GitHub
2. Run Tests (PHPUnit, Laravel Dusk)
3. Static Analysis (PHPStan, Larastan)
4. Security Scan (OWASP, npm audit)
5. Build Assets (npm run production)
6. Deploy to Staging
7. Smoke Tests
8. Deploy to Production (blue-green)
9. Health Check
10. Rollback on failure
```

---

## Future Enhancements

### Phase 18-23 (Planned)

**Phase 18:** Multi-Currency Support
**Phase 19:** Client Communication Hub
**Phase 20:** Client Success Scoring
**Phase 21:** Advanced Reporting Engine
**Phase 22:** Integration Marketplace (Zapier, Make)
**Phase 23:** Mobile Apps (Technician & Executive)

See `FUTURE_WORK_BACKLOG.md` for detailed specifications.

---

## Document Version

**Version:** 1.0  
**Author:** FinOps Architecture Team  
**Last Review:** 2025-12-28  
**Next Review:** Q2 2026  

**Related Documents:**
- APPLICATION_UX_UI_STANDARDS.md
- PROJECT_CONTEXT.md
- API_REFERENCE.md
- FUTURE_WORK_BACKLOG.md

# Future Work Backlog & Roadmap
**Version:** 1.0  
**Date:** 2025-12-28  
**Status:** Planning Document  
**Purpose:** Detailed workload planning for newly identified user stories

---

## Executive Summary

### Current State
- ✅ **Phases 1-16 Complete:** 100% of originally scoped features implemented
- ✅ **Production Ready:** All core billing workflows operational
- ✅ **Test Coverage:** 87% with 209 passing tests
- ✅ **UX Standards:** Full "Pilot's Cockpit" compliance

### Future Work Summary

| Priority | Phases | Stories | Effort Range | Business Impact |
|----------|--------|---------|--------------|-----------------|
| **HIGH** | 2 phases | 6 stories | 92-128 hours | Revenue/Retention |
| **MEDIUM** | 4 phases | 13 stories | 152-216 hours | Efficiency/Insight |
| **LOW** | 3 phases | 11 stories | 128-180 hours | Innovation/Scale |
| **TOTAL** | **9 phases** | **30 stories** | **372-524 hours** | **47-66 weeks @ 8hrs/wk** |

---

## High Priority Work (Wave 1)

### Phase 19: Client Communication Hub 🔥
**Business Value:** Reduces collections effort by 40%, improves client satisfaction  
**Estimated Effort:** 28-36 hours  
**Target Completion:** Q1 2025  
**Dependencies:** None (standalone)

#### Story 19.1: Unified Communication Timeline
**Effort:** 12-16 hours  
**Persona:** Finance Admin  
**User Story:** See all client communication in one timeline (emails, calls, portal messages) for full context before collections calls.

**Technical Specification:**
```php
// New Model
class CommunicationLog extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'type', // email, call, portal_message, sms
        'direction', // inbound, outbound
        'subject',
        'content',
        'metadata', // JSON: email headers, call duration, etc.
        'communicated_at'
    ];
    
    protected $casts = [
        'metadata' => 'json',
        'communicated_at' => 'datetime'
    ];
}

// Service
class CommunicationService
{
    public function logEmail($companyId, $direction, $subject, $content, $metadata)
    {
        return CommunicationLog::create([...]);
    }
    
    public function getTimelineForCompany($companyId, $limit = 50)
    {
        return CommunicationLog::where('company_id', $companyId)
            ->orderBy('communicated_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
```

**UI Components:**
- `x-communication-timeline` - Vertical timeline with event icons
- `x-communication-entry` - Expandable entry with full content
- Filter controls (type, date range, user)
- Export to PDF button

**Acceptance Criteria:**
- [ ] All dunning emails automatically logged
- [ ] Portal messages logged in real-time
- [ ] Manual call logging with notes
- [ ] Timeline loads in < 2 seconds for 500+ entries
- [ ] Export includes attachments (if any)

#### Story 19.2: Template Management for Client Communications
**Effort:** 10-12 hours  
**Persona:** Finance Admin  

**Technical Specification:**
```php
// New Model
class EmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'category', // collections, welcome, updates, custom
        'subject',
        'body', // HTML with merge fields
        'merge_fields', // JSON array of available fields
        'is_active'
    ];
    
    protected $casts = [
        'merge_fields' => 'array'
    ];
}

// Service
class TemplateService
{
    public function render($templateId, $mergeData)
    {
        $template = EmailTemplate::findOrFail($templateId);
        $subject = $this->replaceMergeFields($template->subject, $mergeData);
        $body = $this->replaceMergeFields($template->body, $mergeData);
        
        return compact('subject', 'body');
    }
    
    private function replaceMergeFields($content, $data)
    {
        foreach ($data as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
        }
        return $content;
    }
}
```

**UI Views:**
- `/billing/finance/templates` - Template library
- `/billing/finance/templates/create` - WYSIWYG editor
- Template preview modal with sample data

**Acceptance Criteria:**
- [ ] CRUD operations for templates
- [ ] WYSIWYG editor (TinyMCE or Quill)
- [ ] Merge field dropdown for easy insertion
- [ ] Preview with sample data before send
- [ ] Template usage analytics (send count, open rate)

#### Story 19.3: Automated Collections Workflow
**Effort:** 6-8 hours  
**Persona:** Finance Admin  

**Technical Specification:**
```php
// New Model
class CollectionsWorkflow extends Model
{
    protected $fillable = [
        'name',
        'stages', // JSON array of workflow stages
        'is_active'
    ];
    
    protected $casts = [
        'stages' => 'array'
    ];
}

// Example stages structure
$stages = [
    ['day' => 3, 'action' => 'email', 'template_id' => 1],
    ['day' => 7, 'action' => 'email', 'template_id' => 2],
    ['day' => 14, 'action' => 'call_task', 'assigned_to' => 'finance_manager'],
    ['day' => 30, 'action' => 'pause_services']
];

// Job
class ProcessCollectionsWorkflowJob implements ShouldQueue
{
    public function handle()
    {
        $overdueInvoices = Invoice::overdue()->get();
        
        foreach ($overdueInvoices as $invoice) {
            $workflow = CollectionsWorkflow::active()->first();
            $daysOverdue = $invoice->due_date->diffInDays(now());
            
            foreach ($workflow->stages as $stage) {
                if ($daysOverdue == $stage['day']) {
                    $this->executeStageAction($invoice, $stage);
                }
            }
        }
    }
}
```

**Acceptance Criteria:**
- [ ] Workflow builder interface (drag-and-drop stages)
- [ ] Per-stage configuration (timing, action, template)
- [ ] Workflow pause/resume per invoice
- [ ] Effectiveness dashboard (collection rate per stage)
- [ ] A/B testing support for workflow variants

---

### Story T.1: Offline Time Entry 🔥
**Effort:** 16-24 hours  
**Priority:** HIGH (critical for field technicians)  
**Persona:** Technician  
**User Story:** Log time entries offline so that I can continue working in locations without internet.

**Technical Specification:**
```javascript
// Service Worker for PWA
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open('billing-v1').then((cache) => {
            return cache.addAll([
                '/field/work-order',
                '/field/timesheet',
                '/css/app.css',
                '/js/app.js'
            ]);
        })
    );
});

// IndexedDB for offline storage
const DB_NAME = 'BillingOffline';
const STORE_NAME = 'timeEntries';

class OfflineTimeEntryService {
    async saveOffline(entry) {
        const db = await this.openDB();
        const tx = db.transaction(STORE_NAME, 'readwrite');
        const store = tx.objectStore(STORE_NAME);
        
        entry.offline_id = Date.now();
        entry.synced = false;
        
        await store.add(entry);
        return entry;
    }
    
    async syncPendingEntries() {
        const db = await this.openDB();
        const tx = db.transaction(STORE_NAME, 'readonly');
        const store = tx.objectStore(STORE_NAME);
        const entries = await store.getAll();
        
        for (const entry of entries.filter(e => !e.synced)) {
            try {
                const response = await fetch('/api/time-entries', {
                    method: 'POST',
                    body: JSON.stringify(entry),
                    headers: { 'Content-Type': 'application/json' }
                });
                
                if (response.ok) {
                    await this.markAsSynced(entry.offline_id);
                }
            } catch (error) {
                console.error('Sync failed for entry', entry.offline_id, error);
            }
        }
    }
}
```

**UI Features:**
- Offline indicator in header (green = online, yellow = offline)
- Visual badge on unsynced entries
- Manual sync button
- Conflict resolution UI if server data changed
- Sync status notifications

**Acceptance Criteria:**
- [ ] App loads completely offline (PWA manifest)
- [ ] Time entries save to IndexedDB when offline
- [ ] Automatic sync when connection restored
- [ ] Visual indicators for sync status
- [ ] Conflict resolution for modified entries
- [ ] Works on iOS Safari and Android Chrome

---

### Story CP.1: Invoice Dispute Workflow Tracking (Enhancement) 🔥
**Effort:** 8-12 hours  
**Priority:** HIGH  
**Persona:** Client Admin  
**User Story:** See the status of my dispute (submitted, under review, resolved) so that I know what's happening.

**Technical Specification:**
```php
// Enhance existing DisputeService
class DisputeService
{
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_AWAITING_INFO = 'awaiting_info';
    const STATUS_RESOLVED_ACCEPTED = 'resolved_accepted';
    const STATUS_RESOLVED_REJECTED = 'resolved_rejected';
    
    public function transitionStatus($disputeId, $newStatus, $note)
    {
        $dispute = Dispute::findOrFail($disputeId);
        $dispute->status = $newStatus;
        $dispute->save();
        
        $dispute->statusHistory()->create([
            'status' => $newStatus,
            'note' => $note,
            'user_id' => auth()->id(),
            'transitioned_at' => now()
        ]);
        
        // Send notification to client
        Mail::to($dispute->company->billing_email)
            ->send(new DisputeStatusChanged($dispute));
    }
}

// New Model for history
class DisputeStatusHistory extends Model
{
    protected $fillable = ['dispute_id', 'status', 'note', 'user_id', 'transitioned_at'];
    protected $casts = ['transitioned_at' => 'datetime'];
}
```

**UI Components:**
- `x-dispute-status-timeline` - Vertical stepper showing status progression
- Status badges with color coding
- Expected resolution time display
- Client-visible notes section
- Email notification preferences

**Acceptance Criteria:**
- [ ] Status timeline visible in client portal
- [ ] Email notifications on status changes
- [ ] Expected resolution time displayed (SLA-based)
- [ ] Clients can add additional info when requested
- [ ] Finance admin can add internal notes (not visible to client)

---

## Medium Priority Work (Wave 2)

### Phase 20: Client Success Scoring
**Business Value:** Reduces churn by 15-20% through proactive intervention  
**Estimated Effort:** 20-28 hours  
**Target Completion:** Q2 2025  

#### Story 20.1: Client Health Score Dashboard
**Effort:** 12-16 hours  
**Persona:** Executive  

**Technical Specification:**
```php
class ClientHealthScoreService
{
    public function calculateHealthScore($companyId)
    {
        $company = Company::with('invoices', 'tickets', 'subscriptions')->find($companyId);
        
        $scores = [
            'payment_timeliness' => $this->scorePaymentTimeliness($company) * 0.30,
            'support_satisfaction' => $this->scoreSupportSatisfaction($company) * 0.20,
            'usage_growth' => $this->scoreUsageGrowth($company) * 0.20,
            'contract_utilization' => $this->scoreContractUtilization($company) * 0.15,
            'engagement' => $this->scoreEngagement($company) * 0.15
        ];
        
        $overallScore = array_sum($scores);
        $trend = $this->calculateTrend($companyId);
        
        return [
            'overall_score' => round($overallScore, 1),
            'component_scores' => $scores,
            'trend' => $trend,
            'status' => $this->determineStatus($overallScore),
            'risk_factors' => $this->identifyRiskFactors($scores)
        ];
    }
    
    private function scorePaymentTimeliness($company)
    {
        $recentInvoices = $company->invoices()->whereBetween('created_at', [now()->subMonths(6), now()])->get();
        $onTimePayments = $recentInvoices->filter(fn($inv) => $inv->paid_at <= $inv->due_date)->count();
        $totalInvoices = $recentInvoices->count();
        
        return $totalInvoices > 0 ? ($onTimePayments / $totalInvoices) * 100 : 100;
    }
    
    private function determineStatus($score)
    {
        if ($score >= 80) return 'healthy';
        if ($score >= 60) return 'at_risk';
        return 'critical';
    }
}
```

**UI Components:**
- `/billing/executive/client-health` - Dashboard with client list
- `x-health-score-gauge` - Circular gauge (0-100)
- `x-health-trend-indicator` - Up/down trend arrow
- `x-risk-factor-list` - Expandable list of issues
- Sortable table with health score column
- Filter by status (healthy, at-risk, critical)

**Acceptance Criteria:**
- [ ] Health scores calculated daily (scheduled job)
- [ ] Historical tracking (12-month trend)
- [ ] Automated alerts for score drops > 15 points
- [ ] Drill-down view showing component scores
- [ ] Suggested intervention actions per risk factor
- [ ] Export capability for sales/success teams

#### Story 20.2: Churn Prediction Model
**Effort:** 8-12 hours  
**Persona:** Executive  

**Technical Specification:**
```python
# ML Model Training Script (Python/Jupyter Notebook)
import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split

# Load historical data
data = pd.read_sql("""
    SELECT 
        company_id,
        payment_delay_days,
        support_ticket_volume,
        usage_decline_percent,
        contract_months_remaining,
        last_login_days_ago,
        churned
    FROM client_churn_analysis
""", connection)

# Train model
X = data.drop(['company_id', 'churned'], axis=1)
y = data['churned']
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2)

model = RandomForestClassifier(n_estimators=100)
model.fit(X_train, y_train)

# Export model for PHP integration
import pickle
with open('churn_model.pkl', 'wb') as f:
    pickle.dump(model, f)
```

```php
// PHP Integration (using Python subprocess)
class ChurnPredictionService
{
    public function predictChurnRisk($companyId)
    {
        $features = $this->extractFeatures($companyId);
        
        $pythonScript = base_path('ml/predict_churn.py');
        $input = json_encode($features);
        
        $output = shell_exec("python3 {$pythonScript} '{$input}'");
        $prediction = json_decode($output, true);
        
        return [
            'churn_probability' => $prediction['probability'],
            'risk_level' => $this->determineRiskLevel($prediction['probability']),
            'contributing_factors' => $prediction['feature_importance'],
            'suggested_actions' => $this->suggestRetentionActions($prediction)
        ];
    }
    
    private function suggestRetentionActions($prediction)
    {
        $actions = [];
        
        if ($prediction['payment_delay_score'] > 0.3) {
            $actions[] = [
                'type' => 'financial',
                'action' => 'Offer payment plan or temporary discount',
                'priority' => 'high'
            ];
        }
        
        if ($prediction['usage_decline_score'] > 0.3) {
            $actions[] = [
                'type' => 'engagement',
                'action' => 'Schedule QBR to discuss underutilization',
                'priority' => 'medium'
            ];
        }
        
        return $actions;
    }
}
```

**UI Components:**
- Churn risk badge on client cards
- `x-churn-risk-indicator` - Color-coded probability bar
- Intervention action checklist
- Success tracking (did intervention prevent churn?)

**Acceptance Criteria:**
- [ ] Model trained on ≥ 100 historical churned clients
- [ ] Prediction accuracy ≥ 75% on test set
- [ ] Predictions updated weekly
- [ ] Automated email alerts for high-risk clients (> 60%)
- [ ] Intervention effectiveness tracking
- [ ] Model retraining quarterly with new data

---

### Phase 17: Multi-Currency Support
**Business Value:** Enables international expansion  
**Estimated Effort:** 24-32 hours  
**Target Completion:** Q2 2025  

#### Story 17.1: Multi-Currency Invoice Generation
**Effort:** 16-20 hours  

**Technical Specification:**
```php
// Migration
Schema::table('companies', function (Blueprint $table) {
    $table->string('currency_code', 3)->default('USD'); // ISO 4217
});

Schema::create('exchange_rates', function (Blueprint $table) {
    $table->id();
    $table->string('from_currency', 3);
    $table->string('to_currency', 3);
    $table->decimal('rate', 12, 6);
    $table->date('effective_date');
    $table->string('source')->default('exchangerate-api.io');
    $table->timestamps();
    
    $table->unique(['from_currency', 'to_currency', 'effective_date']);
});

// Service
class CurrencyService
{
    private $baseCurrency = 'USD';
    
    public function convertAmount($amount, $fromCurrency, $toCurrency, $date = null)
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }
        
        $rate = $this->getExchangeRate($fromCurrency, $toCurrency, $date ?? today());
        return round($amount * $rate, 2);
    }
    
    public function getExchangeRate($from, $to, $date)
    {
        // Try database first
        $cachedRate = ExchangeRate::where('from_currency', $from)
            ->where('to_currency', $to)
            ->where('effective_date', $date)
            ->first();
            
        if ($cachedRate) {
            return $cachedRate->rate;
        }
        
        // Fetch from API
        $rate = $this->fetchRateFromAPI($from, $to);
        
        // Cache it
        ExchangeRate::create([
            'from_currency' => $from,
            'to_currency' => $to,
            'rate' => $rate,
            'effective_date' => $date
        ]);
        
        return $rate;
    }
    
    private function fetchRateFromAPI($from, $to)
    {
        $apiKey = config('services.exchangerate.api_key');
        $response = Http::get("https://v6.exchangerate-api.com/v6/{$apiKey}/pair/{$from}/{$to}");
        
        if ($response->successful()) {
            return $response->json()['conversion_rate'];
        }
        
        throw new Exception("Failed to fetch exchange rate for {$from}/{$to}");
    }
}

// Enhance InvoiceService
class InvoiceService
{
    public function generateInvoice($companyId, $lineItems)
    {
        $company = Company::find($companyId);
        $clientCurrency = $company->currency_code;
        $baseCurrency = config('billing.base_currency', 'USD');
        
        // Calculate in base currency
        $totalBase = collect($lineItems)->sum('amount');
        
        // Convert to client currency if different
        if ($clientCurrency !== $baseCurrency) {
            $totalClient = app(CurrencyService::class)->convertAmount(
                $totalBase,
                $baseCurrency,
                $clientCurrency
            );
            
            $exchangeRate = $totalClient / $totalBase;
        } else {
            $totalClient = $totalBase;
            $exchangeRate = 1.0;
        }
        
        return Invoice::create([
            'company_id' => $companyId,
            'total_base' => $totalBase,
            'total_client' => $totalClient,
            'currency_code' => $clientCurrency,
            'exchange_rate' => $exchangeRate,
            'exchange_rate_date' => today(),
            // ... other fields
        ]);
    }
}
```

**UI Changes:**
- Currency selector on company profile
- Dual currency display on invoices (client currency + base currency)
- Exchange rate disclaimer on invoice PDF
- Currency symbol formatting per locale

**Acceptance Criteria:**
- [ ] Support for 20+ major currencies
- [ ] Exchange rates fetched daily and cached
- [ ] Invoice shows both client and base currency
- [ ] Historical exchange rate locked at invoice date
- [ ] Audit trail of all conversions
- [ ] Compliance with GAAP/IFRS for forex reporting

#### Story 17.2: Currency Conversion Reporting
**Effort:** 8-12 hours  

**Technical Specification:**
```php
class MultiCurrencyReportService
{
    public function generateRevenueReport($startDate, $endDate, $reportCurrency = 'USD')
    {
        $invoices = Invoice::whereBetween('created_at', [$startDate, $endDate])->get();
        
        $totalRevenue = 0;
        $forexGainLoss = 0;
        
        foreach ($invoices as $invoice) {
            // Convert to report currency
            $revenueInReportCurrency = app(CurrencyService::class)->convertAmount(
                $invoice->total_client,
                $invoice->currency_code,
                $reportCurrency,
                $invoice->created_at
            );
            
            $totalRevenue += $revenueInReportCurrency;
            
            // Calculate forex gain/loss if payment date differs
            if ($invoice->paid_at) {
                $revenueAtPayment = app(CurrencyService::class)->convertAmount(
                    $invoice->total_client,
                    $invoice->currency_code,
                    $reportCurrency,
                    $invoice->paid_at
                );
                
                $forexGainLoss += ($revenueAtPayment - $revenueInReportCurrency);
            }
        }
        
        return [
            'total_revenue' => $totalRevenue,
            'forex_gain_loss' => $forexGainLoss,
            'net_revenue' => $totalRevenue + $forexGainLoss,
            'currency' => $reportCurrency
        ];
    }
}
```

**Acceptance Criteria:**
- [ ] All reports support currency selection
- [ ] Forex gain/loss tracked separately
- [ ] YTD forex variance reporting
- [ ] Export to accounting systems preserves currency data

---

### Phase 18: Advanced Client Segmentation
**Business Value:** Enables tiered pricing strategy  
**Estimated Effort:** 16-24 hours  
**Target Completion:** Q2 2025  

#### Story 18.1: Client Tier Management
**Effort:** 10-14 hours  

**Technical Specification:**
```php
// Migration
Schema::table('companies', function (Blueprint $table) {
    $table->enum('tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
    $table->timestamp('tier_updated_at')->nullable();
});

// Enhanced PricingEngineService
class PricingEngineService
{
    public function calculatePrice($productId, $companyId, $quantity = 1)
    {
        $product = Product::find($productId);
        $company = Company::find($companyId);
        
        // Start with base price
        $price = $product->base_price;
        
        // Apply tier discount
        $tierDiscount = $this->getTierDiscount($company->tier);
        $price = $price * (1 - $tierDiscount);
        
        // Apply client-specific overrides (highest priority)
        $override = Override::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->first();
            
        if ($override) {
            $price = $override->custom_price;
        }
        
        return $price * $quantity;
    }
    
    private function getTierDiscount($tier)
    {
        return match($tier) {
            'bronze' => 0.00,    // 0% discount
            'silver' => 0.05,    // 5% discount
            'gold' => 0.10,      // 10% discount
            'platinum' => 0.15,  // 15% discount
        };
    }
}

// Service for tier management
class ClientTierService
{
    public function autoPromoteTier($companyId)
    {
        $company = Company::find($companyId);
        
        // Promotion criteria
        $mrr = $this->calculateMRR($companyId);
        $tenure = $company->created_at->diffInMonths(now());
        $paymentScore = $this->getPaymentScore($companyId);
        
        $recommendedTier = $this->determineTier($mrr, $tenure, $paymentScore);
        
        if ($this->shouldPromote($company->tier, $recommendedTier)) {
            $this->promoteTier($companyId, $recommendedTier, 'Auto-promoted based on performance');
        }
    }
    
    private function determineTier($mrr, $tenure, $paymentScore)
    {
        if ($mrr >= 5000 && $tenure >= 24 && $paymentScore >= 95) return 'platinum';
        if ($mrr >= 2500 && $tenure >= 12 && $paymentScore >= 90) return 'gold';
        if ($mrr >= 1000 && $tenure >= 6 && $paymentScore >= 85) return 'silver';
        return 'bronze';
    }
}
```

**UI Components:**
- `/billing/admin/client-tiers` - Tier management dashboard
- `x-tier-badge` - Visual tier indicator with icon
- Tier promotion wizard (bulk or individual)
- Tier performance analytics dashboard
- Tier upgrade recommendations report

**Acceptance Criteria:**
- [ ] Manual tier assignment by Finance Admin
- [ ] Automated tier promotion based on criteria
- [ ] Tier change audit log
- [ ] Visual tier badges throughout app
- [ ] Tier-based pricing reflected in quotes and invoices
- [ ] Client notification on tier promotion

#### Story 18.2: Tier-Based Service Level Agreements
**Effort:** 6-10 hours  

**Technical Specification:**
```php
// Configuration
config/tiers.php:
return [
    'bronze' => [
        'response_time_hours' => 24,
        'resolution_time_hours' => 72,
        'support_channels' => ['email', 'portal'],
        'account_manager' => false,
        'quarterly_reviews' => false
    ],
    'silver' => [
        'response_time_hours' => 12,
        'resolution_time_hours' => 48,
        'support_channels' => ['email', 'portal', 'phone'],
        'account_manager' => false,
        'quarterly_reviews' => true
    ],
    'gold' => [
        'response_time_hours' => 4,
        'resolution_time_hours' => 24,
        'support_channels' => ['email', 'portal', 'phone', 'chat'],
        'account_manager' => true,
        'quarterly_reviews' => true
    ],
    'platinum' => [
        'response_time_hours' => 1,
        'resolution_time_hours' => 8,
        'support_channels' => ['email', 'portal', 'phone', 'chat', 'dedicated_hotline'],
        'account_manager' => true,
        'quarterly_reviews' => true,
        'priority_support' => true
    ]
];

// SLA Tracking Service
class SLAComplianceService
{
    public function checkCompliance($ticketId)
    {
        $ticket = Ticket::with('company')->find($ticketId);
        $sla = config("tiers.{$ticket->company->tier}");
        
        $responseTime = $ticket->first_response_at 
            ? $ticket->created_at->diffInHours($ticket->first_response_at)
            : $ticket->created_at->diffInHours(now());
            
        $resolutionTime = $ticket->resolved_at
            ? $ticket->created_at->diffInHours($ticket->resolved_at)
            : null;
        
        return [
            'response_within_sla' => $responseTime <= $sla['response_time_hours'],
            'resolution_within_sla' => $resolutionTime ? $resolutionTime <= $sla['resolution_time_hours'] : null,
            'response_time_actual' => $responseTime,
            'response_time_target' => $sla['response_time_hours'],
            'resolution_time_actual' => $resolutionTime,
            'resolution_time_target' => $sla['resolution_time_hours']
        ];
    }
    
    public function getTierComplianceReport($tier, $startDate, $endDate)
    {
        $tickets = Ticket::whereHas('company', fn($q) => $q->where('tier', $tier))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
            
        $compliant = 0;
        $breached = 0;
        
        foreach ($tickets as $ticket) {
            $compliance = $this->checkCompliance($ticket->id);
            if ($compliance['response_within_sla'] && ($compliance['resolution_within_sla'] ?? true)) {
                $compliant++;
            } else {
                $breached++;
            }
        }
        
        return [
            'tier' => $tier,
            'total_tickets' => $tickets->count(),
            'compliant' => $compliant,
            'breached' => $breached,
            'compliance_rate' => $tickets->count() > 0 ? ($compliant / $tickets->count()) * 100 : 0
        ];
    }
}
```

**UI Components:**
- `/billing/reports/sla-compliance` - Dashboard by tier
- `x-sla-indicator` - Red/green badge on tickets
- Breach alert notifications
- Compliance trend chart (6-month rolling)

**Acceptance Criteria:**
- [ ] SLA targets configured per tier
- [ ] Real-time compliance tracking per ticket
- [ ] Automated alerts on SLA breaches
- [ ] Compliance reporting dashboard
- [ ] Tier-based routing for support tickets

---

### Phase 21: Advanced Reporting & Analytics
**Business Value:** Data-driven strategic decisions  
**Estimated Effort:** 24-32 hours  
**Target Completion:** Q3 2025  

#### Story 21.1: Custom Report Builder
**Effort:** 16-20 hours  

**Technical Specification:**
```javascript
// React Component for Report Builder
import { useState } from 'react';
import { DndProvider, useDrag, useDrop } from 'react-dnd';

function ReportBuilder() {
    const [metrics, setMetrics] = useState([]);
    const [dimensions, setDimensions] = useState([]);
    const [filters, setFilters] = useState([]);
    const [chartType, setChartType] = useState('bar');
    
    const availableMetrics = [
        { id: 'mrr', name: 'Monthly Recurring Revenue', aggregation: 'sum' },
        { id: 'arr', name: 'Annual Recurring Revenue', aggregation: 'sum' },
        { id: 'churn_rate', name: 'Churn Rate', aggregation: 'avg' },
        { id: 'invoice_count', name: 'Invoice Count', aggregation: 'count' }
    ];
    
    const availableDimensions = [
        { id: 'company', name: 'Client' },
        { id: 'product', name: 'Product' },
        { id: 'month', name: 'Month' },
        { id: 'sales_agent', name: 'Sales Agent' }
    ];
    
    const generateReport = async () => {
        const response = await fetch('/api/reports/custom', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                metrics,
                dimensions,
                filters,
                chart_type: chartType
            })
        });
        
        const data = await response.json();
        setReportData(data);
    };
    
    return (
        <div className="report-builder">
            <DndProvider backend={HTML5Backend}>
                <MetricsPanel metrics={availableMetrics} onSelect={setMetrics} />
                <DimensionsPanel dimensions={availableDimensions} onSelect={setDimensions} />
                <FiltersPanel onFiltersChange={setFilters} />
                <ChartTypeSelector value={chartType} onChange={setChartType} />
                <button onClick={generateReport}>Generate Report</button>
                <ReportPreview data={reportData} chartType={chartType} />
            </DndProvider>
        </div>
    );
}
```

```php
// Backend API
class CustomReportController extends Controller
{
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'metrics' => 'required|array',
            'dimensions' => 'required|array',
            'filters' => 'array',
            'chart_type' => 'required|in:bar,line,pie,table'
        ]);
        
        $query = $this->buildQuery($validated);
        $data = $query->get();
        
        return [
            'data' => $data,
            'metadata' => [
                'generated_at' => now(),
                'row_count' => $data->count()
            ]
        ];
    }
    
    private function buildQuery($config)
    {
        // Build dynamic query based on selected metrics and dimensions
        // This is complex and would use Laravel's query builder dynamically
    }
}
```

**Acceptance Criteria:**
- [ ] Drag-and-drop interface for building reports
- [ ] 15+ metrics available
- [ ] 10+ dimensions available
- [ ] Filter builder with AND/OR logic
- [ ] 4 chart types (bar, line, pie, table)
- [ ] Save and share reports
- [ ] Schedule automatic delivery (daily, weekly, monthly)

#### Story 21.2: Cohort Analysis
**Effort:** 6-8 hours  

**Technical Specification:**
```php
class CohortAnalysisService
{
    public function generateRetentionCohorts($startDate, $endDate)
    {
        $cohorts = [];
        
        $signupMonths = DB::table('companies')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as cohort_month')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('cohort_month')
            ->pluck('cohort_month');
        
        foreach ($signupMonths as $cohortMonth) {
            $cohortCompanies = Company::whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$cohortMonth])
                ->pluck('id');
            
            $retentionData = [];
            
            for ($month = 0; $month <= 12; $month++) {
                $targetMonth = Carbon::parse($cohortMonth)->addMonths($month);
                
                $activeCompanies = Company::whereIn('id', $cohortCompanies)
                    ->whereHas('invoices', function($q) use ($targetMonth) {
                        $q->whereYear('created_at', $targetMonth->year)
                          ->whereMonth('created_at', $targetMonth->month);
                    })
                    ->count();
                
                $retentionData[$month] = [
                    'active_count' => $activeCompanies,
                    'retention_rate' => $cohortCompanies->count() > 0 
                        ? ($activeCompanies / $cohortCompanies->count()) * 100 
                        : 0
                ];
            }
            
            $cohorts[$cohortMonth] = [
                'cohort_size' => $cohortCompanies->count(),
                'retention_by_month' => $retentionData
            ];
        }
        
        return $cohorts;
    }
}
```

**UI Component:**
- Cohort heatmap visualization
- Month-over-month retention percentages
- Color coding (green = high retention, red = high churn)
- Export to Excel

**Acceptance Criteria:**
- [ ] Heatmap shows 12-month retention by cohort
- [ ] Interactive tooltips show exact percentages
- [ ] Export capability
- [ ] Automatic monthly updates

#### Story 21.3: What-If Scenario Planning
**Effort:** 2-4 hours  

**Technical Specification:**
```php
class ScenarioModelingService
{
    public function projectRevenue($baselineData, $scenarios)
    {
        $projections = [];
        
        foreach ($scenarios as $scenario) {
            $projection = [
                'name' => $scenario['name'],
                'months' => []
            ];
            
            for ($month = 1; $month <= 12; $month++) {
                $baseRevenue = $baselineData['mrr'];
                
                // Apply scenario adjustments
                if (isset($scenario['price_change'])) {
                    $baseRevenue *= (1 + $scenario['price_change'] / 100);
                }
                
                if (isset($scenario['churn_change'])) {
                    $churnImpact = $scenario['churn_change'] / 100 * $baseRevenue;
                    $baseRevenue -= $churnImpact;
                }
                
                if (isset($scenario['new_client_rate'])) {
                    $newRevenue = $scenario['new_client_rate'] * $baselineData['avg_deal_size'];
                    $baseRevenue += $newRevenue;
                }
                
                $projection['months'][$month] = round($baseRevenue, 2);
            }
            
            $projections[] = $projection;
        }
        
        return $projections;
    }
}
```

**UI Component:**
- Scenario builder form (sliders for variables)
- Side-by-side comparison chart
- Sensitivity analysis table
- Export to Excel/PDF

**Acceptance Criteria:**
- [ ] Model 3+ scenarios simultaneously
- [ ] Variables: price change, churn rate, new client rate
- [ ] Visual comparison (line chart)
- [ ] Sensitivity analysis (impact of each variable)
- [ ] Share scenarios with team

---

## Low Priority Work (Wave 3)

### Phase 22: Integration Marketplace
**Effort:** 32-40 hours  
**Target:** Q4 2025

### Phase 23: Mobile App Companion
**Effort:** 60-80 hours  
**Target:** 2026

### Additional Smaller Stories
**Total:** 76-116 hours across 8 stories  
**Target:** As capacity allows

---

## Implementation Timeline

### Q1 2025 (High Priority - Wave 1)
- **Phase 19:** Client Communication Hub (28-36 hours)
- **Story T.1:** Offline Time Entry (16-24 hours)
- **Story CP.1:** Dispute Tracking Enhancement (8-12 hours)
- **Total:** 52-72 hours (6-9 weeks @ 8hrs/week)

### Q2 2025 (Medium Priority - Wave 2)
- **Phase 20:** Client Success Scoring (20-28 hours)
- **Phase 17:** Multi-Currency Support (24-32 hours)
- **Phase 18:** Client Segmentation (16-24 hours)
- **Total:** 60-84 hours (8-11 weeks @ 8hrs/week)

### Q3 2025 (Medium Priority - Continued)
- **Phase 21:** Advanced Reporting (24-32 hours)
- **Additional Stories:** Batch actions, templates, etc. (40-60 hours)
- **Total:** 64-92 hours (8-12 weeks @ 8hrs/week)

### Q4 2025 and Beyond (Low Priority - Wave 3)
- **Phase 22:** Integration Marketplace (32-40 hours)
- **Phase 23:** Mobile Apps (60-80 hours)
- **Total:** 92-120 hours (12-15 weeks @ 8hrs/week)

---

## Resource Requirements

### Development Team
- **1 Senior Full-Stack Developer** (PHP/Laravel + Vue.js/React)
- **1 Junior Developer** (support, testing, documentation)
- **Part-time UX Designer** (quarterly reviews for new features)

### Estimated Timeline
- **Total Effort:** 372-524 hours
- **At 8 hours/week:** 47-66 weeks (~11-15 months)
- **At 16 hours/week:** 23-33 weeks (~6-8 months)
- **At 40 hours/week:** 9-13 weeks (~2-3 months)

---

## Risk Mitigation

### Technical Risks
- **Multi-Currency Complexity:** Phase 17 requires careful handling of exchange rates
  - **Mitigation:** Use established APIs, implement comprehensive testing
- **Offline Sync Conflicts:** Story T.1 requires robust conflict resolution
  - **Mitigation:** Implement last-write-wins with manual override option
- **ML Model Accuracy:** Phase 20.2 depends on sufficient historical data
  - **Mitigation:** Start with rule-based scoring, add ML when data sufficient

### Business Risks
- **Feature Creep:** Backlog continues growing
  - **Mitigation:** Strict prioritization, quarterly review of backlog
- **Resource Constraints:** Limited dev capacity
  - **Mitigation:** Focus on Wave 1 (high ROI), defer Wave 3 indefinitely

---

## Success Metrics

### Wave 1 Success Criteria
- **Phase 19:** 50%+ reduction in time to respond to collections inquiries
- **Story T.1:** 90%+ technician adoption of offline time entry
- **Story CP.1:** 80%+ client satisfaction with dispute transparency

### Wave 2 Success Criteria
- **Phase 20:** 15%+ reduction in churn rate within 6 months
- **Phase 17:** 10+ international clients onboarded
- **Phase 18:** 25%+ increase in tier upgrade rate

### Wave 3 Success Criteria
- **Phase 21:** 40%+ of users create custom reports monthly
- **Phase 22:** 5+ integration partnerships established
- **Phase 23:** 60%+ technician adoption of mobile app

---

**Document Status:** ✅ COMPLETE  
**Next Action:** Prioritize Wave 1, allocate resources, begin Phase 19 planning

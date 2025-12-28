# Phase 11: Executive Dashboard & KPI Enhancements

## Overview
**Priority:** HIGH  
**Estimated Effort:** 16-24 hours  
**UX Pattern:** Control Tower Dashboard  
**Style Guide Compliance:** [APPLICATION_UX_UI_STANDARDS.md](../APPLICATION_UX_UI_STANDARDS.md)

## Objective
Deliver a dedicated Executive Dashboard designed specifically for strategic decision-makers. Following the "Pilot's Cockpit" philosophy, it provides high-level visibility without operational noise.

---

## User Stories

### Story 11.1: Dedicated Executive Dashboard
**Priority:** P0 (Critical)  
**Effort:** 4-6 hours

**Acceptance Criteria:**
- [ ] Route `/billing/executive/dashboard` accessible to executives
- [ ] 5 KPI cards prominently displayed (MRR, Churn, Gross Margin, LTV, AR Aging)
- [ ] Real-time updates every 30 seconds (Alpine.js polling)
- [ ] Mobile-responsive grid layout
- [ ] Export to PDF functionality
- [ ] All colors use semantic classes (no hardcoded Tailwind colors)

**Components to Create:**
- `x-kpi-card` - Large metric display with trend sparkline
- `x-trend-indicator` - Up/down arrows with color coding

**Technical Details:**
```php
// Controller
class ExecutiveDashboardController extends Controller
{
    public function index()
    {
        $kpis = [
            'mrr' => $this->analyticsService->getCurrentMRR(),
            'churn' => $this->analyticsService->getChurnRate(),
            'grossMargin' => $this->analyticsService->getGrossMargin(),
            'ltv' => $this->analyticsService->getCustomerLTV(),
            'arAging' => $this->analyticsService->getARAgingSummary(),
        ];
        
        return view('billing::executive.dashboard', compact('kpis'));
    }
    
    public function refreshKPIs()
    {
        // API endpoint for Alpine.js polling
        return response()->json($this->index()->getData());
    }
}
```

---

### Story 11.2: Historical Trend Analysis
**Priority:** P0 (Critical)  
**Effort:** 3-4 hours

**Acceptance Criteria:**
- [ ] Each KPI card shows MoM % change
- [ ] Each KPI card shows YoY % change
- [ ] 12-month rolling average displayed
- [ ] Inline SVG sparkline charts (no external charting library)
- [ ] Color-coded trend arrows (green up, red down for revenue; inverse for costs)

**Components to Create:**
- `x-trend-comparison` - Displays MoM/YoY comparisons
- `x-sparkline-chart` - Lightweight SVG sparkline generator

**Technical Details:**
```php
// Service
class TrendAnalyticsService
{
    public function calculateMoM(string $metric, ?Carbon $date = null): array
    {
        $current = $this->getMetricValue($metric, $date ?? now());
        $previous = $this->getMetricValue($metric, $date->subMonth());
        
        return [
            'current' => $current,
            'previous' => $previous,
            'change_percent' => ($current - $previous) / $previous * 100,
            'direction' => $current > $previous ? 'up' : 'down',
        ];
    }
    
    public function calculateYoY(string $metric, ?Carbon $date = null): array
    {
        // Similar to MoM but with year offset
    }
    
    public function get12MonthAverage(string $metric): float
    {
        $values = collect();
        for ($i = 0; $i < 12; $i++) {
            $values->push($this->getMetricValue($metric, now()->subMonths($i)));
        }
        return $values->average();
    }
}
```

---

### Story 11.3: Alert Configuration UI
**Priority:** P1 (High)  
**Effort:** 5-7 hours

**Acceptance Criteria:**
- [ ] Modal wizard with 3 steps (Select Alerts → Configure Thresholds → Delivery Preferences)
- [ ] Support for 4 alert types (Churn, AR Aging, Margin, LTV:CAC)
- [ ] Multiple notification channels (Email, In-App, Slack)
- [ ] Preview of alert conditions before saving
- [ ] Confirmation modal with typed confirmation
- [ ] State preservation when navigating between steps

**Components to Create:**
- `x-alert-configuration-modal` - Multi-step wizard
- `x-threshold-input` - Numeric input with validation
- `x-notification-preferences` - Channel selector

**Technical Details:**
```php
// Database Migration
Schema::table('company_settings', function (Blueprint $table) {
    $table->json('executive_alerts')->nullable();
});

// Service Extension
class AlertService
{
    public function configureExecutiveAlert(Company $company, array $config): void
    {
        $alerts = $company->settings->executive_alerts ?? [];
        $alerts[$config['type']] = [
            'enabled' => true,
            'threshold' => $config['threshold'],
            'comparison' => $config['comparison'], // 'greater_than', 'less_than'
            'channels' => $config['channels'], // ['email', 'slack']
            'recipients' => $config['recipients'],
        ];
        
        $company->settings->update(['executive_alerts' => $alerts]);
    }
}
```

---

### Story 11.4: Effective Hourly Rate Display
**Priority:** P1 (High)  
**Effort:** 2-3 hours

**Acceptance Criteria:**
- [ ] EHR displayed as KPI card on executive dashboard
- [ ] Current EHR vs. Target comparison
- [ ] Variance percentage with color coding (green/red)
- [ ] 6-month trend sparkline
- [ ] Calculation: Total Revenue ÷ Total Billable Hours

**Technical Details:**
```php
// Existing service enhancement
class AnalyticsService
{
    public function getEffectiveHourlyRateWithTrend(Company $company): array
    {
        $current = $this->calculateEffectiveHourlyRate($company);
        $target = $company->settings->target_hourly_rate ?? 150;
        
        $trend = collect();
        for ($i = 0; $i < 6; $i++) {
            $trend->push($this->calculateEffectiveHourlyRate(
                $company, 
                now()->subMonths($i)
            ));
        }
        
        return [
            'current' => $current,
            'target' => $target,
            'variance_percent' => ($current - $target) / $target * 100,
            'status' => $current >= $target ? 'on_target' : 'below_target',
            'trend' => $trend->reverse()->values(),
        ];
    }
}
```

---

### Story 11.5: Weekly Email Digest
**Priority:** P1 (High)  
**Effort:** 4-5 hours

**Acceptance Criteria:**
- [ ] Scheduled job runs every Monday at 8am
- [ ] Responsive HTML email template
- [ ] Week-over-week KPI changes
- [ ] Top 3 at-risk clients (by health score)
- [ ] Notable alerts triggered this week
- [ ] Quick action links (deep links to dashboard)
- [ ] User preference to enable/disable digest

**Technical Details:**
```php
// Job
class SendExecutiveDigestJob implements ShouldQueue
{
    public function handle(TrendAnalyticsService $trends): void
    {
        $executives = User::role('executive')
            ->where('preferences->digest_enabled', true)
            ->get();
        
        foreach ($executives as $executive) {
            $data = [
                'kpis' => $this->getWeeklyKPIs($executive->company),
                'at_risk_clients' => $this->getAtRiskClients($executive->company, 3),
                'alerts' => $this->getTriggeredAlerts($executive->company),
            ];
            
            Mail::to($executive)->send(new ExecutiveDigestMail($data));
        }
    }
}

// Schedule in Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->job(new SendExecutiveDigestJob)
        ->weeklyOn(1, '8:00') // Monday at 8am
        ->timezone('America/New_York');
}
```

---

### Story 11.6: Industry Benchmark Comparison
**Priority:** P2 (Medium)  
**Effort:** 6-8 hours

**Acceptance Criteria:**
- [ ] Benchmark data fetched from external APIs (HTG, Service Leadership)
- [ ] 4 key metrics compared (Gross Margin %, EBITDA %, Revenue/Employee, CAC)
- [ ] Gauge chart showing position within industry range
- [ ] Percentile ranking displayed
- [ ] Graceful degradation if API unavailable
- [ ] Benchmark data cached (24-hour TTL)

**Components to Create:**
- `x-benchmark-comparison-card` - Gauge chart with percentile
- `x-gauge-chart` - SVG gauge chart component

**Technical Details:**
```php
// Service
class BenchmarkingService
{
    public function fetchBenchmarks(string $industry): array
    {
        return Cache::remember("benchmarks.{$industry}", 86400, function () use ($industry) {
            try {
                $htg = $this->fetchHTGBenchmarks($industry);
                $serviceLeadership = $this->fetchServiceLeadershipBenchmarks($industry);
                
                return $this->mergeBenchmarks($htg, $serviceLeadership);
            } catch (\Exception $e) {
                Log::warning('Benchmark API unavailable', ['error' => $e->getMessage()]);
                return $this->getDefaultBenchmarks();
            }
        });
    }
    
    public function compareToIndustry(Company $company): array
    {
        $benchmarks = $this->fetchBenchmarks($company->industry);
        $metrics = $this->analyticsService->getCompanyMetrics($company);
        
        return [
            'gross_margin' => [
                'value' => $metrics['gross_margin'],
                'benchmark_range' => $benchmarks['gross_margin'],
                'percentile' => $this->calculatePercentile($metrics['gross_margin'], $benchmarks['gross_margin']),
            ],
            // ... other metrics
        ];
    }
}
```

---

## Implementation Order

1. **Sprint 1 (8-10 hours):**
   - Story 11.1: Executive Dashboard layout
   - Story 11.2: Historical Trend Analysis
   - Story 11.4: EHR Display

2. **Sprint 2 (8-10 hours):**
   - Story 11.3: Alert Configuration UI
   - Story 11.5: Weekly Email Digest

3. **Sprint 3 (6-8 hours):**
   - Story 11.6: Industry Benchmarking
   - Testing and polish
   - Documentation

---

## Testing Strategy

### Unit Tests
```php
class TrendAnalyticsServiceTest extends TestCase
{
    public function test_mom_calculation_with_growth()
    {
        $result = $this->service->calculateMoM('mrr', Carbon::parse('2024-02-01'));
        
        $this->assertArrayHasKey('change_percent', $result);
        $this->assertEquals('up', $result['direction']);
    }
    
    public function test_twelve_month_average()
    {
        $avg = $this->service->get12MonthAverage('mrr');
        
        $this->assertIsFloat($avg);
        $this->assertGreaterThan(0, $avg);
    }
}
```

### Feature Tests
```php
class ExecutiveDashboardTest extends TestCase
{
    public function test_executive_can_access_dashboard()
    {
        $executive = User::factory()->role('executive')->create();
        
        $response = $this->actingAs($executive)->get('/billing/executive/dashboard');
        
        $response->assertOk();
        $response->assertViewHas('kpis');
    }
    
    public function test_non_executive_cannot_access()
    {
        $user = User::factory()->role('technician')->create();
        
        $response = $this->actingAs($user)->get('/billing/executive/dashboard');
        
        $response->assertForbidden();
    }
}
```

### Browser Tests (Dusk)
```php
public function test_kpi_cards_update_in_realtime()
{
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->executive)
            ->visit('/billing/executive/dashboard')
            ->assertSee('MRR')
            ->waitFor('@mrr-value', 5)
            ->pause(31000) // Wait for 30s polling + buffer
            ->assertAttributeChanged('@mrr-value', 'data-value');
    });
}
```

---

## Accessibility Checklist

- [ ] All KPI cards have `role="region"` and `aria-label`
- [ ] Trend arrows have `aria-label` describing direction and magnitude
- [ ] Alert configuration wizard has clear step indicators
- [ ] Focus management in modal wizard (trap focus, return on close)
- [ ] Color is not the only indicator (use icons + text)
- [ ] Sparklines have text alternatives
- [ ] Keyboard navigation works throughout

---

## Performance Considerations

1. **KPI Calculation Caching:**
   - Cache complex calculations (5-minute TTL)
   - Use Redis for real-time polling
   - Pre-calculate at end of day for historical data

2. **Polling Optimization:**
   - Use efficient API endpoints
   - Return only changed data (delta updates)
   - Debounce client-side updates

3. **Email Digest Generation:**
   - Queue digest generation
   - Batch recipients to avoid rate limits
   - Use transactional email service (SendGrid/Postmark)

---

## Style Guide Compliance

### Color Usage
```blade
<!-- ✅ CORRECT: Semantic colors -->
<div class="bg-primary-600 text-white">
<span class="text-success-700">↑ 12%</span>
<span class="text-danger-700">↓ 5%</span>

<!-- ❌ WRONG: Hardcoded colors -->
<div class="bg-indigo-600 text-white">
<span class="text-green-700">↑ 12%</span>
```

### Component Pattern
```blade
<!-- KPI Card Component -->
<x-kpi-card
    title="Monthly Recurring Revenue"
    :value="$kpis['mrr']"
    :trend="$trends['mrr']"
    format="currency"
    status="success"
/>
```

### Loading States
```blade
<div x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 1000)">
    <div x-show="loading" class="animate-pulse">
        <div class="h-24 bg-gray-200 rounded"></div>
    </div>
    <div x-show="!loading" x-transition>
        <!-- Actual KPI content -->
    </div>
</div>
```

---

## Documentation Deliverables

1. **User Guide:** "Executive Dashboard Quick Start"
2. **Admin Guide:** "Configuring Executive Alerts"
3. **Technical Docs:** "Benchmark API Integration"
4. **Troubleshooting:** "Email Digest Issues"

---

## Success Metrics

- **Adoption:** 80%+ of executive users access dashboard weekly
- **Time-to-Insight:** < 10 seconds to understand business health
- **Alert Effectiveness:** 90%+ of threshold breaches result in timely action
- **User Satisfaction:** Net Promoter Score > 50 from executive users
- **Performance:** Dashboard loads in < 2 seconds
- **Uptime:** 99.9% availability (external API failures don't block core functionality)

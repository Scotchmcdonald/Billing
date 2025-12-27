# Batch 9: Advanced Reporting & Business Intelligence

**Execution Order:** Parallel (After Batch 1-2)
**Parallelization:** Reports can be developed in parallel
**Estimated Effort:** 4-5 days
**Priority:** P2-P3

---

## Agent Prompt

```
You are a Senior Data Engineer specializing in reporting, analytics, and business intelligence.

Your task is to implement advanced reporting capabilities for the FinOps billing module. These reports provide insights into revenue, profitability, and client health.

## Primary Objectives
1. Build comprehensive financial reports
2. Implement export capabilities (PDF, Excel, CSV)
3. Create interactive dashboards with charts
4. Design scheduled report delivery

## Technical Standards
- Report services in `Modules/Billing/Services/Reports/`
- Use Laravel Excel for spreadsheet exports
- Use DomPDF or Snappy for PDF generation
- Cache expensive report queries
- Use database views for complex aggregations

## Report Design Principles
- Reports should be parameterizable (date range, filters)
- Include drill-down capability where appropriate
- Export format should match on-screen view
- Large reports should be generated async

## Files to Reference
- Existing reporting: `Modules/Billing/Services/ReportingService.php`
- PDF templates: `Modules/Billing/Resources/views/reports/pdf/`
- Chart library: Chart.js via CDN

## Validation Criteria
- All reports match accounting data
- PDFs render correctly
- Excel files open without errors
- Scheduled reports deliver on time
```

---

## Context & Technical Details

### Report Architecture
```
Modules/Billing/Services/Reports/
├── ReportingService.php         # Base service
├── RevenueReportService.php     # MRR, ARR, Revenue
├── ArAgingReportService.php     # AR Aging
├── ProfitabilityReportService.php
├── ClientHealthReportService.php
└── TaxReportService.php

Modules/Billing/Exports/
├── InvoicesExport.php           # Laravel Excel
├── PaymentsExport.php
└── ArAgingExport.php
```

### Chart.js Integration
```blade
<canvas id="revenueChart"></canvas>
<script>
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: @json($chartData),
        options: { responsive: true }
    });
</script>
```

### PDF Generation
```php
use Barryvdh\DomPDF\Facade\Pdf;

$pdf = Pdf::loadView('billing::reports.pdf.ar-aging', $data);
return $pdf->download('ar-aging.pdf');
```

---

## Task Checklist

### 9.1 Revenue Reports

#### MRR/ARR Report
- [ ] Create `Modules/Billing/Services/Reports/RevenueReportService.php`
  ```php
  class RevenueReportService
  {
      public function getMRRReport(Carbon $startDate, Carbon $endDate): array
      {
          return [
              'summary' => [
                  'total_mrr' => $this->calculateMRR($endDate),
                  'mrr_growth' => $this->calculateMRRGrowth($startDate, $endDate),
                  'new_mrr' => $this->getNewMRR($startDate, $endDate),
                  'churned_mrr' => $this->getChurnedMRR($startDate, $endDate),
                  'expansion_mrr' => $this->getExpansionMRR($startDate, $endDate),
              ],
              'trend' => $this->getMRRTrend($startDate, $endDate),
              'by_product' => $this->getMRRByProduct($endDate),
              'by_client_tier' => $this->getMRRByClientTier($endDate),
          ];
      }
      
      public function getRevenueByCategory(Carbon $startDate, Carbon $endDate): Collection
      public function getRevenueByClient(Carbon $startDate, Carbon $endDate): Collection
  }
  ```

#### Revenue Dashboard View
- [ ] Create `Modules/Billing/Resources/views/reports/revenue.blade.php`
- [ ] MRR trend line chart
- [ ] Revenue breakdown pie chart
- [ ] Top clients table
- [ ] Growth metrics cards

### 9.2 AR Aging Report

#### Enhanced AR Aging
- [ ] Enhance `ArAgingReportService.php`
  ```php
  class ArAgingReportService
  {
      public function getAgingReport(array $filters = []): array
      {
          return [
              'buckets' => [
                  'current' => $this->getInvoicesInBucket(0, 0),
                  '1_30' => $this->getInvoicesInBucket(1, 30),
                  '31_60' => $this->getInvoicesInBucket(31, 60),
                  '61_90' => $this->getInvoicesInBucket(61, 90),
                  'over_90' => $this->getInvoicesInBucket(91, null),
              ],
              'totals' => $this->getBucketTotals(),
              'by_client' => $this->getAgingByClient(),
              'risk_assessment' => $this->calculateRiskMetrics(),
          ];
      }
  }
  ```

#### AR Aging View
- [ ] Create `Modules/Billing/Resources/views/reports/ar-aging.blade.php`
- [ ] Bucket summary cards
- [ ] Detailed table with sorting
- [ ] Client breakdown accordion
- [ ] Export buttons (PDF, Excel)

### 9.3 Profitability Reports

#### Client Profitability
- [ ] Create `Modules/Billing/Services/Reports/ProfitabilityReportService.php`
  ```php
  class ProfitabilityReportService
  {
      public function getClientProfitability(Carbon $startDate, Carbon $endDate): Collection
      {
          return Company::query()
              ->withSum(['invoices' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate])], 'total')
              ->withSum(['timeEntries' => fn($q) => $q->whereBetween('created_at', [$startDate, $endDate])], 'cost')
              ->get()
              ->map(fn($company) => [
                  'company' => $company,
                  'revenue' => $company->invoices_sum_total,
                  'cost' => $company->time_entries_sum_cost,
                  'profit' => $company->invoices_sum_total - $company->time_entries_sum_cost,
                  'margin' => $this->calculateMargin($company),
              ])
              ->sortByDesc('profit');
      }
      
      public function getServiceProfitability(Carbon $startDate, Carbon $endDate): Collection
      public function getTechnicianProfitability(Carbon $startDate, Carbon $endDate): Collection
  }
  ```

#### Profitability Dashboard
- [ ] Client profitability table
- [ ] Margin distribution chart
- [ ] Top/bottom performers
- [ ] Trend analysis

### 9.4 Client Health Report

#### Health Score Calculation
- [ ] Create `Modules/Billing/Services/Reports/ClientHealthReportService.php`
  ```php
  class ClientHealthReportService
  {
      public function getHealthScores(): Collection
      {
          return Company::active()->get()->map(fn($company) => [
              'company' => $company,
              'health_score' => $this->calculateHealthScore($company),
              'factors' => [
                  'payment_history' => $this->scorePaymentHistory($company),
                  'contract_status' => $this->scoreContractStatus($company),
                  'support_engagement' => $this->scoreSupportEngagement($company),
                  'growth_trajectory' => $this->scoreGrowth($company),
              ],
              'risk_level' => $this->determineRiskLevel($company),
              'recommendations' => $this->generateRecommendations($company),
          ]);
      }
  }
  ```

#### Health Dashboard
- [ ] Health score distribution
- [ ] At-risk clients list
- [ ] Factor breakdown per client
- [ ] Action recommendations

### 9.5 Tax Reports

#### Tax Summary Report
- [ ] Create `Modules/Billing/Services/Reports/TaxReportService.php`
  ```php
  class TaxReportService
  {
      public function getTaxSummary(Carbon $startDate, Carbon $endDate): array
      {
          return [
              'total_collected' => $this->getTotalTaxCollected($startDate, $endDate),
              'by_jurisdiction' => $this->getTaxByJurisdiction($startDate, $endDate),
              'by_rate' => $this->getTaxByRate($startDate, $endDate),
              'exempt_sales' => $this->getExemptSales($startDate, $endDate),
          ];
      }
      
      public function generateTaxFilingReport(string $jurisdiction, Carbon $period): array
  }
  ```

#### Tax Report View
- [ ] Summary by jurisdiction
- [ ] Rate breakdown
- [ ] Filing-ready format

### 9.6 Excel Exports

#### Invoice Export
- [ ] Create `Modules/Billing/Exports/InvoicesExport.php`
  ```php
  class InvoicesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
  {
      public function __construct(
          public Carbon $startDate,
          public Carbon $endDate,
          public array $filters = []
      ) {}
      
      public function query()
      {
          return Invoice::query()
              ->whereBetween('created_at', [$this->startDate, $this->endDate])
              ->with(['company', 'lineItems']);
      }
      
      public function headings(): array
      {
          return ['Invoice #', 'Client', 'Date', 'Due Date', 'Subtotal', 'Tax', 'Total', 'Status'];
      }
      
      public function map($invoice): array
      {
          return [
              $invoice->invoice_number,
              $invoice->company->name,
              $invoice->created_at->format('Y-m-d'),
              $invoice->due_date->format('Y-m-d'),
              $invoice->subtotal,
              $invoice->tax,
              $invoice->total,
              $invoice->status,
          ];
      }
  }
  ```

#### Additional Exports
- [ ] Create `PaymentsExport.php`
- [ ] Create `ArAgingExport.php`
- [ ] Create `RevenueExport.php`
- [ ] Create `ProfitabilityExport.php`

### 9.7 PDF Reports

#### AR Aging PDF
- [ ] Create `Modules/Billing/Resources/views/reports/pdf/ar-aging.blade.php`
  ```blade
  <!DOCTYPE html>
  <html>
  <head>
      <style>
          body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
          .header { border-bottom: 2px solid #333; margin-bottom: 20px; }
          .bucket { margin-bottom: 30px; }
          table { width: 100%; border-collapse: collapse; }
          th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
          th { background: #f5f5f5; }
          .total { font-weight: bold; background: #e5e5e5; }
      </style>
  </head>
  <body>
      <div class="header">
          <h1>AR Aging Report</h1>
          <p>Generated: {{ now()->format('M d, Y') }}</p>
      </div>
      
      @foreach($buckets as $name => $bucket)
          <div class="bucket">
              <h2>{{ $name }}: {{ format_currency($bucket['total']) }}</h2>
              <table>
                  <thead>
                      <tr>
                          <th>Invoice #</th>
                          <th>Client</th>
                          <th>Invoice Date</th>
                          <th>Due Date</th>
                          <th>Days Overdue</th>
                          <th>Amount</th>
                      </tr>
                  </thead>
                  <tbody>
                      @foreach($bucket['invoices'] as $invoice)
                          <tr>
                              <td>{{ $invoice->invoice_number }}</td>
                              <td>{{ $invoice->company->name }}</td>
                              <td>{{ $invoice->created_at->format('M d, Y') }}</td>
                              <td>{{ $invoice->due_date->format('M d, Y') }}</td>
                              <td>{{ $invoice->days_overdue }}</td>
                              <td>{{ format_currency($invoice->balance_due) }}</td>
                          </tr>
                      @endforeach
                  </tbody>
              </table>
          </div>
      @endforeach
  </body>
  </html>
  ```

#### Additional PDF Templates
- [ ] Create `revenue-summary.blade.php`
- [ ] Create `client-statement.blade.php`
- [ ] Create `profitability.blade.php`

### 9.8 Report Controller

#### Controller
- [ ] Create `Modules/Billing/Http/Controllers/ReportController.php`
  ```php
  class ReportController extends Controller
  {
      public function revenue(Request $request)
      {
          $data = $this->revenueReport->getMRRReport(
              $request->date('start_date', now()->subYear()),
              $request->date('end_date', now())
          );
          
          return view('billing::reports.revenue', $data);
      }
      
      public function exportRevenue(Request $request)
      {
          return Excel::download(
              new RevenueExport($request->start_date, $request->end_date),
              'revenue-report.xlsx'
          );
      }
      
      public function arAgingPdf(Request $request)
      {
          $data = $this->arAgingReport->getAgingReport();
          $pdf = Pdf::loadView('billing::reports.pdf.ar-aging', $data);
          return $pdf->download('ar-aging-report.pdf');
      }
  }
  ```

### 9.9 Scheduled Reports

#### Report Scheduler Job
- [ ] Create `Modules/Billing/Jobs/SendScheduledReportJob.php`
  ```php
  class SendScheduledReportJob implements ShouldQueue
  {
      public function __construct(
          public string $reportType,
          public array $recipients,
          public array $parameters = []
      ) {}
      
      public function handle(): void
      {
          $report = $this->generateReport();
          
          foreach ($this->recipients as $email) {
              Mail::to($email)->send(new ScheduledReportMail($report));
          }
      }
  }
  ```

#### Report Subscriptions
- [ ] Create migration: `create_report_subscriptions_table`
  ```php
  Schema::create('billing_report_subscriptions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained();
      $table->string('report_type');
      $table->string('frequency'); // daily, weekly, monthly
      $table->json('parameters')->nullable();
      $table->string('format'); // pdf, excel
      $table->time('delivery_time')->default('08:00');
      $table->timestamps();
  });
  ```

#### Subscription Management UI
- [ ] Create subscription management view
- [ ] Report type selector
- [ ] Frequency options
- [ ] Parameter configuration

### 9.10 Interactive Dashboards

#### Dashboard Builder
- [ ] Create `Modules/Billing/Resources/views/reports/dashboard.blade.php`
- [ ] Draggable widget layout (Alpine.js + Sortable.js)
- [ ] Configurable date range
- [ ] Auto-refresh option

#### Widget Components
- [ ] `<x-billing.widgets.mrr-card />`
- [ ] `<x-billing.widgets.revenue-chart />`
- [ ] `<x-billing.widgets.ar-aging-summary />`
- [ ] `<x-billing.widgets.top-clients />`
- [ ] `<x-billing.widgets.recent-payments />`

### 9.11 Report Caching

#### Cache Strategy
- [ ] Cache expensive queries with tags
  ```php
  public function getMRR(Carbon $date): float
  {
      return Cache::tags(['billing', 'reports', 'mrr'])
          ->remember("mrr:{$date->format('Y-m-d')}", now()->addHour(), function () use ($date) {
              return $this->calculateMRR($date);
          });
  }
  ```

#### Cache Invalidation
- [ ] Clear report cache on invoice create/update
- [ ] Clear report cache on payment record
- [ ] Use queue for cache warming

### 9.12 Benchmark Comparisons

#### Industry Benchmarks
- [ ] Add benchmark data table
- [ ] Compare client metrics to industry averages
- [ ] Percentile rankings
- [ ] Trend vs benchmark chart

---

## Routes

```php
// Modules/Billing/Routes/web.php
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('revenue', [ReportController::class, 'revenue'])->name('revenue');
    Route::get('revenue/export', [ReportController::class, 'exportRevenue'])->name('revenue.export');
    
    Route::get('ar-aging', [ReportController::class, 'arAging'])->name('ar-aging');
    Route::get('ar-aging/pdf', [ReportController::class, 'arAgingPdf'])->name('ar-aging.pdf');
    Route::get('ar-aging/excel', [ReportController::class, 'arAgingExcel'])->name('ar-aging.excel');
    
    Route::get('profitability', [ReportController::class, 'profitability'])->name('profitability');
    Route::get('client-health', [ReportController::class, 'clientHealth'])->name('client-health');
    Route::get('tax', [ReportController::class, 'tax'])->name('tax');
    
    Route::get('subscriptions', [ReportController::class, 'subscriptions'])->name('subscriptions');
    Route::post('subscriptions', [ReportController::class, 'subscribe'])->name('subscriptions.store');
});
```

---

## Completion Verification

```bash
# Test report generation
php artisan tinker --execute="
    \$report = app(\Modules\Billing\Services\Reports\RevenueReportService::class)
        ->getMRRReport(now()->subYear(), now());
    dump(\$report['summary']);
"

# Test PDF generation
curl -o test-ar-aging.pdf http://localhost/billing/reports/ar-aging/pdf

# Test Excel export
curl -o test-invoices.xlsx http://localhost/billing/reports/revenue/export

# Verify cache
php artisan cache:tags billing,reports
```

---

## Downstream Dependencies
- None (feature batch)
- Uses data from **Batch 1** models
- Integrates with dashboards from **Batch 3A**

# Batch 7: AI/ML Features & Intelligent Automation

**Execution Order:** Parallel (After Batch 1-2)
**Parallelization:** All AI features independent
**Estimated Effort:** 4-5 days
**Priority:** P3 (Enhancement)

---

## Agent Prompt

```
You are a Senior AI/ML Engineer with expertise in Laravel and practical machine learning applications.

Your task is to implement intelligent features for the FinOps billing module. These features use statistical analysis and ML techniques to provide predictive insights and automation.

## Primary Objectives
1. Implement anomaly detection for billing patterns
2. Create predictive analytics for revenue forecasting
3. Build smart categorization and suggestions
4. Design conversational AI for client inquiries

## Technical Standards
- AI services in `Modules/Billing/Services/AI/`
- Use simple statistical methods where possible (no heavy ML frameworks)
- Consider using OpenAI API for NLP tasks
- Store model data in dedicated tables
- Cache predictions for performance

## AI Design Principles
- Explainable: Show why predictions/decisions were made
- Fallback gracefully: Always have a non-AI path
- User control: Allow overrides and corrections
- Privacy aware: Don't send sensitive data to external APIs
- Audit trail: Log all AI-assisted decisions

## Files to Reference
- Service pattern: `Modules/Billing/Services/`
- Config for API keys: `config/services.php`
- Existing analytics: `Modules/Billing/Services/ReportingService.php`

## Validation Criteria
- All AI features have manual override
- Predictions logged with confidence scores
- External API calls are optional/configurable
- Performance under 200ms for real-time features
```

---

## Context & Technical Details

### AI Service Architecture
```
Modules/Billing/Services/AI/
├── AnomalyDetectionService.php   # Statistical anomaly detection
├── RevenueForecastService.php    # Time series prediction
├── SmartCategorizationService.php # Text classification
├── ConversationalService.php     # LLM integration
└── SuggestionEngineService.php   # Recommendation engine
```

### Statistical Approach (No Heavy ML)
For MVP, use simple statistical methods:
- Anomaly: Z-score, IQR method
- Forecast: Moving average, linear regression
- Classification: Keyword matching, rule-based

### Optional LLM Integration
```php
// config/services.php
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'enabled' => env('OPENAI_ENABLED', false),
],
```

---

## Task Checklist

### 7.1 Enhanced Anomaly Detection

#### Statistical Service
- [ ] Create `Modules/Billing/Services/AI/AnomalyDetectionService.php`
  ```php
  class AnomalyDetectionService
  {
      public function analyzeInvoice(Invoice $invoice): AnomalyResult
      {
          $scores = [
              'amount' => $this->checkAmountAnomaly($invoice),
              'timing' => $this->checkTimingAnomaly($invoice),
              'lineItems' => $this->checkLineItemAnomaly($invoice),
          ];
          
          return new AnomalyResult(
              score: $this->calculateCompositeScore($scores),
              factors: $scores,
              recommendation: $this->generateRecommendation($scores)
          );
      }
      
      private function checkAmountAnomaly(Invoice $invoice): float
      {
          $history = $invoice->company->invoices()
              ->where('id', '!=', $invoice->id)
              ->pluck('total');
          
          $mean = $history->avg();
          $stdDev = $this->standardDeviation($history);
          $zScore = abs(($invoice->total - $mean) / $stdDev);
          
          return min($zScore / 3, 1.0); // Normalize to 0-1
      }
  }
  ```

#### DTO for Results
- [ ] Create `Modules/Billing/DataTransferObjects/AnomalyResult.php`
  ```php
  class AnomalyResult
  {
      public function __construct(
          public float $score,           // 0-1, higher = more anomalous
          public array $factors,          // Individual factor scores
          public string $recommendation,  // Human-readable suggestion
          public string $confidence,      // low, medium, high
      ) {}
      
      public function isAnomaly(): bool
      public function getSeverity(): string // normal, warning, critical
  }
  ```

#### UI Integration
- [ ] Add anomaly badge to invoice row
- [ ] Show factor breakdown in modal
- [ ] Allow "dismiss" with reason

### 7.2 Revenue Forecasting

#### Forecast Service
- [ ] Create `Modules/Billing/Services/AI/RevenueForecastService.php`
  ```php
  class RevenueForecastService
  {
      public function forecastMRR(int $monthsAhead = 3): Collection
      {
          $historical = $this->getHistoricalMRR(12);
          
          return collect(range(1, $monthsAhead))->map(fn($m) => [
              'month' => now()->addMonths($m)->format('Y-m'),
              'predicted' => $this->linearRegression($historical, $m),
              'lower_bound' => $this->confidenceInterval($historical, $m, 'lower'),
              'upper_bound' => $this->confidenceInterval($historical, $m, 'upper'),
          ]);
      }
      
      public function forecastChurn(): ChurnPrediction
      {
          // Analyze payment patterns, support tickets, usage
      }
  }
  ```

#### Forecast Display
- [ ] Add forecast chart to executive dashboard
- [ ] Show confidence intervals
- [ ] Compare actual vs predicted (after month ends)

### 7.3 Smart Categorization

#### Service
- [ ] Create `Modules/Billing/Services/AI/SmartCategorizationService.php`
  ```php
  class SmartCategorizationService
  {
      public function categorizeLineItem(string $description): ?string
      {
          $keywords = [
              'labor' => ['hours', 'support', 'consultation', 'time'],
              'hardware' => ['server', 'computer', 'laptop', 'switch', 'router'],
              'software' => ['license', 'subscription', 'saas', 'cloud'],
              'maintenance' => ['backup', 'monitoring', 'managed', 'maintenance'],
          ];
          
          foreach ($keywords as $category => $words) {
              foreach ($words as $word) {
                  if (str_contains(strtolower($description), $word)) {
                      return $category;
                  }
              }
          }
          
          return null;
      }
      
      public function suggestProduct(string $description): ?Product
      {
          // Find best matching product from catalog
      }
  }
  ```

#### Learning from Corrections
- [ ] Track user corrections
- [ ] Weight corrections in future suggestions
- [ ] Store in `billing_ai_training` table

### 7.4 Smart Suggestions

#### Suggestion Engine
- [ ] Create `Modules/Billing/Services/AI/SuggestionEngineService.php`
  ```php
  class SuggestionEngineService
  {
      public function suggestNextAction(User $user): array
      {
          return [
              $this->checkOverdueInvoices($user),
              $this->checkPendingQuotes($user),
              $this->checkUpcomingRenewals($user),
              $this->checkLowRetainers($user),
          ]->filter()->sortByDesc('priority')->take(5);
      }
      
      public function suggestLineItems(Company $company): Collection
      {
          // Based on past invoices, suggest common line items
      }
  }
  ```

#### UI Widget
- [ ] "Smart Actions" panel on dashboard
- [ ] Priority-ordered task suggestions
- [ ] One-click action buttons

### 7.5 Conversational AI (Client Portal)

#### Service
- [ ] Create `Modules/Billing/Services/AI/ConversationalService.php`
  ```php
  class ConversationalService
  {
      public function handleQuery(Company $company, string $query): ConversationResponse
      {
          // First, try rule-based response
          $ruleResponse = $this->checkRules($query);
          if ($ruleResponse) return $ruleResponse;
          
          // If OpenAI enabled, use LLM
          if (config('services.openai.enabled')) {
              return $this->askLLM($company, $query);
          }
          
          return new ConversationResponse(
              answer: "I couldn't understand your question. Please contact support.",
              suggestedActions: ['contact_support']
          );
      }
      
      private function checkRules(string $query): ?ConversationResponse
      {
          $intents = [
              'balance' => ['balance', 'owe', 'outstanding', 'due'],
              'payment' => ['pay', 'payment', 'credit card'],
              'invoice' => ['invoice', 'bill', 'statement'],
          ];
          
          // Match intent and return appropriate response
      }
  }
  ```

#### Chat Widget
- [ ] Create `billing.portal.chat-widget` Blade component
- [ ] Floating chat button
- [ ] Suggested questions
- [ ] Escalate to human option

#### Common Questions Handled
- [ ] "What is my current balance?"
- [ ] "When is my next invoice?"
- [ ] "How do I update my payment method?"
- [ ] "Why was I charged X?"
- [ ] "Can I get a copy of invoice #?"

### 7.6 Payment Prediction

#### Service
- [ ] Create `Modules/Billing/Services/AI/PaymentPredictionService.php`
  ```php
  class PaymentPredictionService
  {
      public function predictPaymentDate(Invoice $invoice): PredictionResult
      {
          $history = $invoice->company->payments()
              ->selectRaw('DATEDIFF(paid_at, invoice.due_date) as days_diff')
              ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
              ->pluck('days_diff');
          
          $avgDaysAfterDue = $history->avg() ?? 0;
          
          return new PredictionResult(
              predictedDate: $invoice->due_date->addDays($avgDaysAfterDue),
              confidence: $this->calculateConfidence($history),
              basedOn: $history->count() . ' historical payments'
          );
      }
      
      public function getRiskScore(Company $company): float
      {
          // Based on payment history, calculate risk 0-1
      }
  }
  ```

#### Cash Flow Projection
- [ ] Use payment predictions for cash flow forecast
- [ ] Show expected payment dates on AR aging

### 7.7 Intelligent Alerts

#### Alert Rules Engine
- [ ] Create `Modules/Billing/Services/AI/AlertRulesEngine.php`
  ```php
  class AlertRulesEngine
  {
      public function evaluateAlerts(): Collection
      {
          return collect([
              $this->checkRevenueDropAlert(),
              $this->checkHighChurnRiskAlert(),
              $this->checkUnusualActivityAlert(),
              $this->checkCashFlowAlert(),
          ])->filter();
      }
      
      private function checkRevenueDropAlert(): ?Alert
      {
          $currentMRR = $this->calculateMRR(now());
          $lastMRR = $this->calculateMRR(now()->subMonth());
          
          if ($currentMRR < $lastMRR * 0.9) { // 10% drop
              return new Alert(
                  type: 'revenue_drop',
                  severity: 'warning',
                  message: "MRR dropped 10% from last month",
                  data: compact('currentMRR', 'lastMRR')
              );
          }
          
          return null;
      }
  }
  ```

#### Executive Alert Dashboard
- [ ] Real-time alert feed
- [ ] Dismiss with acknowledgment
- [ ] Trend over time

### 7.8 Auto-Categorization Training

#### Training Data Storage
- [ ] Create migration: `create_billing_ai_training_table`
  ```php
  Schema::create('billing_ai_training', function (Blueprint $table) {
      $table->id();
      $table->string('type'); // categorization, suggestion, etc.
      $table->text('input');
      $table->string('predicted');
      $table->string('corrected')->nullable();
      $table->foreignId('corrected_by')->nullable()->constrained('users');
      $table->timestamps();
  });
  ```

#### Learning Loop
- [ ] When user corrects AI suggestion, store correction
- [ ] Periodically analyze corrections
- [ ] Update keyword weights based on corrections

---

## Configuration

### AI Settings
```php
// config/billing.php
'ai' => [
    'anomaly_detection' => [
        'enabled' => env('BILLING_AI_ANOMALY_ENABLED', true),
        'threshold' => env('BILLING_AI_ANOMALY_THRESHOLD', 0.7),
    ],
    'forecasting' => [
        'enabled' => env('BILLING_AI_FORECAST_ENABLED', true),
        'months_ahead' => 6,
    ],
    'conversational' => [
        'enabled' => env('BILLING_AI_CHAT_ENABLED', false),
        'fallback_to_support' => true,
    ],
],
```

---

## Completion Verification

```bash
# Test anomaly detection
php artisan tinker --execute="
    \$invoice = \Modules\Billing\Models\Invoice::first();
    \$result = app(\Modules\Billing\Services\AI\AnomalyDetectionService::class)
        ->analyzeInvoice(\$invoice);
    dump(\$result);
"

# Test forecast
php artisan tinker --execute="
    \$forecast = app(\Modules\Billing\Services\AI\RevenueForecastService::class)
        ->forecastMRR(3);
    dump(\$forecast);
"

# Test chat (if enabled)
curl -X POST http://localhost/api/billing/chat \
  -H 'Content-Type: application/json' \
  -d '{"query": "What is my current balance?"}'
```

---

## Downstream Dependencies
- None (feature batch)
- Enhances dashboards from **Batch 3A**
- Enhances portal from **Batch 3B**

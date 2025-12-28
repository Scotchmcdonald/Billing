# Batch 2: Core Services & Business Logic

**Execution Order:** Second (Depends on Batch 1)
**Parallelization:** All services can be developed in parallel
**Estimated Effort:** 3-4 days
**Priority:** P1

---

## Agent Prompt

```
You are a Senior Laravel Backend Engineer specializing in service layer architecture and business logic.

Your task is to implement core business services for the FinOps billing module. These services encapsulate complex business logic and will be consumed by controllers and jobs.

## Primary Objectives
1. Create new service classes following single-responsibility principle
2. Implement business logic for retainers, credit notes, and audit logging
3. Enhance existing services with new capabilities
4. Write unit tests for all new service methods

## Technical Standards
- Services go in `Modules/Billing/Services/`
- Use constructor dependency injection
- Return DTOs or arrays, not Eloquent models directly (when complex)
- Throw custom exceptions for business rule violations
- All monetary calculations in cents (integers)
- Log important business events using Laravel's Log facade

## Files to Reference
- Existing services: `Modules/Billing/Services/`
- DTOs: `Modules/Billing/DataTransferObjects/`
- Events: `Modules/Billing/Events/`

## Validation Criteria
- All services have corresponding unit tests
- Tests pass: `php artisan test Modules/Billing/Tests/Unit/`
- No direct database queries in services (use repositories or models)
- Proper exception handling with meaningful messages
```

---

## Context & Technical Details

### Existing Services Architecture
```
Modules/Billing/Services/
├── AnalyticsService.php      # KPIs, metrics
├── AnomalyDetectionService.php
├── ForecastingService.php
├── InvoiceGenerationService.php
├── PricingEngineService.php
├── ProrationCalculator.php
└── RevenueRecognitionService.php
```

### Design Patterns in Use
- Service classes are injected via constructor
- Events dispatched for cross-cutting concerns
- Jobs for async processing

---

## Task Checklist

### 2.1 RetainerService
- [ ] Create `Modules/Billing/Services/RetainerService.php`
  ```php
  class RetainerService
  {
      public function purchaseRetainer(Company $company, float $hours, int $priceCents, ?Carbon $expiresAt): Retainer
      public function deductHours(Retainer $retainer, float $hours, BillableEntry $entry): void
      public function getActiveRetainer(Company $company): ?Retainer
      public function checkLowBalanceThreshold(Retainer $retainer, float $threshold = 5.0): bool
      public function expireOverdueRetainers(): int // Returns count expired
  }
  ```
- [ ] Create `RetainerLowBalanceNotification` notification class
- [ ] Create unit tests: `RetainerServiceTest.php`

### 2.2 CreditNoteService
- [ ] Create `Modules/Billing/Services/CreditNoteService.php`
  ```php
  class CreditNoteService
  {
      public function issueCreditNote(Invoice $invoice, int $amountCents, string $reason, User $issuedBy): CreditNote
      public function applyCreditNote(CreditNote $creditNote): void
      public function getCreditNotesForInvoice(Invoice $invoice): Collection
      public function getCreditNotesForCompany(Company $company): Collection
  }
  ```
- [ ] Dispatch `CreditNoteIssued` event
- [ ] Create unit tests: `CreditNoteServiceTest.php`

### 2.3 AuditService
- [ ] Create `Modules/Billing/Services/AuditService.php`
  ```php
  class AuditService
  {
      public function log(Model $auditable, string $event, ?array $oldValues = null, ?array $newValues = null): BillingAuditLog
      public function getLogsForEntity(string $type, int $id): Collection
      public function getLogsForUser(User $user, ?Carbon $since = null): Collection
      public function getRecentActivity(int $limit = 50): Collection
  }
  ```
- [ ] Create model observers for automatic audit logging:
  - `InvoiceObserver` - log status changes, approvals
  - `PaymentObserver` - log all payments
  - `PriceOverrideObserver` - log changes
- [ ] Register observers in `BillingServiceProvider`

### 2.4 DisputeService
- [ ] Create `Modules/Billing/Services/DisputeService.php`
  ```php
  class DisputeService
  {
      public function flagAsDisputed(Invoice $invoice, string $reason, User $flaggedBy): void
      public function resolveDispute(Invoice $invoice, string $resolution, User $resolvedBy): void
      public function pauseDunning(Invoice $invoice): void
      public function resumeDunning(Invoice $invoice): void
      public function getDisputedInvoices(): Collection
  }
  ```
- [ ] Integrate with `SendPaymentReminderJob` to skip disputed invoices
- [ ] Create unit tests

### 2.5 ContractService
- [ ] Create `Modules/Billing/Services/ContractService.php`
  ```php
  class ContractService
  {
      public function getExpiringContracts(int $daysAhead = 60): Collection
      public function sendRenewalReminder(Subscription $subscription, int $daysRemaining): void
      public function markAsRenewed(Subscription $subscription, ?Carbon $newEndDate = null): void
      public function markAsChurned(Subscription $subscription, string $reason): void
  }
  ```
- [ ] Create scheduled command: `billing:check-contract-renewals`
- [ ] Create unit tests

### 2.6 ClientHealthService
- [ ] Create `Modules/Billing/Services/ClientHealthService.php`
  ```php
  class ClientHealthService
  {
      public function calculateHealthScore(Company $company): array // Returns ['score' => 0-100, 'factors' => [...]]
      public function getAtRiskClients(int $threshold = 40): Collection
      public function getHealthFactors(Company $company): array
      // Factors: profitability, avg_days_to_pay, ticket_volume_trend, contract_age, last_interaction
  }
  ```
- [ ] Create unit tests

### 2.7 QuoteTrackingService
- [ ] Create `Modules/Billing/Services/QuoteTrackingService.php`
  ```php
  class QuoteTrackingService
  {
      public function recordView(Quote $quote, string $ipAddress, ?string $userAgent = null): void
      public function recordAcceptance(Quote $quote, string $signerName, string $signerEmail, ?string $signatureData = null): void
      public function getViewHistory(Quote $quote): Collection
      public function notifyOwnerOfView(Quote $quote): void
  }
  ```
- [ ] Create `QuoteViewedNotification` notification class
- [ ] Create unit tests

### 2.8 QuoteConversionService
- [ ] Create `Modules/Billing/Services/QuoteConversionService.php`
  ```php
  class QuoteConversionService
  {
      public function convertToInvoice(Quote $quote): Invoice
      public function convertToSubscription(Quote $quote): Subscription
      public function convertToInvoiceAndSubscription(Quote $quote): array // Returns ['invoice' => ..., 'subscription' => ...]
      public function triggerProcurement(Quote $quote): void // If hardware items
  }
  ```
- [ ] Dispatch `QuoteConverted` event
- [ ] Create unit tests

### 2.9 ExportService
- [ ] Create `Modules/Billing/Services/ExportService.php`
  ```php
  class ExportService
  {
      public function exportToExcel(string $reportType, array $filters = []): string // Returns file path
      public function exportInvoicesToPdf(array $invoiceIds): string // Returns ZIP path
      public function exportArAging(array $filters = []): string
      public function exportPaymentsRegister(Carbon $startDate, Carbon $endDate): string
  }
  ```
- [ ] Use Laravel Excel package (maatwebsite/excel)
- [ ] Store exports in `storage/app/exports/`

### 2.10 Enhance AnalyticsService
- [ ] Add method: `calculateEffectiveHourlyRate(Company $company): float`
- [ ] Add method: `calculateEffectiveHourlyRateAll(): Collection`
- [ ] Add method: `getMetricsWithComparison(string $period = 'mom'): array` // MoM or YoY
- [ ] Add unit tests for new methods

### 2.11 AlertService
- [ ] Create `Modules/Billing/Services/AlertService.php`
  ```php
  class AlertService
  {
      public function checkThresholds(): array // Returns triggered alerts
      public function getThresholdConfig(): array
      public function setThreshold(string $metric, float $value): void
      public function sendAlert(string $alertType, array $data): void
  }
  ```
- [ ] Default thresholds: Churn > 5%, AR Aging > $50k, MRR drop > 10%
- [ ] Create scheduled command: `billing:check-alerts`

---

## Completion Verification

```bash
# Run all unit tests
php artisan test Modules/Billing/Tests/Unit/ --filter=Service

# Verify services are injectable
php artisan tinker --execute="
    app(Modules\Billing\Services\RetainerService::class);
    app(Modules\Billing\Services\CreditNoteService::class);
    app(Modules\Billing\Services\AuditService::class);
    echo 'All services instantiated successfully';
"

# Test a specific service
php artisan tinker --execute="
    \$service = app(Modules\Billing\Services\ClientHealthService::class);
    \$company = Modules\Billing\Models\Company::first();
    print_r(\$service->calculateHealthScore(\$company));
"
```

---

## Downstream Dependencies
- **Batch 3** (UI): Controllers will inject these services
- **Batch 4** (Integrations): Alert and Export services needed
- **Batch 5** (Jobs): Services called by background jobs

# Batch 4: Integrations & External Services

**Execution Order:** Fourth (Depends on Batch 1 & 2)
**Parallelization:** All integrations can be developed in parallel
**Estimated Effort:** 4-5 days
**Priority:** P2-P3

---

## Agent Prompt

```
You are a Senior Laravel Backend Engineer specializing in third-party API integrations.

Your task is to implement external service integrations for the FinOps billing module. These integrations connect the billing system to accounting software, notification services, and RMM platforms.

## Primary Objectives
1. Create robust, error-tolerant API clients
2. Implement webhook handlers for incoming data
3. Build retry mechanisms for failed requests
4. Log all external API interactions

## Technical Standards
- Integration services in `Modules/Billing/Services/Integrations/`
- Webhook controllers in `Modules/Billing/Http/Controllers/Webhooks/`
- Use Laravel HTTP client with retry() and timeout()
- Store API credentials in config/services.php (via .env)
- Queue long-running sync operations

## Error Handling
- Catch all external API exceptions
- Log failures with context
- Implement exponential backoff for retries
- Notify admin on repeated failures
- Never expose API keys in logs or errors

## Files to Reference
- Existing integrations: `Modules/Billing/Services/AccountingExportService.php`
- Existing webhooks: `Modules/Billing/Http/Controllers/Webhooks/`
- Config: `config/services.php`

## Validation Criteria
- All integrations have unit tests with mocked API responses
- Webhook endpoints return proper HTTP status codes
- Failed syncs are logged and retryable
- API keys stored securely in environment
```

---

## Context & Technical Details

### Existing Integrations
```
Modules/Billing/Services/
├── AccountingExportService.php  # QuickBooks sync
└── (Stripe handled by Laravel Cashier)

Modules/Billing/Http/Controllers/Webhooks/
├── StripeWebhookController.php
└── RmmWebhookController.php
```

### Integration Patterns
- Services wrap API clients
- Jobs handle async sync operations
- Webhooks validate signatures before processing
- Config stored in `config/services.php`

---

## Task Checklist

### 4.1 Slack Integration

#### Service
- [ ] Create `Modules/Billing/Services/Integrations/SlackService.php`
  ```php
  class SlackService
  {
      public function sendNotification(string $channel, string $message, array $blocks = []): bool
      public function sendPaymentReceivedAlert(Payment $payment): void
      public function sendQuoteAcceptedAlert(Quote $quote): void
      public function sendAnomalyAlert(Invoice $invoice, float $score): void
  }
  ```

#### Configuration
- [ ] Add to `config/services.php`:
  ```php
  'slack' => [
      'webhook_url' => env('SLACK_WEBHOOK_URL'),
      'channel' => env('SLACK_CHANNEL', '#billing'),
  ],
  ```

#### Message Formatting
- [ ] Use Slack Block Kit for rich messages
- [ ] Include: Amount, Client, Link to detail
- [ ] Color-code by type (green=payment, blue=quote)

### 4.2 Microsoft Teams Integration

#### Service
- [ ] Create `Modules/Billing/Services/Integrations/TeamsService.php`
  ```php
  class TeamsService
  {
      public function sendNotification(string $message, array $card = []): bool
      public function sendInvoiceAlert(Invoice $invoice, string $alertType): void
  }
  ```

#### Configuration
- [ ] Add Teams webhook URL to config
- [ ] Support Adaptive Cards format

### 4.3 SMS Notifications (Twilio)

#### Service
- [ ] Create `Modules/Billing/Services/Integrations/SmsService.php`
  ```php
  class SmsService
  {
      public function send(string $phone, string $message): bool
      public function sendOverdueReminder(Invoice $invoice): void
      public function sendPaymentConfirmation(Payment $payment): void
  }
  ```

#### Configuration
- [ ] Twilio Account SID, Auth Token, From Number
- [ ] Rate limiting (max 1 SMS per client per day)

#### Opt-In Management
- [ ] Add `sms_notifications_enabled` to Company
- [ ] Add phone number validation
- [ ] Respect opt-out preferences

### 4.4 Xero Accounting Integration

#### Service
- [ ] Create `Modules/Billing/Services/Integrations/XeroService.php`
  ```php
  class XeroService
  {
      public function connect(): string // Returns auth URL
      public function handleCallback(string $code): void
      public function syncInvoice(Invoice $invoice): string // Returns Xero ID
      public function syncPayment(Payment $payment): string
      public function getContacts(): Collection
      public function createContact(Company $company): string
  }
  ```

#### OAuth Flow
- [ ] Implement OAuth 2.0 with PKCE
- [ ] Store tokens in `billing_settings` or dedicated table
- [ ] Handle token refresh

#### Sync Logic
- [ ] Map local Invoice to Xero Invoice
- [ ] Map Company to Xero Contact
- [ ] Queue sync operations

### 4.5 Additional RMM Webhooks

#### Datto RMM Handler
- [ ] Create `Modules/Billing/Http/Controllers/Webhooks/DattoWebhookController.php`
- [ ] Handle device count updates
- [ ] Handle alert notifications
- [ ] Verify webhook signature

#### NinjaRMM Handler
- [ ] Create `Modules/Billing/Http/Controllers/Webhooks/NinjaWebhookController.php`
- [ ] Handle device sync
- [ ] Map device types to billing products

#### ConnectWise Manage Sync
- [ ] Create `Modules/Billing/Services/Integrations/ConnectWiseService.php`
- [ ] Sync companies
- [ ] Sync tickets (for time entry reference)
- [ ] Sync products/services

### 4.6 Generic CSV Export

#### Service Enhancement
- [ ] Add to `ExportService.php`:
  ```php
  public function exportToGenericCsv(string $reportType, array $filters = []): string
  public function getExportFormats(): array // Excel, CSV, PDF
  ```

#### Supported Exports
- [ ] Invoices (for any accounting system)
- [ ] Payments
- [ ] AR Aging
- [ ] Time entries

#### CSV Mapping
- [ ] Configurable column mapping
- [ ] Date format options
- [ ] Delimiter options (comma, semicolon, tab)

### 4.7 Payment Gateway: PayPal

#### Service
- [ ] Create `Modules/Billing/Services/Integrations/PayPalService.php`
  ```php
  class PayPalService
  {
      public function createOrder(Invoice $invoice): array // Returns order ID and approval URL
      public function captureOrder(string $orderId): Payment
      public function handleWebhook(Request $request): void
  }
  ```

#### Portal Integration
- [ ] Add PayPal button to payment modal
- [ ] Redirect flow: Portal → PayPal → Return URL → Confirm

#### Webhook Handler
- [ ] Create `Modules/Billing/Http/Controllers/Webhooks/PayPalWebhookController.php`
- [ ] Handle: PAYMENT.CAPTURE.COMPLETED
- [ ] Verify webhook signature

### 4.8 GoCardless (SEPA/BACS)

#### Service
- [ ] Create `Modules/Billing/Services/Integrations/GoCardlessService.php`
  ```php
  class GoCardlessService
  {
      public function createMandate(Company $company): string // Returns redirect flow URL
      public function createPayment(Invoice $invoice, string $mandateId): Payment
      public function handleWebhook(Request $request): void
  }
  ```

#### Mandate Flow
- [ ] Client sets up direct debit mandate
- [ ] Store mandate ID on Company
- [ ] Use mandate for recurring payments

### 4.9 In-App Notification System

#### Database
- [ ] Create migration: `create_notifications_table` (if not using Laravel's)
- [ ] Fields: user_id, type, data (JSON), read_at

#### Service
- [ ] Create `Modules/Billing/Services/NotificationService.php`
  ```php
  class NotificationService
  {
      public function notify(User $user, string $type, array $data): void
      public function getUnread(User $user): Collection
      public function markAsRead(int $notificationId): void
      public function markAllAsRead(User $user): void
  }
  ```

#### UI Component
- [ ] Create notification bell component
- [ ] Dropdown showing recent notifications
- [ ] Badge count for unread
- [ ] Real-time updates via polling or WebSocket

### 4.10 Benchmark API Integration

#### Service
- [ ] Create `Modules/Billing/Services/Integrations/BenchmarkService.php`
  ```php
  class BenchmarkService
  {
      public function getIndustryBenchmarks(): array
      public function compareMetric(string $metric, float $value): array // Returns percentile, avg, etc.
  }
  ```

#### Data Sources (Evaluate)
- [ ] HTG Peer Groups API
- [ ] Service Leadership API
- [ ] ConnectWise benchmark data
- [ ] Fallback: Static industry averages

---

## Completion Verification

```bash
# Test Slack integration
php artisan tinker --execute="
    app(\Modules\Billing\Services\Integrations\SlackService::class)
        ->sendNotification('#test', 'Test message from FinOps');
"

# Test webhook endpoints
curl -X POST http://localhost/webhooks/datto \
  -H 'Content-Type: application/json' \
  -d '{"event": "device.count", "data": {}}'

# Verify integration configs
php artisan config:show services
```

---

## Downstream Dependencies
- **Batch 5** (Jobs): Integration services called by notification jobs
- **Batch 6** (Testing): Integration tests with mocked APIs

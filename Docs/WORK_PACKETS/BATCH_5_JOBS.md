# Batch 5: Jobs & Automation

**Execution Order:** Fifth (Depends on Batch 1, 2, 4)
**Parallelization:** Jobs can be developed in parallel
**Estimated Effort:** 3-4 days
**Priority:** P2

---

## Agent Prompt

```
You are a Senior Laravel Backend Engineer specializing in queue systems and scheduled tasks.

Your task is to implement automated jobs for the FinOps billing module. These jobs handle recurring processes like billing cycles, payment reminders, and anomaly detection.

## Primary Objectives
1. Create queueable jobs for async processing
2. Implement scheduled commands for recurring tasks
3. Build robust failure handling and retry logic
4. Ensure idempotent job execution

## Technical Standards
- Jobs in `Modules/Billing/Jobs/`
- Console commands in `Modules/Billing/Console/Commands/`
- Use database queue driver for persistence
- Implement `ShouldQueue` with `InteractsWithQueue`
- Add jobs to schedule in ServiceProvider

## Job Design Principles
- Jobs should be idempotent (safe to retry)
- Store job state for debugging
- Use unique job IDs to prevent duplicates
- Set appropriate timeout and retry values
- Batch large operations to avoid timeouts

## Files to Reference
- Existing commands: `app/Console/Commands/`
- Queue config: `config/queue.php`
- Scheduler: `app/Console/Kernel.php`
- Service classes from Batch 2

## Validation Criteria
- All jobs have unit tests
- Jobs handle failures gracefully
- Schedule entries documented
- No duplicate job execution
```

---

## Context & Technical Details

### Queue Configuration
```php
// config/queue.php - using database driver
'default' => env('QUEUE_CONNECTION', 'database'),
```

### Schedule Registration
Jobs should be registered in the module's ServiceProvider boot method:
```php
// Modules/Billing/Providers/BillingServiceProvider.php
public function boot()
{
    $this->app->booted(function () {
        $schedule = $this->app->make(Schedule::class);
        // Register scheduled tasks here
    });
}
```

### Existing Jobs Pattern
```php
class ExampleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 3;
    public $backoff = [60, 300, 900]; // Exponential backoff
    
    public function handle(): void
    {
        // Job logic
    }
    
    public function failed(Throwable $exception): void
    {
        // Failure handling
    }
}
```

---

## Task Checklist

### 5.1 Scheduled Invoice Generation

#### Job
- [ ] Create `Modules/Billing/Jobs/GenerateRecurringInvoicesJob.php`
  ```php
  class GenerateRecurringInvoicesJob implements ShouldQueue
  {
      public $queue = 'billing';
      
      public function handle(InvoiceService $service): void
      {
          // Get all clients due for invoice generation
          // Process each with individual try/catch
          // Log success/failure per client
      }
  }
  ```

#### Console Command
- [ ] Create `Modules/Billing/Console/Commands/GenerateInvoicesCommand.php`
- [ ] Support flags: `--company-id`, `--dry-run`, `--period`

#### Schedule
- [ ] Daily at 2:00 AM for monthly invoices
- [ ] Weekly on Monday for weekly invoices

### 5.2 Payment Reminder Jobs

#### Upcoming Payment Reminder (7 days)
- [ ] Create `Modules/Billing/Jobs/SendUpcomingPaymentReminderJob.php`
- [ ] Query: Invoices due in 7 days, not paid, not already reminded
- [ ] Send email via `InvoiceUpcomingNotification`
- [ ] Record reminder sent timestamp

#### Overdue Payment Reminder (1 day)
- [ ] Create `Modules/Billing/Jobs/SendOverduePaymentReminderJob.php`
- [ ] Query: Invoices 1 day overdue
- [ ] Send email + optional SMS

#### Escalation Reminder (7+ days)
- [ ] Create `Modules/Billing/Jobs/SendEscalationReminderJob.php`
- [ ] Query: Invoices 7, 14, 30 days overdue
- [ ] Escalate notification urgency
- [ ] Optionally notify account manager

### 5.3 Auto-Charge Job

#### Job
- [ ] Create `Modules/Billing/Jobs/ProcessAutoPaymentsJob.php`
  ```php
  class ProcessAutoPaymentsJob implements ShouldQueue
  {
      public function handle(): void
      {
          $invoices = Invoice::query()
              ->where('status', 'sent')
              ->whereDate('due_date', '<=', today())
              ->whereHas('company', fn($q) => $q->where('auto_pay_enabled', true))
              ->get();
          
          foreach ($invoices as $invoice) {
              ChargeInvoiceJob::dispatch($invoice);
          }
      }
  }
  ```

#### Individual Charge Job
- [ ] Create `Modules/Billing/Jobs/ChargeInvoiceJob.php`
- [ ] Handle individual invoice charging
- [ ] Log success/failure
- [ ] Send receipt or failure notification

#### Schedule
- [ ] Daily at 6:00 AM

### 5.4 Anomaly Detection Job

#### Job
- [ ] Create `Modules/Billing/Jobs/DetectBillingAnomaliesJob.php`
  ```php
  class DetectBillingAnomaliesJob implements ShouldQueue
  {
      public function handle(AnomalyDetectionService $service): void
      {
          $invoices = Invoice::query()
              ->where('status', 'draft')
              ->where('anomaly_checked', false)
              ->get();
          
          foreach ($invoices as $invoice) {
              $score = $service->calculateAnomalyScore($invoice);
              
              if ($score > 0.7) {
                  $service->flagForReview($invoice, $score);
              }
          }
      }
  }
  ```

#### Schedule
- [ ] Hourly (or after batch invoice generation)

### 5.5 Retainer Drawdown Check

#### Job
- [ ] Create `Modules/Billing/Jobs/CheckRetainerLevelsJob.php`
  ```php
  class CheckRetainerLevelsJob implements ShouldQueue
  {
      public function handle(RetainerService $service): void
      {
          $retainers = Retainer::query()
              ->where('status', 'active')
              ->get();
          
          foreach ($retainers as $retainer) {
              $usagePercent = $service->calculateUsagePercent($retainer);
              
              if ($usagePercent >= 80) {
                  // Send low balance warning
              }
              
              if ($usagePercent >= 100) {
                  // Handle overage
              }
          }
      }
  }
  ```

#### Notifications
- [ ] Create `RetainerLowBalanceNotification.php`
- [ ] Create `RetainerDepletedNotification.php`

#### Schedule
- [ ] Daily at 8:00 AM

### 5.6 RMM Device Sync

#### Job
- [ ] Create `Modules/Billing/Jobs/SyncRmmDeviceCountsJob.php`
- [ ] Pull device counts from all connected RMM platforms
- [ ] Update `billing_quantities` table
- [ ] Flag significant changes for review

#### Schedule
- [ ] Every 6 hours

### 5.7 Accounting Sync

#### QuickBooks Sync Job
- [ ] Create `Modules/Billing/Jobs/SyncToQuickBooksJob.php`
- [ ] Batch sync invoices created since last sync
- [ ] Batch sync payments
- [ ] Handle rate limits

#### Xero Sync Job
- [ ] Create `Modules/Billing/Jobs/SyncToXeroJob.php`
- [ ] Similar to QuickBooks sync

#### Schedule
- [ ] Every 4 hours or on-demand

### 5.8 Report Generation Jobs

#### Weekly AR Aging Report
- [ ] Create `Modules/Billing/Jobs/GenerateWeeklyArReportJob.php`
- [ ] Generate AR Aging report PDF
- [ ] Email to finance team

#### Monthly Revenue Summary
- [ ] Create `Modules/Billing/Jobs/GenerateMonthlyRevenueReportJob.php`
- [ ] Generate MRR/ARR summary
- [ ] Include trend charts
- [ ] Email to executives

#### Schedule
- [ ] AR Aging: Every Monday at 7 AM
- [ ] Revenue Summary: First of month at 8 AM

### 5.9 Data Cleanup Jobs

#### Archive Old Invoices
- [ ] Create `Modules/Billing/Jobs/ArchiveOldInvoicesJob.php`
- [ ] Move invoices > 7 years to archive table
- [ ] Maintain audit trail

#### Cleanup Draft Invoices
- [ ] Create `Modules/Billing/Jobs/CleanupAbandonedDraftsJob.php`
- [ ] Delete draft invoices older than 30 days with no activity
- [ ] Notify creator before deletion

#### Schedule
- [ ] Monthly on 15th

### 5.10 Alert Notification Dispatcher

#### Job
- [ ] Create `Modules/Billing/Jobs/DispatchAlertNotificationsJob.php`
  ```php
  class DispatchAlertNotificationsJob implements ShouldQueue
  {
      public function __construct(
          public string $alertType,
          public array $data,
          public array $channels = ['email', 'slack', 'in_app']
      ) {}
      
      public function handle(NotificationService $notifications): void
      {
          foreach ($this->channels as $channel) {
              $notifications->send($channel, $this->alertType, $this->data);
          }
      }
  }
  ```

---

## Schedule Summary

```php
// Modules/Billing/Providers/BillingServiceProvider.php

protected function registerScheduledTasks(Schedule $schedule): void
{
    // Daily tasks
    $schedule->job(new GenerateRecurringInvoicesJob)->dailyAt('02:00');
    $schedule->job(new ProcessAutoPaymentsJob)->dailyAt('06:00');
    $schedule->job(new CheckRetainerLevelsJob)->dailyAt('08:00');
    $schedule->job(new SendUpcomingPaymentReminderJob)->dailyAt('09:00');
    $schedule->job(new SendOverduePaymentReminderJob)->dailyAt('10:00');
    
    // Hourly tasks
    $schedule->job(new DetectBillingAnomaliesJob)->hourly();
    
    // Every few hours
    $schedule->job(new SyncRmmDeviceCountsJob)->everyFourHours();
    $schedule->job(new SyncToQuickBooksJob)->everyFourHours();
    
    // Weekly tasks
    $schedule->job(new GenerateWeeklyArReportJob)->weeklyOn(1, '07:00');
    $schedule->job(new SendEscalationReminderJob)->weeklyOn(1, '10:00');
    
    // Monthly tasks
    $schedule->job(new GenerateMonthlyRevenueReportJob)->monthlyOn(1, '08:00');
    $schedule->job(new ArchiveOldInvoicesJob)->monthlyOn(15, '03:00');
}
```

---

## Completion Verification

```bash
# Test individual job
php artisan tinker --execute="
    \Modules\Billing\Jobs\GenerateRecurringInvoicesJob::dispatchSync();
"

# Verify schedule
php artisan schedule:list

# Test queue worker
php artisan queue:work --queue=billing --once

# Check failed jobs
php artisan queue:failed
```

---

## Downstream Dependencies
- **Batch 6** (Testing): Job-specific tests
- Jobs consume services from **Batch 2**
- Jobs use integrations from **Batch 4**

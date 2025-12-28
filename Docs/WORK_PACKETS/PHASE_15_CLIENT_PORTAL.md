# Phase 15: Client Portal Self-Service Enhancements

## Overview
**Priority:** HIGH  
**Estimated Effort:** 28-36 hours  
**UX Pattern:** Guided Journey + Resilient Design  

## Key Features

### 1. Invoice PDF Download (2-3 hours)
- Button in invoice list and detail modal
- Instant generation (< 2 seconds)
- Professional branded template
- Watermark for unpaid invoices
- Filename: `Invoice_[Number]_[Client]_[Date].pdf`

### 2. Auto-Pay Configuration (6-8 hours)
- 3-step wizard: Payment Method → Schedule → Confirmation
- Job: `ProcessAutopayInvoicesJob` (runs daily)
- Retry logic: 3 attempts, 3 days apart
- Email confirmations (before charge, after success, on failure)
- Pause/disable anytime

### 3. Self-Service Profile Editing (5-6 hours)
- Editable fields: Address, contacts, tax ID, industry
- Address verification API (SmartyStreets)
- Approval workflow for critical fields
- Real-time validation feedback
- Audit trail of changes

### 4. Team Member Management (5-6 hours)
- Portal roles: Admin, Billing, Viewer
- Email invitation with magic links
- Email domain verification
- Max users limit (default: 10)
- Last login tracking

### 5. Procurement Tracking (6-8 hours)
- Order status timeline: Quote → Ordered → Shipped → Delivered
- Carrier tracking (FedEx, UPS, USPS APIs)
- Email/SMS notifications
- New table: `procurement_orders`
- Webhook integration for auto-updates

### 6. Invoice Line Item Transparency (4-5 hours)
- Expandable line item details
- Show: Ticket #, Technician, Date, Requester
- "Question this charge" link
- Privacy-configurable display
- Link billable_entries to tickets

### 7. Enhanced Dispute Submission (3-4 hours)
- Per-line-item dispute capability
- Multi-file upload (drag-and-drop)
- Dispute reasons dropdown
- Status tracking timeline
- Pauses dunning automatically

## Database Changes

```sql
-- Auto-pay settings
ALTER TABLE companies ADD COLUMN auto_pay_enabled BOOLEAN DEFAULT FALSE;
ALTER TABLE companies ADD COLUMN auto_pay_method_id INT NULL;
ALTER TABLE companies ADD COLUMN auto_pay_retry_attempts INT DEFAULT 3;
ALTER TABLE companies ADD COLUMN auto_pay_grace_days INT DEFAULT 3;

-- Procurement tracking
CREATE TABLE procurement_orders (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  company_id BIGINT NOT NULL,
  quote_id BIGINT NULL,
  status ENUM('quote_accepted', 'ordered', 'processing', 'shipped', 'delivered', 'installed'),
  tracking_number VARCHAR(255) NULL,
  carrier VARCHAR(100) NULL,
  ship_date DATE NULL,
  delivery_date DATE NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Line item attribution
ALTER TABLE billable_entries ADD COLUMN ticket_id BIGINT NULL;
ALTER TABLE billable_entries ADD COLUMN requester_id BIGINT NULL;
```

## Component Library

- `x-auto-pay-wizard` - 3-step configuration
- `x-payment-method-selector` - Card/ACH chooser
- `x-invite-user-modal` - Team invitation form
- `x-order-timeline` - Progress stepper
- `x-line-item-detail-expander` - Accordion for details
- `x-dispute-wizard` - Enhanced dispute flow

## Technical Notes

### Auto-Pay Job
```php
class ProcessAutopayInvoicesJob implements ShouldQueue
{
    public function handle()
    {
        $companies = Company::where('auto_pay_enabled', true)->get();
        
        foreach ($companies as $company) {
            $invoices = Invoice::where('company_id', $company->id)
                ->where('status', 'sent')
                ->where('due_date', '<=', now()->addDays($company->auto_pay_grace_days))
                ->get();
                
            foreach ($invoices as $invoice) {
                try {
                    $this->paymentService->processAutopay($invoice, $company);
                } catch (\Exception $e) {
                    $this->handleAutopayFailure($invoice, $company, $e);
                }
            }
        }
    }
}
```

## Success Metrics

- 60%+ clients enable auto-pay within 90 days
- 50% reduction in "how do I pay" support tickets
- 80% disputes resolved within 48 hours
- 90%+ profile accuracy
- Client portal NPS > 60

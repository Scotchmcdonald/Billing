# Phase 16: Sales Pipeline & Quote-to-Cash

## Overview
**Priority:** HIGH  
**Estimated Effort:** 32-40 hours  
**UX Pattern:** Control Tower + Guided Journey  

## Key Features

### 1. Pipeline Kanban Dashboard (8-10 hours)
- Route: `/billing/sales/pipeline`
- 6 stages: Draft → Sent → Viewed → Negotiating → Accepted → Lost
- Drag-and-drop between stages (SortableJS)
- Quote cards show: Number, client, value, margin %, days in stage
- Metrics panel: Total value, conversion rate, average time per stage
- Filters: Date, value range, agent, client type

### 2. Quote View Tracking & Notifications (5-6 hours)
- Track: First view, re-views, duration
- Notifications: In-app, email, Slack, SMS (optional)
- Rules: Notify on first view, re-views after 7+ days, long duration (5+ min)
- Table: `quote_views` (id, quote_id, viewer_ip, viewed_at, duration)
- Privacy: Respect Do Not Track, anonymize IPs after 30 days

### 3. Quote Cloning (3-4 hours)
- Button: "Clone Quote" in actions dropdown
- Modal wizard: Source → New Client → Adjustments → Save
- Smart adjustments: Client pricing, expiration date reset
- Link to original quote (audit trail)
- Service method: `QuoteService::cloneQuote()`

### 4. Margin Display & Validation (4-5 hours)
- Component: `x-margin-indicator`
- Real-time calculation on changes
- Color-coded: Green (above target), Yellow (near floor), Red (below floor)
- Display: Per line item and overall quote
- Data: Product costs from `products.cost`

### 5. Margin Floor Enforcement (4-5 hours)
- Company setting: `margin_floor_percent`
- Warning modal when below floor
- Options: Adjust pricing, Request approval, Override (if permission)
- Approval workflow: Notification to manager, approve/reject with notes
- Cannot send quote until approved or adjusted

### 6. Product Bundles (6-8 hours)
- CRUD: `/billing/bundles` for management
- Structure: Name, description, products with quantities, optional discount
- Quote builder: "Add Bundle" button, search/filter, customize
- Templates: New Employee Setup, Office Move, Server Upgrade, Backup & Security
- Pricing: Sum of items with bundle discount applied

### 7. One-Click Quote Conversion (4-5 hours)
- Button: "Convert to Order" (prominent, primary)
- Modal: Preview, select type (Invoice/Subscription/Hybrid)
- Options: Due date, start date, send immediately
- Service: Existing `QuoteConversionService`
- Post-conversion: Redirect, success message, update pipeline

### 8. Quote Expiration Management (3-4 hours)
- Field: `expires_at` (default 30 days from sent)
- Job: `ExpireQuotesJob` (runs daily)
- Reminders: 7 days before, 1 day before
- Client experience: "Expired" badge, request extension option
- Renewal: "Renew Quote" button, optionally update pricing

## Database Changes

```sql
-- Quote view tracking
CREATE TABLE quote_views (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  quote_id BIGINT NOT NULL,
  viewer_ip VARCHAR(45) NULL,
  viewer_user_agent TEXT NULL,
  viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  duration_seconds INT NULL
);

-- Quote expiration
ALTER TABLE quotes ADD COLUMN expires_at TIMESTAMP NULL;
ALTER TABLE quotes ADD COLUMN bulk_operation_id VARCHAR(36) NULL;

-- Bundle tracking
ALTER TABLE quote_line_items ADD COLUMN bundle_id BIGINT NULL;

-- Company settings
ALTER TABLE company_settings ADD COLUMN margin_floor_percent DECIMAL(5,2) DEFAULT 20.00;
```

## Component Library

- `x-pipeline-kanban` - Drag-and-drop board
- `x-quote-card` - Summary card for kanban
- `x-pipeline-filters` - Advanced filtering
- `x-margin-indicator` - Real-time margin display
- `x-gauge-chart` - Margin gauge visualization
- `x-bundle-selector` - Bundle chooser in quote builder

## Technical Notes

### Pipeline Kanban Data Structure
```php
class PipelineController
{
    public function getKanbanData()
    {
        $stages = ['draft', 'sent', 'viewed', 'negotiating', 'accepted', 'lost'];
        
        $data = [];
        foreach ($stages as $stage) {
            $data[$stage] = Quote::where('status', $stage)
                ->with('company')
                ->get()
                ->map(function ($quote) {
                    return [
                        'id' => $quote->id,
                        'number' => $quote->number,
                        'client' => $quote->company->name,
                        'value' => $quote->total,
                        'margin' => $quote->margin_percent,
                        'days_in_stage' => $quote->updated_at->diffInDays(now()),
                    ];
                });
        }
        
        return response()->json($data);
    }
}
```

### Quote View Tracking JavaScript
```javascript
// On public quote page
if (!navigator.doNotTrack) {
    let startTime = Date.now();
    
    // Track view
    fetch('/api/quotes/{{ $quote->uuid }}/track-view', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'view' })
    });
    
    // Track duration on page unload
    window.addEventListener('beforeunload', () => {
        let duration = Math.floor((Date.now() - startTime) / 1000);
        navigator.sendBeacon('/api/quotes/{{ $quote->uuid }}/track-view', 
            JSON.stringify({ action: 'duration', duration: duration })
        );
    });
}
```

## Success Metrics

- 100% quote tracking in pipeline within 1 week
- 15% improvement in quote-to-cash conversion
- 50% faster follow-up after client views quote
- 95% of quotes meet or exceed margin floor
- 40% of quotes include at least one bundle
- 70% reduction in time to convert quote to invoice

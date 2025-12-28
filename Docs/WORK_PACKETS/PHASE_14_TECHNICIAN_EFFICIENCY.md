# Phase 14: Technician Efficiency & Context Awareness

## Overview
**Priority:** MEDIUM-LOW  
**Estimated Effort:** 24-32 hours  
**UX Pattern:** State-Aware + Contextual Indicators  

## Key Features

### 1. Client AR Status Indicator (4-5 hours)
- Color-coded badges in Work Order Panel
- Statuses: Current (green), 1-30 days (yellow), 31-60 (orange), 60+ (red)
- Tooltip with days overdue and amount
- Action guidance based on status
- 5-minute cache per company

### 2. Contract Coverage Lookup (5-6 hours)
- Real-time check before time entry
- Visual states: Covered ✅ / Partially ⚠️ / Not Covered ❌ / Unknown ❓
- Links to subscription details
- Service: `ContractCoverageService`
- Configurable coverage rules engine

### 3. My Utilization Dashboard (5-6 hours)
- Route: `/billing/technician/my-stats`
- Metrics: Billable hours, utilization rate, ticket resolution time
- Target indicators with streak counters
- Gamification: badges, anonymous peer comparison
- Mobile-first design

### 4. Daily Timesheet View (5-6 hours)
- Route: `/billing/technician/timesheet`
- High-density table with inline editing
- Quick timer controls per ticket
- Keyboard shortcuts (Tab, Enter, Space)
- Real-time save on blur

### 5. Barcode Scanning for Hardware (4-5 hours)
- HTML5 camera API integration
- Support: QR codes, Code128, EAN
- Library: QuaggaJS or ZXing
- Auto-capture and decode
- Fallback to manual entry

### 6. Real-Time Inventory Levels (3-4 hours)
- Stock quantity with color indicators
- Integration with Inventory module (optional)
- Reserved quantity tracking
- Alternative part suggestions
- WebSocket or 30s polling updates

## Database Changes

```sql
-- Add technician permissions
INSERT INTO permissions (name) VALUES 
  ('billing.view_ar_status'),
  ('billing.manage_parts');

-- Add utilization targets
ALTER TABLE users ADD COLUMN utilization_target DECIMAL(5,2) DEFAULT 70.00;
```

## Component Library

- `x-ar-status-badge` - Color-coded payment status
- `x-contract-coverage-indicator` - Coverage status display
- `x-utilization-gauge` - Circular progress gauge
- `x-daily-hours-chart` - Bar chart for week view
- `x-inline-time-entry` - Editable table cells
- `x-quick-timer` - Start/stop timer control
- `x-barcode-scanner` - Camera integration

## Technical Notes

### Contract Coverage Logic
```php
class ContractCoverageService
{
    public function checkCoverage(int $clientId, string $serviceType): string
    {
        $subscriptions = Subscription::active()
            ->where('company_id', $clientId)
            ->get();
            
        foreach ($subscriptions as $sub) {
            if ($sub->covers($serviceType)) {
                return 'covered';
            }
        }
        
        return 'not_covered';
    }
}
```

## Success Metrics

- 85%+ technician adoption of timesheet view
- 40% reduction in time entry overhead
- 30% fewer incorrect billability classifications
- 90% AR awareness before starting work

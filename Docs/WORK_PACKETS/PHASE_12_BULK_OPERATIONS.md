# Phase 12: Bulk Operations & Finance Admin Tools

## Overview
**Priority:** MEDIUM  
**Estimated Effort:** 20-28 hours  
**UX Pattern:** Guided Journey + Control Tower  
**Style Guide Compliance:** [APPLICATION_UX_UI_STANDARDS.md](../APPLICATION_UX_UI_STANDARDS.md)

## Objective
Enhance Finance Admin capabilities with bulk operations, advanced reporting, and improved audit visibility. Implements wizard patterns for complex operations and strengthens financial controls.

---

## User Stories Summary

1. **Bulk Price Override Manager** (P0, 6-8 hours)
2. **Pre-Flight Excel Export** (P1, 2-3 hours)
3. **Effective Hourly Rate in Profitability** (P1, 3-4 hours)
4. **Enhanced Audit Log Viewer** (P1, 4-5 hours)
5. **Invoice Internal Notes** (P2, 2-3 hours)
6. **Dunning Pause Control** (P1, 3-4 hours)

---

## Story 12.1: Bulk Price Override Manager

**Acceptance Criteria:**
- [ ] 3-step wizard (Selection → Configuration → Preview)
- [ ] Support for "All Clients", "Filtered by Tier", "Specific Selection"
- [ ] Percentage increase or flat amount adjustment
- [ ] Effective date selection (immediate or scheduled)
- [ ] Before/after comparison table in preview
- [ ] Typed confirmation required ("APPLY CHANGES")
- [ ] Dry-run preview before commit
- [ ] Rollback capability within 24 hours
- [ ] Audit log of all changes

**Technical Implementation:**

```php
// Service
class BulkOverrideService
{
    public function previewBulkUpdate(array $criteria, array $changes): array
    {
        $clients = $this->selectClients($criteria);
        
        $preview = $clients->map(function ($client) use ($changes) {
            $current = $this->pricingEngine->getCurrentPricing($client);
            $new = $this->applyChanges($current, $changes);
            
            return [
                'client' => $client,
                'current_pricing' => $current,
                'new_pricing' => $new,
                'difference' => $new - $current,
            ];
        });
        
        return [
            'affected_count' => $clients->count(),
            'total_impact' => $preview->sum('difference'),
            'preview' => $preview,
        ];
    }
    
    public function applyBulkUpdate(array $criteria, array $changes): void
    {
        DB::transaction(function () use ($criteria, $changes) {
            $clients = $this->selectClients($criteria);
            
            foreach ($clients as $client) {
                $override = PriceOverride::create([
                    'company_id' => $client->id,
                    'product_id' => $changes['product_id'],
                    'override_price' => $this->calculateNewPrice($client, $changes),
                    'effective_date' => $changes['effective_date'] ?? now(),
                    'applied_by' => auth()->id(),
                    'bulk_operation_id' => Str::uuid(),
                ]);
                
                BillingAuditLog::create([
                    'action' => 'bulk_price_override',
                    'model_type' => 'PriceOverride',
                    'model_id' => $override->id,
                    'user_id' => auth()->id(),
                    'changes' => ['criteria' => $criteria, 'changes' => $changes],
                ]);
            }
        });
    }
    
    public function rollback(string $bulkOperationId): void
    {
        $overrides = PriceOverride::where('bulk_operation_id', $bulkOperationId)
            ->where('created_at', '>', now()->subDay())
            ->get();
            
        foreach ($overrides as $override) {
            $override->delete();
            
            BillingAuditLog::create([
                'action' => 'bulk_override_rollback',
                'model_type' => 'PriceOverride',
                'model_id' => $override->id,
                'user_id' => auth()->id(),
            ]);
        }
    }
}
```

**Frontend (Blade):**

```blade
<!-- Step 1: Selection -->
<div x-show="step === 1">
    <h3 class="text-lg font-semibold text-gray-900">Select Clients</h3>
    
    <div class="mt-4 space-y-4">
        <label class="flex items-center">
            <input type="radio" name="selection" value="all" x-model="criteria.selection" class="text-primary-600">
            <span class="ml-2">All Clients</span>
        </label>
        
        <label class="flex items-center">
            <input type="radio" name="selection" value="tier" x-model="criteria.selection" class="text-primary-600">
            <span class="ml-2">Filtered by Tier</span>
        </label>
        
        <label class="flex items-center">
            <input type="radio" name="selection" value="specific" x-model="criteria.selection" class="text-primary-600">
            <span class="ml-2">Specific Selection</span>
        </label>
    </div>
</div>

<!-- Step 3: Preview with Typed Confirmation -->
<div x-show="step === 3">
    <x-troubleshooting-card status="warning">
        <p class="font-semibold">⚠️ This action will affect {{ $affectedCount }} clients</p>
        <p class="mt-2">Type <code class="bg-gray-100 px-2 py-1 rounded">APPLY CHANGES</code> to confirm:</p>
        <input 
            type="text" 
            x-model="confirmation" 
            class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg"
            placeholder="Type here..."
        >
    </x-troubleshooting-card>
    
    <button 
        @click="submitBulkUpdate()"
        :disabled="confirmation !== 'APPLY CHANGES'"
        class="mt-4 btn-primary"
        :class="{ 'opacity-50 cursor-not-allowed': confirmation !== 'APPLY CHANGES' }"
    >
        Apply Changes
    </button>
</div>
```

---

## Story 12.2: Pre-Flight Excel Export

**Acceptance Criteria:**
- [ ] "Export to Excel" button with loading state
- [ ] Generates `.xlsx` file with multiple sheets
- [ ] Sheet 1: Draft invoices with anomaly scores
- [ ] Sheet 2: Line item breakdown
- [ ] Sheet 3: Summary statistics
- [ ] Styled headers and frozen top row
- [ ] Filename: `PreFlight_YYYY-MM-DD.xlsx`
- [ ] Download completes in < 5 seconds for 100 invoices

**Technical Implementation:**

```php
// Service Extension
class ExportService
{
    public function exportPreFlightToExcel(Collection $draftInvoices): string
    {
        $export = new PreFlightExport($draftInvoices);
        
        $filename = 'PreFlight_' . now()->format('Y-m-d') . '.xlsx';
        $path = storage_path('app/exports/' . $filename);
        
        Excel::store($export, $path);
        
        return $path;
    }
}

// Export Class
class PreFlightExport implements WithMultipleSheets
{
    protected $invoices;
    
    public function __construct(Collection $invoices)
    {
        $this->invoices = $invoices;
    }
    
    public function sheets(): array
    {
        return [
            new InvoiceSummarySheet($this->invoices),
            new LineItemDetailSheet($this->invoices),
            new StatisticsSheet($this->invoices),
        ];
    }
}

class InvoiceSummarySheet implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return $this->invoices->map(function ($invoice) {
            return [
                'Invoice #' => $invoice->number,
                'Client' => $invoice->company->name,
                'Amount' => $invoice->total,
                'Anomaly Score' => $invoice->anomaly_score,
                'Status' => $invoice->status,
                'Due Date' => $invoice->due_date->format('Y-m-d'),
            ];
        });
    }
    
    public function headings(): array
    {
        return ['Invoice #', 'Client', 'Amount', 'Anomaly Score', 'Status', 'Due Date'];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'E5E7EB']]],
        ];
    }
}
```

---

## Story 12.3: Effective Hourly Rate in Profitability Dashboard

**Acceptance Criteria:**
- [ ] New "EHR" column in profitability table
- [ ] Calculation: Revenue ÷ Billable Hours per client
- [ ] Color-coded cells (green > target, red < target)
- [ ] Variance percentage shown as tooltip
- [ ] Sortable column
- [ ] Cached hourly refresh

**Technical Implementation:**

```php
// Enhance existing profitability query
class ProfitabilityController
{
    public function index()
    {
        $clients = Company::with(['invoices', 'billableEntries'])
            ->get()
            ->map(function ($client) {
                $revenue = $client->invoices->sum('total');
                $hours = $client->billableEntries->sum('hours');
                $ehr = $hours > 0 ? $revenue / $hours : 0;
                $target = setting('target_hourly_rate', 150);
                
                return [
                    'client' => $client,
                    'revenue' => $revenue,
                    'hours' => $hours,
                    'ehr' => $ehr,
                    'target' => $target,
                    'variance' => ($ehr - $target) / $target * 100,
                    'status' => $ehr >= $target ? 'on_target' : 'below_target',
                ];
            });
            
        return view('billing::profitability.index', compact('clients'));
    }
}
```

**Frontend:**

```blade
<td class="px-6 py-4">
    <div class="flex items-center space-x-2">
        <span class="font-semibold" :class="{
            'text-success-700': client.status === 'on_target',
            'text-danger-700': client.status === 'below_target'
        }">
            ${{ client.ehr | number_format(2) }}
        </span>
        <span class="text-xs text-gray-500" x-tooltip="'Target: $' + client.target">
            ({{ client.variance > 0 ? '+' : '' }}{{ client.variance | number_format(1) }}%)
        </span>
    </div>
</td>
```

---

## Story 12.4-12.6: Additional Features

See full implementation details in [detailed specification document].

---

## Testing Strategy

```php
class BulkOverrideServiceTest extends TestCase
{
    public function test_preview_calculates_impact_correctly()
    {
        $clients = Company::factory()->count(5)->create();
        
        $preview = $this->service->previewBulkUpdate(
            ['selection' => 'all'],
            ['type' => 'percentage', 'value' => 10]
        );
        
        $this->assertCount(5, $preview['preview']);
        $this->assertGreaterThan(0, $preview['total_impact']);
    }
    
    public function test_rollback_within_24_hours()
    {
        $bulkId = $this->service->applyBulkUpdate([...]);
        
        $this->service->rollback($bulkId);
        
        $this->assertCount(0, PriceOverride::where('bulk_operation_id', $bulkId)->get());
    }
}
```

---

## Success Metrics

- **Efficiency:** 90% reduction in time for global price changes
- **Adoption:** 60%+ of Finance Admins use Pre-Flight export monthly
- **Accuracy:** 100% rollback success rate when needed
- **Audit:** 100% of bulk operations logged
- **Performance:** Bulk updates complete in < 30 seconds for 100 clients

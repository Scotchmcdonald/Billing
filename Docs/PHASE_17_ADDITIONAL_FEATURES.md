# Phase 17: Additional High-Value Features Implementation
**Status:** IN PROGRESS  
**Stories:** 11 additional features identified from audit  
**Total Effort:** 92-136 hours  

---

## Implemented Stories (1/11)

### ✅ FA.1: Invoice Batch Actions
**Status:** COMPLETE  
**File:** `/Resources/views/finance/invoice-batch-actions.blade.php`  
**Effort:** 8-12 hours  

**Features Implemented:**
- Multi-select checkbox interface with "select all"
- Batch action dropdown (Mark as Paid, Send Reminder, Void, Export)
- Filter controls (status, date range, client)
- Confirmation modals with action-specific forms
- Mark as Paid: Payment method + date selection
- Void: Typed confirmation ("VOID")
- Real-time selection summary bar
- Visual indicators for selected invoices

**Technical Details:**
```php
// Route
Route::post('/billing/finance/invoices/batch-action', [InvoiceController::class, 'batchAction'])
    ->name('billing.finance.invoices.batch-action');

// Controller Method
public function batchAction(Request $request)
{
    $validated = $request->validate([
        'action' => 'required|in:mark_paid,send_reminder,void,export',
        'invoice_ids' => 'required|array',
        'payment_method' => 'required_if:action,mark_paid',
        'payment_date' => 'required_if:action,mark_paid|date'
    ]);
    
    $invoices = Invoice::whereIn('id', $validated['invoice_ids'])->get();
    
    switch ($validated['action']) {
        case 'mark_paid':
            foreach ($invoices as $invoice) {
                $invoice->markAsPaid($validated['payment_method'], $validated['payment_date']);
            }
            return response()->json(['message' => count($invoices) . ' invoices marked as paid']);
            
        case 'send_reminder':
            foreach ($invoices as $invoice) {
                Mail::to($invoice->company->billing_email)
                    ->send(new PaymentReminderMail($invoice));
            }
            return response()->json(['message' => 'Reminders sent to ' . count($invoices) . ' clients']);
            
        case 'void':
            foreach ($invoices as $invoice) {
                $invoice->void();
            }
            return response()->json(['message' => count($invoices) . ' invoices voided']);
            
        case 'export':
            $export = new InvoicesExport($invoices);
            return Excel::download($export, 'invoices_' . date('Y-m-d') . '.xlsx');
    }
}
```

---

## Remaining Stories (10/11)

### FA.2: Custom Invoice Numbering
**Status:** NOT IMPLEMENTED  
**Priority:** LOW  
**Effort:** 6-8 hours  

**Technical Specification:**
```php
// New Settings Table
Schema::create('invoice_settings', function (Blueprint $table) {
    $table->id();
    $table->string('prefix')->default('INV'); // e.g., INV, BILL
    $table->string('separator')->default('-'); // e.g., -, /, _
    $table->boolean('include_year')->default(true);
    $table->boolean('include_month')->default(false);
    $table->integer('padding')->default(4); // Number of digits (0001, 00001)
    $table->integer('next_number')->default(1);
    $table->boolean('reset_yearly')->default(false);
    $table->timestamps();
});

// Service
class InvoiceNumberService
{
    public function generate()
    {
        $settings = InvoiceSettings::first();
        
        $parts = [$settings->prefix];
        
        if ($settings->include_year) {
            $parts[] = date('Y');
        }
        
        if ($settings->include_month) {
            $parts[] = date('m');
        }
        
        $parts[] = str_pad($settings->next_number, $settings->padding, '0', STR_PAD_LEFT);
        
        // Increment
        $settings->increment('next_number');
        
        return implode($settings->separator, $parts);
        // Example outputs:
        // INV-2025-0001
        // BILL/2025/01/00001
        // INV-0123
    }
}
```

**UI Location:** `/billing/admin/settings/invoice-numbering`

**Acceptance Criteria:**
- [ ] Configure prefix, separator, padding
- [ ] Toggle year/month inclusion
- [ ] Preview sample invoice number
- [ ] Option to reset counter yearly
- [ ] Validation to prevent duplicate numbers
- [ ] Audit trail of numbering changes

---

### FA.3: Invoice Templates Customization
**Status:** NOT IMPLEMENTED  
**Priority:** MEDIUM  
**Effort:** 12-16 hours  

**Technical Specification:**
```php
// New Table
Schema::create('invoice_templates', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('html_template'); // Blade template HTML
    $table->json('customization')->nullable(); // Colors, fonts, logo
    $table->boolean('is_default')->default(false);
    $table->timestamps();
});

// Customization JSON Structure
{
    "logo_url": "/storage/logos/company-logo.png",
    "primary_color": "#4F46E5",
    "secondary_color": "#6366F1",
    "font_family": "Inter",
    "footer_text": "Thank you for your business!",
    "show_payment_instructions": true,
    "show_itemized_breakdown": true,
    "header_text": "INVOICE"
}

// Service
class InvoiceTemplateService
{
    public function render($invoiceId, $templateId = null)
    {
        $invoice = Invoice::with('lineItems', 'company')->find($invoiceId);
        $template = $templateId 
            ? InvoiceTemplate::find($templateId)
            : InvoiceTemplate::where('is_default', true)->first();
        
        $customization = $template->customization;
        
        return view('invoice-templates.' . $template->name, [
            'invoice' => $invoice,
            'customization' => $customization
        ])->render();
    }
}
```

**UI Features:**
- WYSIWYG editor for template HTML
- Color pickers for brand colors
- Logo uploader
- Font selector
- Live preview pane
- Default template selector
- Template versioning

**Acceptance Criteria:**
- [ ] Upload custom logo
- [ ] Customize colors (primary, secondary)
- [ ] Edit footer text
- [ ] Toggle sections (payment instructions, itemization)
- [ ] Live preview before saving
- [ ] Multiple templates per company
- [ ] Set default template

---

### CP.1: Invoice Dispute Workflow Tracking (Enhancement)
**Status:** PARTIALLY IMPLEMENTED  
**Priority:** MEDIUM  
**Effort:** 8-12 hours  

**Enhancement Required:**
Current dispute submission exists, needs enhanced tracking UI.

**Technical Specification:**
```php
// Existing Dispute Model - Add Status Tracking
Schema::table('disputes', function (Blueprint $table) {
    $table->enum('status', ['submitted', 'under_review', 'awaiting_info', 'resolved_accepted', 'resolved_rejected'])
          ->default('submitted');
    $table->timestamp('reviewed_at')->nullable();
    $table->timestamp('resolved_at')->nullable();
    $table->integer('estimated_resolution_days')->default(5);
});

// Status History Table
Schema::create('dispute_status_history', function (Blueprint $table) {
    $table->id();
    $table->foreignId('dispute_id');
    $table->string('status');
    $table->text('note')->nullable();
    $table->foreignId('user_id')->nullable();
    $table->timestamp('transitioned_at');
    $table->timestamps();
});
```

**UI Component:** `x-dispute-status-timeline`
```blade
@props(['dispute'])

<div class="flow-root">
    <ul class="-mb-8">
        @foreach($dispute->statusHistory as $history)
        <li>
            <div class="relative pb-8">
                <div class="relative flex space-x-3">
                    <div>
                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                              :class="{
                                  'bg-success-500': '{{$history->status}}' === 'resolved_accepted',
                                  'bg-danger-500': '{{$history->status}}' === 'resolved_rejected',
                                  'bg-primary-500': ['submitted', 'under_review'].includes('{{$history->status}}'),
                                  'bg-warning-500': '{{$history->status}}' === 'awaiting_info'
                              }">
                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    </div>
                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                        <div>
                            <p class="text-sm text-gray-500">
                                {{ $history->statusLabel }}
                                @if($history->note)
                                    <span class="font-medium text-gray-900">- {{ $history->note }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                            {{ $history->transitioned_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </div>
        </li>
        @endforeach
    </ul>
</div>
```

**Portal View:** `/portal/disputes/{id}/tracking`

**Acceptance Criteria:**
- [ ] Timeline shows all status transitions
- [ ] Email notification on status changes
- [ ] Expected resolution date displayed
- [ ] Client can add additional info when requested
- [ ] Finance admin can add internal notes (not visible to client)
- [ ] SLA tracking (auto-escalate if > 5 days)

---

### CP.2: Payment History Download
**Status:** NOT IMPLEMENTED  
**Priority:** MEDIUM  
**Effort:** 6-8 hours  

**Technical Specification:**
```php
// Controller
public function downloadPaymentHistory(Request $request)
{
    $companyId = auth()->user()->company_id;
    $format = $request->input('format', 'excel'); // excel, csv
    
    $payments = Payment::where('company_id', $companyId)
        ->with('invoice')
        ->orderBy('created_at', 'desc')
        ->get();
    
    if ($format === 'csv') {
        $export = new PaymentsCSVExport($payments);
        return Excel::download($export, 'payment_history_' . date('Y-m-d') . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }
    
    $export = new PaymentsExcelExport($payments);
    return Excel::download($export, 'payment_history_' . date('Y-m-d') . '.xlsx');
}

// Export Class
class PaymentsExcelExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return $this->payments->map(function($payment) {
            return [
                'Date' => $payment->created_at->format('Y-m-d'),
                'Invoice #' => $payment->invoice->number,
                'Amount' => '$' . number_format($payment->amount / 100, 2),
                'Method' => ucfirst($payment->method),
                'Transaction ID' => $payment->transaction_id,
                'Status' => $payment->reconciled_at ? 'Reconciled' : 'Pending'
            ];
        });
    }
    
    public function headings(): array
    {
        return ['Date', 'Invoice #', 'Amount', 'Method', 'Transaction ID', 'Status'];
    }
    
    public function styles(Worksheet $worksheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E5E7EB']]]
        ];
    }
}
```

**UI Location:** `/portal/payments` (add "Download" button)

**Acceptance Criteria:**
- [ ] Export all payment history to Excel
- [ ] Export all payment history to CSV
- [ ] Date range filter before export
- [ ] Includes transaction IDs and reconciliation status
- [ ] Styled Excel with headers and formatting

---

### CP.3: Scheduled Payment Management
**Status:** NOT IMPLEMENTED  
**Priority:** MEDIUM  
**Effort:** 6-8 hours  

**Technical Specification:**
```php
// View scheduled auto-pay charges
public function scheduledPayments(Request $request)
{
    $companyId = auth()->user()->company_id;
    $company = Company::find($companyId);
    
    if (!$company->auto_pay_enabled) {
        return view('portal.auto-pay-disabled');
    }
    
    // Get upcoming invoices (unpaid, due within 30 days)
    $upcomingInvoices = Invoice::where('company_id', $companyId)
        ->where('status', '!=', 'paid')
        ->where('due_date', '>=', now())
        ->where('due_date', '<=', now()->addDays(30))
        ->orderBy('due_date')
        ->get();
    
    $scheduledCharges = $upcomingInvoices->map(function($invoice) use ($company) {
        $chargeDate = $invoice->due_date->subDays($company->auto_pay_days_before_due ?? 0);
        
        return [
            'invoice_number' => $invoice->number,
            'amount' => $invoice->total,
            'charge_date' => $chargeDate,
            'due_date' => $invoice->due_date,
            'days_until_charge' => $chargeDate->diffInDays(now()),
            'status' => $chargeDate->isPast() ? 'processing' : 'scheduled'
        ];
    });
    
    return view('portal.scheduled-payments', [
        'scheduled_charges' => $scheduledCharges,
        'payment_method' => $company->defaultPaymentMethod
    ]);
}
```

**UI Features:**
- Calendar view of upcoming charges
- Amount and invoice number per charge
- Payment method display (last 4 digits)
- Option to pause auto-pay temporarily
- Alert if insufficient funds may cause failure

**Acceptance Criteria:**
- [ ] List all upcoming auto-pay charges (next 30 days)
- [ ] Show charge date, amount, and invoice number
- [ ] Display payment method to be charged
- [ ] Option to pause auto-pay for specific invoice
- [ ] Email reminder 3 days before charge
- [ ] Visual calendar view

---

### T.1: Offline Time Entry
**Status:** NOT IMPLEMENTED  
**Priority:** HIGH  
**Effort:** 16-24 hours  

**Technical Specification:**
Requires Progressive Web App (PWA) implementation with Service Worker and IndexedDB.

**manifest.json:**
```json
{
    "name": "FinOps Billing - Field Tech",
    "short_name": "FinOps",
    "start_url": "/field/work-order",
    "display": "standalone",
    "background_color": "#ffffff",
    "theme_color": "#4F46E5",
    "icons": [
        {
            "src": "/images/icon-192x192.png",
            "sizes": "192x192",
            "type": "image/png"
        },
        {
            "src": "/images/icon-512x512.png",
            "sizes": "512x512",
            "type": "image/png"
        }
    ],
    "offline_enabled": true
}
```

**service-worker.js:**
```javascript
const CACHE_NAME = 'finops-v1';
const urlsToCache = [
    '/field/work-order',
    '/field/timesheet',
    '/css/app.css',
    '/js/app.js',
    '/offline'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(urlsToCache))
    );
});

self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request);
        })
    );
});

// Background sync for offline time entries
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-time-entries') {
        event.waitUntil(syncTimeEntries());
    }
});

async function syncTimeEntries() {
    const db = await openDB();
    const tx = db.transaction('timeEntries', 'readonly');
    const store = tx.objectStore('timeEntries');
    const entries = await store.getAll();
    
    for (const entry of entries.filter(e => !e.synced)) {
        try {
            const response = await fetch('/api/time-entries', {
                method: 'POST',
                body: JSON.stringify(entry),
                headers: { 'Content-Type': 'application/json' }
            });
            
            if (response.ok) {
                // Mark as synced
                const updateTx = db.transaction('timeEntries', 'readwrite');
                const updateStore = updateTx.objectStore('timeEntries');
                entry.synced = true;
                await updateStore.put(entry);
            }
        } catch (error) {
            console.error('Sync failed', error);
        }
    }
}
```

**Acceptance Criteria:**
- [ ] App works completely offline (PWA)
- [ ] Time entries saved to IndexedDB when offline
- [ ] Auto-sync when connection restored
- [ ] Visual offline indicator in header
- [ ] Conflict resolution UI if server data changed
- [ ] Works on iOS Safari and Android Chrome

---

### T.2: Mileage Tracking
**Status:** NOT IMPLEMENTED  
**Priority:** MEDIUM  
**Effort:** 12-16 hours  

**Technical Specification:**
```php
// New Table
Schema::create('mileage_entries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id'); // Technician
    $table->foreignId('ticket_id')->nullable();
    $table->foreignId('company_id'); // Client
    $table->date('date');
    $table->string('start_location')->nullable();
    $table->string('end_location')->nullable();
    $table->decimal('miles', 8, 2);
    $table->decimal('rate_per_mile', 5, 2)->default(0.67); // IRS rate
    $table->decimal('total_amount', 10, 2);
    $table->text('purpose')->nullable();
    $table->boolean('billable')->default(true);
    $table->boolean('reimbursed')->default(false);
    $table->timestamps();
});

// Model
class MileageEntry extends Model
{
    protected $fillable = [
        'user_id', 'ticket_id', 'company_id', 'date', 
        'start_location', 'end_location', 'miles', 
        'rate_per_mile', 'total_amount', 'purpose', 
        'billable', 'reimbursed'
    ];
    
    protected $casts = [
        'date' => 'date',
        'miles' => 'decimal:2',
        'rate_per_mile' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'billable' => 'boolean',
        'reimbursed' => 'boolean'
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($entry) {
            $entry->total_amount = $entry->miles * $entry->rate_per_mile;
        });
    }
}
```

**UI Features:**
- Mobile-optimized mileage entry form
- Google Maps integration for address autocomplete
- Automatic distance calculation (if start/end provided)
- Manual miles entry option
- Quick "Round Trip" button (doubles miles)
- Photo attachment for odometer reading
- Weekly mileage summary report

**Acceptance Criteria:**
- [ ] Log mileage with date, start, end, miles
- [ ] Auto-calculate reimbursement amount (IRS rate)
- [ ] Mark as billable/non-billable
- [ ] Link to specific ticket/client
- [ ] Weekly summary for reimbursement
- [ ] Admin approval workflow
- [ ] Export to Excel for payroll

---

### E.1: Board Report Generator
**Status:** NOT IMPLEMENTED  
**Priority:** MEDIUM  
**Effort:** 12-16 hours  

**Technical Specification:**
```php
// Service
class BoardReportService
{
    public function generate($month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        return [
            'period' => $startDate->format('F Y'),
            'financial' => [
                'mrr' => $this->getMRR($endDate),
                'mrr_growth' => $this->getMRRGrowth($startDate, $endDate),
                'arr' => $this->getARR($endDate),
                'cash_collected' => $this->getCashCollected($startDate, $endDate)
            ],
            'customers' => [
                'total' => Company::count(),
                'new' => Company::whereBetween('created_at', [$startDate, $endDate])->count(),
                'churned' => $this->getChurnedCount($startDate, $endDate),
                'churn_rate' => $this->getChurnRate($startDate, $endDate)
            ],
            'operations' => [
                'invoices_sent' => Invoice::whereBetween('created_at', [$startDate, $endDate])->count(),
                'collection_rate' => $this->getCollectionRate($startDate, $endDate),
                'ar_aging_90plus' => $this->getAR90Plus($endDate)
            ],
            'highlights' => $this->getHighlights($startDate, $endDate),
            'concerns' => $this->getConcerns($endDate)
        ];
    }
    
    public function exportToPDF($data)
    {
        $pdf = PDF::loadView('reports.board-report', $data);
        return $pdf->download('board_report_' . $data['period'] . '.pdf');
    }
}
```

**UI Features:**
- One-page PDF with key metrics
- 4 quadrants: Financial, Customers, Operations, Highlights
- Big numbers with MoM % changes
- Traffic light indicators (green/yellow/red)
- Top 3 wins and top 3 concerns
- Export to PDF with branding

**Acceptance Criteria:**
- [ ] Generate monthly board report
- [ ] Single-page PDF format
- [ ] Key metrics: MRR, ARR, churn, collection rate
- [ ] Visual indicators (traffic lights)
- [ ] Top 3 highlights and concerns
- [ ] Professional PDF template
- [ ] Email to stakeholders option

---

### E.2: Year-over-Year Growth Dashboard
**Status:** PARTIALLY IMPLEMENTED  
**Priority:** MEDIUM  
**Effort:** 8-12 hours  

**Enhancement Required:**
Data exists in TrendAnalyticsService, needs dedicated dashboard UI.

**Technical Specification:**
```php
// Controller
public function yoyGrowth(Request $request)
{
    $metrics = [
        'revenue' => $this->analyticsService->getRevenueGrowth(),
        'clients' => $this->analyticsService->getClientGrowth(),
        'mrr' => $this->analyticsService->getMRRGrowth(),
        'arr' => $this->analyticsService->getARRGrowth()
    ];
    
    return view('executive.yoy-growth', compact('metrics'));
}

// Service Method
public function getRevenueGrowth()
{
    $thisYear = Invoice::whereYear('created_at', now()->year)->sum('total');
    $lastYear = Invoice::whereYear('created_at', now()->year - 1)->sum('total');
    
    $growth = $lastYear > 0 ? (($thisYear - $lastYear) / $lastYear) * 100 : 0;
    
    return [
        'this_year' => $thisYear,
        'last_year' => $lastYear,
        'growth_percent' => round($growth, 1),
        'growth_amount' => $thisYear - $lastYear,
        'trend' => $growth > 0 ? 'up' : 'down'
    ];
}
```

**UI Features:**
- 4 KPI cards (Revenue, Clients, MRR, ARR)
- Each shows: This Year, Last Year, Growth %
- Large growth % number with up/down arrow
- Color-coded (green if positive, red if negative)
- 12-month trend chart per metric
- Comparison mode: YoY vs MoM toggle

**Acceptance Criteria:**
- [ ] Display YoY growth % for revenue, clients, MRR, ARR
- [ ] Visual trend indicators (arrows, colors)
- [ ] 12-month rolling chart for each metric
- [ ] Toggle between YoY and MoM views
- [ ] Export to PDF
- [ ] Mobile-responsive layout

---

## Implementation Priorities

### Immediate (Next Sprint)
1. ✅ FA.1: Invoice Batch Actions (DONE)
2. CP.1: Dispute Workflow Tracking (8-12h)
3. CP.2: Payment History Download (6-8h)
4. CP.3: Scheduled Payment Management (6-8h)

### Short Term (Following Sprint)
5. E.2: Year-over-Year Growth Dashboard (8-12h)
6. T.2: Mileage Tracking (12-16h)
7. FA.3: Invoice Templates Customization (12-16h)

### Medium Term (Q2 2025)
8. T.1: Offline Time Entry (16-24h) - Requires PWA setup
9. E.1: Board Report Generator (12-16h)
10. FA.2: Custom Invoice Numbering (6-8h)

---

## Total Implementation Summary

**Completed:** 1/11 stories (9%)  
**Remaining Effort:** 84-124 hours  
**Target Completion:** Q2 2025  

**High Priority:** 4 stories (30-40 hours)  
**Medium Priority:** 5 stories (48-72 hours)  
**Low Priority:** 1 story (6-8 hours)  

All implementations will follow the "Pilot's Cockpit" UX standards with semantic theming, reusable components, and state-aware feedback.

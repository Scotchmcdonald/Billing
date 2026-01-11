<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Quote;
use Modules\Inventory\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\BillingLog;
use Illuminate\Support\Facades\Mail;
use Modules\Billing\Mail\QuoteSent;
use Modules\Billing\Models\Invoice;

class QuoteController extends Controller
{
    public function index()
    {
        $quotes = Quote::with('company')->orderBy('created_at', 'desc')->get();
        return view('billing::finance.quotes.index', compact('quotes'));
    }

    public function create(): \Illuminate\View\View
    {
        $companies = Company::where('is_active', true)->get();
        
        // Pass data to Alpine
        $products = Product::where('is_active', true)->get()->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'type' => $p->category === 'Hardware' ? 'hardware' : 'service', 
                'base_price' => (float)$p->base_price,
                'monthly_price' => (float)($p->price_monthly ?? $p->base_price),
                'sku' => $p->sku
            ];
        });
        
        $defaultApprovalThreshold = config('quotes.default_approval_threshold', 15.00);

        // Using our new view
        return view('billing::quotes.create', compact('companies', 'products', 'defaultApprovalThreshold'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'prospect_name' => 'nullable|string|required_without:company_id',
            'prospect_email' => 'nullable|email|required_without:company_id',
            'pricing_tier' => 'nullable|in:standard,non_profit,consumer',
            'approval_threshold_percent' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.strategy' => 'required|string', // one_time, monthly, rto_12, rto_24
            'items.*.unit_price' => 'nullable|numeric', // Optional override
            'notes' => 'nullable|string',
            'valid_until' => 'nullable|date',
        ]);

        $approvalThreshold = $validated['approval_threshold_percent'] ?? config('quotes.default_approval_threshold', 15.00);
        
        $quote = Quote::create([
            'company_id' => $validated['company_id'],
            'prospect_name' => $validated['prospect_name'],
            'prospect_email' => $validated['prospect_email'],
            'pricing_tier' => $validated['pricing_tier'] ?? 'standard',
            'notes' => $validated['notes'],
            'valid_until' => $validated['valid_until'] ?? now()->addDays(30),
            'status' => 'draft',
            'requires_approval' => false,
            'approval_threshold_percent' => $approvalThreshold,
            'token' => Str::random(32),
            'quote_number' => 'Q-' . strtoupper(uniqid()),
        ]);

        $totalInitial = 0;
        
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            
            // Logic to determine Unit Price based on Strategy
            $unitPrice = $product->base_price;
            $unitPriceMonthly = null;
            $billingFrequency = 'one_time';

            if ($item['strategy'] === 'monthly') {
                $unitPriceMonthly = $product->price_monthly ?? $product->base_price;
                $unitPrice = $unitPriceMonthly;
                $billingFrequency = 'monthly';
            } elseif (str_starts_with($item['strategy'], 'rto_')) {
                $months = (int) filter_var($item['strategy'], FILTER_SANITIZE_NUMBER_INT);
                $unitPriceMonthly = $product->base_price / max($months, 12); 
                $unitPrice = $unitPriceMonthly;
                $billingFrequency = $item['strategy']; // 'rto_12', 'rto_24'
            } else {
                // one_time
                $billingFrequency = 'one_time';
            }

            $subtotal = $item['quantity'] * $unitPrice;
            
            $quote->lineItems()->create([
                'product_id' => $product->id,
                'description' => $product->name . " [" . strtoupper($item['strategy']) . "]",
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
                'unit_price_monthly' => $unitPriceMonthly,
                'standard_price' => $product->base_price,
                'subtotal' => $subtotal,
                'billing_frequency' => $billingFrequency,
            ]);
            
            // Calculate Total logic (TCV vs Upfront)
            // For now, we sum the 'unit_price' which corresponds to the first payment amount (Upfront or 1st Month)
            $totalInitial += $subtotal; 
        }

        $quote->update([
            'subtotal' => $totalInitial,
            'total' => $totalInitial,
        ]);

        return redirect()->route('billing.finance.quotes.show', $quote->id);
    }

    // --- Legacy / Preserved Methods ---

    public function edit(int $id): \Illuminate\View\View
    {
        $quote = Quote::with(['lineItems'])->findOrFail($id);
        $companies = Company::all();
        $products = Product::all();
        $defaultApprovalThreshold = config('quotes.default_approval_threshold', 15.00);
        return view('billing::quotes.edit', compact('quote', 'companies', 'products', 'defaultApprovalThreshold'));
    }

    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        // ... (Legacy update logic, or TBD update for Hybrid)
        // Keeping minimal for MVP Create flow focus
        $quote = Quote::findOrFail($id);
        $quote->update($request->only('notes', 'valid_until', 'company_id'));
        return redirect()->route('billing.finance.quotes.show', $quote->id);
    }

    public function send(int $id): \Illuminate\Http\RedirectResponse
    {
        $quote = Quote::findOrFail($id);
        $recipient = $quote->company ? $quote->company->email : $quote->prospect_email;
        
        if ($recipient) {
            Mail::to($recipient)->send(new QuoteSent($quote));
            $quote->update(['status' => 'sent']);
            BillingLog::create([
                'company_id' => $quote->company_id,
                'event' => 'quote.sent',
                'description' => "Quote #{$quote->quote_number} sent to {$recipient}",
                'payload' => ['quote_id' => $quote->id, 'recipient' => $recipient],
                'action' => 'send' 
            ]);
            return back()->with('success', 'Quote sent to ' . $recipient);
        }
        return back()->with('error', 'No email address found.');
    }

    public function show(int $id): \Illuminate\View\View
    {
        $quote = Quote::with(['lineItems', 'company'])->findOrFail($id);
        if (!$quote->token) {
            $quote->update(['token' => Str::random(32)]);
        }
        return view('billing::quotes.show', compact('quote'));
    }

    public function convertToInvoice(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        /** @var Quote $quote */
        $quote = Quote::with(['lineItems', 'company'])->findOrFail($id);

        if (!$quote->company_id) {
            return redirect()->back()->with('error', 'Quote must be associated with a company.');
        }

        DB::beginTransaction();
        try {
            // 1. Handle One-Time Items (Invoice)
            $oneTimeItems = $quote->lineItems->filter(fn($item) => $item->billing_frequency === 'one_time');
            
            if ($oneTimeItems->isNotEmpty()) {
                $invoice = Invoice::create([
                    'company_id' => $quote->company_id,
                    'invoice_number' => 'INV-' . strtoupper(uniqid()),
                    'issue_date' => now(),
                    'due_date' => now()->addDays(30),
                    'subtotal' => $oneTimeItems->sum('subtotal'),
                    'total' => $oneTimeItems->sum('subtotal'),
                    'status' => 'draft',
                    'notes' => 'Converted from Quote #' . $quote->quote_number,
                ]);

                foreach ($oneTimeItems as $item) {
                    $invoice->lineItems()->create([
                        'product_id' => $item->product_id,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal,
                    ]);
                }
            }

            // 2. Handle Recurring / RTO Items (Subscriptions)
            $recurringItems = $quote->lineItems->filter(fn($item) => $item->billing_frequency !== 'one_time');

            foreach ($recurringItems as $item) {
                // Determine billing details
                $frequency = str_starts_with($item->billing_frequency, 'rto') ? 'monthly' : $item->billing_frequency;
                
                // Create Subscription
                $subscription = \Modules\Billing\Models\Subscription::create([
                    'company_id' => $quote->company_id,
                    'product_id' => $item->product_id,
                    'name' => $item->description, 
                    'quantity' => $item->quantity,
                    'effective_price' => $item->unit_price, 
                    'billing_frequency' => $frequency, 
                    'starts_at' => now(),
                    'is_active' => true,
                    'next_billing_date' => now()->addMonth()
                ]);

                // If RTO, create Billing Agreement
                if (str_starts_with($item->billing_frequency, 'rto')) {
                    $months = (int) filter_var($item->billing_frequency, FILTER_SANITIZE_NUMBER_INT);
                    $totalCents = ($item->standard_price * $item->quantity) * 100;
                    
                    \Modules\Billing\Models\BillingAgreement::create([
                        'company_id' => $quote->company_id,
                        'asset_id' => null, 
                        'billing_strategy' => $item->billing_frequency,
                        'rto_total_cents' => $totalCents,
                        'rto_balance_cents' => $totalCents, 
                        'status' => 'active',
                    ]);
                    
                    // Update Subscription to limit duration
                     $subscription->update([
                        'ends_at' => now()->addMonths($months),
                        'metadata' => ['linked_agreement_type' => 'rto']
                     ]);
                }
            }

            $quote->update(['status' => 'converted']);
            DB::commit();
            
            if ($oneTimeItems->isEmpty() && $recurringItems->isNotEmpty()) {
                 return redirect()->back()->with('success', 'Quote converted to Subscriptions/Agreements.');
            }

            return redirect()->back()->with('success', 'Quote converted to invoice successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }
}

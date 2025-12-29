<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Quote;
use Modules\Inventory\Models\Product;

class QuoteController extends Controller
{
    public function index()
    {
        $quotes = Quote::with('company')->orderBy('created_at', 'desc')->get();
        return view('billing::finance.quotes.index', compact('quotes'));
    }

    public function create(): \Illuminate\View\View
    {
        $companies = Company::all();
        $products = Product::all();
        
        // Enrich products with tier pricing information
        foreach ($products as $product) {
            $product->tier_prices = [
                'standard' => $product->getPriceForTier('standard'),
                'non_profit' => $product->getPriceForTier('non_profit'),
                'consumer' => $product->getPriceForTier('consumer'),
            ];
        }
        
        $defaultApprovalThreshold = config('quotes.default_approval_threshold', 15.00);

        return view('billing::quotes.create', compact('companies', 'products', 'defaultApprovalThreshold'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'prospect_name' => 'nullable|string|required_without:company_id',
            'prospect_email' => 'nullable|email|required_without:company_id',
            'pricing_tier' => 'required|in:standard,non_profit,consumer',
            'approval_threshold_percent' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.standard_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'valid_until' => 'nullable|date',
        ]);

        $approvalThreshold = $validated['approval_threshold_percent'] ?? config('quotes.default_approval_threshold', 15.00);
        $requiresApproval = false;

        /** @var Quote $quote */
        $quote = Quote::create([
            'company_id' => $validated['company_id'],
            'prospect_name' => $validated['prospect_name'],
            'prospect_email' => $validated['prospect_email'],
            'pricing_tier' => $validated['pricing_tier'],
            'notes' => $validated['notes'],
            'valid_until' => $validated['valid_until'],
            'status' => 'draft',
            'requires_approval' => false, // Will be updated below
            'approval_threshold_percent' => $approvalThreshold,
            'token' => \Illuminate\Support\Str::random(32),
        ]);

        $total = 0;
        foreach ($validated['items'] as $item) {
            $subtotal = $item['quantity'] * $item['unit_price'];
            
            // Calculate variance from standard price
            $standardPrice = $item['standard_price'] ?? null;
            $varianceAmount = 0;
            $variancePercent = 0;
            
            if ($standardPrice && $standardPrice > 0) {
                $varianceAmount = $item['unit_price'] - $standardPrice;
                $variancePercent = ($varianceAmount / $standardPrice) * 100;
                
                // Check if this item requires approval
                if (abs($variancePercent) > $approvalThreshold) {
                    $requiresApproval = true;
                }
            }
            
            $quote->lineItems()->create([
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'unit_price_monthly' => $item['unit_price'],
                'unit_price_annually' => $item['unit_price'] * 12,
                'standard_price' => $standardPrice,
                'variance_amount' => $varianceAmount,
                'variance_percent' => $variancePercent,
                'subtotal' => $subtotal,
            ]);
            $total += $subtotal;
        }

        $quote->update([
            'total' => $total,
            'requires_approval' => $requiresApproval,
            'billing_frequency' => 'monthly',
        ]);

        $message = 'Quote created successfully.';
        if ($requiresApproval) {
            $message .= ' This quote requires approval due to price variance exceeding ' . $approvalThreshold . '%.';
        }

        return redirect()->route('billing.finance.quotes.show', $quote->id)->with('success', $message);
    }

    public function edit(int $id): \Illuminate\View\View
    {
        $quote = Quote::with(['lineItems'])->findOrFail($id);
        $companies = Company::all();
        $products = Product::all();
        
        // Enrich products with tier pricing information
        foreach ($products as $product) {
            $product->tier_prices = [
                'standard' => $product->getPriceForTier('standard'),
                'non_profit' => $product->getPriceForTier('non_profit'),
                'consumer' => $product->getPriceForTier('consumer'),
            ];
        }
        
        $defaultApprovalThreshold = config('quotes.default_approval_threshold', 15.00);

        return view('billing::quotes.edit', compact('quote', 'companies', 'products', 'defaultApprovalThreshold'));
    }

    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $quote = Quote::findOrFail($id);
        
        $validated = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'prospect_name' => 'nullable|string|required_without:company_id',
            'prospect_email' => 'nullable|email|required_without:company_id',
            'pricing_tier' => 'required|in:standard,non_profit,consumer',
            'approval_threshold_percent' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.standard_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'valid_until' => 'nullable|date',
        ]);

        $approvalThreshold = $validated['approval_threshold_percent'] ?? config('quotes.default_approval_threshold', 15.00);
        $requiresApproval = false;

        $quote->update([
            'company_id' => $validated['company_id'],
            'prospect_name' => $validated['prospect_name'],
            'prospect_email' => $validated['prospect_email'],
            'pricing_tier' => $validated['pricing_tier'],
            'notes' => $validated['notes'],
            'valid_until' => $validated['valid_until'],
            'approval_threshold_percent' => $approvalThreshold,
            'status' => 'draft', // Reset status to draft on edit
        ]);

        // Re-create line items
        $quote->lineItems()->delete();
        
        $total = 0;
        foreach ($validated['items'] as $item) {
            $subtotal = $item['quantity'] * $item['unit_price'];
            
            $standardPrice = $item['standard_price'] ?? null;
            $varianceAmount = 0;
            $variancePercent = 0;
            
            if ($standardPrice && $standardPrice > 0) {
                $varianceAmount = $item['unit_price'] - $standardPrice;
                $variancePercent = ($varianceAmount / $standardPrice) * 100;
                
                if (abs($variancePercent) > $approvalThreshold) {
                    $requiresApproval = true;
                }
            }
            
            $quote->lineItems()->create([
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'unit_price_monthly' => $item['unit_price'],
                'unit_price_annually' => $item['unit_price'] * 12,
                'standard_price' => $standardPrice,
                'variance_amount' => $varianceAmount,
                'variance_percent' => $variancePercent,
                'subtotal' => $subtotal,
            ]);
            $total += $subtotal;
        }

        $quote->update([
            'total' => $total,
            'requires_approval' => $requiresApproval,
            'billing_frequency' => 'monthly',
        ]);

        return redirect()->route('billing.finance.quotes.show', $quote->id)->with('success', 'Quote updated successfully.');
    }

    public function send(int $id): \Illuminate\Http\RedirectResponse
    {
        $quote = Quote::findOrFail($id);
        
        // Send email
        $recipient = $quote->company ? $quote->company->email : $quote->prospect_email;
        
        if ($recipient) {
            \Illuminate\Support\Facades\Mail::to($recipient)->send(new \Modules\Billing\Mail\QuoteSent($quote));
            
            $quote->update(['status' => 'sent']);
            
            // Log activity
            \Modules\Billing\Models\BillingLog::create([
                'company_id' => $quote->company_id,
                'event' => 'quote.sent',
                'description' => "Quote #{$quote->quote_number} sent to {$recipient}",
                'payload' => ['quote_id' => $quote->id, 'recipient' => $recipient],
                'level' => 'info'
            ]);

            return back()->with('success', 'Quote sent to ' . $recipient);
        }
        
        return back()->with('error', 'No email address found for this client.');
    }

    public function show(int $id): \Illuminate\View\View
    {
        $quote = Quote::with(['lineItems', 'company'])->findOrFail($id);
        
        // Generate token if it doesn't exist (for quotes created before the feature)
        if (!$quote->token) {
            $quote->update(['token' => \Illuminate\Support\Str::random(32)]);
        }
        
        return view('billing::quotes.show', compact('quote'));
    }

    public function convertToInvoice(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        /** @var Quote $quote */
        $quote = Quote::with(['lineItems', 'company'])->findOrFail($id);

        // Verify quote is accepted
        if (!$quote->is_accepted) {
            return redirect()->back()->with('error', 'Quote must be accepted before conversion.');
        }

        // Verify company exists
        if (!$quote->company_id) {
            return redirect()->back()->with('error', 'Quote must be associated with a company to convert to invoice.');
        }

        \DB::beginTransaction();
        try {
            // Create invoice
            $invoice = \Modules\Billing\Models\Invoice::create([
                'company_id' => $quote->company_id,
                'invoice_number' => 'INV-' . now()->format('Y') . '-' . str_pad((string) (\Modules\Billing\Models\Invoice::count() + 1), 4, '0', STR_PAD_LEFT),
                'issue_date' => now(),
                'due_date' => now()->addDays(30),
                'subtotal' => $quote->total,
                'tax_total' => 0,
                'total' => $quote->total,
                'status' => 'draft',
                'notes' => 'Converted from Quote #' . $quote->quote_number,
            ]);

            // Copy line items
            foreach ($quote->lineItems as $item) {
                $invoice->lineItems()->create([
                    'product_id' => $item->product_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]);
            }

            // Update quote status
            $quote->update(['status' => 'converted']);

            \DB::commit();

            return redirect()->route('billing.finance.invoices')->with('success', 'Quote converted to invoice successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Failed to convert quote: ' . $e->getMessage());
        }
    }
}

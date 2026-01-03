<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Quote;
use Modules\Inventory\Models\Product;
use Modules\Billing\Services\QuoteConversionService;
use Illuminate\Support\Str;

class PublicQuoteController extends Controller
{
    protected $conversionService;

    public function __construct(QuoteConversionService $conversionService)
    {
        $this->conversionService = $conversionService;
    }

    public function index()
    {
        // Fetch products available for public quoting
        // Assuming we want to show all active products or a specific category
        $products = Product::where('is_active', true)->get();

        return view('billing::public.quote-builder', compact('products'));
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $total = 0;
        $breakdown = [];

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            // Use base price for public quotes
            // In a real scenario, we might check for volume discounts (tier pricing) here
            // But for now, simple base price
            $lineTotal = $product->base_price * $item['quantity'];
            $total += $lineTotal;

            $breakdown[] = [
                'product' => $product->name,
                'quantity' => $item['quantity'],
                'unit_price' => $product->base_price,
                'total' => $lineTotal,
            ];
        }

        return response()->json([
            'total' => $total,
            'breakdown' => $breakdown,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $quote = Quote::create([
            'prospect_name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'prospect_email' => $validated['email'],
            'notes' => 'Generated via Public Quote Builder. Company: ' . ($validated['company_name'] ?? 'N/A') . '. Phone: ' . ($validated['phone'] ?? 'N/A'),
            'status' => 'draft', // or 'pending_review'
            'token' => Str::random(32),
            'valid_until' => now()->addDays(30),
        ]);

        $total = 0;
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            $subtotal = $product->base_price * $item['quantity'];
            $total += $subtotal;

            $quote->lineItems()->create([
                'product_id' => $product->id,
                'description' => $product->name,
                'quantity' => $item['quantity'],
                'unit_price' => $product->base_price,
                'subtotal' => $subtotal,
            ]);
        }

        $quote->update(['total' => $total]);

        // Here we would trigger an email to the sales team and the prospect

        return response()->json([
            'message' => 'Quote generated successfully!',
            'quote_token' => $quote->token,
            'redirect_url' => route('billing.public.quote.show', $quote->token),
        ]);
    }

    public function show($token)
    {
        $quote = Quote::where('token', $token)->with(['lineItems', 'company'])->firstOrFail();
        
        // Track view
        if (!$quote->viewed_at) {
            $quote->update(['viewed_at' => now()]);
            
            // Log activity
            \Modules\Billing\Models\BillingLog::create([
                'company_id' => $quote->company_id,
                'event' => 'quote.viewed',
                'description' => "Quote #{$quote->quote_number} viewed by client",
                'payload' => ['quote_id' => $quote->id, 'ip' => request()->ip()],
                'level' => 'info'
            ]);
        }
        
        return view('billing::public.quote-accept', compact('quote'));
    }

    public function accept(Request $request, $token)
    {
        $request->validate([
            'terms_accepted' => 'required|accepted',
            'accepted_by_name' => 'required|string|max:255',
            'accepted_by_email' => 'required|email|max:255',
            'notes' => 'nullable|string',
        ]);

        $quote = Quote::where('token', $token)->firstOrFail();

        // Verify quote is still valid
        if ($quote->is_accepted) {
            return redirect()->back()->with('error', 'This quote has already been accepted.');
        }

        if ($quote->valid_until < now()) {
            return redirect()->back()->with('error', 'This quote has expired.');
        }

        // Accept the quote
        $quote->update([
            'accepted_at' => now(),
            'accepted_by_name' => $request->accepted_by_name,
            'accepted_by_email' => $request->accepted_by_email,
            'status' => 'accepted',
            'notes' => $quote->notes . ($request->notes ? "\n\nAcceptance Note: " . $request->notes : ''),
        ]);

        // Log activity
        \Modules\Billing\Models\BillingLog::create([
            'company_id' => $quote->company_id,
            'event' => 'quote.accepted',
            'description' => "Quote #{$quote->quote_number} accepted by {$request->accepted_by_name}",
            'payload' => ['quote_id' => $quote->id, 'accepted_by' => $request->accepted_by_email],
            'level' => 'info'
        ]);

        // Trigger quote conversion to invoice/subscription workflow
        try {
            $this->conversionService->convert($quote);
        } catch (\Exception $e) {
            // Log error but don't fail the user request, finance can retry manually
            \Illuminate\Support\Facades\Log::error("Failed to convert quote #{$quote->id}: " . $e->getMessage());
        }

        return view('billing::public.quote-accepted', compact('quote'));
    }

    public function reject(Request $request, $token)
    {
        $request->validate([
            'rejected_by_name' => 'required|string|max:255',
            'rejected_by_email' => 'required|email|max:255',
            'rejection_reason' => 'required|string',
        ]);

        $quote = Quote::where('token', $token)->firstOrFail();

        if ($quote->status !== 'draft' && $quote->status !== 'sent') {
             return redirect()->back()->with('error', 'Cannot reject this quote.');
        }

        $quote->update([
            'status' => 'rejected',
            'notes' => $quote->notes . "\n\nRejection Reason: " . $request->rejection_reason . " (by {$request->rejected_by_name})",
        ]);

        // Log activity
        \Modules\Billing\Models\BillingLog::create([
            'company_id' => $quote->company_id,
            'event' => 'quote.rejected',
            'description' => "Quote #{$quote->quote_number} rejected by {$request->rejected_by_name}",
            'payload' => ['quote_id' => $quote->id, 'reason' => $request->rejection_reason],
            'level' => 'warning'
        ]);

        return view('billing::public.quote-rejected', compact('quote'));
    }
}

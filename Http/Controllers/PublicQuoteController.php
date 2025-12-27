<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Quote;
use Modules\Inventory\Models\Product;
use Illuminate\Support\Str;

class PublicQuoteController extends Controller
{
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
        $quote = Quote::where('token', $token)->with('lineItems')->firstOrFail();

        return view('billing::public.quote-show', compact('quote'));
    }
}

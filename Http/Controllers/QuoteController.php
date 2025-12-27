<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Quote;
use Modules\Inventory\Models\Product;

class QuoteController extends Controller
{
    public function create()
    {
        $companies = Company::all();
        $products = Product::all();

        return view('billing::quotes.create', compact('companies', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'prospect_name' => 'nullable|string|required_without:company_id',
            'prospect_email' => 'nullable|email|required_without:company_id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'valid_until' => 'nullable|date',
        ]);

        $quote = Quote::create([
            'company_id' => $validated['company_id'],
            'prospect_name' => $validated['prospect_name'],
            'prospect_email' => $validated['prospect_email'],
            'notes' => $validated['notes'],
            'valid_until' => $validated['valid_until'],
            'status' => 'draft',
            'token' => \Illuminate\Support\Str::random(32),
        ]);

        $total = 0;
        foreach ($validated['items'] as $item) {
            $subtotal = $item['quantity'] * $item['unit_price'];
            $quote->lineItems()->create([
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $subtotal,
            ]);
            $total += $subtotal;
        }

        $quote->update(['total' => $total]);

        return redirect()->route('billing.finance.quotes.show', $quote->id)->with('success', 'Quote created successfully.');
    }

    public function show($id)
    {
        $quote = Quote::with('lineItems')->findOrFail($id);
        return view('billing::quotes.show', compact('quote'));
    }
}

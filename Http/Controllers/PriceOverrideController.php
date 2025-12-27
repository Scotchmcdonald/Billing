<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class PriceOverrideController extends Controller
{
    public function index()
    {
        // Placeholder data
        $overrides = [
            [
                'id' => 1,
                'company_name' => 'Acme Corp',
                'product_name' => 'Enterprise License',
                'type' => 'Discount %',
                'value' => 15,
                'starts_at' => '2024-01-01',
                'ends_at' => '2024-12-31',
                'margin_impact' => 25, // %
                'approved_by' => 'John Doe',
                'active' => true,
            ],
            [
                'id' => 2,
                'company_name' => 'Globex Inc',
                'product_name' => 'Support Package',
                'type' => 'Fixed Price',
                'value' => 500,
                'starts_at' => '2024-02-01',
                'ends_at' => null,
                'margin_impact' => 12, // % (Low!)
                'approved_by' => 'Jane Smith',
                'active' => true,
            ],
        ];

        return view('billing::finance.overrides', compact('overrides'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'product_id' => 'required', 
            'price' => 'required|numeric',
            'cost' => 'required|numeric', 
            'confirmed_low_margin' => 'boolean'
        ]);

        $company = \Modules\Billing\Models\Company::findOrFail($validated['company_id']);
        $marginFloor = $company->margin_floor_percent ?? 20.0; 

        $price = $validated['price'];
        $cost = $validated['cost'];
        
        $marginPercent = $price > 0 ? (($price - $cost) / $price) * 100 : 0;

        if ($marginPercent < $marginFloor && empty($validated['confirmed_low_margin'])) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'warning',
                    'message' => "Margin Alert: This price results in " . number_format($marginPercent, 1) . "% margin, below your floor of {$marginFloor}%",
                    'margin_percent' => $marginPercent,
                    'margin_floor' => $marginFloor,
                    'requires_confirmation' => true
                ], 422);
            }
            return redirect()->back()->withErrors(['margin' => "Margin Alert: This price results in " . number_format($marginPercent, 1) . "% margin."]);
        }

        // Logic to create override would go here
        // PriceOverride::create($validated);

        return redirect()->back()->with('success', 'Override created successfully.');
    }
}

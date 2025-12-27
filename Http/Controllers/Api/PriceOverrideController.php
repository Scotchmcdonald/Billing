<?php

namespace Modules\Billing\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Inventory\Models\PriceOverride;
use Modules\Billing\Services\PricingEngineService;
use Modules\Billing\Models\Company;
use Modules\Inventory\Models\Product;
use Illuminate\Support\Facades\Auth;

class PriceOverrideController extends Controller
{
    protected $pricingService;

    public function __construct(PricingEngineService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * List active overrides.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $overrides = PriceOverride::with(['company', 'product', 'approver'])
            ->where('is_active', true)
            ->get();

        return response()->json($overrides);
    }

    /**
     * Create a new override.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:fixed,discount_percent,markup_percent',
            'value' => 'required|numeric',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'notes' => 'nullable|string',
            'confirm_margin_warning' => 'boolean',
        ]);

        // Margin Check
        $company = Company::findOrFail($validated['company_id']);
        $product = Product::findOrFail($validated['product_id']);
        
        $proposedPrice = 0;
        if ($validated['type'] === 'fixed') {
            $proposedPrice = $validated['value'];
        } elseif ($validated['type'] === 'discount_percent') {
            $basePrice = $product->base_price; 
            $proposedPrice = $basePrice * (1 - ($validated['value'] / 100));
        } elseif ($validated['type'] === 'markup_percent') {
            $basePrice = $product->cost_price;
            $proposedPrice = $basePrice * (1 + ($validated['value'] / 100));
        }

        $validation = $this->pricingService->validateMargin($company, $product, $proposedPrice);

        if (!$validation->is_safe && empty($validated['confirm_margin_warning'])) {
             return response()->json([
                 'error' => 'Margin Alert',
                 'validation' => $validation,
                 'message' => 'Proposed price is below margin floor. Please confirm to proceed.'
             ], 422);
        }

        $validated['approved_by'] = Auth::id();
        $validated['is_active'] = true;
        unset($validated['confirm_margin_warning']);

        $override = PriceOverride::create($validated);

        return response()->json([
            'message' => 'Price override created successfully.',
            'override' => $override,
            'margin_validation' => $validation,
        ], 201);
    }


    /**
     * Update an override.
     *
     * @param Request $request
     * @param PriceOverride $override
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, PriceOverride $override)
    {
        $validated = $request->validate([
            'type' => 'sometimes|in:fixed,discount_percent,markup_percent',
            'value' => 'sometimes|numeric',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'notes' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $override->update($validated);

        return response()->json([
            'message' => 'Price override updated successfully.',
            'override' => $override,
        ]);
    }

    /**
     * Deactivate an override.
     *
     * @param PriceOverride $override
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(PriceOverride $override)
    {
        $override->update(['is_active' => false]);

        return response()->json([
            'message' => 'Price override deactivated successfully.',
        ]);
    }
}

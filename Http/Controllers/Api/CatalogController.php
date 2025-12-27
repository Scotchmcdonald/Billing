<?php

namespace Modules\Billing\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Models\Company;
use Modules\Billing\Services\PricingEngineService;
use Modules\Inventory\Models\Product;

class CatalogController extends Controller
{
    protected $pricingEngine;

    public function __construct(PricingEngineService $pricingEngine)
    {
        $this->pricingEngine = $pricingEngine;
    }

    public function index()
    {
        // Auth: Can:finance.admin (Middleware should handle this)
        
        $products = Product::where('is_active', true)->get();
        
        $data = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'base_price' => $product->base_price,
                'tier_prices' => $product->getTierPriceMatrix(),
            ];
        });

        return response()->json($data);
    }

    public function showForCompany(Company $company)
    {
        // Auth: billing.auth (Middleware should handle this)
        
        $products = Product::where('is_active', true)->get();
        
        $data = $products->map(function ($product) use ($company) {
            $effectivePrice = $this->pricingEngine->calculateEffectivePrice($company, $product);
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $effectivePrice->price,
                'source' => $effectivePrice->source,
            ];
        });

        return response()->json($data);
    }

    public function showProductPricing(Product $product)
    {
        // Auth: Can:finance.admin
        
        $overrides = $product->priceOverrides()
            ->with('company:id,name')
            ->where('is_active', true)
            ->get()
            ->map(function ($override) {
                return [
                    'company_id' => $override->company_id,
                    'company_name' => $override->company->name,
                    'type' => $override->type,
                    'value' => $override->value,
                ];
            });

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'base_price' => $product->base_price,
            ],
            'tier_prices' => $product->getTierPriceMatrix(),
            'overrides' => $overrides,
        ]);
    }
}

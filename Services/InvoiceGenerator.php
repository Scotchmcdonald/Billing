<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Company;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Services\CatalogService;

class InvoiceGenerator
{
    protected CatalogService $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    /**
     * Generate invoice line item data from an Inventory SKU.
     *
     * @param Company $company
     * @param string $sku
     * @param int $quantity
     * @return array
     */
    public function generateLineItem(Company $company, string $sku, int $quantity = 1): array
    {
        $product = Product::where('sku', $sku)->firstOrFail();
        $unitPrice = $this->catalogService->getPriceForCompany($company, $sku);

        return [
            'product_id' => $product->id,
            'sku' => $product->sku,
            'description' => $product->description ?: $product->name,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total' => round($unitPrice * $quantity, 2),
        ];
    }
}

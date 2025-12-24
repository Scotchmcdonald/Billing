<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Company;
use Modules\Billing\Models\InvoiceLineItem;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Services\CatalogService;
use Illuminate\Support\Collection;

class InvoiceBuilderService
{
    protected CatalogService $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    /**
     * Draft an invoice (collection of line items) for a company.
     *
     * @param Company $company
     * @param array $items Array of ['sku' => string, 'quantity' => int]
     * @return Collection
     */
    public function draftInvoice(Company $company, array $items): Collection
    {
        $lineItems = collect();

        foreach ($items as $item) {
            $sku = $item['sku'];
            $quantity = $item['quantity'] ?? 1;

            $product = Product::where('sku', $sku)->firstOrFail();
            
            // Use CatalogService to get the correct price (PriceBook override or Base Price)
            $unitPrice = $this->catalogService->getPriceForCompany($company, $sku);
            
            $subtotal = round($unitPrice * $quantity, 2);

            // Create a non-persisted InvoiceLineItem instance for preview/draft
            $lineItem = new InvoiceLineItem([
                'product_id' => $product->id,
                'description' => $product->description ?: $product->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'tax_amount' => 0, // Placeholder for tax calculation logic
                'is_fee' => false,
            ]);

            $lineItems->push($lineItem);
        }

        return $lineItems;
    }
}

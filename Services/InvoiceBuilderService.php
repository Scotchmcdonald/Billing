<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Company;
use Modules\Billing\Models\InvoiceLineItem;
use Modules\Inventory\Models\Product;
use Modules\Billing\Services\PricingEngineService;
use Illuminate\Support\Collection;

class InvoiceBuilderService
{
    protected PricingEngineService $pricingEngineService;

    public function __construct(PricingEngineService $pricingEngineService)
    {
        $this->pricingEngineService = $pricingEngineService;
    }

    /**
     * Draft an invoice (collection of line items) for a company.
     *
     * @param Company $company
     * @param array<int, array{sku: string, quantity?: int}> $items Array of ['sku' => string, 'quantity' => int]
     * @return Collection<int, InvoiceLineItem>
     */
    public function draftInvoice(Company $company, array $items): Collection
    {
        $lineItems = collect();

        foreach ($items as $item) {
            $sku = $item['sku'];
            $quantity = $item['quantity'] ?? 1;

            $product = Product::where('sku', $sku)->firstOrFail();
            
            // Use PricingEngineService to get the correct price and tax credit
            $priceResult = $this->pricingEngineService->calculateEffectivePrice($company, $product);
            $unitPrice = $priceResult->price;
            $taxCredit = $priceResult->tax_credit;
            
            $subtotal = round($unitPrice * $quantity, 2);
            $totalTaxCredit = round($taxCredit * $quantity, 2);

            // Create a non-persisted InvoiceLineItem instance for preview/draft
            $lineItem = new InvoiceLineItem([
                'product_id' => $product->id,
                'description' => $product->description ?: $product->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'tax_amount' => 0, // Placeholder for tax calculation logic
                'tax_credit_amount' => $totalTaxCredit,
                'is_fee' => false,
            ]);

            $lineItems->push($lineItem);
        }

        return $lineItems;
    }
}

<?php

namespace Modules\Billing\Observers;

use Modules\Billing\Models\Invoice;
use Modules\Inventory\Models\Product;

class InvoiceObserver
{
    public function updated(Invoice $invoice)
    {
        if ($invoice->isDirty('status')) {
            $originalStatus = $invoice->getOriginal('status');
            $newStatus = $invoice->status;

            if ($newStatus === 'paid' && $originalStatus !== 'paid') {
                $this->adjustAssetCredits($invoice, 'add');
            } elseif ($originalStatus === 'paid' && $newStatus !== 'paid') {
                $this->adjustAssetCredits($invoice, 'remove');
            }
        }
    }

    protected function adjustAssetCredits(Invoice $invoice, string $operation)
    {
         foreach ($invoice->lineItems as $item) {
             // Access product, assuming relationship or query
             // Note: InvoiceLineItem might store product details, or rely on relation.
             // If product is deleted, this might fail, but usually acceptable risk or handled by SoftDeletes.
             $product = Product::find($item->product_id);
             
             if ($product && $product->category === 'Asset Credit') {
                 $amount = $item->subtotal; // Use subtotal for credit value
                 
                 if ($operation === 'add') {
                     $invoice->company->increment('account_balance', $amount);
                 } else {
                     $invoice->company->decrement('account_balance', $amount);
                 }
             }
        }
    }
}

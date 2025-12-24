<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Inventory\Models\Product;

class InvoiceLineItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'subtotal',
        'tax_amount',
        'is_fee',
    ];

    protected $casts = [
        'unit_price' => 'decimal:4',
        'subtotal' => 'decimal:4',
        'tax_amount' => 'decimal:4',
        'is_fee' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

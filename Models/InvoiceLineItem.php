<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Inventory\Models\Product;

/**
 * @property int $id
 * @property int $invoice_id
 * @property int|null $product_id
 * @property string $description
 * @property float $quantity
 * @property float $unit_price
 * @property float $subtotal
 * @property float $tax_amount
 * @property float $tax_credit_amount
 * @property bool $is_fee
 * @property string|null $type
 * @property \Illuminate\Support\Carbon|null $service_period_start
 * @property \Illuminate\Support\Carbon|null $service_period_end
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read Product|null $product
 * @property-read Invoice $invoice
 */
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
        'tax_credit_amount',
        'is_fee',
    ];

    protected $casts = [
        'unit_price' => 'decimal:4',
        'subtotal' => 'decimal:4',
        'tax_amount' => 'decimal:4',
        'tax_credit_amount' => 'decimal:4',
        'is_fee' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

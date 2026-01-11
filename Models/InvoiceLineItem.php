<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Billing\Database\Factories\InvoiceLineItemFactory;
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
    use HasFactory;

        protected $fillable = [
        'description',
        'dispute_reason',
        'invoice_id',
        'is_disputed',
        'is_fee',
                'product_id',
        'quantity',
        'service_period_end',
        'service_period_start',
        'standard_unit_price',
        'subtotal',
        'tax_amount',
        'tax_credit_amount',
                        'unit_price',
    ];

    protected static function newFactory()
    {
        return InvoiceLineItemFactory::new();
    }

    

    protected $casts = [
        'unit_price' => 'decimal:4',
        'standard_unit_price' => 'decimal:4',
        'subtotal' => 'decimal:4',
        'tax_amount' => 'decimal:4',
        'tax_credit_amount' => 'decimal:4',
        'is_fee' => 'boolean',
        'is_disputed' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

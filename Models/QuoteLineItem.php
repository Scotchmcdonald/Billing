<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Billing\Database\Factories\QuoteLineItemFactory;

/**
 * @property int $id
 * @property int $quote_id
 * @property int|null $product_id
 * @property string $description
 * @property int $quantity
 * @property float $unit_price
 * @property float|null $unit_price_monthly
 * @property float|null $unit_price_annually
 * @property float|null $standard_price
 * @property float $variance_amount
 * @property float $variance_percent
 * @property float $subtotal
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read Quote $quote
 * @property-read \Modules\Inventory\Models\Product|null $product
 */
class QuoteLineItem extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return QuoteLineItemFactory::new();
    }

        protected $fillable = [
        'quote_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'subtotal',
        'is_recurring',
        'billing_frequency',
        'frequency_locked',
        'unit_price_monthly',
        'unit_price_annually',
        'standard_price',
        'variance_amount',
        'variance_percent',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'unit_price_monthly' => 'decimal:2',
        'unit_price_annually' => 'decimal:2',
        'standard_price' => 'decimal:2',
        'variance_amount' => 'decimal:2',
        'variance_percent' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'is_recurring' => 'boolean',
        'frequency_locked' => 'boolean',
    ];

    public function quote(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Models\Product::class);
    }
}

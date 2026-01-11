<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTierPrice extends Model
{
        protected $fillable = [
        'product_id',
        'tier',
        'price',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'price' => 'decimal:4',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    // Assuming Product model exists in Modules\Inventory\Models\Product or similar.
    // Since I don't have the exact namespace for Product, I'll leave the relationship commented or generic for now, 
    // or try to find it.
    // The context mentions "Modules\Inventory\Providers\InventoryServiceProvider", so likely Modules\Inventory\Models\Product.
    
    public function product()
    {
        // Assuming the Product model is in the Inventory module
        return $this->belongsTo(\Modules\Inventory\Models\Product::class);
    }
}

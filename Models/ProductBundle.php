<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductBundle extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'product_ids' => 'array',
        'discount_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get active bundles.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get inactive bundles.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}

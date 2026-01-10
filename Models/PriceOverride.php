<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Billing\Models\Company;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PriceOverride extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Modules\Billing\Database\Factories\PriceOverrideFactory::new();
    }

    protected $guarded = [];

    protected $casts = [
        'value' => 'decimal:4',
        'margin_percent' => 'decimal:2',
        'is_active' => 'boolean',
        'below_minimum_margin' => 'boolean',
        'requested_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(\Modules\Inventory\Models\Product::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
    
    /**
     * Calculate margin percentage for this override
     */
    public function calculateMargin(): ?float
    {
        $product = $this->product;
        if (!$product || !$product->cost_price || $this->value <= 0) {
            return null;
        }
        
        return (($this->value - $product->cost_price) / $this->value) * 100;
    }
    
    /**
     * Check if this override is below the product's minimum margin
     */
    public function isBelowMinimumMargin(): bool
    {
        $margin = $this->calculateMargin();
        $product = $this->product;
        
        if (!$margin || !$product) {
            return false;
        }
        
        return $margin < ($product->min_margin_percent ?? 0);
    }
    
    /**
     * Scope for active overrides
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('status', 'approved');
    }
    
    /**
     * Scope for pending approval
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    /**
     * Scope for approved overrides
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}

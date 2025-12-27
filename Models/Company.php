<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;
use Modules\Billing\Models\BillingAuthorization;
use Modules\Billing\Models\PriceOverride;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Models\Invoice;
use App\Models\Customer;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use Billable, HasFactory;

    protected static function newFactory()
    {
        return \Modules\Billing\Database\Factories\CompanyFactory::new();
    }

    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
        'margin_floor_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function billingAuthorizations()
    {
        return $this->hasMany(BillingAuthorization::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'billing_authorizations')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function primaryContact()
    {
        return $this->belongsTo(User::class, 'primary_contact_id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function priceOverrides()
    {
        return $this->hasMany(PriceOverride::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function billableEntries()
    {
        return $this->hasMany(BillableEntry::class);
    }

    public function payments()
    {
        return $this->hasMany(\Modules\Billing\Models\Payment::class);
    }

    public function retainers()
    {
        return $this->hasMany(Retainer::class);
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class);
    }

    /**
     * Get the Stripe supported payment method types.
     *
     * @return array
     */
    public function supportedPaymentMethods()
    {
        return ['card', 'us_bank_account'];
    }

    public function getEffectivePrice(\Modules\Inventory\Models\Product $product, ?\Illuminate\Support\Carbon $date = null): float
    {
        // This logic will be handled by PricingEngineService, but we can add a convenience method here
        // or delegate to the service. For now, I'll leave it as a placeholder or simple implementation
        // if the service isn't injected.
        // Ideally, models shouldn't depend on services.
        // So this method might just check overrides and tiers directly if simple, 
        // but the requirement says "Check Override -> Tier -> Base".
        
        // Let's implement the basic logic here as requested by the Work Packet "Key Methods" section for Company Model.
        
        $date = $date ?? now();

        // 1. Check Override
        $override = $this->priceOverrides()
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->where(function ($query) use ($date) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $date);
            })
            ->where(function ($query) use ($date) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $date);
            })
            ->first();

        if ($override) {
            if ($override->type === 'fixed') {
                return $override->value;
            } elseif ($override->type === 'discount_percent') {
                return $product->base_price * (1 - ($override->value / 100));
            } elseif ($override->type === 'markup_percent') {
                return $product->base_price * (1 + ($override->value / 100));
            }
        }

        // 2. Check Tier
        // Assuming Product has a method getPriceForTier, or we query ProductTierPrice directly.
        // Since Product model is in another module, we should use the relationship if available or query.
        // The Product model enhancement is next in the todo list.
        // For now, let's assume we can access tier prices via product.
        
        $tierPrice = $product->tierPrices()
            ->where('tier', $this->pricing_tier)
            ->where(function ($query) use ($date) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $date);
            })
            ->where(function ($query) use ($date) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $date);
            })
            ->orderBy('starts_at', 'desc') // Get the most recent applicable price
            ->first();

        if ($tierPrice) {
            return $tierPrice->price;
        }

        // 3. Base Price
        return $product->base_price ?? 0.0;
    }

    public function hasOverrideFor(\Modules\Inventory\Models\Product $product): bool
    {
        return $this->priceOverrides()
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->exists();
    }

    public function isMarginSafe(float $price, float $cost): bool
    {
        if ($price <= 0) return false;
        $margin = (($price - $cost) / $price) * 100;
        return $margin >= $this->margin_floor_percent;
    }

    public function getPricingTierLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->pricing_tier));
    }
}

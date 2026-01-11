<?php

namespace Modules\Billing\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\Billing\DataTransferObjects\PriceResult;
use Modules\Billing\DataTransferObjects\ValidationResult;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\ProductTierPrice;
use Modules\Inventory\DataTransferObjects\ProductSnapshot;
use App\ValueObjects\Money;

class PricingEngineService
{
    /**
     * Calculate Price using immutable ProductSnapshot.
     * Decoupled from generic Inventory Model.
     */
    public function calculateEffectivePrice(Company $company, ProductSnapshot $product, ?Carbon $date = null): PriceResult
    {
        $date = $date ?? now();
        $cacheKey = "price_{$company->id}_{$product->id}_{$date->format('Y-m-d')}";

        return Cache::remember($cacheKey, 300, function () use ($company, $product, $date) {
            // BACKWARDS COMPATIBILITY: Convert cents to float for now
            $basePrice = Money::fromCents($product->base_price_cents);
            $price = $basePrice;
            $source = 'base';

            // 1. Check for active PriceOverride
            $override = $company->priceOverrides()
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
                    // Assuming override value is stored as float in DB currently, need adaptation
                    // Ideally override value should be cents too, but for now we convert
                    $price = Money::fromFloat((float)$override->value);
                } elseif ($override->type === 'discount_percent') {
                     $price = $basePrice->multiply(1 - ($override->value / 100));
                }
                $source = 'override';
            } else {
                // 2. Check ProductTierPrice (Direct Query via Billing Model)
                $tierPrice = ProductTierPrice::where('product_id', $product->id)
                    ->where('tier', $company->pricing_tier)
                    ->where(function ($query) use ($date) {
                        $query->whereNull('starts_at')->orWhere('starts_at', '<=', $date);
                    })
                    ->where(function ($query) use ($date) {
                        $query->whereNull('ends_at')->orWhere('ends_at', '>=', $date);
                    })
                    ->orderBy('starts_at', 'desc')
                    ->first();

                if ($tierPrice) {
                    $price = Money::fromFloat((float)$tierPrice->price);
                    $source = 'tier';
                }
            }

            // Calculate Tax Credit for Non-Profits
            $taxCredit = Money::fromCents(0);
            if ($company->pricing_tier === 'non_profit') {
                // Determine Standard Price (Standard Tier or Base Price)
                $standardTierPrice = ProductTierPrice::where('product_id', $product->id)
                    ->where('tier', 'standard')
                    ->where(function ($query) use ($date) {
                        $query->whereNull('starts_at')->orWhere('starts_at', '<=', $date);
                    })
                    ->where(function ($query) use ($date) {
                        $query->whereNull('ends_at')->orWhere('ends_at', '>=', $date);
                    })
                    ->orderBy('starts_at', 'desc')
                    ->first();
                
                $standardPrice = $standardTierPrice ? Money::fromFloat((float)$standardTierPrice->price) : $basePrice;
                
                // credit = max(0, standard - price)
                if ($standardPrice->amount > $price->amount) {
                    $taxCredit = $standardPrice->subtract($price);
                }
            }

            // Note: getGrossMarginPercent was on the Model. We simulate it here for now.
            // Margin % = ((Price - Cost) / Price) * 100
            $cost = Money::fromCents($product->cost_price_cents);
            $marginPercent = 0.0;
            
            if ($price->amount > 0) {
                $marginPercent = (($price->amount - $cost->amount) / $price->amount) * 100;
                $marginPercent = round($marginPercent, 2);
            }

            return new PriceResult(
                price: $price,
                source: $source,
                margin_percent: $marginPercent,
                tax_credit: $taxCredit
            );
        });
    }

    public function validateMargin(Company $company, ProductSnapshot $product, float $proposedPrice): ValidationResult
    {
        $cost = $product->cost_price_cents; // cents
        $priceCents = (int) round($proposedPrice * 100);
        
        $marginPercent = 0.0;
        if ($priceCents > 0) {
            $marginPercent = (($priceCents - $cost) / $priceCents) * 100;
            $marginPercent = round($marginPercent, 2);
        }
        
        $isSafe = $marginPercent >= $company->margin_floor_percent;
        $warnings = [];

        if (!$isSafe) {
            $warnings[] = "Proposed margin {$marginPercent}% is below company floor of {$company->margin_floor_percent}%";
        }


        return new ValidationResult(
            is_safe: $isSafe,
            margin_percent: $marginPercent,
            margin_floor_percent: $company->margin_floor_percent,
            warnings: $warnings
        );
    }

    public function getPriceBreakdown(Company $company, Product $product): array
    {
        $snapshot = ProductSnapshot::fromModel($product);
        $effective = $this->calculateEffectivePrice($company, $snapshot);
        $tierPrice = $product->getPriceForTier($company->pricing_tier);
        
        return [
            'base_price' => $product->base_price,
            'tier_price' => $tierPrice,
            'override_price' => $effective->source === 'override' ? $effective->price : null,
            'effective_price' => $effective->price,
            'tier_label' => $company->getPricingTierLabel(),
            'has_override' => $effective->source === 'override',
            'source' => $effective->source,
        ];
    }
}

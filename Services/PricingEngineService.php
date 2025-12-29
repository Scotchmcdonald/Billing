<?php

namespace Modules\Billing\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\Billing\DataTransferObjects\PriceResult;
use Modules\Billing\DataTransferObjects\ValidationResult;
use Modules\Billing\Models\Company;
use Modules\Inventory\Models\Product;

class PricingEngineService
{
    public function calculateEffectivePrice(Company $company, Product $product, ?Carbon $date = null): PriceResult
    {
        $date = $date ?? now();
        $cacheKey = "price_{$company->id}_{$product->id}_{$date->format('Y-m-d')}";

        return Cache::remember($cacheKey, 300, function () use ($company, $product, $date) {
            $price = $product->base_price;
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
                    $price = $override->value;
                } elseif ($override->type === 'discount_percent') {
                    $price = $product->base_price * (1 - ($override->value / 100));
                } elseif ($override->type === 'markup_percent') {
                    $price = $product->base_price * (1 + ($override->value / 100));
                }
                $source = 'override';
            } else {
                // 2. Check ProductTierPrice
                $tierPrice = $product->tierPrices()
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
                    $price = $tierPrice->price;
                    $source = 'tier';
                }
            }

            // Calculate Tax Credit for Non-Profits
            $taxCredit = 0.0;
            if ($company->pricing_tier === 'non_profit') {
                // Determine Standard Price (Standard Tier or Base Price)
                $standardTierPrice = $product->tierPrices()
                    ->where('tier', 'standard')
                    ->where(function ($query) use ($date) {
                        $query->whereNull('starts_at')->orWhere('starts_at', '<=', $date);
                    })
                    ->where(function ($query) use ($date) {
                        $query->whereNull('ends_at')->orWhere('ends_at', '>=', $date);
                    })
                    ->orderBy('starts_at', 'desc')
                    ->first();
                
                $standardPrice = $standardTierPrice ? $standardTierPrice->price : $product->base_price;
                $taxCredit = max(0, $standardPrice - $price);
            }

            return new PriceResult(
                price: $price,
                source: $source,
                margin_percent: $product->getGrossMarginPercent($price),
                tax_credit: $taxCredit
            );
        });
    }

    public function validateMargin(Company $company, Product $product, float $proposedPrice): ValidationResult
    {
        $marginPercent = $product->getGrossMarginPercent($proposedPrice);
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
        $effective = $this->calculateEffectivePrice($company, $product);
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

<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Billing\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $stripe_status
 * @property string|null $stripe_id
 * @property string|null $stripe_price
 * @property int $product_id
 * @property int $quantity
 * @property float $effective_price
 * @property string $billing_frequency
 * @property \Illuminate\Support\Carbon $starts_at
 * @property \Illuminate\Support\Carbon|null $ends_at
 * @property \Illuminate\Support\Carbon $next_billing_date
 * @property bool $is_active
 * @property array $metadata
 * @property \Illuminate\Support\Carbon|null $contract_start_date
 * @property \Illuminate\Support\Carbon|null $contract_end_date
 * @property string|null $renewal_status
 * @property float $monthly_amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection|SubscriptionItem[] $items
 * @property-read Company $company
 * @property-read \Modules\Inventory\Models\Product $product
 */
class Subscription extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Modules\Billing\Database\Factories\SubscriptionFactory::new();
    }

    protected $table = 'billing_subscriptions';

    protected $guarded = [];

    protected $casts = [
        'effective_price' => 'decimal:4',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'next_billing_date' => 'date',
        'is_active' => 'boolean',
        'metadata' => 'array',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(SubscriptionItem::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(\Modules\Inventory\Models\Product::class);
    }
}

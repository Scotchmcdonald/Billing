<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Billing\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
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

        protected $fillable = [
        'company_id',
        'product_id',
        'name',
                'quantity',
        'billing_frequency',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'next_billing_date',
        'contract_start_date',
        'contract_end_date',
        'contract_document_path',
        'effective_price',
        'renewal_status',
        'is_active',
        'metadata',
            ];

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

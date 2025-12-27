<?php

namespace Modules\Billing\Models;

use Laravel\Cashier\Subscription as CashierSubscription;
use Modules\Billing\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends CashierSubscription
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

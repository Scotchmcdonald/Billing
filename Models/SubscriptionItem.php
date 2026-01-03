<?php

namespace Modules\Billing\Models;

use Laravel\Cashier\SubscriptionItem as CashierSubscriptionItem;

class SubscriptionItem extends CashierSubscriptionItem
{
    protected $table = 'billing_subscription_items';

    protected $guarded = [];

    public function assets()
    {
        return $this->belongsToMany(\Modules\Inventory\Models\Asset::class, 'asset_subscription_item', 'subscription_item_id', 'asset_id');
    }
}

<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionItem extends Model
{
    protected $table = 'billing_subscription_items';

        protected $fillable = [
        'subscription_id',
                'quantity',
    ];

    public function assets()
    {
        return $this->belongsToMany(\Modules\Inventory\Models\Asset::class, 'asset_subscription_item', 'subscription_item_id', 'asset_id');
    }
}

<?php

namespace Modules\Billing\Models;

use Laravel\Cashier\SubscriptionItem as CashierSubscriptionItem;

class SubscriptionItem extends CashierSubscriptionItem
{
    protected $table = 'billing_subscription_items';

    protected $guarded = [];
}

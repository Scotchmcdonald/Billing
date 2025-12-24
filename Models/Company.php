<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;
use Modules\Billing\Models\BillingAuthorization;

class Company extends Model
{
    use Billable;

    protected $guarded = [];

    public function billingAuthorizations()
    {
        return $this->hasMany(BillingAuthorization::class);
    }

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'billing_authorizations')
                    ->withPivot('role')
                    ->withTimestamps();
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
}

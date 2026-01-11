<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Inventory\Models\Asset;

class BillingAgreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'asset_id',
        'billing_strategy',
        'rto_total_cents',
        'rto_balance_cents',
        'is_separate_hosting',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}

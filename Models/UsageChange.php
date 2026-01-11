<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UsageChange extends Model
{
    use HasFactory;

        protected $fillable = [
        'subscription_id',
        'company_id',
        'old_quantity',
        'new_quantity',
        'delta',
        'source',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected $appends = [
        'delta',
    ];

    public function getDeltaAttribute()
    {
        return $this->new_quantity - $this->old_quantity;
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}

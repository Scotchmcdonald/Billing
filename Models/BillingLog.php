<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;

class BillingLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

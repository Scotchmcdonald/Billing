<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;

class BillingLog extends Model
{
        protected $fillable = [
        'company_id',
        'user_id',
        'action',
        'description',
        'payload',
        'ip_address',
    ];

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

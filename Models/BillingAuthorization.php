<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;

class BillingAuthorization extends Model
{
        protected $fillable = [
        'company_id',
        'user_id',
        'role',
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

<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invitation extends Model
{
    use HasFactory;

    protected $table = 'billing_invitations';

        protected $fillable = [
        'company_id',
        'email',
        'role',
        'token',
        'expires_at',
        'company_name',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }
}

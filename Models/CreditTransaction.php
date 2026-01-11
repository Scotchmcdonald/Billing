<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    protected $table = 'billing_credit_transactions';

    protected $fillable = [
        'company_id',
        'type',
        'amount',
        'reference_type',
        'reference_id',
        'description',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

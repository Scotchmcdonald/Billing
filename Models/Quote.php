<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quote extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'valid_until' => 'date',
        'total' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function lineItems()
    {
        return $this->hasMany(QuoteLineItem::class);
    }
}

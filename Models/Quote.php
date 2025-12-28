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
        'viewed_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function lineItems()
    {
        return $this->hasMany(QuoteLineItem::class);
    }

    /**
     * Check if the quote has been viewed.
     */
    public function getIsViewedAttribute(): bool
    {
        return !is_null($this->viewed_at);
    }

    /**
     * Check if the quote has been accepted.
     */
    public function getIsAcceptedAttribute(): bool
    {
        return !is_null($this->accepted_at);
    }

    /**
     * Get the number of days it took for the quote to be viewed.
     */
    public function getDaysToViewAttribute(): ?int
    {
        if (!$this->is_viewed || !$this->created_at) {
            return null;
        }
        
        return $this->created_at->diffInDays($this->viewed_at);
    }
}

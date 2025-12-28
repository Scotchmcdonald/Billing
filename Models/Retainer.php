<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Retainer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'hours_purchased' => 'decimal:2',
        'hours_remaining' => 'decimal:2',
        'price_paid' => 'integer',
        'purchased_at' => 'date',
        'expires_at' => 'date',
    ];

    /**
     * Get the company this retainer belongs to.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope to get active retainers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get depleted retainers.
     */
    public function scopeDepleted($query)
    {
        return $query->where('status', 'depleted');
    }

    /**
     * Scope to get expired retainers.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }
}

<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $company_id
 * @property float $hours_purchased
 * @property float $hours_remaining
 * @property int $price_paid
 * @property \Illuminate\Support\Carbon $purchased_at
 * @property \Illuminate\Support\Carbon $expires_at
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read Company $company
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|Retainer active()
 * @method static \Illuminate\Database\Eloquent\Builder|Retainer depleted()
 * @method static \Illuminate\Database\Eloquent\Builder|Retainer expired()
 */
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
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
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

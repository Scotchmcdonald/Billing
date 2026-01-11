<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Billing\Database\Factories\QuoteFactory;

/**
 * @property int $id
 * @property int $company_id
 * @property string $pricing_tier
 * @property string $quote_number
 * @property \Illuminate\Support\Carbon $valid_until
 * @property float $total
 * @property string $status
 * @property string $billing_frequency
 * @property bool $requires_approval
 * @property float $approval_threshold_percent
 * @property \Illuminate\Support\Carbon|null $viewed_at
 * @property string|null $viewed_ip
 * @property \Illuminate\Support\Carbon|null $accepted_at
 * @property string|null $signer_name
 * @property string|null $signer_email
 * @property string|null $signature_data
 * @property string $public_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read Company $company
 * @property-read \Illuminate\Database\Eloquent\Collection|QuoteLineItem[] $lineItems
 * @property-read bool $is_viewed
 * @property-read bool $is_accepted
 * @property-read int|null $days_to_view
 */
class Quote extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return QuoteFactory::new();
    }

        protected $fillable = [
        'company_id',
        'client_id',
        'quote_number',
        'title',
        'status',
        'valid_until',
        'subtotal',
        'tax_total',
        'total',
        'notes',
        'billing_frequency',
        'pricing_tier',
        'requires_approval',
        'approval_threshold_percent',
        'token',
        'viewed_at',
        'viewed_ip',
        'accepted_at',
        'signer_name',
        'signer_email',
        'signature_data',
        'prospect_name',
        'prospect_email',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'total' => 'decimal:2',
        'viewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'requires_approval' => 'boolean',
        'approval_threshold_percent' => 'decimal:2',
    ];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\Crm\Models\Client::class);
    }

    public function lineItems(): \Illuminate\Database\Eloquent\Relations\HasMany
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

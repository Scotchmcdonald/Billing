<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispute extends Model
{
    protected $table = 'invoice_disputes';

    protected $fillable = [
        'invoice_id',
        'company_id',
        'reason',
        'disputed_amount',
        'line_item_ids',
        'explanation',
        'status',
        'resolution',
        'created_by',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'line_item_ids' => 'array',
        'disputed_amount' => 'decimal:2',
        'resolved_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'resolved_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DisputeAttachment::class);
    }
}

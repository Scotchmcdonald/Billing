<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisputeAttachment extends Model
{
    protected $table = 'dispute_attachments';

    protected $fillable = [
        'dispute_id',
        'filename',
        'path',
        'mime_type',
        'size',
    ];

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }
}

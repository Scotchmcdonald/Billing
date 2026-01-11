<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Billing\Models\Company;
use App\Models\User;
use Modules\Billing\Models\InvoiceLineItem;

/**
 * @property int $id
 * @property int $company_id
 * @property int|null $user_id
 * @property int|null $invoice_line_item_id
 * @property int|null $subscription_id
 * @property string $description
 * @property float $quantity
 * @property float $rate
 * @property float $subtotal
 * @property bool $is_billable
 * @property string $type
 * @property \Illuminate\Support\Carbon $date
 * @property array $metadata
 * @property float $amount
 * @property string $billing_status
 * @property \Illuminate\Support\Carbon|null $status_changed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read Company $company
 * @property-read User|null $user
 * @property-read InvoiceLineItem|null $invoiceLineItem
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|BillableEntry unbilled()
 */
class BillableEntry extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Modules\Billing\Database\Factories\BillableEntryFactory::new();
    }

        protected $fillable = [
        'company_id',
        'user_id',
        'ticket_id',
        'ticket_tier',
        'invoice_id',
        'invoice_line_item_id',
        'type',
        'description',
        'quantity',
        'rate',
        'subtotal',
        'is_billable',
        'billing_status',
        'date',
        'metadata',
        'quantity',
        'rate',
        'subtotal',
        'is_billable',
        'date',
        'metadata',
        'ticket_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'rate' => 'decimal:4',
        'subtotal' => 'decimal:4',
        'is_billable' => 'boolean',
        'date' => 'date',
        'metadata' => 'array',
    ];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoiceLineItem(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InvoiceLineItem::class);
    }

    public function scopeUnbilled($query)
    {
        return $query->whereNull('invoice_line_item_id')->where('is_billable', true);
    }

    /**
     * Get the receipt URL if available.
     * Only returns URLs for valid receipt paths stored in the receipts directory.
     */
    public function getReceiptAttribute(): ?string
    {
        if (!$this->receipt_path) {
            return null;
        }
        
        // Sanitize the path by removing any directory traversal attempts
        $sanitizedPath = basename($this->receipt_path);
        
        // Validate that the sanitized path is not empty and contains valid characters
        if (empty($sanitizedPath) || !preg_match('/^[a-zA-Z0-9_\-\.]+$/', $sanitizedPath)) {
            return null;
        }
        
        // Use Laravel's Storage facade for secure path handling
        return \Illuminate\Support\Facades\Storage::url('receipts/' . $sanitizedPath);
    }
}

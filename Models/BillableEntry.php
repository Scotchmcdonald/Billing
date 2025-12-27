<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Billing\Models\Company;
use App\Models\User;
use Modules\Billing\Models\InvoiceLineItem;

class BillableEntry extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Modules\Billing\Database\Factories\BillableEntryFactory::new();
    }

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:2',
        'rate' => 'decimal:4',
        'subtotal' => 'decimal:4',
        'is_billable' => 'boolean',
        'date' => 'date',
        'metadata' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoiceLineItem()
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

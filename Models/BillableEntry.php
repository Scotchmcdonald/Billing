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
     */
    public function getReceiptAttribute(): ?string
    {
        if (!$this->receipt_path) {
            return null;
        }
        
        // Assuming receipts are stored in storage/app/receipts
        // You may need to adjust this based on your actual storage configuration
        return asset('storage/receipts/' . $this->receipt_path);
    }
}

<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Billing\Database\Factories\InvoiceFactory;
use Modules\Billing\Models\Company;

/**
 * @property int $id
 * @property int $company_id
 * @property string $invoice_number
 * @property \Illuminate\Support\Carbon $issue_date
 * @property \Illuminate\Support\Carbon $due_date
 * @property float $subtotal
 * @property float $tax_total
 * @property float $total
 * @property string $status
 * @property string|null $notes
 * @property string $currency
 * @property bool $is_disputed
 * @property bool $dunning_paused
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $approved_by
 * @property float $paid_amount
 * @property string|null $xero_invoice_id
 * @property string|null $stripe_invoice_id
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read Company $company
 * @property-read \Illuminate\Database\Eloquent\Collection|InvoiceLineItem[] $lineItems
 * @property-read \Illuminate\Database\Eloquent\Collection|Payment[] $payments
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \Illuminate\Database\Eloquent\Collection|CreditNote[] $creditNotes
 * @property-read \Illuminate\Database\Eloquent\Collection|BillableEntry[] $billableEntries
 */
class Invoice extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return InvoiceFactory::new();
    }

    protected $guarded = [];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:4',
        'tax_total' => 'decimal:4',
        'total' => 'decimal:4',
        'is_disputed' => 'boolean',
        'dunning_paused' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(\Modules\Crm\Models\Client::class, 'client_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function lineItems()
    {
        return $this->hasMany(InvoiceLineItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class);
    }

    public function billableEntries()
    {
        return $this->hasManyThrough(BillableEntry::class, InvoiceLineItem::class);
    }

    /**
     * Get the total amount of disputed line items.
     */
    public function getDisputedAmountAttribute()
    {
        return $this->lineItems()->where('is_disputed', true)->sum('subtotal');
    }

    /**
     * Get the amount that is currently payable (Total - Disputed - Paid).
     */
    public function getPayableAmountAttribute()
    {
        $disputed = $this->disputed_amount;
        $paid = $this->paid_amount;
        
        return max(0, $this->total - $disputed - $paid);
    }

    /**
     * Check if the invoice has any disputed items.
     */
    public function getIsPartiallyDisputedAttribute()
    {
        return $this->lineItems()->where('is_disputed', true)->exists();
    }
}

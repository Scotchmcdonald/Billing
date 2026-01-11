<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

/**
 * @property int $id
 * @property int $invoice_id
 * @property int $company_id
 * @property int $amount
 * @property string $reason
 * @property string|null $notes
 * @property int|null $issued_by
 * @property \Illuminate\Support\Carbon|null $applied_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * 
 * @property-read Invoice $invoice
 * @property-read Company $company
 * @property-read User|null $issuedBy
 */
class CreditNote extends Model
{
    use HasFactory, SoftDeletes;

        protected $fillable = [
        'company_id',
        'invoice_id',
        'amount',
        'reason',
        'notes',
        'issued_by',
        'applied_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'applied_at' => 'datetime',
        'issue_date' => 'datetime',
    ];

    /**
     * Get the invoice this credit note belongs to.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the company this credit note belongs to.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who issued this credit note.
     */
    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}

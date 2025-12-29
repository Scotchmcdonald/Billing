<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Company;
use App\Models\User;

/**
 * @property int $id
 * @property int $invoice_id
 * @property int $company_id
 * @property float $amount
 * @property \Illuminate\Support\Carbon $payment_date
 * @property string $payment_method
 * @property string|null $transaction_id
 * @property string|null $notes
 * @property string $status
 * @property string|null $gateway_payment_id
 * @property string|null $payment_reference
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read Invoice $invoice
 * @property-read Company $company
 * @property-read User|null $creator
 */
class Payment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:4',
        'payment_date' => 'date',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

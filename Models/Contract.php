<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Contract extends Model
{
    use HasFactory;

    protected $table = 'billing_subscriptions';

        protected $fillable = [
        'company_id',
        'product_id',
        'name',
                'is_active',
        'starts_at',
        'ends_at',
        'contract_start_date',
        'contract_end_date',
        'billing_frequency',
        'quantity',
        'effective_price',
        'contract_document_path',
        'renewal_status',
        'metadata',
        'next_billing_date',
            ];

    protected $casts = [
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope('contract', function (Builder $builder) {
            $builder->whereNotNull('contract_start_date');
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

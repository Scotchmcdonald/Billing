<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Contract extends Model
{
    use HasFactory;

    protected $table = 'billing_subscriptions';

    protected $guarded = [];

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

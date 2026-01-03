<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Billing\Database\Factories\ContractPriceHistoryFactory;

class ContractPriceHistory extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return ContractPriceHistoryFactory::new();
    }

    protected $fillable = ['contract_id', 'unit_price', 'started_at', 'ended_at'];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(ServiceContract::class, 'contract_id');
    }
}

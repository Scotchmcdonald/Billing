<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Crm\Models\Client;
use Modules\Billing\Database\Factories\ServiceContractFactory;

class ServiceContract extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return ServiceContractFactory::new();
    }

    protected $fillable = ['client_id', 'name', 'status', 'standard_rate'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function priceHistory()
    {
        return $this->hasMany(ContractPriceHistory::class, 'contract_id');
    }

    public function currentPrice()
    {
        return $this->hasOne(ContractPriceHistory::class, 'contract_id')
            ->whereNull('ended_at')
            ->latest('started_at');
    }
}

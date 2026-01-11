<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class BillingSettings extends Model
{
        protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    public function getValueAttribute($value)
    {
        if ($this->is_encrypted && !empty($value)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value;
            }
        }
        
        if ($this->type === 'boolean') {
            return (bool) $value;
        }
        if ($this->type === 'integer') {
            return (int) $value;
        }
        if ($this->type === 'float') {
            return (float) $value;
        }
        if ($this->type === 'json') {
            return json_decode($value, true);
        }

        return $value;
    }

    public function setValueAttribute($value)
    {
        if ($this->is_encrypted && !empty($value)) {
            $this->attributes['value'] = Crypt::encryptString($value);
        } else {
            if (is_array($value) || is_object($value)) {
                $this->attributes['value'] = json_encode($value);
            } else {
                $this->attributes['value'] = $value;
            }
        }
    }
}

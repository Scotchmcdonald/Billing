<?php

declare(strict_types=1);

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Crypt;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class BillingSetting extends Model
{
    use LogsActivity;

    protected $fillable = [
        'key',
        'value',
        'is_encrypted',
        'group',
        'type',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Billing setting has been {$eventName}");
    }

    /**
     * Get the value attribute.
     * Automatically decrypts if is_encrypted is true.
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                if ($value === null) {
                    return null;
                }

                if ($attributes['is_encrypted'] ?? false) {
                    try {
                        return Crypt::decryptString($value);
                    } catch (\Exception $e) {
                        return $value; // Return raw if decryption fails (shouldn't happen)
                    }
                }

                // Cast based on type
                return match ($attributes['type'] ?? 'string') {
                    'boolean' => (bool) $value,
                    'integer' => (int) $value,
                    'float' => (float) $value,
                    'json' => json_decode($value, true),
                    default => $value,
                };
            },
            set: function ($value, $attributes) {
                if ($attributes['is_encrypted'] ?? false) {
                    return Crypt::encryptString((string) $value);
                }
                
                if (is_array($value) || is_object($value)) {
                    return json_encode($value);
                }

                return (string) $value;
            }
        );
    }
}

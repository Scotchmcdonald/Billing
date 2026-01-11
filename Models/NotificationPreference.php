<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class NotificationPreference extends Model
{
    use HasFactory;

        protected $fillable = [
        'user_id',
        'notification_type',
        'email_enabled',
        'in_app_enabled',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'in_app_enabled' => 'boolean',
    ];

    /**
     * Get the user that owns this preference.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get preferences for a specific notification type.
     */
    public function scopeForType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    /**
     * Scope to get preferences where email is enabled.
     */
    public function scopeEmailEnabled($query)
    {
        return $query->where('email_enabled', true);
    }

    /**
     * Scope to get preferences where in-app is enabled.
     */
    public function scopeInAppEnabled($query)
    {
        return $query->where('in_app_enabled', true);
    }
}

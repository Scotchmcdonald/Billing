<?php

namespace Modules\Billing\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Modules\Billing\Models\BillingAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Log an audit event for a model.
     */
    public function log(
        Model $auditable,
        string $event,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $user = null
    ): BillingAuditLog {
        $user = $user ?? Auth::user();

        $log = BillingAuditLog::create([
            'auditable_type' => get_class($auditable),
            'auditable_id' => $auditable->id,
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_id' => $user?->id,
            'ip_address' => request()->ip(),
        ]);

        return $log;
    }

    /**
     * Get audit logs for a specific entity.
     */
    public function getLogsForEntity(string $type, int $id): Collection
    {
        return BillingAuditLog::where('auditable_type', $type)
            ->where('auditable_id', $id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get audit logs for a specific user.
     */
    public function getLogsForUser(User $user, ?Carbon $since = null): Collection
    {
        $query = BillingAuditLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($since) {
            $query->where('created_at', '>=', $since);
        }

        return $query->get();
    }

    /**
     * Get recent activity across all entities.
     */
    public function getRecentActivity(int $limit = 50): Collection
    {
        return BillingAuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs filtered by event type.
     */
    public function getLogsByEvent(string $event, ?int $limit = null): Collection
    {
        $query = BillingAuditLog::event($event)
            ->with('user')
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get audit logs for a specific model instance.
     */
    public function getLogsForModel(Model $model): Collection
    {
        return $this->getLogsForEntity(get_class($model), $model->id);
    }

    /**
     * Log a model creation event.
     */
    public function logCreated(Model $model, ?User $user = null): BillingAuditLog
    {
        return $this->log(
            $model,
            'created',
            null,
            $model->getAttributes(),
            $user
        );
    }

    /**
     * Log a model update event.
     */
    public function logUpdated(Model $model, array $changes, ?User $user = null): BillingAuditLog
    {
        return $this->log(
            $model,
            'updated',
            $changes['old'] ?? null,
            $changes['new'] ?? null,
            $user
        );
    }

    /**
     * Log a model deletion event.
     */
    public function logDeleted(Model $model, ?User $user = null): BillingAuditLog
    {
        return $this->log(
            $model,
            'deleted',
            $model->getAttributes(),
            null,
            $user
        );
    }

    /**
     * Log a status change event.
     */
    public function logStatusChanged(
        Model $model,
        string $oldStatus,
        string $newStatus,
        ?User $user = null
    ): BillingAuditLog {
        return $this->log(
            $model,
            'status_changed',
            ['status' => $oldStatus],
            ['status' => $newStatus],
            $user
        );
    }

    /**
     * Get audit trail summary for an entity (grouped by event type).
     */
    public function getAuditSummary(string $type, int $id): array
    {
        $logs = $this->getLogsForEntity($type, $id);

        return [
            'total_events' => $logs->count(),
            'events_by_type' => $logs->groupBy('event')->map->count()->toArray(),
            'first_event' => $logs->last()?->created_at,
            'last_event' => $logs->first()?->created_at,
            'unique_users' => $logs->pluck('user_id')->unique()->count(),
        ];
    }
}

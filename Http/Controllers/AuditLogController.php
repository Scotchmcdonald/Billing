<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Services\AuditService;
use Modules\Billing\Models\BillingAuditLog;
use Illuminate\Support\Carbon;

class AuditLogController extends Controller
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Display audit logs with filtering.
     */
    public function index(Request $request)
    {
        $query = BillingAuditLog::with('user');

        // Filter by entity type
        if ($request->has('entity_type') && $request->entity_type) {
            $query->where('auditable_type', $request->entity_type);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by event type
        if ($request->has('event') && $request->event) {
            $query->where('event', $request->event);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }
        if ($request->has('end_date')) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        // Get unique entity types and events for filters
        $entityTypes = BillingAuditLog::select('auditable_type')
            ->distinct()
            ->pluck('auditable_type');
        
        $eventTypes = BillingAuditLog::select('event')
            ->distinct()
            ->pluck('event');

        $users = \App\Models\User::orderBy('name')->get();

        return view('billing::finance.audit-log', compact(
            'logs',
            'entityTypes',
            'eventTypes',
            'users'
        ));
    }

    /**
     * Display audit logs for a specific entity.
     */
    public function forEntity(Request $request, string $type, int $id)
    {
        $logs = $this->auditService->getLogsForEntity($type, $id);
        $summary = $this->auditService->getAuditSummary($type, $id);

        return view('billing::finance.audit-log-entity', compact('logs', 'summary', 'type', 'id'));
    }

    /**
     * Get recent activity (API endpoint).
     */
    public function recent(Request $request)
    {
        $limit = $request->input('limit', 50);
        $activity = $this->auditService->getRecentActivity($limit);

        return response()->json($activity);
    }
}

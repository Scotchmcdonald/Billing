<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TechnicianFeedbackController extends Controller
{
    /**
     * Display technician's time entry feedback
     */
    public function index(Request $request): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }
        
        // Get date range
        $range = $request->get('range', 'month');
        $startDate = match ($range) {
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'quarter' => Carbon::now()->startOfQuarter(),
            default => Carbon::now()->subYears(2),
        };

        // Build query
        $query = \Modules\Billing\Models\BillableEntry::query()
            ->where('user_id', $user->id)
            ->with(['company', 'invoiceLineItem.invoice'])
            ->where('date', '>=', $startDate);

        // Filter by status if specified
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('billing_status', $request->status);
        }

        // Get time entries
        $timeEntries = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Calculate summary
        $allEntries = \Modules\Billing\Models\BillableEntry::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->get();
        
        $summary = [
            'total_hours' => $allEntries->sum('hours'),
            'pending_hours' => $allEntries->where('billing_status', 'pending')->sum('hours'),
            'pending_value' => $allEntries->where('billing_status', 'pending')->sum('billable_amount'),
            'billed_hours' => $allEntries->where('billing_status', 'billed')->sum('hours'),
            'billed_value' => $allEntries->where('billing_status', 'billed')->sum('billable_amount'),
            'paid_hours' => $allEntries->where('billing_status', 'paid')->sum('hours'),
            'paid_value' => $allEntries->where('billing_status', 'paid')->sum('billable_amount'),
            'disputed_hours' => $allEntries->where('billing_status', 'disputed')->sum('hours'),
            'disputed_value' => $allEntries->where('billing_status', 'disputed')->sum('billable_amount'),
        ];

        // Get recent status changes (last 30 days)
        $recentChanges = \Modules\Billing\Models\BillableEntry::where('user_id', $user->id)
            ->with(['company'])
            ->whereNotNull('status_changed_at')
            ->where('status_changed_at', '>=', Carbon::now()->subDays(30))
            ->orderBy('status_changed_at', 'desc')
            ->limit(10)
            ->get();

        return view('billing::technician.feedback', compact('timeEntries', 'summary', 'recentChanges'));
    }
}

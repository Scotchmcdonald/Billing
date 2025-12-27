<?php

namespace Modules\Billing\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Models\BillableEntry;
use Modules\Billing\Models\Company;
use Illuminate\Support\Facades\Auth;

class BillableEntryController extends Controller
{
    /**
     * Get unbilled entries for a company.
     *
     * @param Company $company
     * @return \Illuminate\Http\JsonResponse
     */
    public function unbilled(Company $company)
    {
        $entries = BillableEntry::where('company_id', $company->id)
            ->unbilled()
            ->get();

        return response()->json($entries);
    }

    /**
     * Create a new billable entry.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'ticket_id' => 'nullable|integer', // Assuming tickets table exists or is external
            'type' => 'required|in:time,expense,product',
            'description' => 'required|string',
            'quantity' => 'required|numeric',
            'rate' => 'required|numeric',
            'date' => 'required|date',
            'is_billable' => 'boolean',
            'metadata' => 'nullable|array',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['subtotal'] = $validated['quantity'] * $validated['rate'];
        $validated['is_billable'] = $validated['is_billable'] ?? true;

        $entry = BillableEntry::create($validated);

        return response()->json([
            'message' => 'Billable entry created successfully.',
            'entry' => $entry,
        ], 201);
    }

    /**
     * Toggle the billable status of an entry.
     *
     * @param BillableEntry $entry
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleBillable(BillableEntry $entry)
    {
        if ($entry->invoice_line_item_id) {
            return response()->json(['error' => 'Cannot modify an entry that has already been invoiced.'], 400);
        }

        $entry->update(['is_billable' => !$entry->is_billable]);

        return response()->json([
            'message' => 'Billable status updated successfully.',
            'entry' => $entry,
        ]);
    }
}

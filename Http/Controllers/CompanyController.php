<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\BillingLog;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        if ($request->has('tier')) {
            $query->where('pricing_tier', $request->get('tier'));
        }

        $companies = $query->withCount(['subscriptions', 'billingAuthorizations'])
                           ->paginate(20);

        return view('billing::companies.index', compact('companies'));
    }

    public function show(Company $company)
    {
        $company->load(['subscriptions', 'billingAuthorizations.user', 'invoices']);
        
        // Mock metrics for now
        $metrics = [
            'lifetime_value' => $company->invoices()->sum('total') / 100,
            'mrr' => $company->subscriptions->sum(function($sub) {
                return $sub->active() ? 100 : 0; // Placeholder
            }),
            'avg_invoice' => 1250.00,
        ];

        return view('billing::companies.show', compact('company', 'metrics'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'pricing_tier' => 'required|string',
            'margin_floor' => 'required|numeric|min:0|max:100',
            'payment_terms' => 'required|string',
        ]);

        $company->update($validated);

        return redirect()->back()->with('success', 'Company settings updated.');
    }
}

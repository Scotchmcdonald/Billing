<?php

namespace Modules\Billing\Http\Controllers\Finance;

use Illuminate\Routing\Controller;
use Modules\Billing\Models\Company;

class PortalAccessController extends Controller
{
    public function index()
    {
        $companies = Company::orderBy('name')->get();

        return view('billing::finance.portal-access.index', compact('companies'));
    }
}

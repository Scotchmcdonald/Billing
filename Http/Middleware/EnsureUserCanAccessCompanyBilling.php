<?php

declare(strict_types=1);

namespace Modules\Billing\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Billing\Services\BillingAuthorizationService;
use Modules\Billing\Models\Company;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessCompanyBilling
{
    protected BillingAuthorizationService $authService;

    public function __construct(BillingAuthorizationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $company = $request->route('company');

        // If company is passed as ID, resolve it
        if (!($company instanceof Company)) {
            $company = Company::find($company);
        }

        if (!$company) {
            abort(404, 'Company not found.');
        }

        if (!$this->authService->canViewBilling($request->user(), $company)) {
            abort(403, 'Unauthorized access to company billing.');
        }

        // Ensure the resolved model is available in the request for the controller
        $request->route()->setParameter('company', $company);

        return $next($request);
    }
}

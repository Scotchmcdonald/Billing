<?php

declare(strict_types=1);

namespace Modules\Billing\Services;

use App\Models\User;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\BillingAuthorization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

class BillingAuthorizationService
{
    /**
     * Check if the user is authorized to view the company's billing info.
     *
     * @param User $user
     * @param Company $company
     * @return bool
     */
    public function canViewBilling(User $user, Company $company): bool
    {
        // Check if user is a global admin or finance admin (internal staff)
        if ($user->isAdmin() || $user->can('finance.admin')) {
            return true;
        }

        // Check if user has a direct authorization for this company
        return $company->billingAuthorizations()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Check if the user is authorized to manage the company's billing info.
     *
     * @param User $user
     * @param Company $company
     * @return bool
     */
    public function canManageBilling(User $user, Company $company): bool
    {
        if ($user->isAdmin() || $user->can('finance.admin')) {
            return true;
        }

        return $company->billingAuthorizations()
            ->where('user_id', $user->id)
            ->where('role', 'billing.admin')
            ->exists();
    }

    /**
     * Get all companies the user has access to.
     *
     * @param User $user
     * @return Collection
     */
    public function getAuthorizedCompanies(User $user): Collection
    {
        if ($user->isAdmin() || $user->can('finance.admin')) {
            return Company::all();
        }

        return Company::whereHas('billingAuthorizations', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();
    }

    /**
     * Alias for getAuthorizedCompanies to maintain backward compatibility if needed,
     * or just for convenience.
     * 
     * @param User $user
     * @return Collection
     */
    public function getUserCompanies(User $user): Collection
    {
        return $this->getAuthorizedCompanies($user);
    }


    /**
     * Scope a query to only include companies the user has access to.
     * This is crucial for preventing data leakage in lists.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, User $user)
    {
        if ($user->isAdmin() || $user->can('finance.admin')) {
            return $query;
        }

        return $query->whereHas('billingAuthorizations', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }
}

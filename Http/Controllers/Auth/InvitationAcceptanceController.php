<?php

namespace Modules\Billing\Http\Controllers\Auth;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Billing\Models\Invitation;
use Modules\Billing\Models\Company;
use App\Models\User;

class InvitationAcceptanceController extends Controller
{
    public function show($token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->isExpired()) {
            abort(403, 'This invitation has expired.');
        }

        return view('billing::auth.register_invite', compact('invitation'));
    }

    public function store(Request $request, $token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->isExpired()) {
            abort(403, 'This invitation has expired.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|confirmed|min:8',
        ]);

        // Create User
        $user = User::create([
            'name' => $request->name,
            'email' => $invitation->email,
            'password' => Hash::make($request->password),
        ]);

        // Handle Company
        if ($invitation->company_id) {
            $company = Company::find($invitation->company_id);
            if ($company) {
                $company->users()->attach($user);
            }
        } elseif ($invitation->company_name) {
            $company = Company::create([
                'name' => $invitation->company_name,
                'email' => $invitation->email,
                'is_active' => true,
            ]);
            $company->users()->attach($user);
        }

        // Delete Invitation
        $invitation->delete();

        // Login User
        Auth::login($user);

        return redirect()->route('billing.portal.dashboard', ['company' => $company->id]);
    }
}

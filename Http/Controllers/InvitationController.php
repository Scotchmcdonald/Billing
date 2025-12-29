<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Modules\Billing\Models\Invitation;
use Modules\Billing\Models\Company;
use Modules\Billing\Mail\UserInvitation;

class InvitationController extends Controller
{
    public function create()
    {
        $companies = Company::orderBy('name')->get();
        return view('billing::invitations.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'company_option' => 'required|in:existing,new',
            'company_id' => 'required_if:company_option,existing|nullable|exists:companies,id',
            'company_name' => 'required_if:company_option,new|nullable|string|max:255',
        ]);

        // Check for pending invitations
        $existingInvite = Invitation::where('email', $request->email)->first();
        if ($existingInvite) {
            return back()->with('error', 'An invitation is already pending for this email.');
        }

        // Get timeout from settings or default to 48 hours
        $timeoutHours = setting('invitation_timeout', 48);

        $invitation = Invitation::create([
            'email' => $request->email,
            'token' => Str::random(32),
            'company_id' => $request->company_option === 'existing' ? $request->company_id : null,
            'company_name' => $request->company_option === 'new' ? $request->company_name : null,
            'expires_at' => now()->addHours($timeoutHours),
        ]);

        Mail::to($invitation->email)->send(new UserInvitation($invitation));

        return redirect()->route('billing.finance.invitations.create')->with('success', 'Invitation sent successfully.');
    }
}

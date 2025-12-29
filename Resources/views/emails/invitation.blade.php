<!DOCTYPE html>
<html>
<head>
    <title>Invitation</title>
</head>
<body>
    <h1>Hello!</h1>
    <p>You have been invited to join {{ config('app.name') }}.</p>
    
    @if($invitation->company_name)
        <p>You will be creating a new account for company: <strong>{{ $invitation->company_name }}</strong>.</p>
    @elseif($invitation->company)
        <p>You will be joining the company: <strong>{{ $invitation->company->name }}</strong>.</p>
    @endif

    <p>Click the link below to accept your invitation and set up your account:</p>
    
    <p>
        <a href="{{ route('billing.invitation.accept', $invitation->token) }}">Accept Invitation</a>
    </p>
    
    <p>This link will expire on {{ $invitation->expires_at->format('M d, Y \a\t g:i A') }}.</p>
    
    <p>If you did not expect this invitation, you can ignore this email.</p>
</body>
</html>

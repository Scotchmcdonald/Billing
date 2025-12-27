<!DOCTYPE html>
<html>
<head>
    <title>Payment Reminder</title>
</head>
<body>
    <h1>Payment Reminder</h1>
    <p>Dear {{ $invoice->company->name }},</p>
    
    @if($template == 'friendly_reminder')
        <p>This is a friendly reminder that Invoice #{{ $invoice->id }} is due on {{ $invoice->due_date->format('M d, Y') }}.</p>
    @elseif($template == 'due_today')
        <p>Invoice #{{ $invoice->id }} is due today.</p>
    @elseif($template == 'overdue_notice')
        <p>We noticed that we haven't received payment for Invoice #{{ $invoice->id }} yet. It is now overdue.</p>
    @elseif($template == 'final_notice')
        <p>This is a final notice regarding Invoice #{{ $invoice->id }}. Please remit payment immediately to avoid service interruption.</p>
    @elseif($template == 'account_hold')
        <p>Your account has been placed on hold due to non-payment of Invoice #{{ $invoice->id }}.</p>
    @endif

    <p>Amount Due: ${{ number_format($invoice->total, 2) }}</p>
    
    <p>
        <a href="{{ route('billing.portal.dashboard', $invoice->company_id) }}">View Invoice & Pay Now</a>
    </p>
    
    <p>Thank you,<br>
    {{ config('app.name') }} Finance Team</p>
</body>
</html>

@component('mail::message')
# Hello {{ $quote->company ? $quote->company->name : $quote->prospect_name }},

Here is the quote you requested.

**Quote Number:** {{ $quote->quote_number }}  
**Total:** ${{ number_format($quote->total, 2) }}  
**Valid Until:** {{ $quote->valid_until ? $quote->valid_until->format('M d, Y') : 'N/A' }}

You can view, accept, or reject this quote online by clicking the button below.

@component('mail::button', ['url' => route('billing.public.quote.show', $quote->token)])
View Quote
@endcomponent

If you have any questions, please reply to this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

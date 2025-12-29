<?php

namespace Modules\Billing\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Billing\Models\Quote;

class QuoteSent extends Mailable
{
    use Queueable, SerializesModels;

    public $quote;

    public function __construct(Quote $quote)
    {
        $this->quote = $quote;
    }

    public function build()
    {
        return $this->subject('Quote #' . $this->quote->quote_number . ' from ' . config('app.name'))
                    ->markdown('billing::emails.quote-sent');
    }
}

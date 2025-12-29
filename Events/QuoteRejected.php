<?php

namespace Modules\Billing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Billing\Models\Quote;

class QuoteRejected
{
    use Dispatchable, SerializesModels;

    public $quote;
    public $reason;

    public function __construct(Quote $quote, string $reason)
    {
        $this->quote = $quote;
        $this->reason = $reason;
    }
}

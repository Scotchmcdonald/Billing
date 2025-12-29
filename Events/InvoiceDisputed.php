<?php

namespace Modules\Billing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Dispute;

class InvoiceDisputed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public Dispute $dispute
    ) {}
}

<?php

namespace Modules\Billing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimeEntryStatusUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public $timeEntry,
        public string $oldStatus,
        public string $newStatus
    ) {}
}

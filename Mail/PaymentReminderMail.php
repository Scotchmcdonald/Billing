<?php

namespace Modules\Billing\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Billing\Models\Invoice;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $template;

    public function __construct(Invoice $invoice, string $template)
    {
        $this->invoice = $invoice;
        $this->template = $template;
    }

    public function build()
    {
        $subject = "Invoice #{$this->invoice->id} Reminder";
        if ($this->template === 'overdue_notice') {
            $subject = "Overdue: Invoice #{$this->invoice->id}";
        } elseif ($this->template === 'account_hold') {
            $subject = "URGENT: Account Hold - Invoice #{$this->invoice->id}";
        }

        return $this->subject($subject)
                    ->view('billing::emails.payment_reminder');
    }
}

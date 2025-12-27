<?php

namespace Modules\Billing\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Mail\PaymentReminderMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendPaymentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $invoices = Invoice::where('status', 'sent')
            ->whereNotNull('due_date')
            ->get();

        foreach ($invoices as $invoice) {
            $company = $invoice->company;
            if (($company->settings['dunning_enabled'] ?? true) === false) {
                continue;
            }

            $dueDate = Carbon::parse($invoice->due_date);
            // diffInDays returns positive difference. We need direction.
            // $dueDate->diffInDays(now(), false) -> positive if now is after due date (overdue)
            
            $daysOverdue = $dueDate->diffInDays(now(), false); 
            // If due date is tomorrow, diff is -1.
            // If due date is today, diff is 0.
            // If due date was yesterday, diff is 1.

            $template = null;
            if ($daysOverdue == -3) {
                $template = 'friendly_reminder';
            } elseif ($daysOverdue == 0) {
                $template = 'due_today';
            } elseif ($daysOverdue == 7) {
                $template = 'overdue_notice';
            } elseif ($daysOverdue == 14) {
                $template = 'final_notice';
            } elseif ($daysOverdue == 30) {
                $template = 'account_hold';
                // TODO: Disable service logic
            }

            if ($template) {
                // Check if already sent today? (Ideally we track sent reminders)
                Mail::to($company->email)->send(new PaymentReminderMail($invoice, $template));
            }
        }
    }
}

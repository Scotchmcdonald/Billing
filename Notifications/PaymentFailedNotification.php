<?php

namespace Modules\Billing\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Billing\Models\Company;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $invoiceId;

    /**
     * Create a new notification instance.
     *
     * @param string $invoiceId
     */
    public function __construct(string $invoiceId)
    {
        $this->invoiceId = $invoiceId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $company = $notifiable instanceof Company ? $notifiable : null;
        $url = $company ? route('billing.portal.payment_methods', $company) : '#';

        return (new MailMessage)
            ->subject('Action Required: Payment Failed for Invoice ' . $this->invoiceId)
            ->greeting('Hello,')
            ->line('We were unable to process the payment for your latest invoice (' . $this->invoiceId . ').')
            ->line('This is often due to an expired card or insufficient funds.')
            ->action('Update Payment Method', $url)
            ->line('Please update your payment method to ensure uninterrupted service.')
            ->line('Thank you for your business!');
    }
}

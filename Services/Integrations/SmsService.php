<?php

namespace Modules\Billing\Services\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $accountSid;
    protected string $authToken;
    protected string $fromNumber;

    public function __construct()
    {
        /** @var string $accountSid */
        $accountSid = config('services.twilio.account_sid', '');
        $this->accountSid = $accountSid;

        /** @var string $authToken */
        $authToken = config('services.twilio.auth_token', '');
        $this->authToken = $authToken;

        /** @var string $fromNumber */
        $fromNumber = config('services.twilio.from_number', '');
        $this->fromNumber = $fromNumber;
    }

    /**
     * Send an SMS message.
     */
    public function send(string $phone, string $message): bool
    {
        if (empty($this->accountSid) || empty($this->authToken)) {
            Log::warning('Twilio credentials not configured');
            return false;
        }

        try {
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->asForm()
                ->timeout(10)
                ->retry(2, 100)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json", [
                    'To' => $phone,
                    'From' => $this->fromNumber,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                Log::info('SMS sent successfully', [
                    'to' => $phone,
                    'message_id' => $response->json('sid'),
                ]);
                return true;
            }

            Log::error('SMS send failed', [
                'status' => $response->status(),
                'error' => $response->json('message'),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('SMS send exception', [
                'error' => $e->getMessage(),
                'phone' => $phone,
            ]);

            return false;
        }
    }

    /**
     * Send overdue invoice reminder.
     */
    public function sendOverdueReminder(\Modules\Billing\Models\Invoice $invoice): void
    {
        $company = $invoice->company;
        
        // Check if company has opted in for SMS
        if (!($company->sms_notifications_enabled ?? false)) {
            return;
        }

        if (empty($company->phone)) {
            Log::warning('Company has no phone number', ['company_id' => $company->id]);
            return;
        }

        $message = "Reminder: Invoice #{$invoice->invoice_number} for \${$invoice->total} is overdue. "
                 . "Please visit your portal to make a payment.";

        $this->send($company->phone, $message);
    }

    /**
     * Send payment confirmation.
     */
    public function sendPaymentConfirmation(\Modules\Billing\Models\Payment $payment): void
    {
        $company = $payment->company;
        
        if (!($company->sms_notifications_enabled ?? false)) {
            return;
        }

        if (empty($company->phone)) {
            return;
        }

        $message = "Payment received: \${$payment->amount} for invoice #{$payment->invoice->invoice_number}. "
                 . "Thank you for your payment!";

        $this->send($company->phone, $message);
    }
}

<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Services\HelcimService;

class PublicPaymentController extends Controller
{
    protected $helcim;

    public function __construct(HelcimService $helcim)
    {
        $this->helcim = $helcim;
    }

    public function show(Invoice $invoice)
    {
        if ($invoice->status !== 'open' && $invoice->status !== 'past_due') {
             if ($invoice->status === 'paid') {
                 // Assuming we might have a paid view, or just show the wizard in read-only/success state
                 // For now, let's just return the wizard but maybe the view handles paid state
                 return view('billing::payment.wizard', [
                     'invoice' => $invoice,
                     'helcimToken' => null,
                     'isPaid' => true
                 ]);
             }
             abort(403, 'Invoice is not payable.');
        }

        $helcimToken = $this->helcim->createHelcimPaySession($invoice->total, $invoice->invoice_number);

        return view('billing::payment.wizard', [
            'invoice' => $invoice,
            'helcimToken' => $helcimToken,
            'isPaid' => false
        ]);
    }
}

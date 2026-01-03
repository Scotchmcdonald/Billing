<?php

namespace Modules\Billing\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Billing\Models\BillingLog;
use Modules\Billing\Models\Company;

class HelcimWebhookController extends Controller
{
    /**
     * Handle Helcim Webhook events.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        // Verify Helcim signature if available (recommended)
        // $signature = $request->header('helcim-signature');
        
        $payload = $request->all();
        $type = $payload['type'] ?? 'unknown'; // Adjust based on actual Helcim webhook structure

        Log::info("Received Helcim Webhook: {$type}", $payload);

        try {
            // Helcim webhooks might differ in structure, assuming 'event' or 'type'
            // Example: transaction.success
            
            if (isset($payload['transactionId']) && isset($payload['status'])) {
                if ($payload['status'] === 'APPROVED') {
                    $this->handlePaymentSucceeded($payload);
                } else {
                    $this->handlePaymentFailed($payload);
                }
            } else {
                Log::info("Unhandled Helcim event structure");
            }

        } catch (\Exception $e) {
            Log::error('Helcim Webhook Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        return response()->json(['status' => 'success']);
    }

    protected function handlePaymentSucceeded(array $payload)
    {
        // Logic to update invoice status, log transaction, etc.
        // $customerCode = $payload['customerCode'];
        // $company = Company::where('helcim_id', $customerCode)->first();
        
        Log::info("Helcim Payment Succeeded: " . ($payload['transactionId'] ?? 'N/A'));
    }

    protected function handlePaymentFailed(array $payload)
    {
        // Logic to handle failed payments
        Log::warning("Helcim Payment Failed: " . ($payload['transactionId'] ?? 'N/A'));
    }
}

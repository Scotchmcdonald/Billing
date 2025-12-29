<?php

namespace Modules\Billing\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Models\UsageChange;
use Modules\Billing\Services\ProrationCalculator;
use Carbon\Carbon;

class RmmWebhookController extends Controller
{
    /**
     * @var ProrationCalculator
     */
    protected $prorationCalculator;

    public function __construct(ProrationCalculator $prorationCalculator)
    {
        $this->prorationCalculator = $prorationCalculator;
    }

    public function deviceCount(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate Request
        $validated = $request->validate([
            'company_id' => 'required|integer',
            'device_count' => 'required|integer|min:0',
            'timestamp' => 'required|date',
            'device_list' => 'array',
        ]);

        $companyId = (int) $validated['company_id'];
        $timestampStr = $validated['timestamp'];
        $timestamp = Carbon::parse(is_string($timestampStr) ? $timestampStr : now());
        $deviceList = $validated['device_list'] ?? [];

        // Filter Stale Devices
        $activeDevices = [];
        $excludedDevices = [];
        
        if (!empty($deviceList)) {
            foreach ($deviceList as $device) {
                if (is_array($device) && isset($device['last_seen'])) {
                    $lastSeenStr = $device['last_seen'];
                    $lastSeen = Carbon::parse(is_string($lastSeenStr) ? $lastSeenStr : now());
                    if ($lastSeen->diffInDays(now()) > 30) {
                        $excludedDevices[] = $device;
                        continue;
                    }
                }
                $activeDevices[] = $device;
            }
            // Override count with active devices count
            $newCount = count($activeDevices);
        } else {
            $newCount = (int) $validated['device_count'];
        }

        Log::info("RMM Webhook received for Company ID: {$companyId}, Count: {$newCount} (Excluded: " . count($excludedDevices) . ")");

        // Find Company
        /** @var Company|null $company */
        $company = Company::find($companyId);
        if (!$company) {
            Log::warning("RMM Webhook: Company not found ID: {$companyId}");
            return response()->json(['message' => 'Company not found'], 404);
        }

        // Find Active Subscription for RMM
        // Assuming product.category = 'rmm_monitoring' or similar logic.
        // For now, we'll look for a subscription that looks like RMM.
        // In a real scenario, we'd query based on product attributes.
        /** @var Subscription|null $subscription */
        $subscription = $company->subscriptions()
            ->where('name', 'like', '%RMM%') // Simplified logic
            ->where('stripe_status', 'active')
            ->first();

        if (!$subscription) {
             Log::warning("RMM Webhook: No active RMM subscription for Company ID: {$companyId}");
             return response()->json(['message' => 'Subscription not found'], 404);
        }

        $currentQuantity = $subscription->quantity;

        if ($currentQuantity !== $newCount) {
            // Create Usage Change Record
            $usageChange = UsageChange::create([
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
                'old_quantity' => $currentQuantity,
                'new_quantity' => $newCount,
                'delta' => $newCount - $currentQuantity,
                'source' => 'rmm',
                'status' => 'pending', // Needs review
                'metadata' => [
                    'device_list' => $activeDevices,
                    'excluded_devices' => $excludedDevices,
                    'timestamp' => $timestamp->toIso8601String(),
                ]
            ]);

            Log::info("Usage Change detected for Company {$companyId}. Delta: " . ($newCount - $currentQuantity));

            // TODO: Trigger Proration Calculation if needed immediately, or leave for approval workflow.
            // The packet says: "Flag in Pre-Flight queue for review".
            // So we just store it as pending.
        }

        return response()->json(['message' => 'Processed successfully']);
    }
}

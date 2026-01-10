<?php

namespace Modules\Billing\Services;

use App\Models\Conversation;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\BillableEntry;
use Modules\Inventory\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;

class TicketBillingService
{
    /**
     * Bill a ticket to a company.
     * 
     * @param Conversation $ticket
     * @param string $resolutionTier 'Tier I', 'Tier II', 'Tier III'
     * @param Company|null $companyOverride
     * @return BillableEntry|null
     * @throws Exception
     */
    public function billTicket(Conversation $ticket, string $resolutionTier, Company $companyOverride = null)
    {
        // 1. Identify Company
        $company = $companyOverride;
        if (!$company) {
             // Try to find company via Customer
             $customer = $ticket->customer;
             if ($customer && $customer->company_id) {
                 $company = Company::find($customer->company_id);
             }
        }
        
        if (!$company) {
            // Cannot bill if no associated company
            // Logging this might be appropriate depending on system
            return null;
        }

        // 2. Check Billing Mode
        // "This will require the company model to be enhanced to specify if they are billed by a service plan or for ad-hoc resolutions."
        // We interpret this as: Only companies marked as 'ad_hoc' auto-generate these charges.
        // Companies on 'service_plan' likely have a retainer or subscription that covers it.
        if ($company->billing_mode !== 'ad_hoc') {
            return null; 
        }

        // 3. Find Product
        $skuMap = [
            'Tier I' => 'SVC-ADHOC-T1',
            'Tier II' => 'SVC-ADHOC-T2',
            'Tier III' => 'SVC-ADHOC-T3',
        ];

        $sku = $skuMap[$resolutionTier] ?? null;
        if (!$sku) {
             throw new Exception("Invalid resolution tier: {$resolutionTier}");
        }

        $product = Product::where('sku', $sku)->first();
        if (!$product) {
            // Fallback or error? Let's throw error as seeding should have handled this.
            throw new Exception("Ad-hoc product not found for SKU: {$sku}");
        }

        // 4. Create Billable Entry
        // Ensure requester name is available
        $requesterName = 'Unknown';
        if ($ticket->customer) {
            $requesterName = trim($ticket->customer->first_name . ' ' . $ticket->customer->last_name);
        } elseif ($info = $ticket->customer_email) {
             $requesterName = $info;
        }

        return BillableEntry::create([
            'company_id' => $company->id,
            'description' => "Ticket #{$ticket->number}: {$ticket->subject} (Requested by: {$requesterName})",
            'quantity' => 1,
            'rate' => $product->base_price,
            'amount' => $product->base_price,
            'subtotal' => $product->base_price,
            'type' => 'ad_hoc',
            'date' => now(),
            'is_billable' => true,
            'billing_status' => 'pending',
            'metadata' => [
                'conversation_id' => $ticket->id,
                'ticket_number' => $ticket->number,
                'resolution_tier' => $resolutionTier,
                'requester_name' => $requesterName,
                'product_id' => $product->id, 
            ],
        ]);
    }
}

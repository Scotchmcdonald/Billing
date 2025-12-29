<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\InvoiceLineItem;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Subscription;
use Modules\Inventory\Models\Product;
use Carbon\Carbon;

class InvoiceSeeder extends Seeder
{
    /**
     * Seed invoices with line items for companies.
     */
    public function run(): void
    {
        $companies = Company::all();
        
        if ($companies->isEmpty()) {
            $this->command->warn('⚠ No companies found. Please run company seeders first.');
            return;
        }

        $products = Product::all();
        $statuses = ['draft', 'pending_approval', 'approved', 'sent', 'paid', 'overdue', 'cancelled'];
        $invoiceCount = 0;
        $lineItemCount = 0;

        foreach ($companies as $company) {
            // Create 3-8 invoices per company across different time periods
            $numInvoices = rand(3, 8);
            
            for ($i = 0; $i < $numInvoices; $i++) {
                // Vary invoice dates over the past 12 months
                $monthsAgo = rand(0, 12);
                $issueDate = Carbon::now()->subMonths($monthsAgo)->startOfMonth()->addDays(rand(0, 28));
                $dueDate = $issueDate->copy()->addDays(30);
                
                // Determine status based on date
                $status = $this->determineStatus($issueDate, $dueDate);
                
                // Calculate totals
                $subtotal = 0;
                $lineItemsData = [];
                
                // Add 2-6 line items per invoice
                $numLineItems = rand(2, 6);
                for ($j = 0; $j < $numLineItems; $j++) {
                    $product = $products->random();
                    $quantity = rand(1, 50);
                    $unitPrice = $this->getProductPrice($product, $company);
                    $lineSubtotal = $quantity * $unitPrice;
                    
                    $lineItemData = [
                        'description' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $lineSubtotal,
                        'product_id' => $product->id,
                    ];
                    
                    // Only add metadata if column exists
                    if (Schema::hasColumn('invoice_line_items', 'metadata')) {
                        $lineItemData['metadata'] = [
                            'sku' => $product->sku,
                            'billing_period' => $issueDate->format('Y-m'),
                        ];
                    }
                    
                    $lineItemsData[] = $lineItemData;
                    
                    $subtotal += $lineSubtotal;
                }
                
                // Calculate tax (10%)
                $taxRate = 0.10;
                $taxTotal = round($subtotal * $taxRate, 2);
                $total = $subtotal + $taxTotal;
                
                // Determine paid amount
                $paidAmount = $this->determinePaidAmount($status, $total);
                
                // Create invoice
                $invoiceData = [
                    'company_id' => $company->id,
                    'invoice_number' => $this->generateInvoiceNumber($issueDate),
                    'issue_date' => $issueDate,
                    'due_date' => $dueDate,
                    'subtotal' => $subtotal,
                    'tax_total' => $taxTotal,
                    'total' => $total,
                    'status' => $status,
                    'notes' => $this->generateInvoiceNotes($status),
                ];
                
                // Add optional columns if they exist
                if (Schema::hasColumn('invoices', 'currency')) {
                    $invoiceData['currency'] = 'USD';
                }
                if (Schema::hasColumn('invoices', 'paid_amount')) {
                    $invoiceData['paid_amount'] = $paidAmount;
                }
                if (Schema::hasColumn('invoices', 'is_disputed')) {
                    $invoiceData['is_disputed'] = false;
                }
                if (Schema::hasColumn('invoices', 'dunning_paused')) {
                    $invoiceData['dunning_paused'] = false;
                }
                if (Schema::hasColumn('invoices', 'approved_at')) {
                    $invoiceData['approved_at'] = in_array($status, ['sent', 'paid', 'overdue']) ? $issueDate->copy()->addDays(1) : null;
                }
                if (Schema::hasColumn('invoices', 'approved_by')) {
                    $invoiceData['approved_by'] = in_array($status, ['sent', 'paid', 'overdue']) ? 1 : null;
                }
                if (Schema::hasColumn('invoices', 'metadata')) {
                    $invoiceData['metadata'] = [
                        'billing_period' => $issueDate->format('Y-m'),
                        'auto_generated' => true,
                        'line_item_count' => $numLineItems,
                    ];
                }
                
                $invoice = Invoice::create($invoiceData);
                
                // Create line items
                foreach ($lineItemsData as $lineData) {
                    InvoiceLineItem::create(array_merge($lineData, [
                        'invoice_id' => $invoice->id,
                    ]));
                    $lineItemCount++;
                }
                
                $invoiceCount++;
            }
        }

        $this->command->info("✓ Created {$invoiceCount} invoices with {$lineItemCount} line items");
        
        // Show status breakdown
        $this->showStatusBreakdown();
    }

    /**
     * Determine invoice status based on dates
     */
    private function determineStatus(Carbon $issueDate, Carbon $dueDate): string
    {
        $now = Carbon::now();
        
        // Recent invoices (last 7 days) - mix of draft/sent
        if ($issueDate->diffInDays($now) <= 7) {
            return ['draft', 'sent'][array_rand(['draft', 'sent'])];
        }
        
        // Overdue invoices
        if ($dueDate->isPast() && rand(1, 100) <= 30) {
            return 'overdue';
        }
        
        // Paid invoices (older ones more likely to be paid)
        $daysSinceIssue = $issueDate->diffInDays($now);
        $paidProbability = min(90, $daysSinceIssue * 3);
        if (rand(1, 100) <= $paidProbability) {
            return 'paid';
        }
        
        // Sent invoices (waiting for payment)
        return 'sent';
    }

    /**
     * Determine paid amount based on status
     */
    private function determinePaidAmount(string $status, float $total): float
    {
        if ($status === 'paid') {
            return $total;
        }
        
        // Some invoices have partial payments
        if (in_array($status, ['sent', 'overdue']) && rand(1, 100) <= 20) {
            return round($total * (rand(20, 80) / 100), 2);
        }
        
        return 0;
    }

    /**
     * Generate invoice number
     */
    private function generateInvoiceNumber(Carbon $issueDate): string
    {
        return 'INV-' . $issueDate->format('Y') . '-' . str_pad(Invoice::whereYear('issue_date', $issueDate->year)->count() + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get product price for company (with potential overrides)
     */
    private function getProductPrice(Product $product, Company $company): float
    {
        // Check for price override
        $override = \Modules\Billing\Models\PriceOverride::where('company_id', $company->id)
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->first();
        
        if ($override) {
            return (float) $override->value;
        }
        
        // Use tier pricing if available
        $tier = $company->pricing_tier ?? 'standard';
        $tierPrice = $product->tierPrices()->where('tier', $tier)->first();
        
        if ($tierPrice) {
            return (float) $tierPrice->price;
        }
        
        return (float) $product->base_price;
    }

    /**
     * Generate notes based on status
     */
    private function generateInvoiceNotes(?string $status): ?string
    {
        $notes = [
            'draft' => 'Invoice awaiting review',
            'pending_approval' => 'Pending finance team approval',
            'approved' => 'Approved - ready to send',
            'sent' => 'Invoice sent to customer',
            'paid' => 'Thank you for your payment',
            'overdue' => 'Payment overdue - please remit immediately',
            'cancelled' => 'Invoice cancelled',
        ];
        
        return $notes[$status] ?? null;
    }

    /**
     * Show status breakdown
     */
    private function showStatusBreakdown(): void
    {
        $breakdown = Invoice::selectRaw('status, COUNT(*) as count, SUM(total) as total_amount')
            ->groupBy('status')
            ->get();
        
        $this->command->info("\nInvoice Status Breakdown:");
        foreach ($breakdown as $row) {
            $this->command->info("  • {$row->status}: {$row->count} invoices (\${$row->total_amount})");
        }
    }
}

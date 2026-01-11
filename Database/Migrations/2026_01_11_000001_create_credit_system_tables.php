<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Credit Transactions (The Ledger)
        Schema::create('billing_credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            $table->string('type'); // PURCHASE, USAGE, EXPIRE, ADJUSTMENT
            $table->integer('amount'); // Positive for Debit (Usage), Negative for Credit (Purchase) or vice versa. 
                                       // Let's standard: +IN (Purchase), -OUT (Usage) like inventory.
            
            $table->string('reference_type')->nullable(); // Ticket, Invoice, etc.
            $table->string('reference_id')->nullable();
            
            $table->text('description')->nullable();
            
            // For expiration logic
            $table->timestamp('expires_at')->nullable();
            
            $table->timestamps();
            
            // Balance index for snapshots if we were doing high volume, 
            // but for now we calculate on fly or cache.
        });

        // 2. Add Billing attributes to Products (for Ticket Costs)
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('ticket_tier')->nullable()->after('type'); // tier_1, tier_2, tier_3
                $table->integer('credit_cost')->default(0)->after('base_price'); // Cost in Credits
            });
        }

        // 3. Add Monthly Limit to Companies
        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                // Limit in Cents ($500.00 = 50000)
                $table->integer('monthly_support_limit')->default(0)->after('account_balance');
            });
        }
        
        // 4. Update Billable Entries if needed (verify presence)
        if (Schema::hasTable('billable_entries')) {
            Schema::table('billable_entries', function (Blueprint $table) {
                if (!Schema::hasColumn('billable_entries', 'ticket_tier')) {
                    $table->string('ticket_tier')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_credit_transactions');
        
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn(['ticket_tier', 'credit_cost']);
            });
        }

        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('monthly_support_limit');
            });
        }
        
        if (Schema::hasTable('billable_entries')) {
            Schema::table('billable_entries', function (Blueprint $table) {
                 $table->dropColumn('ticket_tier');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update Products (Template Definition)
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->jsonb('internal_bundle_costs')->nullable()->after('cost_price'); 
                // Note: using jsonb if postgres, json if mysql. Laravl 'json' handles both.
            });
        }

        // 2. Update Assets (Instances)
        if (Schema::hasTable('assets')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->string('ownership_status')->default('stock')->after('status'); // Consignment, Stock, Client-Owned
                $table->boolean('is_billable')->default(true)->after('status');
                $table->json('specifications')->nullable()->after('serial_number');
                
                // Polymorphic Custody (Replacing simple Client/User IDs eventually, but adding alongside for now)
                $table->nullableMorphs('custody'); // custody_type, custody_id
            });
        }

        // 3. Create Billing Agreements (RTO & Contracts)
        // This links specific Assets to Financial Terms
        if (!Schema::hasTable('billing_agreements')) {
            Schema::create('billing_agreements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete(); // Client
                $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
                
                $table->string('billing_strategy'); // Monthly, Annual, RTO, Milestone, Usage
                
                // RTO Specifics
                $table->integer('rto_total_cents')->default(0);
                $table->integer('rto_balance_cents')->default(0);
                $table->boolean('is_separate_hosting')->default(false);
                
                $table->string('status')->default('active'); // active, completed, cancelled
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_agreements');
        
        if (Schema::hasTable('assets')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->dropColumn(['ownership_status', 'is_billable', 'specifications', 'custody_type', 'custody_id']);
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('internal_bundle_costs');
            });
        }
    }
};

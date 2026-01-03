<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Product Prototypes (Inventory)
        if (!Schema::hasTable('product_prototypes')) {
            Schema::create('product_prototypes', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Laptop, Server, Firewall
                $table->string('category'); // Hardware, Software, Service
                $table->json('spec_template')->nullable(); // {"cpu": "string", "ram": "string"}
                $table->timestamps();
            });
        }

        // 2. Procurement Records (Inventory)
        if (!Schema::hasTable('procurement_records')) {
            Schema::create('procurement_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_prototype_id')->constrained('product_prototypes');
                $table->string('model_name');
                $table->string('vendor');
                $table->decimal('cost_price', 10, 2);
                $table->date('purchase_date');
                $table->json('specs')->nullable(); // {"ram": "16GB", "cpu": "i7"}
                $table->timestamps();
            });
        }

        // 3. Update Quote Line Items (Billing)
        Schema::table('quote_line_items', function (Blueprint $table) {
            if (!Schema::hasColumn('quote_line_items', 'is_recurring')) {
                $table->boolean('is_recurring')->default(false);
            }
        });

        // 4. Update Invoices (Billing)
        if (!Schema::hasTable('invoices')) {
             Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
                $table->string('status')->default('Draft');
                $table->decimal('total', 10, 2)->default(0.00);
                $table->date('due_date')->nullable();
                $table->timestamps();
            });
        }
        
        Schema::table('invoices', function (Blueprint $table) {
             // Ensure status can handle 'Partially Disputed' - usually handled by string type, but good to note
        });

        if (!Schema::hasTable('invoice_line_items')) {
            Schema::create('invoice_line_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
                $table->string('description');
                $table->decimal('amount', 10, 2);
                $table->boolean('is_disputed')->default(false);
                $table->string('dispute_reason')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('invoice_line_items', function (Blueprint $table) {
                if (!Schema::hasColumn('invoice_line_items', 'is_disputed')) {
                    $table->boolean('is_disputed')->default(false);
                    $table->string('dispute_reason')->nullable();
                }
            });
        }

        // 5. Link Assets to Subscriptions (Billing/Inventory)
        // We'll use a pivot table to allow an asset to be covered by a subscription item
        if (!Schema::hasTable('asset_subscription_item')) {
            Schema::create('asset_subscription_item', function (Blueprint $table) {
                $table->id();
                $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
                // Assuming billing_subscription_items is the granular level
                $table->foreignId('subscription_item_id')->constrained('billing_subscription_items')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('asset_subscription_item');
        Schema::dropIfExists('procurement_records');
        Schema::dropIfExists('product_prototypes');
        
        if (Schema::hasColumn('quote_line_items', 'is_recurring')) {
            Schema::table('quote_line_items', function (Blueprint $table) {
                $table->dropColumn('is_recurring');
            });
        }
        
        if (Schema::hasColumn('invoice_line_items', 'is_disputed')) {
            Schema::table('invoice_line_items', function (Blueprint $table) {
                $table->dropColumn(['is_disputed', 'dispute_reason']);
            });
        }
    }
};

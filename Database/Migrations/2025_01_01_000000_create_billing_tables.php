<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Companies
        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('country')->nullable();
                $table->string('website')->nullable();
                $table->string('currency')->default('USD');
                $table->string('locale')->default('en');
                $table->string('timezone')->default('UTC');
                
                // Enhanced Fields
                $table->string('helcim_id')->nullable()->index();
                $table->string('helcim_card_token')->nullable();
                $table->enum('pricing_tier', ['standard', 'non_profit', 'consumer'])->default('standard');
                $table->string('tax_id', 50)->nullable();
                $table->text('billing_address')->nullable(); // JSON
                $table->unsignedBigInteger('primary_contact_id')->nullable();
                $table->json('settings')->nullable();
                $table->decimal('margin_floor_percent', 5, 2)->default(20.00);
                $table->boolean('is_active')->default(true);
                $table->boolean('sms_notifications_enabled')->default(false);
                
                $table->timestamps();
            });
        }

        // 2. Billing Settings
        if (!Schema::hasTable('billing_settings')) {
            Schema::create('billing_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('group')->default('general');
                $table->string('type')->default('string');
                $table->boolean('is_public')->default(false);
                $table->timestamps();
            });
        }

        // 3. Billing Logs
        if (!Schema::hasTable('billing_logs')) {
            Schema::create('billing_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
                $table->string('event');
                $table->text('description')->nullable();
                $table->json('payload')->nullable();
                $table->string('level')->default('info');
                $table->timestamps();
            });
        }

        // 4. Products (Shared with Inventory)
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('sku')->unique()->nullable();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('category')->nullable();
                $table->string('type')->nullable();
                $table->decimal('base_price', 15, 4)->default(0);
                $table->decimal('cost_price', 15, 4)->default(0);
                $table->string('gl_account_code')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('helcim_product_id')->nullable()->index();
                
                // Enhanced Fields
                $table->string('pricing_model')->default('flat_fee');
                $table->string('unit_of_measure')->nullable();
                $table->string('billing_frequency')->nullable();
                $table->integer('min_quantity')->default(1);
                $table->integer('included_quantity')->default(0);
                $table->decimal('additional_unit_price', 10, 2)->nullable();
                $table->decimal('floor_unit_price', 10, 2)->nullable();
                $table->decimal('min_margin_percent', 5, 2)->default(0);
                $table->string('tax_code')->nullable();
                $table->boolean('is_addon')->default(false);
                $table->unsignedBigInteger('parent_product_id')->nullable();

                $table->timestamps();
            });
        } else {
            // Ensure columns exist if table was created by Inventory
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'helcim_product_id')) {
                    $table->string('helcim_product_id')->nullable()->index();
                }
                if (!Schema::hasColumn('products', 'pricing_model')) {
                    $table->string('pricing_model')->default('flat_fee');
                }
                // ... Add other columns as needed if missing
            });
        }

        // 5. Product Tier Prices
        if (!Schema::hasTable('product_tier_prices')) {
            Schema::create('product_tier_prices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->enum('tier', ['standard', 'non_profit', 'consumer']);
                $table->decimal('price', 10, 2);
                $table->timestamps();
                
                $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
                $table->unique(['product_id', 'tier']);
            });
        }

        // 6. Subscriptions
        if (!Schema::hasTable('billing_subscriptions')) {
            Schema::create('billing_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->string('name');
                $table->string('helcim_id')->nullable()->unique();
                $table->string('helcim_status')->nullable();
                $table->string('helcim_price')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->integer('quantity')->default(1);
                $table->decimal('effective_price', 15, 4)->nullable();
                $table->enum('billing_frequency', ['monthly', 'quarterly', 'annual'])->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamp('trial_ends_at')->nullable();
                $table->date('next_billing_date')->nullable();
                
                // Contract fields
                $table->date('contract_start_date')->nullable();
                $table->date('contract_end_date')->nullable();
                $table->string('contract_document_path')->nullable();
                
                $table->boolean('is_active')->default(true);
                $table->enum('renewal_status', ['active', 'pending_renewal', 'churned'])->default('active');
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'is_active']);
                $table->index('next_billing_date');
                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            });
        }

        // 7. Subscription Items
        if (!Schema::hasTable('billing_subscription_items')) {
            Schema::create('billing_subscription_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('subscription_id');
                $table->string('helcim_id')->nullable()->unique();
                $table->string('helcim_product')->nullable();
                $table->string('helcim_price')->nullable();
                $table->integer('quantity')->nullable();
                $table->timestamps();

                $table->foreign('subscription_id')->references('id')->on('billing_subscriptions')->cascadeOnDelete();
                $table->index(['subscription_id', 'helcim_price']);
            });
        }

        // 8. Usage Changes
        if (!Schema::hasTable('usage_changes')) {
            Schema::create('usage_changes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('subscription_id')->index();
                $table->integer('old_quantity');
                $table->integer('new_quantity');
                $table->integer('delta');
                $table->string('status')->default('pending');
                $table->string('source')->default('rmm');
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        // 9. Quotes
        if (!Schema::hasTable('quotes')) {
            Schema::create('quotes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->string('quote_number')->unique()->nullable();
                $table->string('title')->nullable();
                
                $table->string('prospect_name')->nullable();
                $table->string('prospect_email')->nullable();
                
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('tax_total', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->string('status')->default('draft');
                
                $table->string('public_token')->nullable()->unique();
                $table->string('token')->nullable()->unique();
                
                $table->date('valid_until')->nullable();
                $table->text('notes')->nullable();
                
                $table->string('pricing_tier')->default('standard');
                $table->boolean('requires_approval')->default(false);
                $table->decimal('approval_threshold_percent', 5, 2)->default(15.00);
                $table->string('billing_frequency')->default('monthly');

                $table->timestamp('viewed_at')->nullable();
                $table->string('viewed_ip')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->text('signature_data')->nullable();
                $table->string('signer_name')->nullable();
                $table->string('signer_email')->nullable();
                
                $table->timestamps();
                
                $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('usage_changes');
        Schema::dropIfExists('billing_subscription_items');
        Schema::dropIfExists('billing_subscriptions');
        Schema::dropIfExists('product_tier_prices');
        // Schema::dropIfExists('products'); // Shared
        Schema::dropIfExists('billing_logs');
        Schema::dropIfExists('billing_settings');
        Schema::dropIfExists('companies');
    }
};

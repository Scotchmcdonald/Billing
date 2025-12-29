<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Subscriptions
        if (!Schema::hasTable('billing_subscriptions')) {
            Schema::create('billing_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->string('name');
                $table->string('stripe_id')->nullable()->unique();
                $table->string('stripe_status')->nullable();
                $table->string('stripe_price')->nullable();
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
                
                // Performance Indexes
                $table->index('contract_end_date', 'subscriptions_contract_end_date_idx');

                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
                // $table->foreign('product_id')->references('id')->on('products');
            });
        } else {
            Schema::table('billing_subscriptions', function (Blueprint $table) {
                if (!Schema::hasColumn('billing_subscriptions', 'contract_start_date')) {
                    $table->date('contract_start_date')->nullable();
                }
                if (!Schema::hasColumn('billing_subscriptions', 'contract_end_date')) {
                    $table->date('contract_end_date')->nullable();
                }
                if (!Schema::hasColumn('billing_subscriptions', 'contract_document_path')) {
                    $table->string('contract_document_path')->nullable();
                }
                if (!Schema::hasColumn('billing_subscriptions', 'renewal_status')) {
                    $table->enum('renewal_status', ['active', 'pending_renewal', 'churned'])->default('active');
                }
            });
        }

        // 2. Subscription Items
        if (!Schema::hasTable('billing_subscription_items')) {
            Schema::create('billing_subscription_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('subscription_id');
                $table->string('stripe_id')->nullable()->unique();
                $table->string('stripe_product')->nullable();
                $table->string('stripe_price')->nullable();
                $table->integer('quantity')->nullable();
                $table->timestamps();

                $table->foreign('subscription_id')->references('id')->on('billing_subscriptions')->cascadeOnDelete();
                $table->index(['subscription_id', 'stripe_price']);
            });
        }

        // 3. Usage Changes
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
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_changes');
        Schema::dropIfExists('billing_subscription_items');
        Schema::dropIfExists('billing_subscriptions');
    }
};

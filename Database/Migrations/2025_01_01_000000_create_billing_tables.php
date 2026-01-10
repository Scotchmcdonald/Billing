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
                $table->string('zip')->nullable();
                $table->string('country')->nullable();
                $table->string('vat_number')->nullable();
                $table->string('helcim_id')->nullable()->index();
                $table->string('helcim_card_token')->nullable();
                $table->decimal('account_balance', 15, 2)->default(0);
                $table->enum('pricing_tier', ['standard', 'non_profit', 'consumer'])->default('standard');
                $table->string('tax_id', 50)->nullable();
                $table->text('billing_address')->nullable();
                $table->unsignedBigInteger('primary_contact_id')->nullable();
                $table->unsignedBigInteger('client_id')->nullable(); 
                $table->json('settings')->nullable();
                $table->decimal('margin_floor_percent', 5, 2)->default(20.00);
                $table->boolean('is_active')->default(true);
                $table->boolean('sms_notifications_enabled')->default(false);
                $table->string('billing_mode')->default('card'); 
                $table->string('pm_type')->nullable();
                $table->string('pm_last_four', 4)->nullable();
                $table->timestamp('trial_ends_at')->nullable();
                $table->string('scenario')->nullable();
                $table->timestamps();
            });
        }

        // 2. Billing Settings
        if (!Schema::hasTable('billing_settings')) {
            Schema::create('billing_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->index();
                $table->text('value')->nullable();
                $table->string('group')->default('general');
                $table->string('type')->default('string');
                $table->boolean('is_encrypted')->default(false);
                $table->timestamps();
            });
        }

        // 3. Billing Logs
        if (!Schema::hasTable('billing_logs')) {
            Schema::create('billing_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable();
                $table->foreignId('user_id')->nullable();
                $table->string('action');
                $table->text('description')->nullable();
                $table->json('payload')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }

        // 4. Billing Audit Logs
        if (!Schema::hasTable('billing_audit_logs')) {
            Schema::create('billing_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->string('event');
                $table->morphs('auditable');
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }

        // 5. Billing Authorizations
        if (!Schema::hasTable('billing_authorizations')) {
            Schema::create('billing_authorizations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id');
                $table->foreignId('user_id');
                $table->string('role')->default('viewer');
                $table->timestamps();
            });
        }

        // 6. Billing Invitations
        if (!Schema::hasTable('billing_invitations')) {
            Schema::create('billing_invitations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id');
                $table->string('email');
                $table->string('role')->default('viewer');
                $table->string('token')->unique();
                $table->timestamp('expires_at')->nullable();
                $table->string('company_name')->nullable();
                $table->timestamps();
            });
        }

        // 7. Notification Preferences
        if (!Schema::hasTable('notification_preferences')) {
            Schema::create('notification_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->index();
                $table->string('notification_type');
                $table->boolean('email_enabled')->default(true);
                $table->boolean('in_app_enabled')->default(true);
                $table->boolean('slack_enabled')->default(false);
                $table->timestamps();
            });
        }

        // 8. Product Bundles
        if (!Schema::hasTable('product_bundles')) {
            Schema::create('product_bundles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->json('product_ids')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
        
        // 9. Product Tier Prices
        if (!Schema::hasTable('product_tier_prices')) {
            Schema::create('product_tier_prices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->enum('tier', ['standard', 'non_profit', 'consumer']);
                $table->decimal('price', 10, 2);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamps();
            });
        }

        // 10. Subscriptions
        if (!Schema::hasTable('billing_subscriptions')) {
            Schema::create('billing_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('name')->nullable();
                $table->integer('quantity')->default(1);
                $table->decimal('effective_price', 15, 4)->nullable();
                $table->enum('billing_frequency', ['monthly', 'quarterly', 'annual', 'custom'])->default('monthly');
                
                $table->string('stripe_id')->nullable();
                $table->string('stripe_status')->nullable();
                $table->string('stripe_price')->nullable();
                
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamp('trial_ends_at')->nullable();
                $table->date('next_billing_date')->nullable();
                
                // Contract Specifics
                $table->date('contract_start_date')->nullable();
                $table->date('contract_end_date')->nullable();
                $table->string('contract_document_path')->nullable();
                
                $table->boolean('is_active')->default(true);
                $table->enum('renewal_status', ['active', 'pending_renewal', 'churned'])->default('active');
                $table->json('metadata')->nullable();
                $table->timestamps();
                
                $table->index('next_billing_date');
            });
        }

        // 11. Subscription Items
        if (!Schema::hasTable('billing_subscription_items')) {
            Schema::create('billing_subscription_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subscription_id')->constrained('billing_subscriptions')->cascadeOnDelete();
                $table->string('stripe_id')->nullable();
                $table->string('stripe_product')->nullable();
                $table->string('stripe_price')->nullable();
                $table->integer('quantity')->default(1);
                $table->timestamps();
            });
        }

        // 12. Contract Price Histories
        if (!Schema::hasTable('contract_price_histories')) {
            Schema::create('contract_price_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('contract_id')->constrained('billing_subscriptions')->cascadeOnDelete();
                $table->decimal('unit_price', 15, 4);
                $table->timestamp('started_at')->useCurrent();
                $table->timestamp('ended_at')->nullable();
                $table->timestamps();
            });
        }

        // 13. Service Contracts
        if (!Schema::hasTable('service_contracts')) {
            Schema::create('service_contracts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id')->index();
                $table->string('name');
                $table->decimal('standard_rate', 10, 2)->default(0);
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        // 14. Invoices
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('client_id')->nullable();
                $table->string('invoice_number')->unique()->nullable();
                $table->enum('status', ['draft', 'sent', 'paid', 'void', 'overdue', 'uncollectible'])->default('draft');
                $table->date('issue_date')->nullable();
                $table->date('due_date')->nullable();
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('tax_total', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->decimal('paid_amount', 15, 2)->default(0);
                $table->string('currency')->default('USD');
                $table->text('notes')->nullable();
                $table->text('internal_notes')->nullable();
                
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                
                $table->boolean('is_disputed')->default(false);
                $table->timestamp('disputed_at')->nullable();
                $table->boolean('dunning_paused')->default(false);
                $table->timestamp('dunning_paused_at')->nullable();
                $table->string('dunning_pause_reason')->nullable();
                
                $table->string('stripe_invoice_id')->nullable()->index();
                $table->string('xero_invoice_id')->nullable()->index();
                $table->string('revenue_recognition_method')->nullable();
                $table->integer('anomaly_score')->default(0);
                $table->json('metadata')->nullable();
                
                $table->timestamps();
            });
        }

        // 15. Invoice Line Items
        if (!Schema::hasTable('invoice_line_items')) {
            Schema::create('invoice_line_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->nullable();
                $table->string('description');
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 15, 4)->default(0);
                $table->decimal('subtotal', 15, 4)->default(0);
                $table->decimal('tax_amount', 15, 4)->default(0);
                $table->decimal('tax_credit_amount', 15, 4)->default(0);
                $table->decimal('standard_unit_price', 15, 4)->nullable();
                
                $table->date('service_period_start')->nullable();
                $table->date('service_period_end')->nullable();
                
                $table->boolean('is_fee')->default(false);
                $table->boolean('is_disputed')->default(false);
                $table->string('dispute_reason')->nullable();
                
                $table->timestamps();
            });
        }

        // 16. Billable Entries
        if (!Schema::hasTable('billable_entries')) {
            Schema::create('billable_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable();
                $table->foreignId('ticket_id')->nullable();
                $table->foreignId('invoice_id')->nullable();
                $table->unsignedBigInteger('invoice_line_item_id')->nullable();
                
                $table->string('description')->nullable();
                $table->decimal('quantity', 8, 2)->default(0);
                $table->decimal('rate', 15, 4)->default(0);
                $table->decimal('subtotal', 15, 4)->default(0);
                
                $table->boolean('is_billable')->default(true);
                $table->string('billing_status')->default('pending'); 
                $table->timestamp('status_changed_at')->nullable();
                
                $table->enum('type', ['labor', 'material', 'expense', 'fixed'])->default('labor');
                $table->date('date')->nullable();
                $table->string('receipt_path')->nullable();
                $table->json('metadata')->nullable();
                
                $table->timestamps();
            });
        }

        // 17. Payments
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('invoice_id')->nullable();
                $table->decimal('amount', 15, 2);
                $table->date('payment_date');
                $table->enum('payment_method', ['credit_card', 'bank_transfer', 'check', 'cash', 'other'])->default('credit_card');
                $table->string('payment_reference')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
        
        // 18. Credit Notes
        if (!Schema::hasTable('credit_notes')) {
            Schema::create('credit_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('invoice_id')->nullable();
                $table->integer('amount')->default(0); 
                $table->string('reason')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('issued_by')->nullable();
                $table->timestamp('applied_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 19. Invoice Disputes
        if (!Schema::hasTable('invoice_disputes')) {
            Schema::create('invoice_disputes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
                $table->decimal('disputed_amount', 15, 2)->default(0);
                $table->string('reason');
                $table->text('explanation')->nullable();
                $table->string('status')->default('open');
                $table->json('line_item_ids')->nullable();
                $table->timestamps();
            });
        }

        // 20. Dispute Attachments
        if (!Schema::hasTable('dispute_attachments')) {
            Schema::create('dispute_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dispute_id')->constrained('invoice_disputes')->cascadeOnDelete();
                $table->string('filename');
                $table->string('path');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size')->nullable();
                $table->timestamps();
            });
        }

        // 21. Quotes
        if (!Schema::hasTable('quotes')) {
            Schema::create('quotes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable();
                $table->foreignId('client_id')->nullable();
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
            });
        }

        // 22. Quote Line Items
        if (!Schema::hasTable('quote_line_items')) {
            Schema::create('quote_line_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->nullable();
                $table->string('description')->nullable();
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 15, 4)->default(0);
                $table->decimal('unit_price_monthly', 15, 4)->nullable();
                $table->decimal('unit_price_annually', 15, 4)->nullable();
                $table->decimal('standard_price', 15, 4)->nullable();
                $table->decimal('subtotal', 15, 4)->default(0);
                $table->decimal('variance_percent', 5, 2)->nullable();
                $table->decimal('variance_amount', 15, 4)->nullable();
                $table->boolean('is_recurring')->default(true);
                $table->timestamps();
            });
        }

        // 23. Retainers
        if (!Schema::hasTable('retainers')) {
            Schema::create('retainers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->integer('price_paid');
                $table->decimal('hours_purchased', 8, 2);
                $table->decimal('hours_remaining', 8, 2);
                $table->date('purchased_at');
                $table->date('expires_at')->nullable();
                $table->enum('status', ['active', 'exhausted', 'expired'])->default('active');
                $table->timestamps();
            });
        }

        // 24. Price Overrides
        if (!Schema::hasTable('price_overrides')) {
            Schema::create('price_overrides', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('subscription_id')->nullable();
                $table->enum('type', ['discount', 'custom_price', 'markup'])->default('custom_price');
                $table->decimal('value', 15, 4)->nullable();
                $table->decimal('custom_price', 15, 4)->nullable();
                
                $table->enum('status', ['pending', 'approved', 'rejected', 'active', 'expired'])->default('pending');
                $table->text('justification')->nullable();
                $table->unsignedBigInteger('requested_by')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('requested_at')->nullable();
                
                $table->boolean('is_active')->default(true);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->datetime('starts_at')->nullable();
                $table->datetime('ends_at')->nullable();
                
                $table->decimal('margin_percent', 5, 2)->nullable();
                $table->boolean('below_minimum_margin')->default(false);
                $table->text('notes')->nullable();
                
                $table->timestamps();
            });
        }

        // 25. Usage Changes
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
        Schema::dropIfExists('price_overrides');
        Schema::dropIfExists('retainers');
        Schema::dropIfExists('quote_line_items');
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('dispute_attachments');
        Schema::dropIfExists('invoice_disputes');
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('billable_entries');
        Schema::dropIfExists('invoice_line_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('service_contracts');
        Schema::dropIfExists('contract_price_histories');
        Schema::dropIfExists('billing_subscription_items');
        Schema::dropIfExists('billing_subscriptions');
        Schema::dropIfExists('product_tier_prices');
        Schema::dropIfExists('product_bundles');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('billing_invitations');
        Schema::dropIfExists('billing_authorizations');
        Schema::dropIfExists('billing_audit_logs');
        Schema::dropIfExists('billing_logs');
        Schema::dropIfExists('billing_settings');
        Schema::dropIfExists('companies');
    }
};

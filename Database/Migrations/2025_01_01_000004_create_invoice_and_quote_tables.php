<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Quotes
        if (!Schema::hasTable('quotes')) {
            Schema::create('quotes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable(); // Nullable for prospects
                $table->string('quote_number')->unique()->nullable(); // Nullable if generated later
                $table->string('title')->nullable();
                
                // Prospect info
                $table->string('prospect_name')->nullable();
                $table->string('prospect_email')->nullable();
                
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('tax_total', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->string('status')->default('draft'); // draft, sent, accepted, rejected
                
                // Tokens
                $table->string('public_token')->nullable()->unique();
                $table->string('token')->nullable()->unique(); // Alias or alternative
                
                $table->date('valid_until')->nullable();
                $table->text('notes')->nullable();
                
                // Enhanced Fields
                $table->string('pricing_tier')->default('standard');
                $table->boolean('requires_approval')->default(false);
                $table->decimal('approval_threshold_percent', 5, 2)->default(15.00);
                $table->string('billing_frequency')->default('monthly'); // monthly, annually

                // Tracking fields
                $table->timestamp('viewed_at')->nullable();
                $table->string('viewed_ip')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->text('signature_data')->nullable(); // Base64 signature image
                $table->string('signer_name')->nullable();
                $table->string('signer_email')->nullable();
                
                $table->timestamps();
                
                // FK
                $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            });
        } else {
            Schema::table('quotes', function (Blueprint $table) {
                if (!Schema::hasColumn('quotes', 'public_token')) {
                    $table->string('public_token')->nullable()->unique();
                }
                if (!Schema::hasColumn('quotes', 'prospect_name')) {
                    $table->string('prospect_name')->nullable();
                }
                if (!Schema::hasColumn('quotes', 'prospect_email')) {
                    $table->string('prospect_email')->nullable();
                }
                if (!Schema::hasColumn('quotes', 'valid_until')) {
                    $table->date('valid_until')->nullable();
                }
                if (!Schema::hasColumn('quotes', 'notes')) {
                    $table->text('notes')->nullable();
                }
                if (!Schema::hasColumn('quotes', 'pricing_tier')) {
                    $table->string('pricing_tier')->default('standard');
                }
                if (!Schema::hasColumn('quotes', 'requires_approval')) {
                    $table->boolean('requires_approval')->default(false);
                }
                if (!Schema::hasColumn('quotes', 'approval_threshold_percent')) {
                    $table->decimal('approval_threshold_percent', 5, 2)->default(15.00);
                }
                if (!Schema::hasColumn('quotes', 'billing_frequency')) {
                    $table->string('billing_frequency')->default('monthly');
                }
                if (!Schema::hasColumn('quotes', 'viewed_at')) {
                    $table->timestamp('viewed_at')->nullable();
                }
                if (!Schema::hasColumn('quotes', 'viewed_ip')) {
                    $table->string('viewed_ip')->nullable();
                }
                if (!Schema::hasColumn('quotes', 'accepted_at')) {
                    $table->timestamp('accepted_at')->nullable();
                }
                if (!Schema::hasColumn('quotes', 'signature_data')) {
                    $table->text('signature_data')->nullable();
                }
                if (!Schema::hasColumn('quotes', 'signer_name')) {
                    $table->string('signer_name')->nullable();
                }
                if (!Schema::hasColumn('quotes', 'signer_email')) {
                    $table->string('signer_email')->nullable();
                }
            });
        }

        // 2. Quote Line Items
        if (!Schema::hasTable('quote_line_items')) {
            Schema::create('quote_line_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('product_id')->nullable(); // Nullable if custom item
                $table->string('description');
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 10, 2);
                $table->decimal('subtotal', 10, 2);
                
                // Enhanced Fields
                $table->decimal('unit_price_monthly', 10, 2)->nullable();
                $table->decimal('unit_price_annually', 10, 2)->nullable();
                $table->decimal('standard_price', 10, 2)->nullable();
                $table->decimal('variance_amount', 10, 2)->default(0);
                $table->decimal('variance_percent', 5, 2)->default(0);
                
                $table->timestamps();
            });
        } else {
            Schema::table('quote_line_items', function (Blueprint $table) {
                if (!Schema::hasColumn('quote_line_items', 'unit_price_monthly')) {
                    $table->decimal('unit_price_monthly', 10, 2)->nullable();
                }
                if (!Schema::hasColumn('quote_line_items', 'unit_price_annually')) {
                    $table->decimal('unit_price_annually', 10, 2)->nullable();
                }
                if (!Schema::hasColumn('quote_line_items', 'standard_price')) {
                    $table->decimal('standard_price', 10, 2)->nullable();
                }
                if (!Schema::hasColumn('quote_line_items', 'variance_amount')) {
                    $table->decimal('variance_amount', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('quote_line_items', 'variance_percent')) {
                    $table->decimal('variance_percent', 5, 2)->default(0);
                }
            });
        }

        // 3. Invoices
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->string('invoice_number')->unique(); // e.g., INV-2025-0001
                $table->date('issue_date');
                $table->date('due_date');
                $table->decimal('subtotal', 15, 4);
                $table->decimal('tax_total', 15, 4)->default(0);
                $table->decimal('total', 15, 4);
                $table->decimal('paid_amount', 15, 4)->default(0);
                $table->string('currency')->default('USD');
                $table->enum('status', ['draft', 'pending_review', 'sent', 'paid', 'overdue', 'void'])->default('draft');
                $table->text('notes')->nullable();
                $table->text('internal_notes')->nullable();
                
                // Integrations
                $table->string('xero_invoice_id')->nullable();
                $table->string('stripe_invoice_id')->nullable();
                $table->json('metadata')->nullable();
                
                // Revenue Recognition
                $table->string('revenue_recognition_method')->default('cash'); // cash, accrual
                
                // Approval Tracking
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users');
                
                // Dispute Tracking
                $table->boolean('is_disputed')->default(false);
                $table->timestamp('disputed_at')->nullable();
                $table->boolean('dunning_paused')->default(false);
                $table->timestamp('dunning_paused_at')->nullable();
                $table->string('dunning_pause_reason')->nullable();
                
                $table->timestamp('paid_at')->nullable();

                $table->timestamps();

                $table->index('company_id');
                $table->index('status');
                $table->index('issue_date');
                
                // Performance Indexes
                $table->index(['company_id', 'status'], 'invoices_company_status_idx');
                $table->index(['status', 'due_date'], 'invoices_status_due_date_idx');
                $table->index('is_disputed', 'invoices_is_disputed_idx');

                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            });
        } else {
            Schema::table('invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('invoices', 'revenue_recognition_method')) {
                    $table->string('revenue_recognition_method')->default('cash');
                }
                if (!Schema::hasColumn('invoices', 'sent_at')) {
                    $table->timestamp('sent_at')->nullable();
                }
                if (!Schema::hasColumn('invoices', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable();
                }
                if (!Schema::hasColumn('invoices', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->constrained('users');
                }
                if (!Schema::hasColumn('invoices', 'is_disputed')) {
                    $table->boolean('is_disputed')->default(false);
                }
                if (!Schema::hasColumn('invoices', 'disputed_at')) {
                    $table->timestamp('disputed_at')->nullable();
                }
                if (!Schema::hasColumn('invoices', 'dunning_paused')) {
                    $table->boolean('dunning_paused')->default(false);
                }
                if (!Schema::hasColumn('invoices', 'dunning_paused_at')) {
                    $table->timestamp('dunning_paused_at')->nullable();
                }
                if (!Schema::hasColumn('invoices', 'dunning_pause_reason')) {
                    $table->string('dunning_pause_reason')->nullable();
                }
                if (!Schema::hasColumn('invoices', 'paid_at')) {
                    $table->timestamp('paid_at')->nullable();
                }
                if (!Schema::hasColumn('invoices', 'internal_notes')) {
                    $table->text('internal_notes')->nullable();
                }
                if (!Schema::hasColumn('invoices', 'paid_amount')) {
                    $table->decimal('paid_amount', 15, 4)->default(0);
                }
                if (!Schema::hasColumn('invoices', 'currency')) {
                    $table->string('currency')->default('USD');
                }
                if (!Schema::hasColumn('invoices', 'xero_invoice_id')) {
                    $table->string('xero_invoice_id')->nullable();
                }
                if (!Schema::hasColumn('invoices', 'stripe_invoice_id')) {
                    $table->string('stripe_invoice_id')->nullable();
                }
                if (!Schema::hasColumn('invoices', 'metadata')) {
                    $table->json('metadata')->nullable();
                }
            });
        }

        // 4. Invoice Line Items
        if (!Schema::hasTable('invoice_line_items')) {
            Schema::create('invoice_line_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id')->index();
                $table->foreignId('product_id')->nullable()->constrained('products'); // Nullable for custom items
                $table->string('description');
                $table->integer('quantity');
                $table->decimal('unit_price', 15, 4);
                $table->decimal('subtotal', 15, 4);
                $table->decimal('tax_amount', 15, 4)->default(0);
                $table->decimal('tax_credit_amount', 15, 4)->default(0); // Added from non-profit fields
                $table->boolean('is_fee')->default(false); // Flag for CC convenience fees
                
                // Service Period
                $table->date('service_period_start')->nullable();
                $table->date('service_period_end')->nullable();
                
                $table->timestamps();
                
                $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            });
        } else {
            Schema::table('invoice_line_items', function (Blueprint $table) {
                if (!Schema::hasColumn('invoice_line_items', 'service_period_start')) {
                    $table->date('service_period_start')->nullable();
                }
                if (!Schema::hasColumn('invoice_line_items', 'service_period_end')) {
                    $table->date('service_period_end')->nullable();
                }
                if (!Schema::hasColumn('invoice_line_items', 'tax_credit_amount')) {
                    $table->decimal('tax_credit_amount', 15, 4)->default(0);
                }
            });
        }

        // 5. Payments
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id');
                $table->unsignedBigInteger('company_id');
                $table->decimal('amount', 15, 4);
                $table->enum('payment_method', ['stripe_card', 'stripe_ach', 'check', 'wire', 'cash', 'other']);
                $table->string('payment_reference')->nullable(); // check number, Stripe payment ID
                $table->date('payment_date');
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by'); // FK to users
                $table->timestamps();

                $table->index('invoice_id');
                $table->index('company_id');
                $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
                $table->foreign('created_by')->references('id')->on('users');
            });
        }

        // 6. Credit Notes
        if (!Schema::hasTable('credit_notes')) {
            Schema::create('credit_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained('invoices');
                $table->foreignId('company_id')->constrained('companies');
                $table->integer('amount'); // cents
                $table->string('reason');
                $table->text('notes')->nullable();
                $table->foreignId('issued_by')->constrained('users');
                $table->timestamp('applied_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                $table->index('invoice_id');
                $table->index('company_id');
            });
        }

        // 7. Retainers
        if (!Schema::hasTable('retainers')) {
            Schema::create('retainers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies');
                $table->decimal('hours_purchased', 8, 2);
                $table->decimal('hours_remaining', 8, 2);
                $table->integer('price_paid'); // cents
                $table->date('purchased_at');
                $table->date('expires_at')->nullable();
                $table->enum('status', ['active', 'depleted', 'expired'])->default('active');
                $table->timestamps();
                
                $table->index('company_id');
                $table->index(['status', 'expires_at']);
            });
        }

        // 8. Billable Entries
        if (!Schema::hasTable('billable_entries')) {
            Schema::create('billable_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->unsignedBigInteger('user_id'); // Technician
                $table->unsignedBigInteger('ticket_id')->nullable(); // FK to tickets/threads
                $table->enum('type', ['time', 'expense', 'product'])->default('time');
                $table->text('description');
                $table->decimal('quantity', 10, 2); // hours or item count
                $table->decimal('rate', 15, 4); // hourly rate or unit price
                $table->decimal('subtotal', 15, 4);
                $table->boolean('is_billable')->default(true);
                
                $table->unsignedBigInteger('invoice_line_item_id')->nullable(); // FK - null if unbilled
                $table->string('receipt_path')->nullable(); // For expense receipts
                
                $table->date('date');
                $table->json('metadata')->nullable(); // mileage, receipt_url, etc.
                
                // Billing Status Tracking
                $table->string('billing_status')->default('pending');
                $table->timestamp('status_changed_at')->nullable();
                $table->foreignId('invoice_id')->nullable()->constrained('invoices');

                $table->timestamps();

                $table->index(['company_id', 'is_billable', 'invoice_line_item_id'], 'billable_entries_lookup_index');
                $table->index('date');
                
                // Performance Indexes
                $table->index('ticket_id', 'billable_entries_ticket_id_idx');
                $table->index(['user_id', 'created_at'], 'billable_entries_user_created_idx');

                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users');
                $table->foreign('invoice_line_item_id')->references('id')->on('invoice_line_items');
            });
        } else {
            Schema::table('billable_entries', function (Blueprint $table) {
                if (!Schema::hasColumn('billable_entries', 'invoice_line_item_id')) {
                    $table->unsignedBigInteger('invoice_line_item_id')->nullable();
                    $table->foreign('invoice_line_item_id')->references('id')->on('invoice_line_items');
                }
                if (!Schema::hasColumn('billable_entries', 'receipt_path')) {
                    $table->string('receipt_path')->nullable();
                }
                if (!Schema::hasColumn('billable_entries', 'billing_status')) {
                    $table->string('billing_status')->default('pending');
                }
                if (!Schema::hasColumn('billable_entries', 'status_changed_at')) {
                    $table->timestamp('status_changed_at')->nullable();
                }
                if (!Schema::hasColumn('billable_entries', 'invoice_id')) {
                    $table->foreignId('invoice_id')->nullable()->constrained('invoices');
                }
            });
        }

        // 9. CFO Margin View
        DB::statement("DROP VIEW IF EXISTS cfo_margin_reports");
        DB::statement("
            CREATE VIEW cfo_margin_reports AS
            SELECT
                ili.invoice_id,
                ili.product_id,
                p.sku,
                p.name as product_name,
                ili.quantity,
                ili.unit_price as billed_unit_price,
                p.cost_price as current_unit_cost,
                (ili.unit_price - p.cost_price) as unit_margin,
                ((ili.unit_price - p.cost_price) * ili.quantity) as total_line_margin,
                ili.created_at as invoiced_at
            FROM
                invoice_line_items ili
            JOIN
                products p ON ili.product_id = p.id
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS cfo_margin_reports");
        Schema::dropIfExists('invoice_disputes');
        Schema::dropIfExists('billable_entries');
        Schema::dropIfExists('retainers');
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_line_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('quote_line_items');
        Schema::dropIfExists('quotes');
    }
};

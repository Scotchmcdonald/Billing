<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Companies Table
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
                $table->timestamps();
            });
        }

        // Add Stripe columns and Enhanced columns to companies
        Schema::table('companies', function (Blueprint $table) {
            // Stripe
            if (!Schema::hasColumn('companies', 'stripe_id')) {
                $table->string('stripe_id')->nullable()->index();
            }
            if (!Schema::hasColumn('companies', 'pm_type')) {
                $table->string('pm_type')->nullable();
            }
            if (!Schema::hasColumn('companies', 'pm_last_four')) {
                $table->string('pm_last_four', 4)->nullable();
            }
            if (!Schema::hasColumn('companies', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable();
            }

            // Enhanced Fields
            if (!Schema::hasColumn('companies', 'pricing_tier')) {
                $table->enum('pricing_tier', ['standard', 'non_profit', 'consumer'])->default('standard');
            }
            if (!Schema::hasColumn('companies', 'tax_id')) {
                $table->string('tax_id', 50)->nullable();
            }
            if (!Schema::hasColumn('companies', 'billing_address')) {
                $table->text('billing_address')->nullable(); // JSON
            }
            if (!Schema::hasColumn('companies', 'primary_contact_id')) {
                $table->unsignedBigInteger('primary_contact_id')->nullable();
            }
            if (!Schema::hasColumn('companies', 'settings')) {
                $table->json('settings')->nullable();
            }
            if (!Schema::hasColumn('companies', 'margin_floor_percent')) {
                $table->decimal('margin_floor_percent', 5, 2)->default(20.00);
            }
            if (!Schema::hasColumn('companies', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        // 2. Billing Settings
        if (!Schema::hasTable('billing_settings')) {
            Schema::create('billing_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('group')->default('general');
                $table->string('type')->default('string'); // string, boolean, integer, json
                $table->boolean('is_public')->default(false);
                $table->timestamps();
            });
        }

        // 3. Billing Logs
        if (!Schema::hasTable('billing_logs')) {
            Schema::create('billing_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
                $table->string('event'); // e.g., 'payment.succeeded', 'invoice.created'
                $table->text('description')->nullable();
                $table->json('payload')->nullable(); // Store webhook payload or related data
                $table->string('level')->default('info'); // info, warning, error
                $table->timestamps();
            });
        }

        // 4. Billing Audit Logs
        if (!Schema::hasTable('billing_audit_logs')) {
            Schema::create('billing_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action'); // e.g., 'created_invoice', 'updated_settings'
                $table->string('entity_type')->nullable(); // e.g., 'Invoice', 'Subscription'
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();

                $table->index(['entity_type', 'entity_id']);
                $table->index('user_id');
            });
        }

        // 5. Notification Preferences
        if (!Schema::hasTable('notification_preferences')) {
            Schema::create('notification_preferences', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('notification_type'); // e.g., 'invoice_paid', 'subscription_renewed'
                $table->boolean('email_enabled')->default(true);
                $table->boolean('sms_enabled')->default(false);
                $table->boolean('slack_enabled')->default(false);
                $table->timestamps();

                $table->unique(['user_id', 'notification_type']);
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        // 6. Billing Authorizations
        if (!Schema::hasTable('billing_authorizations')) {
            Schema::create('billing_authorizations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->string('role')->default('billing.payer'); // billing.admin, billing.payer
                $table->timestamps();

                $table->unique(['user_id', 'company_id']);
            });
        }

        // 7. Customers (External Table Modification)
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (!Schema::hasColumn('customers', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('id');
                    $table->index('company_id');
                    $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (Schema::hasColumn('customers', 'company_id')) {
                    // Check if foreign key exists before dropping? 
                    // Hard to check reliably in all DBs, but try/catch or just drop column usually drops FK in some, but strictly should drop FK first.
                    // $table->dropForeign(['company_id']); 
                    $table->dropColumn('company_id');
                }
            });
        }

        Schema::dropIfExists('billing_authorizations');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('billing_audit_logs');
        Schema::dropIfExists('billing_logs');
        Schema::dropIfExists('billing_settings');
        
        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                $columns = [
                    'stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at',
                    'pricing_tier', 'tax_id', 'billing_address', 'primary_contact_id',
                    'settings', 'margin_floor_percent', 'is_active'
                ];
                // Only drop columns that exist
                $dropColumns = [];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('companies', $col)) {
                        $dropColumns[] = $col;
                    }
                }
                if (!empty($dropColumns)) {
                    $table->dropColumn($dropColumns);
                }
            });
        }
    }
};

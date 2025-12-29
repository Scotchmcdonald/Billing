<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Products (Ensure it exists)
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('sku')->unique()->nullable(); // Added nullable for compatibility if not strictly required by Billing but good for Inventory
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('category')->nullable();
                $table->string('type')->nullable();
                $table->decimal('base_price', 10, 2)->default(0);
                $table->decimal('cost_price', 10, 2)->default(0);
                $table->string('gl_account')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('stripe_product_id')->nullable()->index();
                $table->timestamps();
            });
        } else {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'stripe_product_id')) {
                    $table->string('stripe_product_id')->nullable()->index();
                }
                // Ensure columns needed for view exist
                if (!Schema::hasColumn('products', 'sku')) {
                    $table->string('sku')->nullable()->unique();
                }
                if (!Schema::hasColumn('products', 'cost_price')) {
                    $table->decimal('cost_price', 10, 2)->default(0);
                }
            });
        }

        // 2. Product Tier Prices
        if (!Schema::hasTable('product_tier_prices')) {
            Schema::create('product_tier_prices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->enum('tier', ['standard', 'non_profit', 'consumer']);
                $table->decimal('price', 15, 4);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamps();

                $table->index('product_id');
                // We can't strictly enforce FK if we are not sure about products table, 
                // but since we created it above if missing, we can try.
                // However, to be safe against existing tables without ID, we'll skip FK constraint for now 
                // or add it only if we created the table.
                // For now, just index.
            });
        }

        // 3. Price Overrides
        if (!Schema::hasTable('price_overrides')) {
            Schema::create('price_overrides', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->unsignedBigInteger('product_id');
                $table->string('type')->default('fixed'); // fixed, discount_percent, markup_percent
                $table->decimal('value', 15, 4);
                $table->text('reason')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'product_id']);
                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            });
        } else {
            Schema::table('price_overrides', function (Blueprint $table) {
                if (!Schema::hasColumn('price_overrides', 'type')) {
                    $table->string('type')->default('fixed');
                }
                if (!Schema::hasColumn('price_overrides', 'value')) {
                    $table->decimal('value', 15, 4)->default(0);
                }
                if (!Schema::hasColumn('price_overrides', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
                if (!Schema::hasColumn('price_overrides', 'starts_at')) {
                    $table->timestamp('starts_at')->nullable();
                }
                if (!Schema::hasColumn('price_overrides', 'ends_at')) {
                    $table->timestamp('ends_at')->nullable();
                }
            });
        }

        // 4. Product Bundles
        if (!Schema::hasTable('product_bundles')) {
            Schema::create('product_bundles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->json('product_ids'); // Array of product IDs with quantities
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 5. CFO Margin View
        DB::statement("DROP VIEW IF EXISTS cfo_margin_reports");
        // Check if invoice_line_items exists before creating view? 
        // This migration runs BEFORE invoice_line_items creation in 000004.
        // So we cannot create the view here if it depends on invoice_line_items.
        // We should move the view creation to 000004 or a later migration.
        // But wait, the user asked to consolidate.
        // I will move this to 000004.
    }

    public function down(): void
    {
        // DB::statement("DROP VIEW IF EXISTS cfo_margin_reports"); // Moved to 000004
        Schema::dropIfExists('product_bundles');
        Schema::dropIfExists('price_overrides');
        Schema::dropIfExists('product_tier_prices');
        Schema::dropIfExists('products');
    }
};

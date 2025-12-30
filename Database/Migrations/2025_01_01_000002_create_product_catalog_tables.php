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
                $table->string('gl_account_code')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('stripe_product_id')->nullable()->index();
                
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
                if (!Schema::hasColumn('products', 'gl_account_code')) {
                    $table->string('gl_account_code')->nullable();
                }
                if (!Schema::hasColumn('products', 'pricing_model')) {
                    $table->string('pricing_model')->default('flat_fee');
                }
                if (!Schema::hasColumn('products', 'unit_of_measure')) {
                    $table->string('unit_of_measure')->nullable();
                }
                if (!Schema::hasColumn('products', 'billing_frequency')) {
                    $table->string('billing_frequency')->nullable();
                }
                if (!Schema::hasColumn('products', 'min_quantity')) {
                    $table->integer('min_quantity')->default(1);
                }
                if (!Schema::hasColumn('products', 'included_quantity')) {
                    $table->integer('included_quantity')->default(0);
                }
                if (!Schema::hasColumn('products', 'additional_unit_price')) {
                    $table->decimal('additional_unit_price', 10, 2)->nullable();
                }
                if (!Schema::hasColumn('products', 'floor_unit_price')) {
                    $table->decimal('floor_unit_price', 10, 2)->nullable();
                }
                if (!Schema::hasColumn('products', 'min_margin_percent')) {
                    $table->decimal('min_margin_percent', 5, 2)->default(0);
                }
                if (!Schema::hasColumn('products', 'tax_code')) {
                    $table->string('tax_code')->nullable();
                }
                if (!Schema::hasColumn('products', 'is_addon')) {
                    $table->boolean('is_addon')->default(false);
                }
                if (!Schema::hasColumn('products', 'parent_product_id')) {
                    $table->unsignedBigInteger('parent_product_id')->nullable();
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
    }

    public function down(): void
    {
        Schema::dropIfExists('product_bundles');
        Schema::dropIfExists('price_overrides');
        Schema::dropIfExists('product_tier_prices');
        Schema::dropIfExists('products');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // If price_books existed, we would rename it. Since it doesn't seem to, we create price_overrides.
        if (Schema::hasTable('price_books')) {
            Schema::rename('price_books', 'price_overrides');
        }

        if (!Schema::hasTable('price_overrides')) {
            Schema::create('price_overrides', function (Blueprint $table) {
                $table->id();
                // Assuming relationships to company and product
                $table->unsignedBigInteger('company_id')->nullable(); 
                $table->unsignedBigInteger('product_id')->nullable();
                
                $table->enum('type', ['fixed', 'discount_percent', 'markup_percent'])->default('fixed');
                $table->decimal('value', 15, 4); // price or percentage
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable(); // FK to users
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('company_id');
                $table->index('product_id');
                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
                // $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
                // $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            });
        } else {
            // If it was renamed or existed, we ensure columns match requirements
            Schema::table('price_overrides', function (Blueprint $table) {
                if (!Schema::hasColumn('price_overrides', 'type')) {
                    $table->enum('type', ['fixed', 'discount_percent', 'markup_percent'])->default('fixed');
                }
                if (!Schema::hasColumn('price_overrides', 'value')) {
                    $table->decimal('value', 15, 4);
                }
                if (Schema::hasColumn('price_overrides', 'custom_price')) {
                    // If we are migrating data, we might want to copy custom_price to value, but for now just drop/rename logic if needed.
                    // Since we are assuming fresh or simple migration:
                    // $table->renameColumn('custom_price', 'value'); // Only if not created above
                }
                if (!Schema::hasColumn('price_overrides', 'notes')) {
                    $table->text('notes')->nullable();
                }
                if (!Schema::hasColumn('price_overrides', 'approved_by')) {
                    $table->unsignedBigInteger('approved_by')->nullable();
                }
                if (!Schema::hasColumn('price_overrides', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('price_overrides');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // If table exists but has wrong columns, we might need to adjust.
        // Based on tinker output, it seems 'quotes' table exists but with different columns.
        // It has: prospect_name, prospect_email, token.
        // It lacks: quote_number, title, subtotal, tax_total, public_token.
        
        if (Schema::hasTable('quotes')) {
            Schema::table('quotes', function (Blueprint $table) {
                if (!Schema::hasColumn('quotes', 'quote_number')) {
                    $table->string('quote_number')->nullable()->unique();
                }
                if (!Schema::hasColumn('quotes', 'title')) {
                    $table->string('title')->nullable();
                }
                if (!Schema::hasColumn('quotes', 'subtotal')) {
                    $table->decimal('subtotal', 15, 2)->default(0);
                }
                if (!Schema::hasColumn('quotes', 'tax_total')) {
                    $table->decimal('tax_total', 15, 2)->default(0);
                }
                if (!Schema::hasColumn('quotes', 'public_token')) {
                    $table->string('public_token')->nullable()->unique();
                }
                // Rename token to public_token if we want to standardize, or just use public_token
            });
        } else {
            Schema::create('quotes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->string('quote_number')->unique();
                $table->string('title')->nullable();
                $table->date('valid_until')->nullable();
                $table->decimal('subtotal', 15, 2);
                $table->decimal('tax_total', 15, 2)->default(0);
                $table->decimal('total', 15, 2);
                $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
                $table->text('notes')->nullable();
                $table->string('public_token')->nullable()->unique(); // For public viewing
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('quote_line_items')) {
            Schema::create('quote_line_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('quote_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('description');
                $table->integer('quantity');
                $table->decimal('unit_price', 15, 2);
                $table->decimal('subtotal', 15, 2);
                $table->timestamps();

                $table->foreign('quote_id')->references('id')->on('quotes')->cascadeOnDelete();
            });
        }
    }

    public function down()
    {
        // We don't drop existing tables if they were there before, but for new ones we do.
        Schema::dropIfExists('quote_line_items');
        // Schema::dropIfExists('quotes'); 
    }
};

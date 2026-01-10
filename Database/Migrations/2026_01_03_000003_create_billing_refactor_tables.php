<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Note: service_contracts and contract_price_histories tables are created
        // in migration 2026_01_03_000013_create_service_contracts_tables.php
        // This migration now only handles quotes and quote_line_items modifications

        // Quotes
        if (!Schema::hasTable('quotes')) {
            Schema::create('quotes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
                $table->string('status')->default('Draft'); // Draft, Sent, Accepted, Converted
                $table->decimal('total', 10, 2)->default(0.00);
                $table->timestamps();
            });
        } else {
            Schema::table('quotes', function (Blueprint $table) {
                if (!Schema::hasColumn('quotes', 'client_id')) {
                    $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('cascade');
                }
            });
        }

        // Quote Line Items
        if (!Schema::hasTable('quote_line_items')) {
            Schema::create('quote_line_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quote_id')->constrained('quotes')->onDelete('cascade');
                $table->string('description');
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 10, 2)->default(0.00); // Allows 0.00
                $table->timestamps();
            });
        } else {
             Schema::table('quote_line_items', function (Blueprint $table) {
                // Ensure columns exist if table exists
                if (!Schema::hasColumn('quote_line_items', 'unit_price')) {
                     $table->decimal('unit_price', 10, 2)->default(0.00);
                }
            });
        }
    }

    public function down()
    {
        // Note: service_contracts and contract_price_histories are dropped
        // in migration 2026_01_03_000013_create_service_contracts_tables.php down() method
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Service Contracts
        if (!Schema::hasTable('service_contracts')) {
            Schema::create('service_contracts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
                $table->string('name');
                $table->string('status')->default('Active'); // Active, Retired
                $table->decimal('standard_rate', 10, 2)->default(0.00); // For tax credit calc
                $table->timestamps();
            });
        }

        // Contract Price History
        if (!Schema::hasTable('contract_price_histories')) {
            Schema::create('contract_price_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('contract_id')->constrained('service_contracts')->onDelete('cascade');
                $table->decimal('unit_price', 10, 2);
                $table->timestamp('started_at');
                $table->timestamp('ended_at')->nullable();
                $table->timestamps();
            });
        }

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
        Schema::dropIfExists('contract_price_histories');
        Schema::dropIfExists('service_contracts');
    }
};

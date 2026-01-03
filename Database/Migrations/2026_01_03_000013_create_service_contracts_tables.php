<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('service_contracts')) {
            Schema::create('service_contracts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
                $table->string('name');
                $table->string('status')->default('active');
                $table->decimal('standard_rate', 10, 2);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('contract_price_histories')) {
            Schema::create('contract_price_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('contract_id')->constrained('service_contracts')->onDelete('cascade');
                $table->decimal('unit_price', 10, 2);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('contract_price_histories');
        Schema::dropIfExists('service_contracts');
    }
};

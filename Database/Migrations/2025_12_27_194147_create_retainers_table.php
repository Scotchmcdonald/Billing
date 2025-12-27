<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retainers');
    }
};

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
        Schema::create('billing_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type'); // Invoice, Payment, PriceOverride
            $table->unsignedBigInteger('auditable_id');
            $table->string('event'); // created, updated, deleted, status_changed
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_audit_logs');
    }
};

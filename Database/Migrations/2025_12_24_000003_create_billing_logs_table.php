<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('billing_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Who performed the action
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete(); // Which company
            $table->string('action'); // e.g., 'permission_change', 'payment_attempt'
            $table->text('description')->nullable();
            $table->json('payload')->nullable(); // Store details
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('billing_logs');
    }
};

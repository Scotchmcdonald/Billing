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
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->foreignId('company_id')->constrained('companies');
            $table->integer('amount'); // cents
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->foreignId('issued_by')->constrained('users');
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('invoice_id');
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};

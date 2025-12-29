<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('invoice_number')->unique(); // e.g., INV-2025-0001
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('subtotal', 15, 4);
            $table->decimal('tax_total', 15, 4)->default(0);
            $table->decimal('total', 15, 4);
            $table->enum('status', ['draft', 'pending_review', 'sent', 'paid', 'overdue', 'void'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('status');
            $table->index('issue_date');
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};

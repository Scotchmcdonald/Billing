<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('company_id');
            $table->decimal('amount', 15, 4);
            $table->enum('payment_method', ['stripe_card', 'stripe_ach', 'check', 'wire', 'cash', 'other']);
            $table->string('payment_reference')->nullable(); // check number, Stripe payment ID
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by'); // FK to users
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('company_id');
            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoice_line_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->index(); // Linking to an Invoice model (to be created or Stripe ID)
            $table->foreignId('product_id')->nullable()->constrained('products'); // Nullable for custom items
            $table->string('description');
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 4);
            $table->decimal('subtotal', 15, 4);
            $table->decimal('tax_amount', 15, 4)->default(0);
            $table->boolean('is_fee')->default(false); // Flag for CC convenience fees
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_line_items');
    }
};

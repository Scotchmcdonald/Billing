<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_tier_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id'); // FK to products, assuming products table exists or will exist
            $table->enum('tier', ['standard', 'non_profit', 'consumer']);
            $table->decimal('price', 15, 4);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index('product_id');
            $table->index('tier');
            // $table->unique(['product_id', 'tier', 'starts_at']); // Unique constraint as requested, but starts_at can be null. 
            // If starts_at is null, it means "active immediately" or "default". 
            // Unique index with NULLs behaves differently in different DBs. 
            // For MariaDB/MySQL, multiple NULLs are allowed in unique index.
            // Let's stick to the request but be aware of NULL behavior.
            $table->unique(['product_id', 'tier', 'starts_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_tier_prices');
    }
};

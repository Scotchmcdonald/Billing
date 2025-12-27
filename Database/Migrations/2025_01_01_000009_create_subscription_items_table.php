<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('billing_subscription_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->string('stripe_id')->nullable()->unique();
            $table->string('stripe_product')->nullable();
            $table->string('stripe_price')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamps();

            $table->foreign('subscription_id')->references('id')->on('billing_subscriptions')->cascadeOnDelete();
            $table->index(['subscription_id', 'stripe_price']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('billing_subscription_items');
    }
};

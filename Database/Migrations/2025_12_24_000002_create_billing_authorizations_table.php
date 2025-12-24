<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('billing_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('role')->default('billing.payer'); // billing.admin, billing.payer
            $table->timestamps();

            $table->unique(['user_id', 'company_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('billing_authorizations');
    }
};

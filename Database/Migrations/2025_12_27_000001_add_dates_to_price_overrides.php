<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('price_overrides', function (Blueprint $table) {
            if (!Schema::hasColumn('price_overrides', 'starts_at')) {
                $table->date('starts_at')->nullable();
            }
            if (!Schema::hasColumn('price_overrides', 'ends_at')) {
                $table->date('ends_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('price_overrides', function (Blueprint $table) {
            $table->dropColumn(['starts_at', 'ends_at']);
        });
    }
};

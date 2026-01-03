<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'address')) {
                $table->string('address')->nullable();
            }
            if (!Schema::hasColumn('companies', 'city')) {
                $table->string('city')->nullable();
            }
            if (!Schema::hasColumn('companies', 'state')) {
                $table->string('state')->nullable();
            }
            if (!Schema::hasColumn('companies', 'postal_code')) {
                $table->string('postal_code')->nullable();
            }
            if (!Schema::hasColumn('companies', 'country')) {
                $table->string('country')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['address', 'city', 'state', 'postal_code', 'country']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('companies') && Schema::hasTable('clients')) {
            Schema::table('companies', function (Blueprint $table) {
                if (!Schema::hasColumn('companies', 'client_id')) {
                    $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('companies') && Schema::hasColumn('companies', 'client_id')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropForeign(['client_id']);
                $table->dropColumn('client_id');
            });
        }
    }
};

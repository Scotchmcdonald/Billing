<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('software_products', function (Blueprint $table) {
            if (!Schema::hasColumn('software_products', 'vendor')) {
                $table->string('vendor')->nullable()->after('name');
            }
            if (!Schema::hasColumn('software_products', 'description')) {
                $table->text('description')->nullable()->after('monthly_cost');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'anomaly_score')) {
                $table->integer('anomaly_score')->default(0)->after('status');
            }
            if (!Schema::hasColumn('invoices', 'client_id')) {
                $table->foreignId('client_id')->nullable()->after('company_id')->constrained('clients')->nullOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('software_products', function (Blueprint $table) {
            $table->dropColumn(['vendor', 'description']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['anomaly_score']);
            if (Schema::hasColumn('invoices', 'client_id')) {
                $table->dropForeign(['client_id']);
                $table->dropColumn('client_id');
            }
        });
    }
};

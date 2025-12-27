<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('revenue_recognition_method')->default('cash')->after('status'); // cash, accrual
        });

        Schema::table('invoice_line_items', function (Blueprint $table) {
            $table->date('service_period_start')->nullable()->after('subtotal');
            $table->date('service_period_end')->nullable()->after('service_period_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('revenue_recognition_method');
        });

        Schema::table('invoice_line_items', function (Blueprint $table) {
            $table->dropColumn(['service_period_start', 'service_period_end']);
        });
    }
};

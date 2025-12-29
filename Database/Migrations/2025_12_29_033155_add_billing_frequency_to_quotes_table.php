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
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('billing_frequency')->default('monthly')->after('status'); // monthly, annually
        });

        Schema::table('quote_line_items', function (Blueprint $table) {
            $table->decimal('unit_price_monthly', 10, 2)->nullable()->after('unit_price');
            $table->decimal('unit_price_annually', 10, 2)->nullable()->after('unit_price_monthly');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_line_items', function (Blueprint $table) {
            $table->dropColumn(['unit_price_monthly', 'unit_price_annually']);
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('billing_frequency');
        });
    }
};

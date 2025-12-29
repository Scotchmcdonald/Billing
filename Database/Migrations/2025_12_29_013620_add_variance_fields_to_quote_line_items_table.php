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
        Schema::table('quote_line_items', function (Blueprint $table) {
            $table->decimal('standard_price', 10, 2)->nullable()->after('unit_price');
            $table->decimal('variance_amount', 10, 2)->default(0)->after('standard_price');
            $table->decimal('variance_percent', 5, 2)->default(0)->after('variance_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_line_items', function (Blueprint $table) {
            $table->dropColumn(['standard_price', 'variance_amount', 'variance_percent']);
        });
    }
};

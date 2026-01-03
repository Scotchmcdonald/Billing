<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('invoice_line_items', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_line_items', 'standard_unit_price')) {
                $table->decimal('standard_unit_price', 10, 4)->nullable()->after('unit_price')->comment('Fair Market Value at time of invoice generation');
            }
        });
    }

    public function down()
    {
        Schema::table('invoice_line_items', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_line_items', 'standard_unit_price')) {
                $table->dropColumn('standard_unit_price');
            }
        });
    }
};

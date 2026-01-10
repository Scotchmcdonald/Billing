<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('invoice_line_items', function (Blueprint $table) {
            // Add unit_price and quantity if they don't exist
            if (!Schema::hasColumn('invoice_line_items', 'unit_price')) {
                $table->decimal('unit_price', 10, 4)->default(0)->after('description');
            }
            if (!Schema::hasColumn('invoice_line_items', 'quantity')) {
                $table->integer('quantity')->default(1)->after('unit_price');
            }
            // Add standard_unit_price for tax credit calculations
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
            if (Schema::hasColumn('invoice_line_items', 'quantity')) {
                $table->dropColumn('quantity');
            }
            if (Schema::hasColumn('invoice_line_items', 'unit_price')) {
                $table->dropColumn('unit_price');
            }
        });
    }
};

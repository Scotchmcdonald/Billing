<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_line_items', function (Blueprint $table) {
            $table->decimal('tax_credit_amount', 15, 4)->default(0)->after('subtotal');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('is_non_profit')->default(false)->after('company_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_line_items', function (Blueprint $table) {
            $table->dropColumn('tax_credit_amount');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('is_non_profit');
        });
    }
};

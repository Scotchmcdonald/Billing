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
        Schema::table('billable_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('billable_entries', 'invoice_line_item_id')) {
                $table->foreignId('invoice_line_item_id')->nullable()->after('ticket_id')->constrained('invoice_line_items');
            }
            if (!Schema::hasColumn('billable_entries', 'receipt_path')) {
                $table->string('receipt_path')->nullable()->after('invoice_line_item_id'); // For expense receipts
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billable_entries', function (Blueprint $table) {
            if (Schema::hasColumn('billable_entries', 'receipt_path')) {
                $table->dropColumn('receipt_path');
            }
            // Note: Not dropping invoice_line_item_id as it may have been added by another migration
        });
    }
};

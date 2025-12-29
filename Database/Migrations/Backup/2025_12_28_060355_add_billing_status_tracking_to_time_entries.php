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
            if (!Schema::hasColumn('billable_entries', 'billing_status')) {
                $table->string('billing_status')->default('pending')->after('subtotal');
            }
            if (!Schema::hasColumn('billable_entries', 'status_changed_at')) {
                $table->timestamp('status_changed_at')->nullable()->after('billing_status');
            }
            if (!Schema::hasColumn('billable_entries', 'invoice_id')) {
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->after('status_changed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billable_entries', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn(['billing_status', 'status_changed_at', 'invoice_id']);
        });
    }
};

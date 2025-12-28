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
        // Compound indexes for dashboard queries
        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['company_id', 'status'], 'invoices_company_status_idx');
            $table->index(['status', 'due_date'], 'invoices_status_due_date_idx');
            $table->index('is_disputed', 'invoices_is_disputed_idx');
        });
        
        Schema::table('billing_subscriptions', function (Blueprint $table) {
            $table->index('contract_end_date', 'subscriptions_contract_end_date_idx');
        });
        
        Schema::table('billable_entries', function (Blueprint $table) {
            $table->index('ticket_id', 'billable_entries_ticket_id_idx');
            $table->index(['user_id', 'created_at'], 'billable_entries_user_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_company_status_idx');
            $table->dropIndex('invoices_status_due_date_idx');
            $table->dropIndex('invoices_is_disputed_idx');
        });
        
        Schema::table('billing_subscriptions', function (Blueprint $table) {
            $table->dropIndex('subscriptions_contract_end_date_idx');
        });
        
        Schema::table('billable_entries', function (Blueprint $table) {
            $table->dropIndex('billable_entries_ticket_id_idx');
            $table->dropIndex('billable_entries_user_created_idx');
        });
    }
};

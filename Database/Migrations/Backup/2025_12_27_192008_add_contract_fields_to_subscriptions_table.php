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
        Schema::table('billing_subscriptions', function (Blueprint $table) {
            $table->date('contract_start_date')->nullable()->after('next_billing_date');
            $table->date('contract_end_date')->nullable()->after('contract_start_date');
            $table->string('contract_document_path')->nullable()->after('contract_end_date');
            $table->enum('renewal_status', ['active', 'pending_renewal', 'churned'])->default('active')->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['contract_start_date', 'contract_end_date', 'contract_document_path', 'renewal_status']);
        });
    }
};

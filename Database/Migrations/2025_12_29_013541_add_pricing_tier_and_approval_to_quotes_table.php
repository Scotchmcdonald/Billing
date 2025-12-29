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
            $table->string('pricing_tier')->default('standard')->after('company_id');
            $table->boolean('requires_approval')->default(false)->after('status');
            $table->decimal('approval_threshold_percent', 5, 2)->default(15.00)->after('requires_approval');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['pricing_tier', 'requires_approval', 'approval_threshold_percent']);
        });
    }
};

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
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('is_disputed')->default(false)->after('status');
            $table->boolean('dunning_paused')->default(false)->after('is_disputed');
            $table->text('internal_notes')->nullable()->after('notes');
            $table->timestamp('approved_at')->nullable()->after('internal_notes');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['is_disputed', 'dunning_paused', 'internal_notes', 'approved_at', 'approved_by']);
        });
    }
};

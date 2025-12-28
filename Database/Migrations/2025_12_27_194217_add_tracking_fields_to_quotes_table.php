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
            $table->timestamp('viewed_at')->nullable()->after('status');
            $table->string('viewed_ip')->nullable()->after('viewed_at');
            $table->timestamp('accepted_at')->nullable()->after('viewed_ip');
            $table->text('signature_data')->nullable()->after('accepted_at'); // Base64 signature image
            $table->string('signer_name')->nullable()->after('signature_data');
            $table->string('signer_email')->nullable()->after('signer_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['viewed_at', 'viewed_ip', 'accepted_at', 'signature_data', 'signer_name', 'signer_email']);
        });
    }
};

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
            $table->timestamp('disputed_at')->nullable()->after('paid_at');
            $table->boolean('dunning_paused')->default(false)->after('disputed_at');
            $table->timestamp('dunning_paused_at')->nullable()->after('dunning_paused');
            $table->string('dunning_pause_reason')->nullable()->after('dunning_paused_at');
        });

        // Create disputes table
        Schema::create('invoice_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies');
            $table->string('reason');
            $table->decimal('disputed_amount', 10, 2);
            $table->json('line_item_ids')->nullable();
            $table->text('explanation');
            $table->string('status')->default('open'); // open, investigating, resolved, rejected
            $table->text('resolution')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        // Create dispute attachments table
        Schema::create('dispute_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispute_id')->constrained('invoice_disputes')->cascadeOnDelete();
            $table->string('filename');
            $table->string('path');
            $table->string('mime_type');
            $table->integer('size');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispute_attachments');
        Schema::dropIfExists('invoice_disputes');
        
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['disputed_at', 'dunning_paused', 'dunning_paused_at', 'dunning_pause_reason']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('billable_entries')) {
            Schema::create('billable_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->unsignedBigInteger('user_id'); // Technician
                $table->unsignedBigInteger('ticket_id')->nullable(); // FK to tickets/threads
                $table->enum('type', ['time', 'expense', 'product'])->default('time');
                $table->text('description');
                $table->decimal('quantity', 10, 2); // hours or item count
                $table->decimal('rate', 15, 4); // hourly rate or unit price
                $table->decimal('subtotal', 15, 4);
                $table->boolean('is_billable')->default(true);
                $table->unsignedBigInteger('invoice_line_item_id')->nullable(); // FK - null if unbilled
                $table->date('date');
                $table->json('metadata')->nullable(); // mileage, receipt_url, etc.
                $table->timestamps();

                $table->index(['company_id', 'is_billable', 'invoice_line_item_id'], 'billable_entries_lookup_index');
                $table->index('date');
                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users');
            });
        } else {
            // Table exists, ensure columns exist (Basic assertion of correctness)
            Schema::table('billable_entries', function (Blueprint $table) {
                if (!Schema::hasColumn('billable_entries', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->after('id');
                    $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('billable_entries', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->after('company_id');
                    $table->foreign('user_id')->references('id')->on('users');
                }
                if (!Schema::hasColumn('billable_entries', 'ticket_id')) {
                    $table->unsignedBigInteger('ticket_id')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('billable_entries', 'type')) {
                    $table->enum('type', ['time', 'expense', 'product'])->default('time')->after('ticket_id');
                }
                if (!Schema::hasColumn('billable_entries', 'description')) {
                    $table->text('description')->after('type');
                }
                if (!Schema::hasColumn('billable_entries', 'quantity')) {
                    $table->decimal('quantity', 10, 2)->after('description');
                }
                if (!Schema::hasColumn('billable_entries', 'rate')) {
                    $table->decimal('rate', 15, 4)->after('quantity');
                }
                if (!Schema::hasColumn('billable_entries', 'subtotal')) {
                    $table->decimal('subtotal', 15, 4)->after('rate');
                }
                if (!Schema::hasColumn('billable_entries', 'is_billable')) {
                    $table->boolean('is_billable')->default(true)->after('subtotal');
                }
                if (!Schema::hasColumn('billable_entries', 'invoice_line_item_id')) {
                    $table->unsignedBigInteger('invoice_line_item_id')->nullable()->after('is_billable');
                }
                if (!Schema::hasColumn('billable_entries', 'date')) {
                    $table->date('date')->after('invoice_line_item_id');
                }
                if (!Schema::hasColumn('billable_entries', 'metadata')) {
                    $table->json('metadata')->nullable()->after('date');
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('billable_entries');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('billing_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->string('group')->index(); // 'stripe', 'fees', 'limits', 'notifications', 'tax'
            $table->string('type')->default('string'); // 'string', 'boolean', 'integer', 'float', 'json'
            $table->timestamps();
        });

        // Seed initial settings
        $settings = [
            // Stripe
            ['key' => 'stripe_key', 'value' => '', 'is_encrypted' => true, 'group' => 'stripe', 'type' => 'string'],
            ['key' => 'stripe_secret', 'value' => '', 'is_encrypted' => true, 'group' => 'stripe', 'type' => 'string'],
            ['key' => 'stripe_webhook_secret', 'value' => '', 'is_encrypted' => true, 'group' => 'stripe', 'type' => 'string'],
            
            // Fees
            ['key' => 'enable_offset_fee', 'value' => '0', 'is_encrypted' => false, 'group' => 'fees', 'type' => 'boolean'],
            ['key' => 'offset_fee_percentage', 'value' => '2.9', 'is_encrypted' => false, 'group' => 'fees', 'type' => 'float'],
            ['key' => 'offset_fee_flat', 'value' => '0.30', 'is_encrypted' => false, 'group' => 'fees', 'type' => 'float'],
            
            // Limits
            ['key' => 'max_transaction_limit', 'value' => '10000', 'is_encrypted' => false, 'group' => 'limits', 'type' => 'integer'],
            
            // Notifications
            ['key' => 'notification_emails', 'value' => '[]', 'is_encrypted' => false, 'group' => 'notifications', 'type' => 'json'],
            
            // Tax
            ['key' => 'tax_provider', 'value' => 'manual', 'is_encrypted' => false, 'group' => 'tax', 'type' => 'string'],
        ];

        DB::table('billing_settings')->insert($settings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_settings');
    }
};
